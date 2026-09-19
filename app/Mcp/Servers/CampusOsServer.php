<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\AcervoDaDisciplinaTool;
use App\Mcp\Tools\BuscarAnotacoesTool;
use App\Mcp\Tools\CriarAnotacaoTool;
use App\Mcp\Tools\DisciplinasLiberadasTool;
use App\Mcp\Tools\MatrizCurricularTool;
use App\Mcp\Tools\MeuHistoricoTool;
use App\Mcp\Tools\MinhaAgendaTool;
use App\Mcp\Tools\MinhaProgressaoTool;
use App\Mcp\Tools\MinhasAnotacoesTool;
use App\Mcp\Tools\MinhasHorasTool;
use App\Mcp\Tools\SimularReprovacaoTool;
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
    aluno; meu_historico quando ele perguntar por uma disciplina específica do
    passado ou desconfiar de algum número da progressão; matriz_curricular
    para perguntas sobre a estrutura do curso (não o progresso dele);
    simular_reprovacao só quando ele perguntar "e se eu reprovar em...";
    acervo_da_disciplina/buscar_anotacoes para o que os veteranos deixaram;
    minhas_anotacoes para o que ele próprio já escreveu; criar_anotacao só
    quando o aluno pedir explicitamente pra salvar algo.
    MARKDOWN)]
final class CampusOsServer extends Server
{
    /** @var array<int, class-string> */
    protected array $tools = [
        MinhaProgressaoTool::class,
        DisciplinasLiberadasTool::class,
        MeuHistoricoTool::class,
        MatrizCurricularTool::class,
        SimularReprovacaoTool::class,
        AcervoDaDisciplinaTool::class,
        BuscarAnotacoesTool::class,
        MinhasAnotacoesTool::class,
        MinhaAgendaTool::class,
        MinhasHorasTool::class,
        CriarAnotacaoTool::class,
    ];
}
