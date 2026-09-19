<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>CampusOS · Documentação da API</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.11.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.11.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-acervo-do-veterano" class="tocify-header">
                <li class="tocify-item level-1" data-unique="acervo-do-veterano">
                    <a href="#acervo-do-veterano">Acervo do veterano</a>
                </li>
                                    <ul id="tocify-subheader-acervo-do-veterano" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="acervo-do-veterano-GETapi-v1-notes">
                                <a href="#acervo-do-veterano-GETapi-v1-notes">Listar notas</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="acervo-do-veterano-POSTapi-v1-notes">
                                <a href="#acervo-do-veterano-POSTapi-v1-notes">Criar nota</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="acervo-do-veterano-GETapi-v1-notes--nte_id-">
                                <a href="#acervo-do-veterano-GETapi-v1-notes--nte_id-">Ver uma nota</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="acervo-do-veterano-POSTapi-v1-notes--note_nte_id--visibility">
                                <a href="#acervo-do-veterano-POSTapi-v1-notes--note_nte_id--visibility">Mudar a visibilidade</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-autenticacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="autenticacao">
                    <a href="#autenticacao">Autenticação</a>
                </li>
                                    <ul id="tocify-subheader-autenticacao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-login">
                                <a href="#autenticacao-POSTapi-v1-auth-login">Entrar</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-signup">
                                <a href="#autenticacao-POSTapi-v1-auth-signup">Cadastrar (aluno)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-GETapi-v1-auth-me">
                                <a href="#autenticacao-GETapi-v1-auth-me">Quem sou eu</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-logout">
                                <a href="#autenticacao-POSTapi-v1-auth-logout">Sair</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-verify-email">
                                <a href="#autenticacao-POSTapi-v1-auth-verify-email">Confirmar e-mail</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-verify-email-resend">
                                <a href="#autenticacao-POSTapi-v1-auth-verify-email-resend">Reenviar código de verificação</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-mcp-token">
                                <a href="#autenticacao-POSTapi-v1-auth-mcp-token">Token do copiloto (B8)</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-catalogo-academico" class="tocify-header">
                <li class="tocify-item level-1" data-unique="catalogo-academico">
                    <a href="#catalogo-academico">Catálogo acadêmico</a>
                </li>
                                    <ul id="tocify-subheader-catalogo-academico" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="catalogo-academico-GETapi-v1-courses">
                                <a href="#catalogo-academico-GETapi-v1-courses">Listar cursos</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="catalogo-academico-GETapi-v1-courses--course_crs_id--curriculum">
                                <a href="#catalogo-academico-GETapi-v1-courses--course_crs_id--curriculum">Matriz curricular do curso</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-elegibilidade-e-simulacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="elegibilidade-e-simulacao">
                    <a href="#elegibilidade-e-simulacao">Elegibilidade e simulação</a>
                </li>
                                    <ul id="tocify-subheader-elegibilidade-e-simulacao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="elegibilidade-e-simulacao-GETapi-v1-me-next-term">
                                <a href="#elegibilidade-e-simulacao-GETapi-v1-me-next-term">O que libera e o que trava</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="elegibilidade-e-simulacao-POSTapi-v1-me-simulate">
                                <a href="#elegibilidade-e-simulacao-POSTapi-v1-me-simulate">E se eu reprovar? — simulação</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-horas-complementares" class="tocify-header">
                <li class="tocify-item level-1" data-unique="horas-complementares">
                    <a href="#horas-complementares">Horas complementares</a>
                </li>
                                    <ul id="tocify-subheader-horas-complementares" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="horas-complementares-GETapi-v1-me-complementary-activities">
                                <a href="#horas-complementares-GETapi-v1-me-complementary-activities">Minhas atividades complementares</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="horas-complementares-POSTapi-v1-me-complementary-activities">
                                <a href="#horas-complementares-POSTapi-v1-me-complementary-activities">Declarar atividade complementar</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-jornada-academica" class="tocify-header">
                <li class="tocify-item level-1" data-unique="jornada-academica">
                    <a href="#jornada-academica">Jornada acadêmica</a>
                </li>
                                    <ul id="tocify-subheader-jornada-academica" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="jornada-academica-GETapi-v1-me-progress">
                                <a href="#jornada-academica-GETapi-v1-me-progress">Minha progressão</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="jornada-academica-POSTapi-v1-me-academic-documents">
                                <a href="#jornada-academica-POSTapi-v1-me-academic-documents">Enviar documento</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="jornada-academica-GETapi-v1-me-academic-documents--erq_id-">
                                <a href="#jornada-academica-GETapi-v1-me-academic-documents--erq_id-">Status da leitura</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="jornada-academica-POSTapi-v1-me-academic-documents--document_erq_id--confirm">
                                <a href="#jornada-academica-POSTapi-v1-me-academic-documents--document_erq_id--confirm">Confirmar a leitura</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-painel-da-coordenacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="painel-da-coordenacao">
                    <a href="#painel-da-coordenacao">Painel da coordenação</a>
                </li>
                                    <ul id="tocify-subheader-painel-da-coordenacao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="painel-da-coordenacao-GETapi-v1-staff-insights-bottlenecks">
                                <a href="#painel-da-coordenacao-GETapi-v1-staff-insights-bottlenecks">Onde a turma empaca</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="painel-da-coordenacao-GETapi-v1-staff-insights-cohorts">
                                <a href="#painel-da-coordenacao-GETapi-v1-staff-insights-cohorts">Quanto custa</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="painel-da-coordenacao-GETapi-v1-staff-insights-at-risk">
                                <a href="#painel-da-coordenacao-GETapi-v1-staff-insights-at-risk">Quem está perto do limite</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="painel-da-coordenacao-GETapi-v1-staff-insights-demand">
                                <a href="#painel-da-coordenacao-GETapi-v1-staff-insights-demand">O que está represado</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-tarefas-da-turma" class="tocify-header">
                <li class="tocify-item level-1" data-unique="tarefas-da-turma">
                    <a href="#tarefas-da-turma">Tarefas da turma</a>
                </li>
                                    <ul id="tocify-subheader-tarefas-da-turma" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="tarefas-da-turma-GETapi-v1-me-agenda">
                                <a href="#tarefas-da-turma-GETapi-v1-me-agenda">Minha agenda</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="tarefas-da-turma-POSTapi-v1-tasks">
                                <a href="#tarefas-da-turma-POSTapi-v1-tasks">Criar tarefa</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="tarefas-da-turma-POSTapi-v1-tasks--task_tsk_id--adopt">
                                <a href="#tarefas-da-turma-POSTapi-v1-tasks--task_tsk_id--adopt">Adotar tarefa da turma</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="tarefas-da-turma-PATCHapi-v1-tasks--task_tsk_id--status">
                                <a href="#tarefas-da-turma-PATCHapi-v1-tasks--task_tsk_id--status">Mudar o status</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: September 19, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>API REST do CampusOS — catálogo acadêmico, jornada do aluno e acervo compartilhado entre veteranos e calouros. Multi-tenant por instituição de ensino.</p>
<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="acervo-do-veterano">Acervo do veterano</h1>

    <p>O caderno pessoal do aluno (organizador do 5.1) e o acervo que atravessa
turmas (o 5.2) são o MESMO recurso — o que muda é só <code>visibility</code>. Toda
leitura já passa pelo <code>NoteVisibilityScope</code>: o que este controller devolve é
exatamente o que o usuário autenticado tem direito de ver, sem <code>if</code> nenhum
aqui.</p>

                                <h2 id="acervo-do-veterano-GETapi-v1-notes">Listar notas</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>As próprias (qualquer visibilidade) mais o que outros compartilharam com
este usuário, pela escada de visibilidade. Filtre por <code>subject_id</code> para
ver o acervo de uma disciplina específica — é a tela do 5.2.</p>

