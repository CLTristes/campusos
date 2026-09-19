<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Bindings de Model cross-módulo
    |--------------------------------------------------------------------------
    |
    | Relações Eloquent que cruzam a fronteira entre módulos são resolvidas por
    | estas chaves (ex.: a relação entity() do trait Entityable), em vez de
    | importar a classe do outro módulo diretamente. Isso preserva a regra
    | "um módulo não importa o interno de outro" no código de produção (src/),
    | mantendo o teste de arquitetura verde. Factories (test helpers) podem
    | referenciar direto se precisarem.
    |
    | Adicione uma chave aqui sempre que um model precisar se relacionar com um
    | model de OUTRO módulo: belongsTo(config('models.x'), ...).
    |
    */

    'entity' => CampusOs\Tenancy\Models\Entity::class,
    'campus' => CampusOs\Tenancy\Models\Campus::class,
    'user' => CampusOs\Tenancy\Models\User::class,

    // catalog — o journey e o lifeos se relacionam com estes sem importar a
    // classe do outro módulo (regra de ouro nº 3).
    'course' => CampusOs\Catalog\Models\Course::class,
    'curriculum' => CampusOs\Catalog\Models\Curriculum::class,
    'curriculum_subject' => CampusOs\Catalog\Models\CurriculumSubject::class,
    'subject' => CampusOs\Catalog\Models\Subject::class,
    'term' => CampusOs\Catalog\Models\Term::class,
    'offering' => CampusOs\Catalog\Models\Offering::class,
    'complementary_category' => CampusOs\Catalog\Models\ComplementaryCategory::class,

    // journey — o lifeos e o insights leem estes por contrato/config.
    'student' => CampusOs\Journey\Models\Student::class,
    'registration' => CampusOs\Journey\Models\Registration::class,
    'subject_enrollment' => CampusOs\Journey\Models\SubjectEnrollment::class,
];
