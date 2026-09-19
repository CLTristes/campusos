<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\EligibilityReadModel;

/**
 * "E se eu reprovar nestas?" — sem escrita nenhuma no banco (regra de ouro
 * nº 8 não se aplica: nada muda de estado, então não há o que auditar).
 * Existe como Action só pela validação de entrada — o cálculo em si é
 * `EligibilityReadModel::simulate`, puro e reaproveitável.
 */
final class SimulateFailureAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'registration_id' => ['required', 'string', 'uuid'],
            'fail' => ['required', 'array', 'min:1'],
            'fail.*' => ['required', 'string', 'exists:subjects,sbj_code'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): array
    {
        $registration = Registration::query()->findOrFail($data['registration_id']);

        return (new EligibilityReadModel)->simulate($registration, $data['fail']);
    }
}
