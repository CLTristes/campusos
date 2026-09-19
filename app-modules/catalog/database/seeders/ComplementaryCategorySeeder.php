<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Seeders;

use CampusOs\Catalog\Models\Curriculum;
use Illuminate\Database\Seeder;

/**
 * Semeia os tetos por categoria de atividade complementar (B7) para a
 * matriz 45.
 *
 * FICTÍCIO — pendência do dono do produto (ver
 * docs/dominio/HORAS_COMPLEMENTARES.md): os tetos reais dependem da
 * resolução de atividades complementares do curso, que ainda não chegou.
 * Os números abaixo replicam o exemplo do desenho
 * (docs-site/features/horas-e-certificados.html) só para a demo não rodar
 * vazia — não são a resolução real da UTFPR.
 */
final class ComplementaryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $curriculum = Curriculum::query()->where('cur_code', '45')->first();

        if ($curriculum === null) {
            return;
        }

        $categorias = [
            ['ccg_name' => 'Participação em eventos', 'ccg_max_hours' => 40, 'ccg_conversion_note' => 'até 10h por evento; exige certificado'],
            ['ccg_name' => 'Monitoria', 'ccg_max_hours' => 80, 'ccg_conversion_note' => null],
            ['ccg_name' => 'Projeto de extensão', 'ccg_max_hours' => 80, 'ccg_conversion_note' => null],
            ['ccg_name' => 'Iniciação científica', 'ccg_max_hours' => 100, 'ccg_conversion_note' => null],
            ['ccg_name' => 'Estágio não obrigatório', 'ccg_max_hours' => 60, 'ccg_conversion_note' => null],
            ['ccg_name' => 'Cursos e certificações', 'ccg_max_hours' => 60, 'ccg_conversion_note' => null],
        ];

        foreach ($categorias as $categoria) {
            $curriculum->complementaryCategories()->firstOrCreate(
                ['ccg_name' => $categoria['ccg_name']],
                ['ccg_max_hours' => $categoria['ccg_max_hours'], 'ccg_conversion_note' => $categoria['ccg_conversion_note']],
            );
        }
    }
}