<span id="example-requests-GETapi-v1-notes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/notes?subject_id=01a0b823-b7d7-72d8-8db9-810d0e28d9c7" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/notes"
);

const params = {
    "subject_id": "01a0b823-b7d7-72d8-8db9-810d0e28d9c7",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-notes">
            <blockquote>
            <p>Example response (200, acervo de uma disciplina):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: &quot;01a0&hellip;&quot;,
            &quot;title&quot;: &quot;Resumo da P2&quot;,
            &quot;kind&quot;: &quot;summary&quot;,
            &quot;visibility&quot;: &quot;subject&quot;,
            &quot;author&quot;: {
                &quot;id&quot;: &quot;01a0&hellip;&quot;,
                &quot;name&quot;: &quot;Um Veterano&quot;
            },
            &quot;term&quot;: {
                &quot;year&quot;: 2024,
                &quot;period&quot;: 1
            }
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-notes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-notes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-notes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-notes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-notes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-notes" data-method="GET"
      data-path="api/v1/notes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-notes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-notes"
                    onclick="tryItOut('GETapi-v1-notes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-notes"
                    onclick="cancelTryOut('GETapi-v1-notes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-notes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/notes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="subject_id"                data-endpoint="GETapi-v1-notes"
               value="01a0b823-b7d7-72d8-8db9-810d0e28d9c7"
               data-component="query">
    <br>
<p>O acervo de uma disciplina específica. Example: <code>01a0b823-b7d7-72d8-8db9-810d0e28d9c7</code></p>
            </div>
                </form>

                    <h2 id="acervo-do-veterano-POSTapi-v1-notes">Criar nota</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Nasce privada — publicar é um passo separado (<code>.../visibility</code>).</p>

<span id="example-requests-POSTapi-v1-notes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/notes" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"title\": \"Resumo da P2\",
    \"body_md\": \"architecto\",
    \"kind\": \"summary\",
    \"subject_id\": \"01a0b823-b7d7-72d8-8db9-810d0e28d9c7\",
    \"offering_id\": \"architecto\",
    \"course_id\": \"architecto\",
    \"term_id\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/notes"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "Resumo da P2",
    "body_md": "architecto",
    "kind": "summary",
    "subject_id": "01a0b823-b7d7-72d8-8db9-810d0e28d9c7",
    "offering_id": "architecto",
    "course_id": "architecto",
    "term_id": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-notes">
            <blockquote>
            <p>Example response (201, criada):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;title&quot;: &quot;Resumo da P2&quot;,
        &quot;visibility&quot;: &quot;private&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-notes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-notes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-notes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-notes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-notes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-notes" data-method="POST"
      data-path="api/v1/notes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-notes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-notes"
                    onclick="tryItOut('POSTapi-v1-notes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-notes"
                    onclick="cancelTryOut('POSTapi-v1-notes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-notes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/notes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-v1-notes"
               value="Resumo da P2"
               data-component="body">
    <br>
<p>Example: <code>Resumo da P2</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>body_md</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="body_md"                data-endpoint="POSTapi-v1-notes"
               value="architecto"
               data-component="body">
    <br>
<p>Conteúdo em markdown. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>kind</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="kind"                data-endpoint="POSTapi-v1-notes"
               value="summary"
               data-component="body">
    <br>
<p>summary, past_exam, solved_exercise_list, material ou tip. Example: <code>summary</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="subject_id"                data-endpoint="POSTapi-v1-notes"
               value="01a0b823-b7d7-72d8-8db9-810d0e28d9c7"
               data-component="body">
    <br>
<p>A disciplina, se houver. Example: <code>01a0b823-b7d7-72d8-8db9-810d0e28d9c7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>offering_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="offering_id"                data-endpoint="POSTapi-v1-notes"
               value="architecto"
               data-component="body">
    <br>
<p>A turma, se houver. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>course_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course_id"                data-endpoint="POSTapi-v1-notes"
               value="architecto"
               data-component="body">
    <br>
<p>O curso, se houver. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>term_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="term_id"                data-endpoint="POSTapi-v1-notes"
               value="architecto"
               data-component="body">
    <br>
<p>O semestre em que foi escrita, se houver. Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="acervo-do-veterano-GETapi-v1-notes--nte_id-">Ver uma nota</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-v1-notes--nte_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/notes/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/notes/architecto"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-notes--nte_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-notes--nte_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-notes--nte_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-notes--nte_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-notes--nte_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-notes--nte_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-notes--nte_id-" data-method="GET"
      data-path="api/v1/notes/{nte_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-notes--nte_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-notes--nte_id-"
                    onclick="tryItOut('GETapi-v1-notes--nte_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-notes--nte_id-"
                    onclick="cancelTryOut('GETapi-v1-notes--nte_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-notes--nte_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/notes/{nte_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-notes--nte_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-notes--nte_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>nte_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="nte_id"                data-endpoint="GETapi-v1-notes--nte_id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the nte. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="acervo-do-veterano-POSTapi-v1-notes--note_nte_id--visibility">Mudar a visibilidade</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Publicar (subir na escada) ou despublicar (voltar para <code>private</code>, que
sempre funciona, sem pré-condição nenhuma). Só o autor pode chamar.</p>

<span id="example-requests-POSTapi-v1-notes--note_nte_id--visibility">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/notes/architecto/visibility" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"visibility\": \"subject\",
    \"subject_id\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/notes/architecto/visibility"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "visibility": "subject",
    "subject_id": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-notes--note_nte_id--visibility">
            <blockquote>
            <p>Example response (200, publicada):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;visibility&quot;: &quot;subject&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, não é o autor):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Esta nota n&atilde;o &eacute; sua.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-notes--note_nte_id--visibility" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-notes--note_nte_id--visibility"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-notes--note_nte_id--visibility"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-notes--note_nte_id--visibility" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-notes--note_nte_id--visibility">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-notes--note_nte_id--visibility" data-method="POST"
      data-path="api/v1/notes/{note_nte_id}/visibility"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-notes--note_nte_id--visibility', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-notes--note_nte_id--visibility"
                    onclick="tryItOut('POSTapi-v1-notes--note_nte_id--visibility');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-notes--note_nte_id--visibility"
                    onclick="cancelTryOut('POSTapi-v1-notes--note_nte_id--visibility');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-notes--note_nte_id--visibility"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/notes/{note_nte_id}/visibility</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-notes--note_nte_id--visibility"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-notes--note_nte_id--visibility"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>note_nte_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="note_nte_id"                data-endpoint="POSTapi-v1-notes--note_nte_id--visibility"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the note nte. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>visibility</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="visibility"                data-endpoint="POSTapi-v1-notes--note_nte_id--visibility"
               value="subject"
               data-component="body">
    <br>
<p>private, offering, subject, course ou institution. Example: <code>subject</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="subject_id"                data-endpoint="POSTapi-v1-notes--note_nte_id--visibility"
               value="architecto"
               data-component="body">
    <br>
<p>Obrigatório se a nota ainda não tiver disciplina e o alvo exigir uma. Example: <code>architecto</code></p>
        </div>
        </form>

                <h1 id="autenticacao">Autenticação</h1>

    <p>Login por e-mail e senha, devolvendo um token Sanctum. O token vai no header
<code>Authorization: Bearer &lt;token&gt;</code> de toda rota autenticada — e é dele que o
sistema descobre a instituição do usuário, nunca de um parâmetro do cliente.</p>

                                <h2 id="autenticacao-POSTapi-v1-auth-login">Entrar</h2>

<p>
</p>

<p>Autentica e devolve o token. O e-mail é único <strong>por instituição</strong>: se o
mesmo endereço existir em mais de uma, responde 422 pedindo <code>entity_id</code>
em vez de escolher uma.</p>

