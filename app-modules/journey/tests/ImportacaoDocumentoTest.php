<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Term;
use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Core\DTOs\ExtractedDocument;
use CampusOs\Core\DTOs\ExtractedLine;
use CampusOs\Core\Exceptions\ExtractorUnavailableException;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Jobs\ParseAcademicDocumentJob;
use CampusOs\Journey\Models\EnrollmentRequest;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    $this->entity = tenantContext();
    Campus::factory()->create(['cps_code' => 'FB']);
    $this->seed(MatrizUtfprSeeder::class);

    $this->user = User::factory()->create();
    $this->student = Student::factory()->create(['user_usr_id' => $this->user->usr_id]);
    $this->registration = Registration::factory()->create([
        'student_std_id' => $this->student->std_id,
        'course_crs_id' => Course::query()->where('crs_code', '25')->value('crs_id'),
        'curriculum_cur_id' => Curriculum::query()->where('cur_code', '45')->value('cur_id'),
        'entry_term_trm_id' => Term::query()->where('trm_year', 2023)->where('trm_period', 1)->value('trm_id'),
        'reg_number' => '2567857',
    ]);
});

/** Um extrator falso: nenhum teste de domínio depende de rede nem de IA. */
function extratorDevolve(ExtractedDocument $doc): void
{
    app()->instance(AcademicDocumentExtractor::class, new class($doc) implements AcademicDocumentExtractor
    {
        public function __construct(private ExtractedDocument $doc) {}

        public function name(): string
        {
            return 'fake';
        }

        public function extract(string $absolutePath, string $mimeType): ExtractedDocument
        {
            return $this->doc;
        }
    });
}

it('o upload responde 202 e enfileira a leitura — não trava a tela', function () {
    Queue::fake();

    $this->actingAs($this->user)
        ->postJson('/api/v1/me/academic-documents', [
            'kind' => 'transcript',
            'file' => UploadedFile::fake()->create('historico.pdf', 100, 'application/pdf'),
        ])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'uploaded')
        ->assertJsonPath('data.is_pending', true);

    Queue::assertPushed(ParseAcademicDocumentJob::class);
});

it('recusa arquivo que não é documento', function () {
    $this->actingAs($this->user)
        ->postJson('/api/v1/me/academic-documents', [
            'kind' => 'transcript',
            'file' => UploadedFile::fake()->create('virus.exe', 10),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

it('o caminho do arquivo nunca sai na resposta', function () {
    Queue::fake();

    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/academic-documents', [
            'kind' => 'transcript',
            'file' => UploadedFile::fake()->create('historico.pdf', 10, 'application/pdf'),
        ])->getContent();

    expect($body)->not->toContain('academic-documents/')
        ->and($body)->not->toContain('erq_file_path');
});

it('a leitura preenche a extração e para em "aguardando conferência"', function () {
    extratorDevolve(new ExtractedDocument(
        kind: 'transcript',
        lines: [new ExtractedLine(code: 'ARC102', status: 'Aprovado Por Nota/Frequência',
            year: 2023, period: 1, grade: 8.5, attendance: 91.2)],
        meta: ['registration_number' => '2567857'],
        confidence: 0.97,
        provider: 'fake',
    ));

    $doc = EnrollmentRequest::factory()->create(['user_usr_id' => $this->user->usr_id]);
    (new ParseAcademicDocumentJob($doc->erq_id, $this->entity->ent_id))
        ->handle(app(AcademicDocumentExtractor::class));

    $doc->refresh();

    // NADA de matrícula ainda: a IA não escreve, ela propõe.
    expect($doc->erq_status)->toBe(DocumentRequestStatus::Parsed)
        ->and($doc->erq_extraction['lines'][0]['code'])->toBe('ARC102')
        ->and((float) $doc->erq_confidence)->toBe(0.97)
        ->and(SubjectEnrollment::query()->count())->toBe(0);
});

it('documento ilegível vira estado final com motivo, sem derrubar nada', function () {
    extratorDevolve(ExtractedDocument::failed('A imagem está muito escura.', 'fake'));

    $doc = EnrollmentRequest::factory()->create(['user_usr_id' => $this->user->usr_id]);
    (new ParseAcademicDocumentJob($doc->erq_id, $this->entity->ent_id))
        ->handle(app(AcademicDocumentExtractor::class));

    expect($doc->refresh()->erq_status)->toBe(DocumentRequestStatus::Failed)
        ->and($doc->erq_failure_reason)->toBe('A imagem está muito escura.');
});

it('erro de transporte SOBE — a fila que decide tentar de novo', function () {
    app()->instance(AcademicDocumentExtractor::class, new class implements AcademicDocumentExtractor
    {
        public function name(): string
        {
            return 'fake';
        }

        public function extract(string $absolutePath, string $mimeType): ExtractedDocument
        {
            throw new ExtractorUnavailableException('502 do provedor');
        }
    });

    $doc = EnrollmentRequest::factory()->create(['user_usr_id' => $this->user->usr_id]);

    expect(fn () => (new ParseAcademicDocumentJob($doc->erq_id, $this->entity->ent_id))
        ->handle(app(AcademicDocumentExtractor::class)))
        ->toThrow(ExtractorUnavailableException::class);

    // Volta para "recebido", não para "falhou": uma instabilidade momentânea
    // não pode matar o upload do aluno.
    expect($doc->refresh()->erq_status)->toBe(DocumentRequestStatus::Uploaded);
});

it('o Job é idempotente — a fila pode reentregar', function () {
    extratorDevolve(new ExtractedDocument('transcript', [new ExtractedLine(code: 'ARC102')], provider: 'fake'));

    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Confirmed,
        'erq_confirmed_at' => now(),
    ]);

    (new ParseAcademicDocumentJob($doc->erq_id, $this->entity->ent_id))
        ->handle(app(AcademicDocumentExtractor::class));

    // Reprocessar um documento já confirmado sobrescreveria a conferência que
    // o aluno fez à mão.
    expect($doc->refresh()->erq_status)->toBe(DocumentRequestStatus::Confirmed);
});

