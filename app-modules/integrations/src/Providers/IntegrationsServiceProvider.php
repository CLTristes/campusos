<?php

declare(strict_types=1);

namespace CampusOs\Integrations\Providers;

use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Integrations\Extractors\GeminiDocumentExtractor;
use CampusOs\Integrations\Extractors\NullDocumentExtractor;
use Illuminate\Support\ServiceProvider;

/**
 * O ÚNICO lugar do sistema que sabe qual provedor de IA lê os documentos.
 *
 * É o que a arquitetura modular compra: o `journey` depende de
 * `AcademicDocumentExtractor`, e quem decide a implementação é este bind. Trocar
 * Gemini por Claude, por um OCR local ou por um parser determinístico é escrever
 * outra classe e mudar `DOCUMENT_EXTRACTOR` — zero mudança no domínio, zero
 * teste de domínio reescrito.
 */
class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/extractors.php', 'extractors');

        $this->app->bind(AcademicDocumentExtractor::class, function ($app): AcademicDocumentExtractor {
            $driver = (string) config('extractors.default');

            return match ($driver) {
                'gemini' => new GeminiDocumentExtractor(
                    apiKey: (string) config('extractors.gemini.api_key'),
                    model: (string) config('extractors.gemini.model'),
                    baseUrl: rtrim((string) config('extractors.gemini.base_url'), '/'),
                    timeout: (int) config('extractors.gemini.timeout'),
                ),
                'null' => new NullDocumentExtractor,
                default => throw new \InvalidArgumentException(
                    "Extrator de documentos desconhecido: [{$driver}]. Veja config/extractors.php."
                ),
            };
        });
    }

    public function boot(): void {}
}
