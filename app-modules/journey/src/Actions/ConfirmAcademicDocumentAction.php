<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Models\EnrollmentRequest;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Journey\Support\DocumentStatusTranslator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A confirmação do aluno — o ÚNICO ponto onde matrícula nasce de um documento.
 *
 * Recebe as linhas já revisadas na tela de conferência, não a extração crua:
 * se a IA leu errado, o aluno corrigiu antes de chegar aqui. É o que permite
 * confiar na leitura automática sem confiar cegamente.
 *
 * Linha cujo código não existe no catálogo vira PENDÊNCIA na resposta, nunca
 * erro fatal: histórico real tem disciplina extinta, e derrubar a importação
 * inteira por causa de uma linha de 2019 é o pior dos mundos.
 *
 * `registration_id` é OPCIONAL: um aluno sem vínculo nenhum não tinha como
 * confirmar nada (só nascia por seeder). Omitido, o vínculo nasce do próprio
 * `meta` que a IA leu do cabeçalho do documento oficial — curso, RA e o
 * semestre de ingresso — que é mais confiável do que pedir para o aluno
 * digitar de novo o que o documento já imprime.
 */
final class ConfirmAcademicDocumentAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'request_id' => ['required', 'string', 'uuid'],
            'registration_id' => ['nullable', 'string', 'uuid'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.code' => ['required', 'string', 'max:32'],
            'lines.*.year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'lines.*.period' => ['required', 'integer', 'in:1,2'],
            // 64 não bastava: o histórico real tem situação administrativa
            // ("Enade - Estudante Dispensado..." > 80 caracteres) que não é uma
            // situação acadêmica reconhecida — ela DEVE virar pendência, não
            // travar a confirmação inteira antes mesmo de chegar lá.
            //
            // `nullable`: o requerimento de matrícula não imprime situação
            // nenhuma (é o semestre CORRENTE, ninguém foi aprovado ainda) —
            // `handle()` resolve `null` como "Cursando" só para
            // `erq_kind === enrollment_request`; para `transcript`, `null`
            // vira pendência (situação ausente é sempre uma anomalia ali).
            'lines.*.status' => ['nullable', 'string', 'max:255'],
            'lines.*.grade' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'lines.*.attendance' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.class_code' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{request: EnrollmentRequest, imported: int, pending: list<array<string, mixed>>}
     */
    protected function handle(array $data): array
    {
        $request = EnrollmentRequest::query()->findOrFail($data['request_id']);

        if ($request->erq_status === DocumentRequestStatus::Confirmed) {
            throw ValidationException::withMessages([
                'request_id' => 'Este documento já foi confirmado.',
            ]);
        }

        $subjectModel = config('models.subject');
        $termModel = config('models.term');

        $registration = ($data['registration_id'] ?? null) !== null
            ? Registration::query()->with('course')->findOrFail($data['registration_id'])
            : $this->resolveRegistration($request)->loadMissing('course');
        $registrationId = $registration->reg_id;

        $imported = 0;
        $pending = [];

        DB::transaction(function () use ($data, $request, $subjectModel, $termModel, $registration, $registrationId, &$imported, &$pending): void {
            foreach ($data['lines'] as $line) {
                $subject = $subjectModel::query()->where('sbj_code', $line['code'])->first();

                if ($subject === null) {
                    // Disciplina extinta ou de outra instituição: registra e segue.
                    $pending[] = ['code' => $line['code'], 'reason' => 'Disciplina não está no catálogo do curso.'];

                    continue;
                }

                $term = $termModel::query()->firstOrCreate(
                    ['trm_year' => $line['year'], 'trm_period' => $line['period']],
                    ['trm_status' => 'closed'],
                );

                // Requerimento de matrícula não imprime situação — é o semestre
                // CORRENTE, ninguém foi aprovado ainda. Só ESTE kind ganha o
                // default; um `transcript` sem situação é uma anomalia real, e
                // vira pendência como sempre foi.
                $statusText = $line['status'] ?? ($request->erq_kind === 'enrollment_request' ? 'Cursando' : null);

                if ($statusText === null) {
                    $pending[] = ['code' => $line['code'], 'reason' => 'Situação não informada.'];

                    continue;
                }

                $status = DocumentStatusTranslator::translate($statusText);

                if ($status === null) {
                    $pending[] = ['code' => $line['code'], 'reason' => "Situação não reconhecida: \"{$statusText}\"."];

                    continue;
                }

                // Histórico real pode imprimir duas linhas pra mesma disciplina/
                // período — a matrícula regular E uma tentativa de exame de
                // suficiência à parte (aprovada ou não) — e as duas caem na MESMA
                // chave (registration, subject, term). Sem esta checagem, a
                // ordem em que o documento lista as linhas decide silenciosamente
                // qual fica valendo; aqui uma aprovação já registrada nunca é
                // apagada por uma linha que não conta como cumprida.
                $existing = SubjectEnrollment::query()
                    ->where('registration_reg_id', $registrationId)
                    ->where('subject_sbj_id', $subject->sbj_id)
                    ->where('term_trm_id', $term->trm_id)
                    ->first();

                if ($existing !== null && $existing->sen_status->countsAsCompleted() && ! $status->countsAsCompleted()) {
                    $pending[] = [
                        'code' => $line['code'],
                        'reason' => "Já existe aprovação registrada para esta disciplina neste período; a situação \"{$statusText}\" não foi aplicada — confira manualmente.",
                    ];

                    continue;
                }

                SubjectEnrollment::query()->updateOrCreate(
                    [
                        'registration_reg_id' => $registrationId,
                        'subject_sbj_id' => $subject->sbj_id,
                        'term_trm_id' => $term->trm_id,
                    ],
                    [
                        'sen_status' => $status,
                        'sen_grade' => $line['grade'] ?? null,
                        'sen_attendance' => $line['attendance'] ?? null,
                        'sen_class_code' => $line['class_code'] ?? null,
                        // Só o requerimento de matrícula tem TURMA sem nota — é
                        // dali que a oferta nasce (ou é reaproveitada, se outro
                        // aluno da mesma turma já confirmou primeiro).
                        'offering_ofr_id' => $this->resolveOffering($request, $registration, $subject, $term, $line['class_code'] ?? null),
                        // Congelada na aprovação: a disciplina pode mudar de
                        // carga depois, o que foi integralizado não muda junto.
                        'sen_hours_earned' => $status->countsAsCompleted() ? (int) $subject->sbj_hours : 0,
                        'sen_source' => $request->erq_kind === 'transcript'
                            ? EnrollmentSource::Transcript
                            : EnrollmentSource::EnrollmentRequest,
                    ],
                );

                $imported++;
            }

            $request->update([
                'erq_status' => DocumentRequestStatus::Confirmed,
                'registration_reg_id' => $registrationId,
                'erq_confirmed_at' => now(),
            ]);
        });

        return ['request' => $request->refresh(), 'imported' => $imported, 'pending' => $pending];
    }

    /**
     * A turma nasce (ou é reaproveitada) só do requerimento de matrícula —
     * é o único documento que imprime `class_code` sem já ter uma nota, e é
     * de onde `offerings` deveria sempre ter nascido (§5 de
     * `docs/dominio/DOCUMENTOS_ACADEMICOS.md`). Sem campus resolvido (curso
     * sem câmpus configurado) ou sem turma na linha, a matrícula ainda
     * grava — só fica sem `offering_ofr_id`, nunca bloqueia a confirmação.
     */
    private function resolveOffering(EnrollmentRequest $request, Registration $registration, mixed $subject, mixed $term, ?string $classCode): ?string
    {
        if ($request->erq_kind !== 'enrollment_request' || $classCode === null) {
            return null;
        }

        $campusId = $registration->course?->campus_cps_id;

        if ($campusId === null) {
            return null;
        }

        $offeringModel = config('models.offering');

        return $offeringModel::query()->firstOrCreate([
            'term_trm_id' => $term->trm_id,
            'subject_sbj_id' => $subject->sbj_id,
            'ofr_class_code' => $classCode,
            'campus_cps_id' => $campusId,
        ])->ofr_id;
    }

    /**
     * Sem `registration_id` explícito, o vínculo vem do que a própria IA leu.
     *
     * Um Student já existente reaproveita o vínculo da MESMA disciplina/curso
     * (o mesmo aluno pode ter dois vínculos — um por curso, nunca um só); sem
     * vínculo nenhum, nasce um novo a partir do cabeçalho do documento.
     */
    private function resolveRegistration(EnrollmentRequest $request): Registration
    {
        $meta = $request->erq_extraction['meta'] ?? [];
        $courseCode = $meta['course_code'] ?? null;

        $student = Student::query()->firstOrCreate(
            ['user_usr_id' => $request->user_usr_id],
            ['std_name' => $request->user?->usr_name ?? 'Aluno'],
        );

        $registrations = $student->registrations();

        if ($courseCode !== null) {
            $registrations->whereHas('course', fn ($q) => $q->where('crs_code', $courseCode));
        }

        $existing = $registrations->first();

        if ($existing !== null) {
            return $existing;
        }

        $entryTerm = $meta['entry_term'] ?? null;

        if ($courseCode === null || $entryTerm === null || ! preg_match('#^(\d)/(\d{4})$#', (string) $entryTerm, $m)) {
            throw ValidationException::withMessages([
                'registration_id' => 'Não foi possível identificar o vínculo automaticamente a partir do documento. Informe registration_id.',
            ]);
        }

        $courseModel = config('models.course');
        $course = $courseModel::query()->where('crs_code', $courseCode)->first();

        if ($course === null) {
            throw ValidationException::withMessages([
                'registration_id' => "O curso \"{$courseCode}\" do documento não está no catálogo desta instituição.",
            ]);
        }

        $termModel = config('models.term');
        $term = $termModel::query()->firstOrCreate(
            ['trm_year' => (int) $m[2], 'trm_period' => (int) $m[1]],
            ['trm_status' => 'closed'],
        );

        return (new CreateRegistrationAction)->execute([
            'student_id' => $student->std_id,
            'course_id' => $course->crs_id,
            'entry_term_id' => $term->trm_id,
            'number' => (string) ($meta['registration_number'] ?? $request->user?->usr_registration_number ?? $student->std_id),
        ]);
    }
}
