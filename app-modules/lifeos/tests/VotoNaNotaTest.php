<?php

declare(strict_types=1);

use CampusOs\Lifeos\Actions\ToggleNoteVoteAction;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Models\NoteVote;
use CampusOs\Tenancy\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
});

it('votar soma um, votar de novo desfaz — nunca um terceiro estado', function () {
    $autor = User::factory()->create();
    $eleitor = User::factory()->create();
    $nota = Note::factory()->create(['author_usr_id' => $autor->usr_id, 'nte_visibility' => Visibility::Institution]);

    $this->actingAs($eleitor);
    $primeiro = (new ToggleNoteVoteAction)->execute(['note_id' => $nota->nte_id, 'user_id' => $eleitor->usr_id]);
    expect($primeiro['voted'])->toBeTrue()
        ->and($primeiro['note']->nte_upvotes_count)->toBe(1)
        ->and(NoteVote::query()->count())->toBe(1);

    $segundo = (new ToggleNoteVoteAction)->execute(['note_id' => $nota->nte_id, 'user_id' => $eleitor->usr_id]);
    expect($segundo['voted'])->toBeFalse()
        ->and($segundo['note']->nte_upvotes_count)->toBe(0)
        ->and(NoteVote::query()->count())->toBe(0);
});

it('não pode votar na própria anotação', function () {
    $autor = User::factory()->create();
    $nota = Note::factory()->create(['author_usr_id' => $autor->usr_id, 'nte_visibility' => Visibility::Institution]);

    $this->actingAs($autor);
    expect(fn () => (new ToggleNoteVoteAction)->execute(['note_id' => $nota->nte_id, 'user_id' => $autor->usr_id]))
        ->toThrow(ValidationException::class);

    expect($nota->refresh()->nte_upvotes_count)->toBe(0);
});

it('o índice único é a regra — duas linhas pro mesmo par (nota, eleitor) quebram no banco', function () {
    $autor = User::factory()->create();
    $eleitor = User::factory()->create();
    $nota = Note::factory()->create(['author_usr_id' => $autor->usr_id, 'nte_visibility' => Visibility::Institution]);

    NoteVote::factory()->create(['note_nte_id' => $nota->nte_id, 'user_usr_id' => $eleitor->usr_id]);

    expect(fn () => NoteVote::factory()->create(['note_nte_id' => $nota->nte_id, 'user_usr_id' => $eleitor->usr_id]))
        ->toThrow(QueryException::class);
});

it('POST /notes/{note}/vote alterna e devolve o contador atualizado', function () {
    $autor = User::factory()->create();
    $eleitor = User::factory()->create();
    $nota = Note::factory()->create(['author_usr_id' => $autor->usr_id, 'nte_visibility' => Visibility::Institution]);

    $this->actingAs($eleitor)
        ->postJson("/api/v1/notes/{$nota->nte_id}/vote")
        ->assertOk()
        ->assertJsonPath('data.upvotes_count', 1)
        ->assertJsonPath('voted', true);

    $this->actingAs($eleitor)
        ->postJson("/api/v1/notes/{$nota->nte_id}/vote")
        ->assertOk()
        ->assertJsonPath('data.upvotes_count', 0)
        ->assertJsonPath('voted', false);
});

it('POST /notes/{note}/vote na própria nota é 422', function () {
    $autor = User::factory()->create();
    $nota = Note::factory()->create(['author_usr_id' => $autor->usr_id, 'nte_visibility' => Visibility::Institution]);

    $this->actingAs($autor)
        ->postJson("/api/v1/notes/{$nota->nte_id}/vote")
        ->assertStatus(422);
});

it('GET /notes ordena por score, empate por data — a nota mais votada aparece primeiro', function () {
    $autor = User::factory()->create();
    $eleitores = User::factory()->count(3)->create();

    $poucoVotada = Note::factory()->create([
        'author_usr_id' => $autor->usr_id,
        'nte_visibility' => Visibility::Institution,
        'nte_title' => 'Pouco votada',
    ]);
    $maisVotada = Note::factory()->create([
        'author_usr_id' => $autor->usr_id,
        'nte_visibility' => Visibility::Institution,
        'nte_title' => 'Mais votada',
    ]);

    foreach ($eleitores as $eleitor) {
        $this->actingAs($eleitor);
        (new ToggleNoteVoteAction)->execute(['note_id' => $maisVotada->nte_id, 'user_id' => $eleitor->usr_id]);
    }
    $this->actingAs($eleitores->first());
    (new ToggleNoteVoteAction)->execute(['note_id' => $poucoVotada->nte_id, 'user_id' => $eleitores->first()->usr_id]);

    $this->actingAs($eleitores->first())
        ->getJson('/api/v1/notes')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Mais votada')
        ->assertJsonPath('data.0.upvotes_count', 3)
        ->assertJsonPath('data.0.voted_by_me', true)
        ->assertJsonPath('data.1.title', 'Pouco votada')
        ->assertJsonPath('data.1.voted_by_me', true);
});