it('SÓ a confirmação cria matrícula — e a barra de progresso anda', function () {
    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'registration_id' => $this->registration->reg_id,
            'lines' => [
                ['code' => 'ARC102', 'year' => 2023, 'period' => 1,
                    'status' => 'Aprovado Por Nota/Frequência', 'grade' => 8.5, 'attendance' => 91.2],
                ['code' => 'MAT029', 'year' => 2023, 'period' => 1,
                    'status' => 'Reprovado Por Nota', 'grade' => 3.0, 'attendance' => 80.0],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'confirmed')
        ->assertJsonPath('imported', 2);

    $aprovada = SubjectEnrollment::query()->whereRelation('subject', 'sbj_code', 'ARC102')->firstOrFail();
    $reprovada = SubjectEnrollment::query()->whereRelation('subject', 'sbj_code', 'MAT029')->firstOrFail();

    expect($aprovada->sen_status)->toBe(EnrollmentStatus::Approved)
        ->and($aprovada->sen_hours_earned)->toBe(60)
        ->and($reprovada->sen_status)->toBe(EnrollmentStatus::FailedGrade)
        ->and($reprovada->sen_hours_earned)->toBe(0);

    // O fim da linha: o documento virou barra de progresso.
    expect((new ProgressReadModel)->for($this->registration)['overall']['completed_hours'])->toBe(60);
});

it('sem registration_id, o vínculo nasce do cabeçalho que a IA leu', function () {
    $semVinculo = User::factory()->create();

    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $semVinculo->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
        'erq_extraction' => [
            'meta' => [
                'registration_number' => '9999999',
                'course_code' => '25',
                'curriculum_code' => '45',
                'entry_term' => '1/2023',
            ],
        ],
    ]);

    $this->actingAs($semVinculo)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'lines' => [
                ['code' => 'ARC102', 'year' => 2023, 'period' => 1,
                    'status' => 'Aprovado Por Nota/Frequência', 'grade' => 8.5, 'attendance' => 91.2],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('imported', 1);

    $student = Student::query()->where('user_usr_id', $semVinculo->usr_id)->firstOrFail();
    $registration = Registration::query()->where('student_std_id', $student->std_id)->firstOrFail();

    expect($registration->reg_number)->toBe('9999999')
        ->and($registration->course_crs_id)->toBe(Course::query()->where('crs_code', '25')->value('crs_id'))
        ->and($registration->curriculum_cur_id)->toBe(Curriculum::query()->where('cur_code', '45')->value('cur_id'))
        ->and($doc->refresh()->registration_reg_id)->toBe($registration->reg_id)
        ->and(SubjectEnrollment::query()->where('registration_reg_id', $registration->reg_id)->count())->toBe(1);
});

