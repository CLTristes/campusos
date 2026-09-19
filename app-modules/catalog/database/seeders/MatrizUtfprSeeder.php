<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Seeders;

use CampusOs\Catalog\Enums\CourseDegree;
use CampusOs\Catalog\Enums\CourseShift;
use CampusOs\Catalog\Enums\CurriculumStatus;
use CampusOs\Catalog\Enums\PrerequisiteType;
use CampusOs\Catalog\Enums\SubjectModel;
use CampusOs\Catalog\Enums\SubjectNature;
use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\ElectiveGroup;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\SubjectEquivalence;
use CampusOs\Catalog\Models\Term;
use Illuminate\Database\Seeder;

/**
 * Semeia a matriz 45 (Sistemas de Informação 02) do Câmpus Francisco Beltrão.
 *
 * Lê de um CSV em vez de array literal em PHP por um motivo prático: quando a
 * coordenação mandar o HTML da página da matriz, regenerar o CSV é trivial e
 * este arquivo não muda. Transcrição é dado, não código.
 *
 * Os números de fechamento vêm do próprio documento
 * (docs/dominio/DOCUMENTOS_ACADEMICOS.md §4.3) e são conferidos pelo teste
 * CatalogoMatrizTest: a soma do CSV TEM de reproduzi-los.
 *
 * Precisa de TenantContext ativo — é chamado de dentro do runAs do DatabaseSeeder.
 */
final class MatrizUtfprSeeder extends Seeder
{
    private const CSV = __DIR__.'/../data/matriz-45-utfpr-fb.csv';

    private const CSV_EQUIVALENCIAS = __DIR__.'/../data/equivalencias-45-utfpr-fb.csv';

