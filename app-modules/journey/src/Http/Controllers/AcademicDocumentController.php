<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Controllers;

use CampusOs\Journey\Actions\ConfirmAcademicDocumentAction;
use CampusOs\Journey\Actions\UploadAcademicDocumentAction;
use CampusOs\Journey\Http\Resources\EnrollmentRequestResource;
use CampusOs\Journey\Models\EnrollmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @group Jornada acadêmica
 *
 * Importação do documento acadêmico do aluno. Três passos: **sobe**, **confere**
 * e **confirma** — e só o último escreve matrícula. Um erro de leitura vira uma
 * correção de 30 segundos, em vez de um histórico corrompido.
 */
final class AcademicDocumentController
{
    /**
     * Enviar documento
     *
     * Aceita o **histórico escolar** (reconstitui a graduação inteira) ou o
     * **requerimento de matrícula** (atualiza o semestre corrente). Responde
     * **202**: a leitura roda em segundo plano, e o front consulta o status.
     *
     * @authenticated
     *
     * @bodyParam kind string required `transcript` ou `enrollment_request`. Example: transcript
     * @bodyParam file file required PDF ou foto (PNG/JPG), até 20 MB.
     *
     * @response 202 scenario="recebido" {"data":{"id":"01a0…","status":"uploaded","status_label":"Recebido","is_pending":true}}
     */
    public function store(Request $request, UploadAcademicDocumentAction $action): JsonResponse
    {
        $document = $action->execute([
            'user_id' => $request->user()->usr_id,
            'kind' => $request->input('kind'),
            'file' => $request->file('file'),
        ]);

        return EnrollmentRequestResource::make($document)
            ->response()
            ->setStatusCode(202);
    }

    /**
     * Status da leitura
     *
     * O que a tela de conferência consome. Enquanto `is_pending` for `true`, o
     * front continua consultando; quando vira `parsed`, `extraction` traz o que
     * foi lido para o aluno revisar.
     *
     * @authenticated
     */
    public function show(Request $request, EnrollmentRequest $document): EnrollmentRequestResource
    {
        $this->assertOwnership($request, $document);

        return EnrollmentRequestResource::make($document);
    }

    /**
     * Confirmar a leitura
     *
     * Recebe as linhas **já revisadas pelo aluno** e cria as matrículas. Linha
     * cujo código não existe no catálogo volta em `pending` — nunca derruba a
     * importação inteira, porque histórico real tem disciplina extinta.
     *
     * @authenticated
     *
     * @bodyParam registration_id string required O vínculo que recebe as matrículas.
     * @bodyParam lines object[] required As linhas conferidas.
     * @bodyParam lines[].code string required Código da disciplina. Example: ARC102
     * @bodyParam lines[].year integer required Example: 2023
     * @bodyParam lines[].period integer required 1 ou 2. Example: 1
     * @bodyParam lines[].status string required O texto da situação. Example: Aprovado Por Nota/Frequência
     * @bodyParam lines[].grade number Nota de 0 a 10. Example: 8.5
     * @bodyParam lines[].attendance number Frequência em %. Example: 91.2
     *
     * @response 200 scenario="confirmado" {"data":{"id":"01a0…","status":"confirmed"},"imported":46,"pending":[{"code":"XYZ999","reason":"Disciplina não está no catálogo do curso."}]}
     */
    public function confirm(
        Request $request,
        EnrollmentRequest $document,
        ConfirmAcademicDocumentAction $action,
    ): JsonResponse {
        $this->assertOwnership($request, $document);

        $result = $action->execute([
            'request_id' => $document->erq_id,
            'registration_id' => $request->input('registration_id'),
            'lines' => $request->input('lines', []),
        ]);

        return EnrollmentRequestResource::make($result['request'])
            ->additional(['imported' => $result['imported'], 'pending' => $result['pending']])
            ->response();
    }

    /**
     * O EntityScope já isola por instituição, mas dentro da MESMA instituição
     * um aluno não pode ler o documento de outro — e histórico escolar é o dado
     * mais sensível do sistema.
     */
    private function assertOwnership(Request $request, EnrollmentRequest $document): void
    {
        if ($document->user_usr_id !== $request->user()->usr_id) {
            throw new AccessDeniedHttpException('Este documento não é seu.');
        }
    }
}
