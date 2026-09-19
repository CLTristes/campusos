<?php

declare(strict_types=1);

namespace Database\Seeders;

use CampusOs\Catalog\Database\Seeders\ComplementaryCategorySeeder;
use CampusOs\Catalog\Models\ComplementaryCategory;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\ActivityStatus;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Models\ComplementaryActivity;
use CampusOs\Journey\Models\EnrollmentRequest;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Lifeos\Enums\NoteKind;
use CampusOs\Lifeos\Enums\TaskKind;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Models\Task;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Database\Seeder;

/**
 * Massa de dados de DEMONSTRAÇÃO — sete alunos fictícios além do Felipe real
 * do `DatabaseSeeder`, pra todo endpoint (REST, MCP, `/staff/*`) ter o que
 * mostrar em vez de responder vazio.
 *
 * Duas razões concretas, não só "mais dado é melhor":
 *
 * 1. `AcademicStatsProvider` (painel da coordenação) tem `MIN_COHORT=5` —
 *    com um aluno só, os quatro endpoints de `insights` respondem vazio por
 *    desenho (piso de anonimato), não por bug. Cinco alunos aqui entram no
 *    MESMO termo de ingresso do Felipe (1/2023) só pra isso parar de
 *    acontecer.
 * 2. `AdminDashboardReadModel`/`StaffStudentController` (o painel NOMEADO)
 *    e o voto em nota (`ToggleNoteVoteAction`) não têm nada pra mostrar
 *    progresso variado, risco de atraso ou ordenação por score com um
 *    aluno só.
 *
 * Idempotente pela contagem de `Student` (rodar duas vezes não duplica) —
 * mais simples que `firstOrCreate` por nome fictício, que não tem chave
 * natural nenhuma.
 */
class DemoDataSeeder extends Seeder
{
    private const NOMES = [
        'Ana Beatriz Souza', 'Bruno Costa Lima', 'Carla Mendes Ferreira',
        'Diego Almeida Rocha', 'Eduarda Santos Ribeiro', 'Fábio Nogueira Dias',
        'Gabriela Martins Cardoso',
    ];

    public function run(): void
    {
        $utfpr = Entity::query()->where('ent_email_domain', 'alunos.utfpr.edu.br')->first();

        if ($utfpr === null) {
            $this->command?->warn('  DemoDataSeeder: rode o DatabaseSeeder primeiro (Entity da UTFPR não existe).');

            return;
        }

        TenantContext::runAs($utfpr->ent_id, function (): void {
            if (Student::query()->count() > 1) {
                $this->command?->info('  DemoDataSeeder: já rodou (mais de um aluno existe) — pulando.');

                return;
            }

            $this->call(ComplementaryCategorySeeder::class);

            $course = Course::query()->where('crs_code', '25')->firstOrFail();
            $curriculum = Curriculum::query()->where('cur_code', '45')->firstOrFail();
            $campus = Campus::query()->where('cps_code', 'FB')->firstOrFail();

            $entryTerm2023 = Term::query()->firstOrCreate(['trm_year' => 2023, 'trm_period' => 1]);
            $entryTerm2024 = Term::query()->firstOrCreate(['trm_year' => 2024, 'trm_period' => 1]);

            // `bottlenecks` (insights) só olha os `last_terms` mais RECENTES
            // com matrícula, globais na instituição — não o termo de ingresso
            // do próprio aluno. Sem isto, os 8 alunos fictícios ficariam de
            // fora da janela padrão (a última atividade real é o histórico
            // do Felipe, anos à frente de 2023/1).
            $termoRecente = SubjectEnrollment::query()
                ->join('terms', 'terms.trm_id', '=', 'subject_enrollments.term_trm_id')
                ->orderByDesc('terms.trm_year')->orderByDesc('terms.trm_period')
                ->value('terms.trm_id');
            $termoAtividade = $termoRecente !== null ? Term::query()->find($termoRecente) : $entryTerm2023;

            $students = $this->createStudents($course, $curriculum, $entryTerm2023, $entryTerm2024, $campus);
            $offering = $this->enrollments($students, $curriculum, $termoAtividade, $campus);
            $this->notes($students);
            $this->tasks($students, $offering);
            $this->complementaryActivities($students);
            $this->enrollmentRequests($students);

            $this->command?->info('  DemoDataSeeder: '.count($students).' alunos fictícios criados (senha: campusos).');
        });
    }