    public function run(): void
    {
        // Campus vive no tenancy: resolvido por config('models.campus') para não
        // importar a classe de outro módulo (regra de ouro nº 3 — o ArchTest cobre).
        $campusModel = config('models.campus');
        $campus = $campusModel::query()->where('cps_code', 'FB')->firstOrFail();

        $course = Course::query()->firstOrCreate(
            ['crs_code' => '25'],
            [
                'campus_cps_id' => $campus->cps_id,
                'crs_name' => 'Bacharelado em Sistemas de Informação',
                'crs_degree' => CourseDegree::Bachelor,
                'crs_shift' => CourseShift::Evening,
            ],
        );

        $curriculum = Curriculum::query()->firstOrCreate(
            ['course_crs_id' => $course->crs_id, 'cur_code' => '45'],
            [
                'cur_name' => 'Sistemas De Informação 02',
                'cur_version' => 2,
                'cur_status' => CurriculumStatus::Active,
                'cur_effective_from' => '2023-01-01',
                // Bloco de fechamento do documento da matriz:
                'cur_mandatory_hours' => 2730,            // CHTOBRIGATORIASMATRIZ
                'cur_elective_hours' => 210,              // CHTOPTATIVASMATRIZ
                'cur_extension_hours' => 300,             // CHEXTENSAO (ortogonal)
                'cur_standalone_extension_hours' => 60,   // TEMA_OBRIGARORIA (soma)
                'cur_expected_terms' => 8,
                'cur_max_term_hours' => 390,              // do requerimento
                'cur_max_weekly_deficit' => 16,           // regra do §3
            ],
        );

        $group = ElectiveGroup::query()->firstOrCreate(
            ['curriculum_cur_id' => $curriculum->cur_id, 'elg_code' => '941'],
            [
                'elg_name' => 'Optativas',
                'elg_required_hours' => 210,
                'elg_weekly_hours' => 14,
                'elg_first_term' => 5,
                'elg_last_term' => 8,
            ],
        );

        /** @var array<string, CurriculumSubject> $byCode */
        $byCode = [];
        $pending = [];

        foreach ($this->rows(self::CSV) as $row) {
            $subject = Subject::query()->firstOrCreate(
                ['sbj_code' => $row['codigo']],
                [
                    'sbj_name' => $row['nome'],
                    'sbj_model' => SubjectModel::fromDocument($row['modelo']),
                    'sbj_weekly_hours' => (int) $row['chs'],
                    'sbj_hours' => (int) $row['cht'],
                    'sbj_extension_hours' => (int) $row['chext'],
                ],
            );

            $isElective = $row['grupo'] !== '';

            $byCode[$row['codigo']] = CurriculumSubject::query()->firstOrCreate(
                ['curriculum_cur_id' => $curriculum->cur_id, 'subject_sbj_id' => $subject->sbj_id],
                [
                    'cbs_term' => (int) $row['periodo'],
                    'cbs_nature' => $isElective ? SubjectNature::Elective : SubjectNature::Mandatory,
                    'elective_group_elg_id' => $isElective ? $group->elg_id : null,
                ],
            );

            if ($row['prereq'] !== '') {
                $pending[$row['codigo']] = $row['prereq'];
            }
        }

        // Segunda passada: o grafo só pode ser montado depois que TODA disciplina
        // da matriz existe — uma aresta pode apontar para um período posterior.
        $edges = 0;
        foreach ($pending as $code => $spec) {
            foreach (explode('+', $spec) as $target) {
                $target = trim($target);

                if (str_starts_with($target, 'PERIODO:')) {
                    Prerequisite::query()->firstOrCreate([
                        'curriculum_subject_cbs_id' => $byCode[$code]->cbs_id,
                        'prq_type' => PrerequisiteType::MinimumTerm,
                        'prq_min_term' => (int) substr($target, 8),
                    ]);
                    $edges++;

                    continue;
                }

                // Aresta para disciplina fora da matriz é ignorada com aviso, não
                // derruba o seed: matriz real tem referência a disciplina extinta.
                if (! isset($byCode[$target])) {
                    $this->command?->warn("  pré-requisito ignorado: {$code} → {$target} (fora da matriz)");

                    continue;
                }

                Prerequisite::query()->firstOrCreate([
                    'curriculum_subject_cbs_id' => $byCode[$code]->cbs_id,
                    'required_cbs_id' => $byCode[$target]->cbs_id,
                    'prq_type' => PrerequisiteType::Subject,
                ]);
                $edges++;
            }
        }

        // Equivalências: "esta disciplina vale por aquela da matriz antiga".
        // É a origem do Crédito Consignado no histórico de quem mudou de matriz.
        $equivalences = 0;
        foreach ($this->rows(self::CSV_EQUIVALENCIAS) as $row) {
            if (! isset($byCode[$row['codigo']])) {
                continue;
            }

            SubjectEquivalence::query()->firstOrCreate(
                [
                    'curriculum_subject_cbs_id' => $byCode[$row['codigo']]->cbs_id,
                    'seq_code' => $row['equivalente'],
                ],
                [
                    'seq_hours' => (int) $row['cht'],
                    // vazio = satisfaz sozinha; N = só o grupo N inteiro satisfaz
                    'seq_group' => $row['grupo'] === '' ? null : (int) $row['grupo'],
                ],
            );
            $equivalences++;
        }

        // Semestres do ingresso da primeira turma até 5 anos à frente.
        foreach (range(2023, 2030) as $year) {
            foreach ([1, 2] as $period) {
                Term::query()->firstOrCreate(
                    ['trm_year' => $year, 'trm_period' => $period],
                    ['trm_status' => $this->statusFor($year, $period)],
                );
            }
        }

        $this->command?->info(sprintf(
            '  Matriz %s · %d disciplinas · %d pré-requisitos · %d equivalências · total a integralizar: %d h',
            $curriculum->cur_code,
            count($byCode),
            $edges,
            $equivalences,
            $curriculum->totalRequiredHours(),
        ));
    }

    /** @return list<array<string, string>> */
    private function rows(string $csv): array
    {
        $lines = array_filter(
            file($csv, FILE_IGNORE_NEW_LINES),
            fn (string $l): bool => $l !== '' && ! str_starts_with($l, '#'),
        );

        $header = str_getcsv((string) array_shift($lines), escape: '\\');
        $rows = [];

        foreach ($lines as $line) {
            /** @var array<int, ?string> $values */
            $values = str_getcsv($line, escape: '\\');
            $rows[] = array_map(fn (?string $v): string => trim((string) $v), array_combine($header, $values));
        }

        return $rows;
    }

    private function statusFor(int $year, int $period): TermStatus
    {
        $key = ($year * 10) + $period;

        return match (true) {
            $key < 20262 => TermStatus::Closed,
            $key === 20262 => TermStatus::Current,
            default => TermStatus::Planned,
        };
    }
}
