<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Jobs\ParseAcademicDocumentJob;
use CampusOs\Journey\Models\EnrollmentRequest;
use Illuminate\Http\UploadedFile;

/**
 * Recebe o documento e devolve na hora — a leitura acontece na fila.
 *
 * Ler um histórico de 6 páginas leva alguns segundos; ninguém deve ficar
 * olhando para uma tela travada esperando (regra de ouro nº 7: 202 + Job).
 */
final class UploadAcademicDocumentAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'kind' => ['required', 'string', 'in:transcript,enrollment_request'],
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:20480'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'file.mimes' => 'Envie o documento em PDF ou como foto (PNG/JPG).',
            'file.max' => 'O arquivo passa de 20 MB. Reduza a qualidade da foto ou envie o PDF.',
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): EnrollmentRequest
    {
        /** @var UploadedFile $file */
        $file = $data['file'];

        $request = EnrollmentRequest::query()->create([
            'user_usr_id' => $data['user_id'],
            'erq_kind' => $data['kind'],
            // Disco local, não público: é histórico escolar de uma pessoa.
            'erq_file_path' => $file->store('academic-documents', 'local'),
            'erq_mime' => $file->getMimeType() ?? 'application/pdf',
            'erq_status' => DocumentRequestStatus::Uploaded,
        ]);

        ParseAcademicDocumentJob::dispatch($request->erq_id, TenantContext::id());

        return $request;
    }
}