    /** @return list<array{user: User, student: Student, registration: Registration}> */
    private function createStudents(Course $course, Curriculum $curriculum, Term $entryTerm2023, Term $entryTerm2024, Campus $campus): array
    {
        $students = [];

        foreach (self::NOMES as $i => $nome) {
            // Cinco no MESMO termo do Felipe real (1/2023) — é o que faz a
            // coorte do painel da coordenação bater o piso de 5.
            $entryTerm = $i < 5 ? $entryTerm2023 : $entryTerm2024;
            $slug = str($nome)->slug('.')->lower();

            $user = User::query()->firstOrCreate(
                ['usr_email' => "{$slug}@alunos.utfpr.edu.br"],
                [
                    'usr_name' => $nome,
                    'usr_password' => 'campusos',
                    'usr_role' => UserRole::Student,
                    'usr_registration_number' => (string) (2500000 + $i),
                    'campus_cps_id' => $campus->cps_id,
                ],
            );

            $student = Student::query()->firstOrCreate(
                ['user_usr_id' => $user->usr_id],
                ['std_name' => $nome],
            );

            $registration = Registration::query()->firstOrCreate(
                ['student_std_id' => $student->std_id, 'course_crs_id' => $course->crs_id],
                [
                    'curriculum_cur_id' => $curriculum->cur_id,
                    'entry_term_trm_id' => $entryTerm->trm_id,
                    'reg_number' => (string) (2500000 + $i),
                ],
            );

            $students[] = ['user' => $user, 'student' => $student, 'registration' => $registration];
        }

        return $students;
    }

    /**
     * Uma turma real (ofertada) pras 5 primeiras disciplinas obrigatórias, e
     * uma mistura de aprovado/reprovado — é o que dá a `bottlenecks` uma
     * taxa de reprovação de verdade pra mostrar, e não zero por falta de
     * amostra.
     *
     * @param  list<array{user: User, student: Student, registration: Registration}>  $students
     */
    private function enrollments(array $students, Curriculum $curriculum, Term $term, Campus $campus): Offering
    {
        $periodo1 = CurriculumSubject::query()
            ->where('curriculum_cur_id', $curriculum->cur_id)
            ->where('cbs_term', 1)
            ->with('subject')
            ->get();

        $offering = Offering::factory()->create([
            'subject_sbj_id' => $periodo1->first()->subject_sbj_id,
            'term_trm_id' => $term->trm_id,
            'campus_cps_id' => $campus->cps_id,
            'ofr_class_code' => '1SI',
        ]);

        // Índice do aluno % 5: 0-1 aprovados, 2 reprovado por nota, 3
        // reprovado por frequência, 4 ainda cursando — a mesma disciplina
        // com resultados diferentes é o que faz `bottlenecks` ter conteúdo.
        $resultados = [
            EnrollmentStatus::Approved, EnrollmentStatus::Approved,
            EnrollmentStatus::FailedGrade, EnrollmentStatus::FailedAbsence,
            EnrollmentStatus::Enrolled,
        ];

        foreach ($students as $i => $s) {
            foreach ($periodo1 as $cs) {
                $status = $resultados[$i % count($resultados)];

                SubjectEnrollment::query()->create([
                    'registration_reg_id' => $s['registration']->reg_id,
                    'subject_sbj_id' => $cs->subject_sbj_id,
                    'term_trm_id' => $term->trm_id,
                    'offering_ofr_id' => $cs->subject_sbj_id === $offering->subject_sbj_id ? $offering->ofr_id : null,
                    'sen_status' => $status,
                    'sen_grade' => $status->countsAsCompleted() ? fake()->randomFloat(1, 6, 10) : ($status->isFailure() ? fake()->randomFloat(1, 1, 5.9) : null),
                    'sen_attendance' => $status->isInProgress() ? null : fake()->randomFloat(1, 50, 100),
                    'sen_hours_earned' => $status->countsAsCompleted() ? (int) $cs->subject->sbj_hours : 0,
                    'sen_source' => 'transcript',
                ]);
            }
        }

        return $offering;
    }