<span id="example-requests-POSTapi-v1-auth-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"aluno@alunos.utfpr.edu.br\",
    \"password\": \"campusos\",
    \"entity_id\": null,
    \"device\": \"iphone-felipe\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "aluno@alunos.utfpr.edu.br",
    "password": "campusos",
    "entity_id": null,
    "device": "iphone-felipe"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-login">
            <blockquote>
            <p>Example response (200, sucesso):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;name&quot;: &quot;Felipe Kurt Pohling&quot;,
        &quot;email&quot;: &quot;aluno@alunos.utfpr.edu.br&quot;,
        &quot;role&quot;: &quot;student&quot;,
        &quot;role_label&quot;: &quot;Estudante&quot;,
        &quot;registration_number&quot;: &quot;2567857&quot;,
        &quot;entity_id&quot;: &quot;01a0&hellip;&quot;,
        &quot;enabled_modules&quot;: []
    },
    &quot;token&quot;: &quot;1|abc&hellip;&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, credenciais inválidas):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Credenciais inv&aacute;lidas.&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;Credenciais inv&aacute;lidas.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-login" data-method="POST"
      data-path="api/v1/auth/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-login"
                    onclick="tryItOut('POSTapi-v1-auth-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-login"
                    onclick="cancelTryOut('POSTapi-v1-auth-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-login"
               value="aluno@alunos.utfpr.edu.br"
               data-component="body">
    <br>
<p>E-mail institucional. Example: <code>aluno@alunos.utfpr.edu.br</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-login"
               value="campusos"
               data-component="body">
    <br>
<p>A senha. Example: <code>campusos</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>entity_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="entity_id"                data-endpoint="POSTapi-v1-auth-login"
               value=""
               data-component="body">
    <br>
<p>Só quando o e-mail existe em mais de uma instituição.</p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device"                data-endpoint="POSTapi-v1-auth-login"
               value="iphone-felipe"
               data-component="body">
    <br>
<p>Rótulo do token, para revogar só este dispositivo depois. Example: <code>iphone-felipe</code></p>
        </div>
        </form>

                    <h2 id="autenticacao-POSTapi-v1-auth-signup">Cadastrar (aluno)</h2>

<p>
</p>

<p>Cadastro livre — sem escolher universidade numa lista. A instituição é
resolvida pelo <strong>domínio do e-mail</strong>; sem um domínio habilitado para
aquele endereço, o cadastro é recusado. Devolve o token <strong>imediatamente</strong>
(acesso já liberado) e dispara um e-mail com um código de 6 dígitos para
confirmar o endereço — que não bloqueia nada, é só a camada de segurança.</p>

<span id="example-requests-POSTapi-v1-auth-signup">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/signup" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"Felipe Kurt Pohling\",
    \"email\": \"aluno@alunos.utfpr.edu.br\",
    \"password\": \"senha-forte-123\",
    \"password_confirmation\": \"senha-forte-123\",
    \"device\": \"iphone-felipe\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/signup"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "Felipe Kurt Pohling",
    "email": "aluno@alunos.utfpr.edu.br",
    "password": "senha-forte-123",
    "password_confirmation": "senha-forte-123",
    "device": "iphone-felipe"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-signup">
            <blockquote>
            <p>Example response (201, cadastrado):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;name&quot;: &quot;Felipe Kurt Pohling&quot;,
        &quot;email&quot;: &quot;aluno@alunos.utfpr.edu.br&quot;,
        &quot;role&quot;: &quot;student&quot;,
        &quot;email_verified&quot;: false
    },
    &quot;token&quot;: &quot;1|abc&hellip;&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, domínio não habilitado):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Use seu e-mail institucional &mdash; este dom&iacute;nio n&atilde;o est&aacute; habilitado para cadastro.&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;Use seu e-mail institucional &mdash; este dom&iacute;nio n&atilde;o est&aacute; habilitado para cadastro.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-signup" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-signup"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-signup"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-signup" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-signup">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-signup" data-method="POST"
      data-path="api/v1/auth/signup"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-signup', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-signup"
                    onclick="tryItOut('POSTapi-v1-auth-signup');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-signup"
                    onclick="cancelTryOut('POSTapi-v1-auth-signup');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-signup"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/signup</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-signup"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-signup"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-auth-signup"
               value="Felipe Kurt Pohling"
               data-component="body">
    <br>
<p>Nome completo. Example: <code>Felipe Kurt Pohling</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-signup"
               value="aluno@alunos.utfpr.edu.br"
               data-component="body">
    <br>
<p>E-mail institucional do aluno. Example: <code>aluno@alunos.utfpr.edu.br</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-signup"
               value="senha-forte-123"
               data-component="body">
    <br>
<p>Mínimo 8 caracteres. Example: <code>senha-forte-123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-v1-auth-signup"
               value="senha-forte-123"
               data-component="body">
    <br>
<p>Repita a senha. Example: <code>senha-forte-123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device"                data-endpoint="POSTapi-v1-auth-signup"
               value="iphone-felipe"
               data-component="body">
    <br>
<p>Rótulo do token. Example: <code>iphone-felipe</code></p>
        </div>
        </form>

                    <h2 id="autenticacao-GETapi-v1-auth-me">Quem sou eu</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>O usuário do token atual. É o que a SPA chama no boot para saber que tela abrir.</p>

<span id="example-requests-GETapi-v1-auth-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/auth/me" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/me"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-auth-me">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-auth-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-auth-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-auth-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-auth-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-auth-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-auth-me" data-method="GET"
      data-path="api/v1/auth/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-auth-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-auth-me"
                    onclick="tryItOut('GETapi-v1-auth-me');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-auth-me"
                    onclick="cancelTryOut('GETapi-v1-auth-me');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-auth-me"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/auth/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-auth-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-auth-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="autenticacao-POSTapi-v1-auth-logout">Sair</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Revoga <strong>apenas o token atual</strong> — os outros dispositivos do usuário seguem logados.</p>

<span id="example-requests-POSTapi-v1-auth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-logout">
            <blockquote>
            <p>Example response (204, sucesso):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-logout" data-method="POST"
      data-path="api/v1/auth/logout"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-logout"
                    onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-logout"
                    onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="autenticacao-POSTapi-v1-auth-verify-email">Confirmar e-mail</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Verifica o código de 6 dígitos enviado no cadastro. Não bloqueia acesso —
é uma marca de segurança, não um portão.</p>

