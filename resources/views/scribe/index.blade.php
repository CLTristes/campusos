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
                    <ul id="tocify-header-autenticacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="autenticacao">
                    <a href="#autenticacao">Autenticação</a>
                </li>
                                    <ul id="tocify-subheader-autenticacao" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-login">
                                <a href="#autenticacao-POSTapi-v1-auth-login">Entrar</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-GETapi-v1-auth-me">
                                <a href="#autenticacao-GETapi-v1-auth-me">Quem sou eu</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-logout">
                                <a href="#autenticacao-POSTapi-v1-auth-logout">Sair</a>
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
    --form "file=@/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/php44qk2r5siobq4HE5WqQ" </code></pre></div>


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
<p>PDF ou foto (PNG/JPG), até 20 MB. Example: <code>/private/var/folders/8h/z9rd082525gcjrf6hpb1cp900000gn/T/php44qk2r5siobq4HE5WqQ</code></p>
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
    --get "http://localhost/api/v1/me/academic-documents/architecto" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/me/academic-documents/architecto"
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
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the erq. Example: <code>architecto</code></p>
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
    "http://localhost/api/v1/me/academic-documents/architecto/confirm" \
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
    "http://localhost/api/v1/me/academic-documents/architecto/confirm"
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
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the document erq. Example: <code>architecto</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>registration_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="registration_id"                data-endpoint="POSTapi-v1-me-academic-documents--document_erq_id--confirm"
               value="architecto"
               data-component="body">
    <br>
<p>O vínculo que recebe as matrículas. Example: <code>architecto</code></p>
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