    /**
     * Notas variadas, com voto o suficiente pra `acervo_da_disciplina`
     * mostrar ordenação de verdade — não só uma lista na ordem de criação.
     *
     * @param  list<array{user: User, student: Student, registration: Registration}>  $students
     */
    private function notes(array $students): void
    {
        $subject = Subject::query()->where('sbj_code', 'ARC102')->firstOrFail();

        $notas = [
            ['title' => 'Resumo da P1 — Arquitetura de Computadores', 'kind' => NoteKind::Summary, 'votantes' => 4],
            ['title' => 'Lista resolvida — exercícios de portas lógicas', 'kind' => NoteKind::SolvedExerciseList, 'votantes' => 2],
            ['title' => 'Prova antiga 2022/1 com gabarito', 'kind' => NoteKind::PastExam, 'votantes' => 0],
            ['title' => 'Dica: como o professor cobra na prova prática', 'kind' => NoteKind::Tip, 'votantes' => 1],
        ];

        foreach ($notas as $i => $n) {
            $autor = $students[$i % count($students)];

            $nota = Note::factory()->create([
                'author_usr_id' => $autor['user']->usr_id,
                'subject_sbj_id' => $subject->sbj_id,
                'nte_title' => $n['title'],
                'nte_kind' => $n['kind']->value,
                'nte_visibility' => Visibility::Subject->value,
                'nte_upvotes_count' => $n['votantes'],
                'nte_published_at' => now()->subDays(fake()->numberBetween(1, 120)),
            ]);

            // As linhas de voto de verdade (não só o contador) — pra
            // `voted_by_me` funcionar quando um dos votantes consultar.
            $votantes = collect($students)->reject(fn ($s) => $s['user']->usr_id === $autor['user']->usr_id)->take($n['votantes']);
            foreach ($votantes as $votante) {
                $nota->votes()->create(['user_usr_id' => $votante['user']->usr_id, 'entity_ent_id' => $nota->entity_ent_id]);
            }
        }

        // Uma nota pessoal, nunca publicada — pra minhas_anotacoes ter algo
        // que NÃO aparece em acervo_da_disciplina.
        Note::factory()->create([
            'author_usr_id' => $students[0]['user']->usr_id,
            'subject_sbj_id' => $subject->sbj_id,
            'nte_title' => 'Rascunho — organizar antes de publicar',
            'nte_visibility' => Visibility::Private->value,
        ]);
    }

    /**
     * @param  list<array{user: User, student: Student, registration: Registration}>  $students
     */
    private function tasks(array $students, Offering $offering): void
    {
        $compartilhada = Task::factory()->create([
            'owner_usr_id' => $students[0]['user']->usr_id,
            'offering_ofr_id' => $offering->ofr_id,
            'subject_sbj_id' => $offering->subject_sbj_id,
            'tsk_title' => 'Entregar lista 2 de Arquitetura',
            'tsk_kind' => TaskKind::Assignment->value,
            'tsk_visibility' => Visibility::Offering->value,
            'tsk_due_at' => now()->addDays(10),
        ]);

        // Um colega adota — mostra a cópia em minha_agenda, distinta da origem.
        Task::factory()->create([
            'owner_usr_id' => $students[1]['user']->usr_id,
            'origin_tsk_id' => $compartilhada->tsk_id,
            'offering_ofr_id' => $offering->ofr_id,
            'subject_sbj_id' => $offering->subject_sbj_id,
            'tsk_title' => $compartilhada->tsk_title,
            'tsk_kind' => TaskKind::Assignment->value,
            'tsk_visibility' => Visibility::Private->value,
            'tsk_due_at' => $compartilhada->tsk_due_at,
        ]);

        Task::factory()->create([
            'owner_usr_id' => $students[2]['user']->usr_id,
            'tsk_title' => 'Revisar anotações antes da prova',
            'tsk_kind' => TaskKind::Personal->value,
            'tsk_visibility' => Visibility::Private->value,
        ]);
    }