<span id="example-requests-POSTapi-v1-auth-verify-email">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/verify-email" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"code\": \"482913\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/verify-email"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "code": "482913"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-verify-email">
            <blockquote>
            <p>Example response (200, verificado):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;email_verified&quot;: true
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422, código inválido):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;C&oacute;digo inv&aacute;lido.&quot;,
    &quot;errors&quot;: {
        &quot;code&quot;: [
            &quot;C&oacute;digo inv&aacute;lido.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-verify-email" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-verify-email"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-verify-email"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-verify-email" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-verify-email">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-verify-email" data-method="POST"
      data-path="api/v1/auth/verify-email"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-verify-email', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-verify-email"
                    onclick="tryItOut('POSTapi-v1-auth-verify-email');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-verify-email"
                    onclick="cancelTryOut('POSTapi-v1-auth-verify-email');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-verify-email"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/verify-email</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-verify-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-verify-email"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="code"                data-endpoint="POSTapi-v1-auth-verify-email"
               value="482913"
               data-component="body">
    <br>
<p>O código de 6 dígitos recebido por e-mail. Example: <code>482913</code></p>
        </div>
        </form>

                    <h2 id="autenticacao-POSTapi-v1-auth-verify-email-resend">Reenviar código de verificação</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>O código anterior pode ter expirado (15 minutos) — gera um novo e reenvia.</p>

<span id="example-requests-POSTapi-v1-auth-verify-email-resend">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/verify-email/resend" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/verify-email/resend"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-verify-email-resend">
            <blockquote>
            <p>Example response (204, reenviado):</p>
        </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-verify-email-resend" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-verify-email-resend"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-verify-email-resend"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-verify-email-resend" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-verify-email-resend">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-verify-email-resend" data-method="POST"
      data-path="api/v1/auth/verify-email/resend"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-verify-email-resend', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-verify-email-resend"
                    onclick="tryItOut('POSTapi-v1-auth-verify-email-resend');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-verify-email-resend"
                    onclick="cancelTryOut('POSTapi-v1-auth-verify-email-resend');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-verify-email-resend"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/verify-email/resend</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-verify-email-resend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-verify-email-resend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="autenticacao-POSTapi-v1-auth-mcp-token">Token do copiloto (B8)</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Um token SEPARADO do login, com o menor escopo possível — <code>mcp:read</code>
sempre, <code>mcp:write</code> só se pedido (habilita a única tool de escrita,
<code>criar_anotacao</code>). Chamar de novo troca o token anterior: nunca existe
mais de um token de copiloto por vez.</p>

<span id="example-requests-POSTapi-v1-auth-mcp-token">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/mcp-token" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"allow_write\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/mcp-token"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "allow_write": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-mcp-token">
            <blockquote>
            <p>Example response (200, só leitura):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;2|xyz&hellip;&quot;,
    &quot;abilities&quot;: [
        &quot;mcp:read&quot;
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (200, leitura e escrita):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;2|xyz&hellip;&quot;,
    &quot;abilities&quot;: [
        &quot;mcp:read&quot;,
        &quot;mcp:write&quot;
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-auth-mcp-token" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-mcp-token"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-mcp-token"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-mcp-token" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-mcp-token">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-mcp-token" data-method="POST"
      data-path="api/v1/auth/mcp-token"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-mcp-token', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-mcp-token"
                    onclick="tryItOut('POSTapi-v1-auth-mcp-token');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-mcp-token"
                    onclick="cancelTryOut('POSTapi-v1-auth-mcp-token');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-mcp-token"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/mcp-token</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-mcp-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-mcp-token"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>allow_write</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-auth-mcp-token" style="display: none">
            <input type="radio" name="allow_write"
                   value="true"
                   data-endpoint="POSTapi-v1-auth-mcp-token"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-auth-mcp-token" style="display: none">
            <input type="radio" name="allow_write"
                   value="false"
                   data-endpoint="POSTapi-v1-auth-mcp-token"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Habilita a tool de escrita. Default: false. Example: <code>false</code></p>
        </div>
        </form>

                <h1 id="catalogo-academico">Catálogo acadêmico</h1>

    <p>Curso, matriz curricular e as disciplinas por período. Dado mestre da
instituição: muda por resolução de colegiado, nunca por ação de aluno.</p>

                                <h2 id="catalogo-academico-GETapi-v1-courses">Listar cursos</h2>

<p>
</p>

<p>Todos os cursos da instituição, com o câmpus de cada um.</p>

<span id="example-requests-GETapi-v1-courses">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/courses" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/courses"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-courses">
            <blockquote>
            <p>Example response (200, UTFPR):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: &quot;01a0&hellip;&quot;,
            &quot;code&quot;: &quot;25&quot;,
            &quot;name&quot;: &quot;Bacharelado em Sistemas de Informa&ccedil;&atilde;o&quot;,
            &quot;degree&quot;: &quot;bachelor&quot;,
            &quot;degree_label&quot;: &quot;Bacharelado&quot;,
            &quot;shift&quot;: &quot;evening&quot;,
            &quot;shift_label&quot;: &quot;Noturno&quot;,
            &quot;campus&quot;: {
                &quot;id&quot;: &quot;01a0&hellip;&quot;,
                &quot;code&quot;: &quot;FB&quot;,
                &quot;name&quot;: &quot;Francisco Beltr&atilde;o&quot;
            }
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-courses" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-courses"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-courses"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-courses" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-courses">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-courses" data-method="GET"
      data-path="api/v1/courses"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-courses', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-courses"
                    onclick="tryItOut('GETapi-v1-courses');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-courses"
                    onclick="cancelTryOut('GETapi-v1-courses');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-courses"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/courses</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-courses"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-courses"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="catalogo-academico-GETapi-v1-courses--course_crs_id--curriculum">Matriz curricular do curso</h2>

<p>
</p>

<p>A matriz ATIVA do curso, com as disciplinas agrupadas por período, os
pré-requisitos de cada uma e o bloco de cargas horárias.</p>
<p><code>workload.total_hours</code> é <strong>calculado</strong>, não armazenado:
<code>mandatory + elective + standalone_extension</code>. As horas extensionistas
embutidas nas disciplinas (<code>extension_hours</code>) são uma exigência
<strong>ortogonal</strong> e por isso <code>extension_counts_in_total</code> é <code>false</code> — somá-las
inventaria horas que o aluno não precisa cursar.</p>

<span id="example-requests-GETapi-v1-courses--course_crs_id--curriculum">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/courses/01a0b860-2cbf-7061-b1fc-ffce925b44c6/curriculum" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/courses/01a0b860-2cbf-7061-b1fc-ffce925b44c6/curriculum"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-courses--course_crs_id--curriculum">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Informe o header X-Tenant-Id (placeholder de autentica&ccedil;&atilde;o do template).&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, curso sem matriz ativa):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Curso n&atilde;o tem matriz ativa.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-courses--course_crs_id--curriculum" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-courses--course_crs_id--curriculum"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-courses--course_crs_id--curriculum"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-courses--course_crs_id--curriculum" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-courses--course_crs_id--curriculum">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-courses--course_crs_id--curriculum" data-method="GET"
      data-path="api/v1/courses/{course_crs_id}/curriculum"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-courses--course_crs_id--curriculum', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-courses--course_crs_id--curriculum"
                    onclick="tryItOut('GETapi-v1-courses--course_crs_id--curriculum');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-courses--course_crs_id--curriculum"
                    onclick="cancelTryOut('GETapi-v1-courses--course_crs_id--curriculum');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-courses--course_crs_id--curriculum"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/courses/{course_crs_id}/curriculum</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-courses--course_crs_id--curriculum"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-courses--course_crs_id--curriculum"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>course_crs_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course_crs_id"                data-endpoint="GETapi-v1-courses--course_crs_id--curriculum"
               value="01a0b860-2cbf-7061-b1fc-ffce925b44c6"
               data-component="url">
    <br>
<p>The ID of the course crs. Example: <code>01a0b860-2cbf-7061-b1fc-ffce925b44c6</code></p>
            </div>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>course</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="course"                data-endpoint="GETapi-v1-courses--course_crs_id--curriculum"
               value="01a0b823-b7d7-72d8-8db9-810d0e28d9c7"
               data-component="url">
    <br>
<p>O id do curso. Example: <code>01a0b823-b7d7-72d8-8db9-810d0e28d9c7</code></p>
            </div>
                    </form>

                <h1 id="elegibilidade-e-simulacao">Elegibilidade e simulação</h1>

    <p>B8, primeira esticada do desafio 5.1: "compreensão dos pré-requisitos",
"planejamento dos próximos períodos" e "impactos de reprovações" — puro
cálculo sobre o histórico e a matriz que já existem, nenhuma tabela nova.</p>

                                <h2 id="elegibilidade-e-simulacao-GETapi-v1-me-next-term">O que libera e o que trava</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>As disciplinas que o aluno ainda não cursa nem cumpriu, separadas em
liberadas (pode pegar já) e travadas — cada travada com o motivo
explícito, não só a etiqueta.</p>

<span id="example-requests-GETapi-v1-me-next-term">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/me/next-term" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/next-term"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-next-term">
            <blockquote>
            <p>Example response (200, com travas):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;current_period&quot;: 6,
        &quot;eligible&quot;: [
            {
                &quot;code&quot;: &quot;ES52C&quot;,
                &quot;name&quot;: &quot;Engenharia de Software II&quot;
            }
        ],
        &quot;blocked&quot;: [
            {
                &quot;code&quot;: &quot;ES62A&quot;,
                &quot;name&quot;: &quot;Compiladores&quot;,
                &quot;blocked_by&quot;: [
                    {
                        &quot;type&quot;: &quot;subject&quot;,
                        &quot;subject&quot;: {
                            &quot;code&quot;: &quot;LIP301&quot;,
                            &quot;name&quot;: &quot;Linguagens Formais&quot;
                        },
                        &quot;reason&quot;: &quot;Precisa ter aprovado Linguagens Formais.&quot;
                    }
                ]
            }
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me-next-term" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-next-term"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-next-term"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-next-term" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-next-term">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-next-term" data-method="GET"
      data-path="api/v1/me/next-term"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-next-term', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me-next-term"
                    onclick="tryItOut('GETapi-v1-me-next-term');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me-next-term"
                    onclick="cancelTryOut('GETapi-v1-me-next-term');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me-next-term"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/next-term</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-next-term"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-next-term"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="elegibilidade-e-simulacao-POSTapi-v1-me-simulate">E se eu reprovar? — simulação</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Sem escrita no banco: clona o histórico marcando as disciplinas
informadas como reprovadas e recalcula a primeira oportunidade de
cada disciplina pendente sobre o mesmo grafo de pré-requisitos.</p>

<span id="example-requests-POSTapi-v1-me-simulate">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/me/simulate" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"fail\": [
        \"MAT034\"
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/simulate"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "fail": [
        "MAT034"
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-simulate">
            <blockquote>
            <p>Example response (200, com impacto em cascata):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;failed&quot;: [
            &quot;MAT034&quot;
        ],
        &quot;current_period&quot;: 6,
        &quot;affected_subjects&quot;: [
            {
                &quot;subject&quot;: {
                    &quot;code&quot;: &quot;WBE501&quot;,
                    &quot;name&quot;: &quot;Desenvolvimento Web Back-End&quot;
                },
                &quot;real_earliest_period&quot;: 6,
                &quot;simulated_earliest_period&quot;: 7,
                &quot;delay_terms&quot;: 1
            }
        ],
        &quot;estimated_graduation_delay_terms&quot;: 1
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-me-simulate" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-simulate"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-simulate"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-simulate" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-simulate">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-simulate" data-method="POST"
      data-path="api/v1/me/simulate"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-simulate', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-simulate"
                    onclick="tryItOut('POSTapi-v1-me-simulate');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-simulate"
                    onclick="cancelTryOut('POSTapi-v1-me-simulate');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-simulate"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/simulate</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-simulate"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-simulate"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>fail</code></b>&nbsp;&nbsp;
<small>string[]</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="fail[0]"                data-endpoint="POSTapi-v1-me-simulate"
               data-component="body">
        <input type="text" style="display: none"
               name="fail[1]"                data-endpoint="POSTapi-v1-me-simulate"
               data-component="body">
    <br>
<p>Os códigos das disciplinas a simular como reprovadas.</p>
        </div>
        </form>

                <h1 id="horas-complementares">Horas complementares</h1>

    <p>O tracker pessoal do aluno para atividades complementares (B7) — teto por
categoria e certificado guardado. Puramente informativo: não muda
<code>GET /me/progress</code> (ver docs/dominio/HORAS_COMPLEMENTARES.md).</p>

                                <h2 id="horas-complementares-GETapi-v1-me-complementary-activities">Minhas atividades complementares</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>A lista mais o resumo por categoria (<code>summary</code>): quanto foi declarado,
quanto conta depois do teto, e quanto foi perdido.</p>

<span id="example-requests-GETapi-v1-me-complementary-activities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/me/complementary-activities" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/complementary-activities"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-complementary-activities">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me-complementary-activities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-complementary-activities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-complementary-activities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-complementary-activities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-complementary-activities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-complementary-activities" data-method="GET"
      data-path="api/v1/me/complementary-activities"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-complementary-activities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me-complementary-activities"
                    onclick="tryItOut('GETapi-v1-me-complementary-activities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me-complementary-activities"
                    onclick="cancelTryOut('GETapi-v1-me-complementary-activities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me-complementary-activities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/complementary-activities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-complementary-activities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-complementary-activities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="horas-complementares-POSTapi-v1-me-complementary-activities">Declarar atividade complementar</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Sem fila de homologação nesta entrega — o contador anda na hora, com
<code>capped</code>/<code>hours_not_counted</code> avisando se a categoria estourou o teto.</p>

<span id="example-requests-POSTapi-v1-me-complementary-activities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/me/complementary-activities" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "category_id=01a0b823-b7d7-72d8-8db9-810d0e28d9c7"\
    --form "title=Semana Acadêmica de Sistemas de Informação"\
    --form "hours_claimed=20"\
    --form "issued_at=architecto"\
    --form "certificate=@/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/phpevk8tvak7op3a3CcmwC" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/complementary-activities"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('category_id', '01a0b823-b7d7-72d8-8db9-810d0e28d9c7');
body.append('title', 'Semana Acadêmica de Sistemas de Informação');
body.append('hours_claimed', '20');
body.append('issued_at', 'architecto');
body.append('certificate', document.querySelector('input[name="certificate"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-complementary-activities">
            <blockquote>
            <p>Example response (201, dentro do teto):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;hours_claimed&quot;: 20
    },
    &quot;capped&quot;: false,
    &quot;hours_not_counted&quot;: 0
}</code>
 </pre>
            <blockquote>
            <p>Example response (201, estourou o teto):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;hours_claimed&quot;: 46
    },
    &quot;capped&quot;: true,
    &quot;hours_not_counted&quot;: 46
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-me-complementary-activities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-complementary-activities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-complementary-activities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-complementary-activities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-complementary-activities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-complementary-activities" data-method="POST"
      data-path="api/v1/me/complementary-activities"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-complementary-activities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-complementary-activities"
                    onclick="tryItOut('POSTapi-v1-me-complementary-activities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-complementary-activities"
                    onclick="cancelTryOut('POSTapi-v1-me-complementary-activities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-complementary-activities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/complementary-activities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>category_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="category_id"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="01a0b823-b7d7-72d8-8db9-810d0e28d9c7"
               data-component="body">
    <br>
<p>Example: <code>01a0b823-b7d7-72d8-8db9-810d0e28d9c7</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="Semana Acadêmica de Sistemas de Informação"
               data-component="body">
    <br>
<p>Example: <code>Semana Acadêmica de Sistemas de Informação</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>hours_claimed</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="hours_claimed"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="20"
               data-component="body">
    <br>
<p>Example: <code>20</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>issued_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="issued_at"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value="architecto"
               data-component="body">
    <br>
<p>Data do certificado (ISO 8601). Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>certificate</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="certificate"                data-endpoint="POSTapi-v1-me-complementary-activities"
               value=""
               data-component="body">
    <br>
<p>O certificado (PDF, PNG ou JPG). Example: <code>/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/phpevk8tvak7op3a3CcmwC</code></p>
        </div>
        </form>

                <h1 id="jornada-academica">Jornada acadêmica</h1>

    <p>A progressão da graduação do aluno autenticado — o endpoint que sozinho
desenha a tela principal do produto.</p>

                                <h2 id="jornada-academica-GETapi-v1-me-progress">Minha progressão</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Quanto falta para formar, por faixa de carga horária, com o que ainda
está pendente e a previsão de formatura pelo ritmo <strong>real</strong> do aluno.</p>
<p><code>overall.required_hours</code> é <strong>calculado</strong> — obrigatórias + optativas +
extensão autônoma — e <code>extension.counts_in_total</code> é <code>false</code> de propósito:
a carga extensionista exigida pelo curso mora <em>dentro</em> das disciplinas e
é verificada em paralelo. Somá-la inventaria horas que o aluno não
precisa cursar.</p>

<span id="example-requests-GETapi-v1-me-progress">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/me/progress" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/progress"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-progress">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404, sem vínculo):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Voc&ecirc; ainda n&atilde;o tem v&iacute;nculo acad&ecirc;mico. Envie seu hist&oacute;rico escolar.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me-progress" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-progress"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-progress"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-progress" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-progress">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-progress" data-method="GET"
      data-path="api/v1/me/progress"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-progress', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me-progress"
                    onclick="tryItOut('GETapi-v1-me-progress');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me-progress"
                    onclick="cancelTryOut('GETapi-v1-me-progress');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me-progress"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/progress</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-progress"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="jornada-academica-POSTapi-v1-me-academic-documents">Enviar documento</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Aceita o <strong>histórico escolar</strong> (reconstitui a graduação inteira) ou o
<strong>requerimento de matrícula</strong> (atualiza o semestre corrente). Responde
<strong>202</strong>: a leitura roda em segundo plano, e o front consulta o status.</p>

<span id="example-requests-POSTapi-v1-me-academic-documents">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/me/academic-documents" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "kind=transcript"\
    --form "file=@/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/php322fa5avjf3g7Cw991O" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/academic-documents"
);

const headers = {
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('kind', 'transcript');
body.append('file', document.querySelector('input[name="file"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-academic-documents">
            <blockquote>
            <p>Example response (202, recebido):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;status&quot;: &quot;uploaded&quot;,
        &quot;status_label&quot;: &quot;Recebido&quot;,
        &quot;is_pending&quot;: true
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-me-academic-documents" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-academic-documents"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-academic-documents"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-academic-documents" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-academic-documents">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-academic-documents" data-method="POST"
      data-path="api/v1/me/academic-documents"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-academic-documents', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-academic-documents"
                    onclick="tryItOut('POSTapi-v1-me-academic-documents');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-academic-documents"
                    onclick="cancelTryOut('POSTapi-v1-me-academic-documents');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-academic-documents"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/academic-documents</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-academic-documents"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-academic-documents"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>kind</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="kind"                data-endpoint="POSTapi-v1-me-academic-documents"
               value="transcript"
               data-component="body">
    <br>
<p><code>transcript</code> ou <code>enrollment_request</code>. Example: <code>transcript</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="file"                data-endpoint="POSTapi-v1-me-academic-documents"
               value=""
               data-component="body">
    <br>
<p>PDF ou foto (PNG/JPG), até 20 MB. Example: <code>/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/php322fa5avjf3g7Cw991O</code></p>
        </div>
        </form>

                    <h2 id="jornada-academica-GETapi-v1-me-academic-documents--erq_id-">Status da leitura</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>O que a tela de conferência consome. Enquanto <code>is_pending</code> for <code>true</code>, o
front continua consultando; quando vira <code>parsed</code>, <code>extraction</code> traz o que
foi lido para o aluno revisar.</p>

<span id="example-requests-GETapi-v1-me-academic-documents--erq_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/me/academic-documents/01a0b896-25f9-7171-b291-5b5a8eba8aef" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/academic-documents/01a0b896-25f9-7171-b291-5b5a8eba8aef"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-academic-documents--erq_id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me-academic-documents--erq_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-academic-documents--erq_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-academic-documents--erq_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-academic-documents--erq_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-academic-documents--erq_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-academic-documents--erq_id-" data-method="GET"
      data-path="api/v1/me/academic-documents/{erq_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-academic-documents--erq_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me-academic-documents--erq_id-"
                    onclick="tryItOut('GETapi-v1-me-academic-documents--erq_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me-academic-documents--erq_id-"
                    onclick="cancelTryOut('GETapi-v1-me-academic-documents--erq_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me-academic-documents--erq_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/academic-documents/{erq_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-academic-documents--erq_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-academic-documents--erq_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>erq_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="erq_id"                data-endpoint="GETapi-v1-me-academic-documents--erq_id-"
               value="01a0b896-25f9-7171-b291-5b5a8eba8aef"
               data-component="url">
    <br>
<p>The ID of the erq. Example: <code>01a0b896-25f9-7171-b291-5b5a8eba8aef</code></p>
            </div>
                    </form>

                    <h2 id="jornada-academica-POSTapi-v1-me-academic-documents--document_erq_id--confirm">Confirmar a leitura</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Recebe as linhas <strong>já revisadas pelo aluno</strong> e cria as matrículas. Linha
cujo código não existe no catálogo volta em <code>pending</code> — nunca derruba a
importação inteira, porque histórico real tem disciplina extinta.</p>

<span id="example-requests-POSTapi-v1-me-academic-documents--document_erq_id--confirm">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/me/academic-documents/01a0b896-25f9-7171-b291-5b5a8eba8aef/confirm" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"registration_id\": \"architecto\",
    \"lines\": [
        {
            \"code\": \"ARC102\",
            \"year\": 2023,
            \"period\": 1,
            \"status\": \"Aprovado Por Nota\\/Frequência\",
            \"grade\": 8.5,
            \"attendance\": 91.2
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/academic-documents/01a0b896-25f9-7171-b291-5b5a8eba8aef/confirm"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "registration_id": "architecto",
    "lines": [
        {
            "code": "ARC102",
            "year": 2023,
            "period": 1,
            "status": "Aprovado Por Nota\/Frequência",
            "grade": 8.5,
            "attendance": 91.2
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-me-academic-documents--document_erq_id--confirm">
            <blockquote>
            <p>Example response (200, confirmado):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;status&quot;: &quot;confirmed&quot;
    },
    &quot;imported&quot;: 46,
    &quot;pending&quot;: [
        {
            &quot;code&quot;: &quot;XYZ999&quot;,
            &quot;reason&quot;: &quot;Disciplina n&atilde;o est&aacute; no cat&aacute;logo do curso.&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-me-academic-documents--document_erq_id--confirm" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-me-academic-documents--document_erq_id--confirm"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-me-academic-documents--document_erq_id--confirm"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-me-academic-documents--document_erq_id--confirm" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-me-academic-documents--document_erq_id--confirm">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-me-academic-documents--document_erq_id--confirm" data-method="POST"
      data-path="api/v1/me/academic-documents/{document_erq_id}/confirm"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-me-academic-documents--document_erq_id--confirm', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-me-academic-documents--document_erq_id--confirm"
                    onclick="tryItOut('POSTapi-v1-me-academic-documents--document_erq_id--confirm');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-me-academic-documents--document_erq_id--confirm"
                    onclick="cancelTryOut('POSTapi-v1-me-academic-documents--document_erq_id--confirm');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-me-academic-documents--document_erq_id--confirm"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/me/academic-documents/{document_erq_id}/confirm</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>document_erq_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="document_erq_id"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="01a0b896-25f9-7171-b291-5b5a8eba8aef"
               data-component="url">
    <br>
<p>The ID of the document erq. Example: <code>01a0b896-25f9-7171-b291-5b5a8eba8aef</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>registration_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="registration_id"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="architecto"
               data-component="body">
    <br>
<p>O vínculo que recebe as matrículas. Omitido, nasce do cabeçalho do documento (curso, RA, ingresso). Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>lines</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>As linhas conferidas.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="lines.0.code"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="ARC102"
               data-component="body">
    <br>
<p>Código da disciplina. Example: <code>ARC102</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>year</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="lines.0.year"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="2023"
               data-component="body">
    <br>
<p>Example: <code>2023</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>period</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="lines.0.period"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="1"
               data-component="body">
    <br>
<p>1 ou 2. Example: <code>1</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="lines.0.status"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="Aprovado Por Nota/Frequência"
               data-component="body">
    <br>
<p>O texto da situação. Example: <code>Aprovado Por Nota/Frequência</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>grade</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="lines.0.grade"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="8.5"
               data-component="body">
    <br>
<p>Nota de 0 a 10. Example: <code>8.5</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>attendance</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="lines.0.attendance"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="91.2"
               data-component="body">
    <br>
<p>Frequência em %. Example: <code>91.2</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                <h1 id="painel-da-coordenacao">Painel da coordenação</h1>

    <p>B8, segunda esticada do desafio 5.1: agregados ANÔNIMOS pra quem coordena
— nunca aluno nomeado (ver docs/dominio/PAINEL_COORDENACAO.md). <code>insights</code>
não tem tabela própria: tudo aqui vem de <code>AcademicStatsProvider</code>, contrato
do <code>core</code> implementado no <code>journey</code>.</p>
<p>O escopo (curso × instituição) é resolvido AQUI, a partir do usuário
autenticado — nunca de um <code>course_id</code> que o cliente mande. Coordenador
enxerga o próprio curso (<code>usr.course_crs_id</code>); <code>institution_admin</code> enxerga
a instituição inteira (<code>courseId: null</code>, o <code>EntityScope</code> já faz o resto).</p>

                                <h2 id="painel-da-coordenacao-GETapi-v1-staff-insights-bottlenecks">Onde a turma empaca</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Taxa de reprovação por disciplina, nota e falta separadas, nos últimos
<code>last_terms</code> termos com matrícula. Disciplinas com menos de 5
tentativas resolvidas não aparecem (piso de anonimato).</p>

<span id="example-requests-GETapi-v1-staff-insights-bottlenecks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/staff/insights/bottlenecks?last_terms=4" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/staff/insights/bottlenecks"
);

const params = {
    "last_terms": "4",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-staff-insights-bottlenecks">
            <blockquote>
            <p>Example response (200, com gargalo):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;subject_code&quot;: &quot;MAT029&quot;,
            &quot;subject_name&quot;: &quot;C&aacute;lculo 2&quot;,
            &quot;total_attempts&quot;: 40,
            &quot;failed_grade&quot;: 15,
            &quot;failed_absence&quot;: 6,
            &quot;failed_both&quot;: 0,
            &quot;failure_rate&quot;: 0.525
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-staff-insights-bottlenecks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-staff-insights-bottlenecks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-staff-insights-bottlenecks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-staff-insights-bottlenecks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-staff-insights-bottlenecks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-staff-insights-bottlenecks" data-method="GET"
      data-path="api/v1/staff/insights/bottlenecks"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-staff-insights-bottlenecks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-staff-insights-bottlenecks"
                    onclick="tryItOut('GETapi-v1-staff-insights-bottlenecks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-staff-insights-bottlenecks"
                    onclick="cancelTryOut('GETapi-v1-staff-insights-bottlenecks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-staff-insights-bottlenecks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/staff/insights/bottlenecks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-staff-insights-bottlenecks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-staff-insights-bottlenecks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>last_terms</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="last_terms"                data-endpoint="GETapi-v1-staff-insights-bottlenecks"
               value="4"
               data-component="query">
    <br>
<p>Quantos termos recentes considerar. Default: 4. Example: <code>4</code></p>
            </div>
                </form>

                    <h2 id="painel-da-coordenacao-GETapi-v1-staff-insights-cohorts">Quanto custa</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Atraso médio (em termos além do previsto pela matriz) entre os
vínculos ativos de cada coorte de ingresso. Coortes com menos de 5
vínculos ativos não aparecem (piso de anonimato).</p>

<span id="example-requests-GETapi-v1-staff-insights-cohorts">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/staff/insights/cohorts" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/staff/insights/cohorts"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-staff-insights-cohorts">
            <blockquote>
            <p>Example response (200, com atraso):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;entry_term&quot;: &quot;2022/1&quot;,
            &quot;active_registrations&quot;: 38,
            &quot;avg_delay_terms&quot;: 2.4
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-staff-insights-cohorts" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-staff-insights-cohorts"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-staff-insights-cohorts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-staff-insights-cohorts" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-staff-insights-cohorts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-staff-insights-cohorts" data-method="GET"
      data-path="api/v1/staff/insights/cohorts"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-staff-insights-cohorts', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-staff-insights-cohorts"
                    onclick="tryItOut('GETapi-v1-staff-insights-cohorts');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-staff-insights-cohorts"
                    onclick="cancelTryOut('GETapi-v1-staff-insights-cohorts');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-staff-insights-cohorts"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/staff/insights/cohorts</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-staff-insights-cohorts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-staff-insights-cohorts"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="painel-da-coordenacao-GETapi-v1-staff-insights-at-risk">Quem está perto do limite</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Quantidade de vínculos ativos cuja previsão de formatura cabe em
<code>within</code> termos ou menos até o prazo de integralização.</p>

<span id="example-requests-GETapi-v1-staff-insights-at-risk">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/staff/insights/at-risk?within=2" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/staff/insights/at-risk"
);

const params = {
    "within": "2",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-staff-insights-at-risk">
            <blockquote>
            <p>Example response (200, ok):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;count&quot;: 12,
        &quot;within_terms&quot;: 2
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-staff-insights-at-risk" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-staff-insights-at-risk"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-staff-insights-at-risk"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-staff-insights-at-risk" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-staff-insights-at-risk">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-staff-insights-at-risk" data-method="GET"
      data-path="api/v1/staff/insights/at-risk"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-staff-insights-at-risk', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-staff-insights-at-risk"
                    onclick="tryItOut('GETapi-v1-staff-insights-at-risk');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-staff-insights-at-risk"
                    onclick="cancelTryOut('GETapi-v1-staff-insights-at-risk');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-staff-insights-at-risk"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/staff/insights/at-risk</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-staff-insights-at-risk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-staff-insights-at-risk"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>within</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="within"                data-endpoint="GETapi-v1-staff-insights-at-risk"
               value="2"
               data-component="query">
    <br>
<p>Quantos termos até o prazo contam como "perto". Default: 2. Example: <code>2</code></p>
            </div>
                </form>

                    <h2 id="painel-da-coordenacao-GETapi-v1-staff-insights-demand">O que está represado</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Quantos vínculos ativos já estão elegíveis para cada disciplina ainda
não cursada — planejamento de oferta direto, sem chute. Disciplinas
com menos de 5 elegíveis não aparecem (piso de anonimato).</p>

<span id="example-requests-GETapi-v1-staff-insights-demand">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/staff/insights/demand" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/staff/insights/demand"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-staff-insights-demand">
            <blockquote>
            <p>Example response (200, com demanda):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;subject_code&quot;: &quot;ES52C&quot;,
            &quot;subject_name&quot;: &quot;Engenharia de Software II&quot;,
            &quot;demand&quot;: 22
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-staff-insights-demand" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-staff-insights-demand"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-staff-insights-demand"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-staff-insights-demand" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-staff-insights-demand">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-staff-insights-demand" data-method="GET"
      data-path="api/v1/staff/insights/demand"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-staff-insights-demand', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-staff-insights-demand"
                    onclick="tryItOut('GETapi-v1-staff-insights-demand');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-staff-insights-demand"
                    onclick="cancelTryOut('GETapi-v1-staff-insights-demand');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-staff-insights-demand"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/staff/insights/demand</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-staff-insights-demand"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-staff-insights-demand"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="tarefas-da-turma">Tarefas da turma</h1>

    <p>B6 do desafio 5.2: uma pessoa cadastra um prazo na oferta, a turma inteira
enxerga na própria agenda (<code>GET /me/agenda</code>) e decide se adota.</p>

                                <h2 id="tarefas-da-turma-GETapi-v1-me-agenda">Minha agenda</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Pendências próprias mais as tarefas que a turma compartilhou nas
ofertas em que você está matriculado <strong>no termo corrente</strong>, ainda não
adotadas (<code>adopted: false</code>) — ordenado por prazo.</p>

<span id="example-requests-GETapi-v1-me-agenda">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/me/agenda" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/agenda"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-me-agenda">
            <blockquote>
            <p>Example response (200, com tarefa da turma pendente de adoção):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: &quot;01a0&hellip;&quot;,
            &quot;title&quot;: &quot;Prova 2&quot;,
            &quot;due_at&quot;: &quot;2026-10-14T23:59:00-03:00&quot;,
            &quot;visibility&quot;: &quot;offering&quot;,
            &quot;adopted&quot;: false
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-me-agenda" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-me-agenda"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-me-agenda"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-me-agenda" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-me-agenda">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-me-agenda" data-method="GET"
      data-path="api/v1/me/agenda"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-me-agenda', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-me-agenda"
                    onclick="tryItOut('GETapi-v1-me-agenda');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-me-agenda"
                    onclick="cancelTryOut('GETapi-v1-me-agenda');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-me-agenda"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/me/agenda</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-me-agenda"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-me-agenda"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="tarefas-da-turma-POSTapi-v1-tasks">Criar tarefa</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Ao contrário da nota, a tarefa não nasce sempre privada: informe
<code>visibility: offering</code> + <code>offering_id</code> para já cadastrar direto na
turma.</p>

<span id="example-requests-POSTapi-v1-tasks">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/tasks" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"title\": \"Prova 2\",
    \"description_md\": \"architecto\",
    \"kind\": \"exam\",
    \"due_at\": \"2026-10-14T23:59:00-03:00\",
    \"visibility\": \"architecto\",
    \"subject_id\": \"architecto\",
    \"offering_id\": \"architecto\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/tasks"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "title": "Prova 2",
    "description_md": "architecto",
    "kind": "exam",
    "due_at": "2026-10-14T23:59:00-03:00",
    "visibility": "architecto",
    "subject_id": "architecto",
    "offering_id": "architecto"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-tasks">
            <blockquote>
            <p>Example response (201, criada na turma):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;title&quot;: &quot;Prova 2&quot;,
        &quot;visibility&quot;: &quot;offering&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-tasks" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-tasks"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-tasks"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-tasks" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-tasks">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-tasks" data-method="POST"
      data-path="api/v1/tasks"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-tasks', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-tasks"
                    onclick="tryItOut('POSTapi-v1-tasks');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-tasks"
                    onclick="cancelTryOut('POSTapi-v1-tasks');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-tasks"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/tasks</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-tasks"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>title</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="title"                data-endpoint="POSTapi-v1-tasks"
               value="Prova 2"
               data-component="body">
    <br>
<p>Example: <code>Prova 2</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>description_md</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="description_md"                data-endpoint="POSTapi-v1-tasks"
               value="architecto"
               data-component="body">
    <br>
<p>Conteúdo em markdown. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>kind</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="kind"                data-endpoint="POSTapi-v1-tasks"
               value="exam"
               data-component="body">
    <br>
<p>exam, assignment, reading ou personal. Example: <code>exam</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>due_at</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="due_at"                data-endpoint="POSTapi-v1-tasks"
               value="2026-10-14T23:59:00-03:00"
               data-component="body">
    <br>
<p>Data limite (ISO 8601). Example: <code>2026-10-14T23:59:00-03:00</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>visibility</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="visibility"                data-endpoint="POSTapi-v1-tasks"
               value="architecto"
               data-component="body">
    <br>
<p>private, offering, subject, course ou institution. Default: private. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>subject_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="subject_id"                data-endpoint="POSTapi-v1-tasks"
               value="architecto"
               data-component="body">
    <br>
<p>A disciplina, se houver. Example: <code>architecto</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>offering_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="offering_id"                data-endpoint="POSTapi-v1-tasks"
               value="architecto"
               data-component="body">
    <br>
<p>A turma — obrigatório se visibility=offering. Example: <code>architecto</code></p>
        </div>
        </form>

                    <h2 id="tarefas-da-turma-POSTapi-v1-tasks--task_tsk_id--adopt">Adotar tarefa da turma</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Cria uma cópia privada apontando pra origem — sem afetar a tarefa
original nem quem mais adotou.</p>

<span id="example-requests-POSTapi-v1-tasks--task_tsk_id--adopt">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/tasks/architecto/adopt" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/tasks/architecto/adopt"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-tasks--task_tsk_id--adopt">
            <blockquote>
            <p>Example response (200, adotada):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;visibility&quot;: &quot;private&quot;,
        &quot;origin_task_id&quot;: &quot;01a0&hellip;&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, não compartilhada):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Esta tarefa n&atilde;o foi compartilhada com a turma.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-v1-tasks--task_tsk_id--adopt" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-tasks--task_tsk_id--adopt"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-tasks--task_tsk_id--adopt"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-tasks--task_tsk_id--adopt" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-tasks--task_tsk_id--adopt">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-tasks--task_tsk_id--adopt" data-method="POST"
      data-path="api/v1/tasks/{task_tsk_id}/adopt"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-tasks--task_tsk_id--adopt', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-tasks--task_tsk_id--adopt"
                    onclick="tryItOut('POSTapi-v1-tasks--task_tsk_id--adopt');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-tasks--task_tsk_id--adopt"
                    onclick="cancelTryOut('POSTapi-v1-tasks--task_tsk_id--adopt');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-tasks--task_tsk_id--adopt"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/tasks/{task_tsk_id}/adopt</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-tasks--task_tsk_id--adopt"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-tasks--task_tsk_id--adopt"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_tsk_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="task_tsk_id"                data-endpoint="POSTapi-v1-tasks--task_tsk_id--adopt"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the task tsk. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="tarefas-da-turma-PATCHapi-v1-tasks--task_tsk_id--status">Mudar o status</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Só o dono da tarefa (a cópia adotada é sua desde a adoção).</p>

<span id="example-requests-PATCHapi-v1-tasks--task_tsk_id--status">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://localhost/api/v1/tasks/architecto/status" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"done\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/tasks/architecto/status"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "done"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PATCHapi-v1-tasks--task_tsk_id--status">
            <blockquote>
            <p>Example response (200, concluída):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: &quot;01a0&hellip;&quot;,
        &quot;status&quot;: &quot;done&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403, não é o dono):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Esta tarefa n&atilde;o &eacute; sua.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-v1-tasks--task_tsk_id--status" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-v1-tasks--task_tsk_id--status"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-v1-tasks--task_tsk_id--status"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-v1-tasks--task_tsk_id--status" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-v1-tasks--task_tsk_id--status">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-v1-tasks--task_tsk_id--status" data-method="PATCH"
      data-path="api/v1/tasks/{task_tsk_id}/status"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-v1-tasks--task_tsk_id--status', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-v1-tasks--task_tsk_id--status"
                    onclick="tryItOut('PATCHapi-v1-tasks--task_tsk_id--status');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-v1-tasks--task_tsk_id--status"
                    onclick="cancelTryOut('PATCHapi-v1-tasks--task_tsk_id--status');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-v1-tasks--task_tsk_id--status"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/v1/tasks/{task_tsk_id}/status</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-v1-tasks--task_tsk_id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-v1-tasks--task_tsk_id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>task_tsk_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="task_tsk_id"                data-endpoint="PATCHapi-v1-tasks--task_tsk_id--status"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the task tsk. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PATCHapi-v1-tasks--task_tsk_id--status"
               value="done"
               data-component="body">
    <br>
<p>todo, doing ou done. Example: <code>done</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
