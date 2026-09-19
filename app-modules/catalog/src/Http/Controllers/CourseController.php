<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Http\Controllers;

use CampusOs\Catalog\Enums\CurriculumStatus;
use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Http\Resources\CourseResource;
use CampusOs\Catalog\Http\Resources\CurriculumResource;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Leitura do catálogo acadêmico.
 *
 * Controller FINO por definição (regra de ouro nº 3): não há Action aqui porque
 * não há escrita nem regra — é leitura de dado mestre, e o escopo de tenant já
 * é aplicado pelo EntityScope. A implantação da matriz (a escrita) vem por
 * Action no módulo integrations, a partir do documento.
 *
 * @group Catálogo acadêmico
 *
 * Curso, matriz curricular e as disciplinas por período. Dado mestre da
 * instituição: muda por resolução de colegiado, nunca por ação de aluno.
 */
final class CourseController
{
    /**
     * Listar cursos
     *
     * Todos os cursos da instituição, com o câmpus de cada um.
     *
     * @response 200 scenario="UTFPR" {"data":[{"id":"01a0…","code":"25","name":"Bacharelado em Sistemas de Informação","degree":"bachelor","degree_label":"Bacharelado","shift":"evening","shift_label":"Noturno","campus":{"id":"01a0…","code":"FB","name":"Francisco Beltrão"}}]}
     */
    public function index(): AnonymousResourceCollection
    {
        return CourseResource::collection(
            Course::query()->with('campus')->orderBy('crs_name')->get()
        );
    }

    /**
     * Matriz curricular do curso
     *
     * A matriz ATIVA do curso, com as disciplinas agrupadas por período, os
     * pré-requisitos de cada uma e o bloco de cargas horárias.
     *
     * `workload.total_hours` é **calculado**, não armazenado:
     * `mandatory + elective + standalone_extension`. As horas extensionistas
     * embutidas nas disciplinas (`extension_hours`) são uma exigência
     * **ortogonal** e por isso `extension_counts_in_total` é `false` — somá-las
     * inventaria horas que o aluno não precisa cursar.
     *
     * `terms[].subjects[].available_this_term` só existe pras OPTATIVAS: nem
     * toda optativa cadastrada roda todo semestre (há rotação), e
     * "disponível" é literalmente "tem `offerings` para ela no termo
     * consultado" — sem `term_id`, cai no termo `current` da instituição; sem
     * termo corrente nenhum configurado, o campo some (`null`) em vez de
     * mentir "indisponível". As disciplinas OBRIGATÓRIAS nunca ganham este
     * campo — a matriz inteira continua aparecendo, nunca filtrada.
     *
     * @urlParam course string required O id do curso. Example: 01a0b823-b7d7-72d8-8db9-810d0e28d9c7
     *
     * @queryParam term_id string O termo pra checar disponibilidade de optativa. Omitido, usa o termo `current` da instituição. Example: 01a0b860-2f64-70fb-8c67-f15096cdb2de
     *
     * @response 404 scenario="curso sem matriz ativa" {"message":"Curso não tem matriz ativa."}
     */
    public function curriculum(Request $request, Course $course): CurriculumResource
    {
        $curriculum = $course->curricula()
            ->where('cur_status', CurriculumStatus::Active)
            ->orderByDesc('cur_effective_from')
            ->with([
                'electiveGroups',
                'complementaryCategories',
                'curriculumSubjects.subject',
                'curriculumSubjects.electiveGroup',
                'curriculumSubjects.prerequisites.required.subject',
            ])
            ->firstOrFail();

        $resource = new CurriculumResource($curriculum);

        $termId = $request->query('term_id') ?? Term::query()->where('trm_status', TermStatus::Current)->value('trm_id');

        if ($termId !== null) {
            $resource->withAvailability(
                Offering::query()->where('term_trm_id', $termId)->pluck('subject_sbj_id')
            );
        }

        return $resource;
    }
}