    /**
     * @param  list<array{user: User, student: Student, registration: Registration}>  $students
     */
    private function complementaryActivities(array $students): void
    {
        $monitoria = ComplementaryCategory::query()->where('ccg_name', 'Monitoria')->first();
        $eventos = ComplementaryCategory::query()->where('ccg_name', 'Participação em eventos')->first();

        if ($monitoria === null || $eventos === null) {
            return;
        }

        ComplementaryActivity::factory()->create([
            'registration_reg_id' => $students[0]['registration']->reg_id,
            'complementary_category_ccg_id' => $monitoria->ccg_id,
            'cac_title' => 'Monitoria de Cálculo 1 — 2024/1',
            'cac_hours_claimed' => 40,
            'cac_status' => ActivityStatus::Submitted,
        ]);

        // Estoura o teto de "Participação em eventos" (40h) de propósito —
        // é o cenário que demonstra o corte de `ComplementaryHoursReadModel`.
        ComplementaryActivity::factory()->create([
            'registration_reg_id' => $students[0]['registration']->reg_id,
            'complementary_category_ccg_id' => $eventos->ccg_id,
            'cac_title' => 'Semana Acadêmica de Sistemas de Informação',
            'cac_hours_claimed' => 20,
            'cac_status' => ActivityStatus::Approved,
        ]);
        ComplementaryActivity::factory()->create([
            'registration_reg_id' => $students[0]['registration']->reg_id,
            'complementary_category_ccg_id' => $eventos->ccg_id,
            'cac_title' => 'Encontro Regional de Computação',
            'cac_hours_claimed' => 30,
            'cac_status' => ActivityStatus::Approved,
        ]);

        ComplementaryActivity::factory()->create([
            'registration_reg_id' => $students[1]['registration']->reg_id,
            'complementary_category_ccg_id' => $eventos->ccg_id,
            'cac_title' => 'Palestra de carreira em TI',
            'cac_hours_claimed' => 8,
            'cac_status' => ActivityStatus::Draft,
        ]);
    }

    /**
     * `parsed` (aguardando o aluno conferir) e `failed` — o que faz
     * `AdminDashboardReadModel::documentRequestsByStatus` ter mais de uma
     * linha. Nenhum arquivo real: `erq_file_path` aponta pra um caminho
     * fictício, coerente com o que o Job normalmente grava.
     *
     * @param  list<array{user: User, student: Student, registration: Registration}>  $students
     */
    private function enrollmentRequests(array $students): void
    {
        EnrollmentRequest::factory()->create([
            'user_usr_id' => $students[3]['user']->usr_id,
            'registration_reg_id' => $students[3]['registration']->reg_id,
            'erq_kind' => 'transcript',
            'erq_status' => DocumentRequestStatus::Parsed,
            'erq_extraction' => ['kind' => 'transcript', 'lines' => [], 'meta' => []],
            'erq_confidence' => 0.94,
            'erq_provider' => 'gemini:gemini-3.6-flash',
            'erq_parsed_at' => now()->subHours(3),
        ]);

        EnrollmentRequest::factory()->create([
            'user_usr_id' => $students[4]['user']->usr_id,
            'registration_reg_id' => $students[4]['registration']->reg_id,
            'erq_kind' => 'transcript',
            'erq_status' => DocumentRequestStatus::Failed,
            'erq_failure_reason' => 'A imagem está muito escura para leitura.',
            'erq_provider' => 'gemini:gemini-3.6-flash',
            'erq_parsed_at' => now()->subDays(1),
        ]);
    }
}
