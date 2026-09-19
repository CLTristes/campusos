<?php

declare(strict_types=1);

namespace CampusOs\Journey\Jobs;

use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Core\Exceptions\ExtractorUnavailableException;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Models\EnrollmentRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Lê o documento do aluno em segundo plano (regra de ouro nº 7).
 *
 * As três disciplinas que este Job respeita:
 *
 * 1. `TenantContext::runAs()` envolve TUDO — worker não tem sessão HTTP, e sem
 *    isso o EntityScope não filtra nada e o Entityable não preenche nada.
 * 2. Erro de TRANSPORTE sobe e a fila reprocessa; recusa de NEGÓCIO vira
 *    `failed` com motivo, estado final. Capturar transporte transformaria uma
 *    indisponibilidade momentânea num documento ilegível para sempre.
 * 3. Guard de idempotência no topo: fila reentrega, e reprocessar um documento
 *    já confirmado sobrescreveria a conferência que o aluno fez à mão.
 */
final class ParseAcademicDocumentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function __construct(
        private readonly string $requestId,
        private readonly string $entityId,
    ) {
        $this->onQueue('imports');
    }

    public function handle(AcademicDocumentExtractor $extractor): void
    {
        TenantContext::runAs($this->entityId, function () use ($extractor): void {
            $request = EnrollmentRequest::query()->find($this->requestId);

            if ($request === null || $request->erq_status->isSettled()) {
                return; // já processado — a fila reentregou
            }

            $request->update(['erq_status' => DocumentRequestStatus::Processing]);

            try {
                $document = $extractor->extract(
                    Storage::disk('local')->path($request->erq_file_path),
                    $request->erq_mime,
                );
            } catch (ExtractorUnavailableException $e) {
                // Transporte: deixa subir. A fila tenta de novo, e só depois do
                // último retry o failed() abaixo marca o documento.
                $request->update(['erq_status' => DocumentRequestStatus::Uploaded]);

                throw $e;
            }

            $request->update($document->succeeded() ? [
                'erq_status' => DocumentRequestStatus::Parsed,
                'erq_extraction' => $document->toArray(),
                'erq_confidence' => $document->confidence,
                'erq_provider' => $document->provider,
                'erq_parsed_at' => now(),
            ] : [
                'erq_status' => DocumentRequestStatus::Failed,
                'erq_failure_reason' => $document->failureReason,
                'erq_provider' => $document->provider,
                'erq_parsed_at' => now(),
            ]);
        });
    }

    /** Esgotados os retries, o documento não fica preso em "lendo" para sempre. */
    public function failed(\Throwable $e): void
    {
        TenantContext::runAs($this->entityId, function () use ($e): void {
            EnrollmentRequest::query()->whereKey($this->requestId)->update([
                'erq_status' => DocumentRequestStatus::Failed,
                'erq_failure_reason' => 'A leitura automática falhou: '.$e->getMessage(),
                'erq_parsed_at' => now(),
            ]);
        });
    }
}
