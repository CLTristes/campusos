<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\AcervoDaDisciplinaTool;
use App\Mcp\Tools\BuscarAnotacoesTool;
use App\Mcp\Tools\CriarAnotacaoTool;
use App\Mcp\Tools\DisciplinasLiberadasTool;
use App\Mcp\Tools\MinhaAgendaTool;
use App\Mcp\Tools\MinhaProgressaoTool;
use App\Mcp\Tools\MinhasHorasTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

/**
 * O copiloto — CampusOS dentro do Claude (B8, terceira esticada).
 *
 * Cada tool é um adaptador FINO sobre uma Action/ReadModel que já existe —
 * ver docs-site/features/copiloto-mcp.html. Seis de leitura, uma de escrita
 * (`CriarAnotacaoTool`, a única que checa a habilidade `mcp:write` do token).
 */
#[Name('CampusOS')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    Você está conectado ao CampusOS, o sistema acadêmico do aluno autenticado.
    Use estas tools para responder com o CONTEXTO REAL da graduação dele —
    prazo, autoria, semestre e conteúdo vêm de tabelas do CampusOS, nunca de
    generalidade de internet. Prefira minha_progressao/disciplinas_liberadas/
    minha_agenda/minhas_horas para perguntas sobre a situação do próprio
    aluno; acervo_da_disciplina/buscar_anotacoes para o que os veteranos
    deixaram; criar_anotacao só quando o aluno pedir explicitamente pra
    salvar algo.
    MARKDOWN)]
final class CampusOsServer extends Server
{
    /** @var array<int, class-string> */
    protected array $tools = [
        MinhaProgressaoTool::class,
        DisciplinasLiberadasTool::class,
        AcervoDaDisciplinaTool::class,
        BuscarAnotacoesTool::class,
        MinhaAgendaTool::class,
        MinhasHorasTool::class,
        CriarAnotacaoTool::class,
    ];
}
