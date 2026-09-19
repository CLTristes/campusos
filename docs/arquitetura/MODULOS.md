# Módulos — o pacote `internachi/modular`

Este documento cobre a **mecânica**: o que é o pacote, por que ele, como instalar
e qual é a árvore de arquivos de cada módulo. O *desenho* (quais módulos e por
quê) está em [`ARQUITETURA.md`](ARQUITETURA.md).

## O que é

`internachi/modular` é, nas palavras do autor (Chris Morrell / InterNACHI), *"tanto
um conjunto de convenções quanto um pacote"*. A ideia central: você cria módulos
num diretório `app-modules/`, e cada módulo usa o **sistema de pacotes nativo do
Laravel** — ou seja, cada módulo é, literalmente, um pacote Composer. Ele se apoia
em duas coisas que o Laravel já tem:

- **Composer path repositories:** cada módulo é registrado no `composer.json` da
  app como um repositório local (`./app-modules/*`) e exigido como dependência
  (`modules/nome:*`). É isso que dá o autoload PSR-4 isolado por módulo.
- **Laravel package discovery:** a inicialização de cada módulo (service
  providers, etc.) usa o mesmo mecanismo de auto-descoberta que o Laravel usa
  para pacotes de terceiros.

O pacote adiciona só "tooling mínimo para preencher as lacunas". A linha 3.x usa
um sistema de plugins com atributos PHP (`#[OnBoot]`, `#[AfterResolving(...)]`)
para customizar o ciclo de vida do boot dos módulos, caso precise.

## Por que ele e não o `nwidart/laravel-modules`

Esses são os dois grandes pacotes de modularização do Laravel. O próprio README
do InterNACHI explica honestamente quando usar cada um:

| Critério                            | nwidart/laravel-modules                       | internachi/modular                        |
| ----------------------------------- | --------------------------------------------- | ----------------------------------------- |
| Filosofia                           | Estrutura de diretórios própria               | Convenções nativas do Laravel             |
| Peso                                | Mais pesado, mais features                    | Mais leve, mínimo                         |
| Caso ideal                          | CMS com módulos de terceiros liga/desliga em runtime | Modularização para organização interna |
| Ligar/desligar módulo dinamicamente | Sim (ponto forte)                             | Não é o foco                              |
| Extrair módulo p/ pacote depois     | Mais trabalhoso                               | Trivial (já é um pacote Composer)         |

Veredito: *se você constrói um CMS que precisa de módulos de terceiros ativáveis
em runtime, use o nwidart; se quer modularização para organização e ficar perto
das convenções do Laravel, use o InterNACHI.* Sistemas construídos com este
template são claramente o segundo caso — organização limpa e a opção de um dia
extrair um módulo para pacote reutilizável (que o InterNACHI dá de graça).

## Instalação (JÁ FEITA no template)

O template nasce com tudo configurado. Para referência, o que foi feito:

```bash
# 1. Instala o pacote (auto-discovery configura tudo)
composer require internachi/modular

# 2. Publica o config para customizar o namespace organizacional
php artisan vendor:publish --tag=modular-config
#    -> config/app-modules.php (namespace `CampusOs\`, vendor `campus-os/`)

# 3. Cria um módulo
php artisan make:module meu-modulo

# 4. Atualiza o Composer (make:module adiciona 2 entradas no composer.json)
composer update modules/meu-modulo

# 5. (Feito no template) Suíte "Modules" no phpunit.xml
php artisan modules:sync
```

