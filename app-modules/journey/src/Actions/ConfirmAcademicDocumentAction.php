<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Models\EnrollmentRequest;
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
 */
final class ConfirmAcademicDocumentAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'request_id' => ['required', 'string', 'uuid'],
            'registration_id' => ['required', 'string', 'uuid'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.code' => ['required', 'string', 'max:32'],
            'lines.*.year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'lines.*.period' => ['required', 'integer', 'in:1,2'],
            'lines.*.status' => ['required', 'string', 'max:64'],
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

        $imported = 0;
        $pending = [];

        DB::transaction(function () use ($data, $request, $subjectModel, $termModel, &$imported, &$pending): void {
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

                $status = DocumentStatusTranslator::translate((string) $line['status']);

                if ($status === null) {
                    $pending[] = ['code' => $line['code'], 'reason' => "Situação não reconhecida: \"{$line['status']}\"."];

                    continue;
                }

                SubjectEnrollment::query()->updateOrCreate(
                    [
                        'registration_reg_id' => $data['registration_id'],
                        'subject_sbj_id' => $subject->sbj_id,
                        'term_trm_id' => $term->trm_id,
                    ],
                    [
                        'sen_status' => $status,
                        'sen_grade' => $line['grade'] ?? null,
                        'sen_attendance' => $line['attendance'] ?? null,
                        'sen_class_code' => $line['class_code'] ?? null,
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
                'registration_reg_id' => $data['registration_id'],
                'erq_confirmed_at' => now(),
            ]);
        });

        return ['request' => $request->refresh(), 'imported' => $imported, 'pending' => $pending];
    }
}
