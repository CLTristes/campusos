<?php

declare(strict_types=1);

namespace CampusOs\Integrations\Extractors;

use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Core\DTOs\ExtractedDocument;

/**
 * Não chama provedor nenhum — devolve uma recusa de negócio explicando que a
 * leitura automática está desligada.
 *
 * Existe por dois motivos práticos: é o extrator dos testes (nenhum teste do
 * domínio deve depender de rede), e é o plano B da demonstração — com
 * DOCUMENT_EXTRACTOR=null o fluxo inteiro continua funcionando pela tela de
 * conferência, que é justamente o ponto de corte previsto no plano de ataque.
 */
final class NullDocumentExtractor implements AcademicDocumentExtractor
{
    public function name(): string
    {
        return 'null';
    }

    public function extract(string $absolutePath, string $mimeType): ExtractedDocument
    {
        return ExtractedDocument::failed(
            'Leitura automática desligada. Preencha as disciplinas na tela de conferência.',
            $this->name(),
        );
    }
}