> **Sobre o namespace:** trocar o default `Modules\` pelo nome do projeto em
> PascalCase (`MeuSistema\`) deixa os imports legíveis
> (`MeuSistema\Orders\Models\Order`) e facilita extrair o módulo para pacote
> separado no futuro. A skill `/prontuario` faz essa renomeação global com
> segurança (namespace + vendor + composer.json de cada módulo + ArchTest).

## O que funciona de graça (auto-discovery por módulo)

Como os módulos seguem convenções nativas, a auto-descoberta do Laravel funciona
dentro de cada `app-modules/*` sem configuração extra:

- Commands auto-registrados no Artisan (ex.: `audit:query` vem do `core`)
- Migrations rodadas pelo Migrator (de todos os módulos, ordenadas por nome)
- Factories auto-carregadas para `factory()` (com `newFactory()` explícito no model)
- Policies auto-descobertas para os Models
- Rotas: todo arquivo em `app-modules/<modulo>/routes/` é carregado
- Componentes Blade com namespace do módulo: `<x-catalog::status-badge />`
- Event listeners auto-descobertos (o template prefere registro explícito no provider)
- Traduções com namespace: `__('journey::messages.approved')`

E os comandos `make:` do Laravel ganham a flag `--module=`:

```bash
php artisan make:model SubjectEnrollment --module=journey
php artisan make:job ParseAcademicDocumentJob --module=journey
php artisan make:observer SubjectEnrollmentObserver --module=journey
php artisan make:test ProgressoTest --module=journey
php artisan db:seed --module=catalog
```

Comandos próprios do pacote: `make:module`, `modules:list`, `modules:cache`
(cacheia para auto-discovery mais rápida — use em produção), `modules:clear`,
`modules:sync`.

> **Pegadinha:** se você já tiver instalado o `nwidart/laravel-modules` antes no
> mesmo projeto, o `ModuleRegistry` do InterNACHI pode falhar lendo
> `bootstrap/cache/modules.php` (formato diferente). Fix: `php artisan
> optimize:clear` antes de instalar.

## Árvores de arquivos

Cada módulo é um mini-Laravel. Abaixo o estado real — só o que existe em código.

### `core` — shared kernel (do template)

```
app-modules/core/src/
  Actions/AbstractAction.php          sanitize→validate→authorize→handle
  Actions/Input/                      adaptadores de entrada
  Models/AuditLog.php
  Models/Concerns/Entityable.php      trait multi-tenant
  Observers/AuditObserver.php         base de toda auditoria
  Scopes/EntityScope.php
  Tenancy/TenantContext.php           set / runAs / withoutScope
  Console/Commands/AuditQueryCommand.php
```

### `tenancy` — a instituição e quem entra

```
app-modules/tenancy/
  src/Models/{Entity,Campus,User}.php
  src/Enums/UserRole.php              student | coordinator | professor | institution_admin
  src/Observers/{Entity,Campus,User}Observer.php
  src/Actions/LoginAction.php         fora do escopo de tenant — é dele que o tenant sai
  src/Http/Controllers/AuthController.php
  src/Http/Resources/UserResource.php
  routes/api.php                      /api/v1/auth/{login,me,logout}
  database/migrations/                entities, campuses, users
  tests/AutenticacaoTest.php
```

### `catalog` — o esqueleto acadêmico

```
app-modules/catalog/
  src/Models/                         Course, Curriculum, Subject, CurriculumSubject,
                                      Prerequisite, ElectiveGroup, SubjectEquivalence,
                                      Term, Offering
  src/Enums/                          CourseDegree, CourseShift, CurriculumStatus,
                                      SubjectNature, SubjectModel, PrerequisiteType,
                                      TermStatus
  src/Http/Controllers/CourseController.php
  src/Http/Resources/{Course,Curriculum}Resource.php
  routes/api.php                      /api/v1/courses[/{course}/curriculum]
  database/data/                      matriz-45-utfpr-fb.csv, equivalencias-*.csv
                                      + parse_matriz_html.py (o GERADOR — não edite
                                        os CSVs à mão, rode o script)
  database/seeders/MatrizUtfprSeeder.php
  tests/{MatrizUtfpr,CatalogoApi}Test.php
```

### `journey` — a trajetória do aluno

```
app-modules/journey/
  src/Models/{Student,Registration,SubjectEnrollment}.php
  src/Enums/{EnrollmentStatus,RegistrationStatus,EnrollmentSource}.php
  src/Support/ApprovalPolicy.php      a regra de aprovação da UTFPR
  src/ReadModels/ProgressReadModel.php  a barra de progresso (calculada)
  src/DTOs/ProgressTrack.php          uma faixa, saturando no próprio teto
  src/Actions/CreateRegistrationAction.php
  src/Http/Controllers/ProgressController.php
  routes/api.php                      /api/v1/me/progress
  tests/{ApprovalPolicy,Progresso,Vinculo}Test.php
```

### `lifeos` · `insights` · `integrations` — esqueletos

Só `composer.json` + `src/Providers/*ServiceProvider.php`. Registrados em
`DOMAIN_MODULES` do ArchTest desde o nascimento, para não nascerem fora da lei.

> `insights` **não terá tabelas por desenho**: responde perguntas de coordenação
> lendo, por read model declarado no `core`, o que `journey` e `catalog` já
> possuem. Dar tabelas próprias a ele seria duplicar dado e inventar um problema
> de sincronização que ninguém tem.

## Referências

- `internachi/modular` — README oficial: github.com/InterNACHI/modular
- Packagist: packagist.org/packages/internachi/modular
- Princípios de modular monolith: Kamil Grzybek, Milan Jovanović (2020–2026)
