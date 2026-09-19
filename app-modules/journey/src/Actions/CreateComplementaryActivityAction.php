<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Journey\Enums\ActivityStatus;
use CampusOs\Journey\Models\ComplementaryActivity;
use CampusOs\Journey\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * O aluno declara — sem fila de homologação nesta entrega (Fase 3 do
 * desenho é ◇ planejado): a atividade nasce `submitted` e o contador anda
 * na hora, com a ressalva de que é declaratório.
 *
 * A categoria precisa pertencer à MESMA matriz do vínculo do aluno — senão
 * um aluno poderia declarar hora contra o teto de um curso alheio.
 */
final class CreateComplementaryActivityAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'registration_id' => ['required', 'string', 'uuid'],
            'category_id' => ['required', 'string', 'uuid', 'exists:complementary_categories,ccg_id'],
            'title' => ['required', 'string', 'max:255'],
            'hours_claimed' => ['required', 'integer', 'min:1'],
            'issued_at' => ['nullable', 'date'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'certificate.mimes' => 'Envie o certificado em PDF ou como foto (PNG/JPG).',
            'certificate.max' => 'O arquivo passa de 10 MB. Reduza a qualidade da foto ou envie o PDF.',
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): ComplementaryActivity
    {
        $registration = Registration::query()->findOrFail($data['registration_id']);

        // A categoria precisa pertencer à MATRIZ CONGELADA do vínculo (mesma
        // fonte de verdade que o resto da progressão usa) — senão um aluno
        // poderia declarar hora contra o teto de uma matriz alheia. Via
        // config('models.*') pra não importar classe do catalog (fronteira).
        $categoryModel = config('models.complementary_category');
        $categoryBelongsToCurriculum = $categoryModel::query()
            ->where('ccg_id', $data['category_id'])
            ->where('curriculum_cur_id', $registration->curriculum_cur_id)
            ->exists();

        if (! $categoryBelongsToCurriculum) {
            throw ValidationException::withMessages([
                'category_id' => 'Esta categoria não pertence à sua matriz.',
            ]);
        }

        /** @var ?UploadedFile $certificate */
        $certificate = $data['certificate'] ?? null;

        return ComplementaryActivity::query()->create([
            'registration_reg_id' => $registration->reg_id,
            'complementary_category_ccg_id' => $data['category_id'],
            'cac_title' => $data['title'],
            'cac_hours_claimed' => $data['hours_claimed'],
            'cac_issued_at' => $data['issued_at'] ?? null,
            'cac_status' => ActivityStatus::Submitted,
            // Disco local, não público: certificado é documento pessoal.
            'cac_certificate_path' => $certificate?->store('complementary-certificates', 'local'),
        ]);
    }
}
