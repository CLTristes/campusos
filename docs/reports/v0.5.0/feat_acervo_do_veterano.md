# 📦 Documento de Entrega — CampusOS · v0.5.0

## O acervo do veterano (B5) — desafio 5.2

> **Data:** 19/09/2026
> **Branch:** `feat/acervo-do-veterano`
> **Escopo:** B5 completo (Fases 0–2 de `docs-site/features/acervo-compartilhado.html`) — a escada de visibilidade e a herança entre semestres. B6 (tarefas da turma) e a curadoria por voto (Fase 4, ◎ esticada) ficam para depois.
> **Qualidade:** 163 testes / 488 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno escreve uma nota, escolhe até onde ela vai numa escada de cinco degraus, e quem cursar a mesma disciplina — em qualquer semestre, mesmo anos depois — recebe automaticamente, sem pedir nada a ninguém.

## 1 — A fronteira sagrada, provada por teste de arquitetura

O desenho do `docs-site` foi explícito: ler `subject_enrollments` direto de dentro do `lifeos` fura a fronteira modular. Criei `EnrolledSubjectsProvider` no `core` (`subjectIdsFor(string $userId): array` — em quais disciplinas o usuário já esteve matriculado, em qualquer termo, somando todos os vínculos) e a implementação real (`JourneyEnrolledSubjectsProvider`) mora no `journey`, bindada em `JourneyServiceProvider`. O `lifeos` nunca importa nada de `CampusOs\Journey` — `ArchTest` prova isso a cada `composer test`.

Para as visibilidades `course`/`offering`, que não carregam a mesma regra de negócio ("atravessar termo"), usei o caminho mais simples já preparado em `config('models.registration')`/`config('models.subject_enrollment')` — consultas por FK direta, sem precisar de um segundo contrato.

## 2 — `NoteVisibilityScope`: um global scope, cinco degraus

```
author_usr_id = usuário atual                          (sempre)
nte_visibility = institution                            (mesmo tenant — já garantido pelo EntityScope)
nte_visibility = course     AND vínculo naquele curso
nte_visibility = subject    AND já cursou a disciplina — EM QUALQUER TERMO
nte_visibility = offering   AND cursou AQUELA turma
```

A ausência de filtro por `term_trm_id` na linha do `subject` é o desafio 5.2 inteiro — está documentada como tal no código, não só na prosa.

**O teste que vale mais que a tela**, exatamente como o desenho pediu:

```php
it('entrega a nota do veterano de 2024/1 ao calouro de 2027/1 — o desafio 5.2', function () {
    $subject  = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');
    $calouro  = enrolledStudent($subject, '1/2027');

    $nota = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);

    expect(Note::visibleTo($calouro['user'])->pluck('nte_id'))->toContain($nota->nte_id);
});
```

`Note::visibleTo($user)` é uma consulta explícita (local scope) que remove o global scope antes de aplicar o mesmo filtro com um usuário à escolha — é o que permite testar sem fingir uma request HTTP inteira, e o que um job futuro usaria sem depender de `Auth::user()`.

## 3 — Privado é o padrão, publicar é sempre deliberado

`CreateNoteAction` ignora qualquer `visibility` que o cliente mande — toda nota nasce `private`, sem exceção. `UpdateNoteVisibilityAction` é o único caminho para subir na escada, e valida que a nota TEM a informação que o degrau-alvo exige antes de aceitar (`subject_id` para `subject`/`offering`, `course_id` para `course`) — sem isso, a nota subiria "publicada" mas invisível para todo mundo, porque nenhum `whereIn` do scope bateria. Despublicar (`visibility: private`) nunca tem pré-condição.

## 4 — O que existe agora

| Rota | O que faz |
| --- | --- |
| `POST /api/v1/notes` | Cria (sempre privada) |
| `GET /api/v1/notes` | Lista o que o usuário enxerga; `?subject_id=` filtra o acervo de uma disciplina |
| `GET /api/v1/notes/{note}` | Uma nota |
| `POST /api/v1/notes/{note}/visibility` | Publica/despublica — só o autor |

16 endpoints no total agora, 18 tabelas.

## 5 — Autoria nunca falta, nota de prova nunca aparece

`NoteResource` sempre expõe `author.name` (Fase 5 regra 4 do desenho — acervo anônimo não cria pertencimento). `Note` não tem relação nenhuma com `sen_grade`/`sen_status`: não há o que vazar por acidente, porque o campo não existe neste recurso.

## Ressalvas honestas

1. **B6 (tarefas da turma) não entrou** — é bloco separado no próprio desenho, e não foi pedido nesta entrega.
2. **Curadoria por voto (Fase 4) é explicitamente ◎ esticada no desenho** — não implementei `note_votes`. A ordenação do acervo hoje é só por `nte_published_at`/`nte_created_at` desc, sem score.
3. **Sem tela**, a prova roda por Pest (11 testes dedicados) e por `curl`/Postman.
4. **`nte_kind` é obrigatório na criação.** O desenho descreve "tipo declarado" como parte da curadoria (Fase 4), mas incluí a coluna desde já por ser barata — decidi exigi-la sempre, para não ter notas "sem tipo" circulando que precisariam de migração depois.
5. **Edição de conteúdo (título/corpo) não tem endpoint.** Só criação e mudança de visibilidade — não apareceu como pedido explícito do desenho nem desta tarefa; se o front precisar, é uma Action pequena (`UpdateNoteContentAction`, autor apenas).
6. **`OfferingFactory` gera `ofr_class_code` aleatório num universo de 8 valores** — dois `Offering::factory()->create()` para o mesmo termo/disciplina/campus podem colidir na unicidade por azar. Não é um bug desta entrega (a factory já existia), mas registro aqui porque um teste meu esbarrou nisso.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/dominio/` | Área nova: `ACERVO.md` (problema, as cinco regras da escada, o fluxo, decisões, mapa de código) — hoje o desenho só existe em `docs-site/features/acervo-compartilhado.html` |
| `docs/reports/SISTEMA.md` | `lifeos` deixa de ser esqueleto; nova tabela `notes` na Parte V; Parte X ganha 4 endpoints (16 no total) |
| `CLAUDE.md` | Mapa de módulos: `lifeos` vira "parcial" (B5 feito, B6 não); §Estado atual |
| `docs/arquitetura/BANCO.md` | Ownership de `notes` |
| `docs-site/features/acervo-compartilhado.html` | Fases 0–2 viram ✅ implementado |