it('sem registration_id, reaproveita o vínculo que o aluno já tem no mesmo curso', function () {
    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
        'erq_extraction' => ['meta' => ['course_code' => '25', 'entry_term' => '1/2023']],
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'lines' => [
                ['code' => 'ARC102', 'year' => 2023, 'period' => 1, 'status' => 'Aprovado Por Nota/Frequência'],
            ],
        ])
        ->assertOk();

    // Nenhum vínculo novo — o do beforeEach foi reaproveitado.
    expect(Registration::query()->where('student_std_id', $this->student->std_id)->count())->toBe(1)
        ->and($doc->refresh()->registration_reg_id)->toBe($this->registration->reg_id);
});

it('sem registration_id e sem cabeçalho suficiente, pede o vínculo explicitamente', function () {
    $semVinculo = User::factory()->create();

    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $semVinculo->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
        'erq_extraction' => ['meta' => []],
    ]);

    $this->actingAs($semVinculo)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'lines' => [['code' => 'ARC102', 'year' => 2023, 'period' => 1, 'status' => 'Aprovado']],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('registration_id');
});

it('situação impressa mais longa que 64 caracteres vira pendência, não derruba a confirmação', function () {
    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
    ]);

    // Ex.: "Enade - Estudante Dispensado De Realização Do Enade, Em Razão Da
    // Natureza Do Curso" no histórico real — texto administrativo, não uma
    // situação acadêmica reconhecida.
    $situacaoLonga = 'Situação Administrativa Extraordinária Não Prevista No Vocabulário Do Documento Oficial';

    $r = $this->actingAs($this->user)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'registration_id' => $this->registration->reg_id,
            'lines' => [
                ['code' => 'ARC102', 'year' => 2023, 'period' => 1, 'status' => $situacaoLonga],
            ],
        ])->assertOk();

    expect($r->json('imported'))->toBe(0)
        ->and($r->json('pending.0.code'))->toBe('ARC102');
});

it('disciplina fora do catálogo vira pendência, não erro fatal', function () {
    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
    ]);

    $r = $this->actingAs($this->user)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'registration_id' => $this->registration->reg_id,
            'lines' => [
                ['code' => 'ARC102', 'year' => 2023, 'period' => 1, 'status' => 'Aprovado'],
                // Disciplina extinta, de uma matriz antiga.
                ['code' => 'ZZZ999', 'year' => 2019, 'period' => 2, 'status' => 'Aprovado'],
            ],
        ])->assertOk();

    // Derrubar a importação inteira por uma linha de 2019 é o pior dos mundos.
    expect($r->json('imported'))->toBe(1)
        ->and($r->json('pending.0.code'))->toBe('ZZZ999');
});

it('confirmar duas vezes é barrado', function () {
    $doc = EnrollmentRequest::factory()->create([
        'user_usr_id' => $this->user->usr_id,
        'erq_status' => DocumentRequestStatus::Confirmed,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/v1/me/academic-documents/{$doc->erq_id}/confirm", [
            'registration_id' => $this->registration->reg_id,
            'lines' => [['code' => 'ARC102', 'year' => 2023, 'period' => 1, 'status' => 'Aprovado']],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('request_id');
});

it('um aluno não lê o documento de outro', function () {
    $outro = User::factory()->create();
    $doc = EnrollmentRequest::factory()->create(['user_usr_id' => $this->user->usr_id]);

    // Mesmo tenant, mesma instituição — mas histórico escolar é da pessoa.
    $this->actingAs($outro)
        ->getJson("/api/v1/me/academic-documents/{$doc->erq_id}")
        ->assertForbidden();
});

it('a extração fica fora da trilha de auditoria', function () {
    $doc = EnrollmentRequest::factory()->create(['user_usr_id' => $this->user->usr_id]);
    $doc->update(['erq_extraction' => ['lines' => [['code' => 'ARC102']]]]);

    $trilha = CampusOs\Core\Models\AuditLog::query()
        ->where('aud_table', 'enrollment_requests')->get();

    expect($trilha)->not->toBeEmpty();

    foreach ($trilha as $log) {
        expect($log->aud_after ?? [])->not->toHaveKey('erq_extraction')
            ->and($log->aud_after ?? [])->not->toHaveKey('erq_file_path');
    }
});
