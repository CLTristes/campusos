<?php

declare(strict_types=1);

namespace CampusOs\Core\Contracts;

use CampusOs\Core\DTOs\ExtractedDocument;
use CampusOs\Core\Exceptions\ExtractorUnavailableException;

/**
 * Leitura de um documento acadêmico — histórico escolar, requerimento de
 * matrícula ou matriz curricular — para dado estruturado.
 *
 * O contrato vive no `core` e a implementação no `integrations`: o `journey`
 * não conhece provedor de IA nenhum. Trocar Gemini por Claude, por um OCR
 * local ou por um parser determinístico é trocar o bind no provider — nenhuma
 * linha de domínio muda. É o motivo de o contrato existir.
 *
 * A implementação NUNCA decide regra de negócio: ela extrai o que está escrito
 * e devolve. Quem interpreta é o `journey`, e só depois de o aluno confirmar
 * na tela de conferência.
 */
interface AcademicDocumentExtractor
{
    /**
     * @param  string  $absolutePath  arquivo local (PDF ou imagem)
     * @param  string  $mimeType  application/pdf, image/png, image/jpeg…
     *
     * @throws ExtractorUnavailableException erro de TRANSPORTE (timeout, 5xx,
     *                                       rate limit, credencial). O Job deixa subir e a fila reprocessa.
     *                                       Recusa de NEGÓCIO (documento ilegível, não é documento acadêmico)
     *                                       não lança: volta no próprio ExtractedDocument como falha.
     */
    public function extract(string $absolutePath, string $mimeType): ExtractedDocument;

    /** Identificador do provedor, gravado junto da extração para auditoria. */
    public function name(): string;
}
