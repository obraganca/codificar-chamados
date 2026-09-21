# Chamados Frontend

SPA (Vue 3 + TypeScript + Vite) do Sistema de Controle de Chamados Internos.
Consome a API do projeto irmão **`chamados-api`** — este projeto não tem
nenhum código de backend, só faz chamadas HTTP e conexão WebSocket.

## Stack e por que essas escolhas

| Camada          | Tecnologia                          | Por quê                                                                 |
|-----------------|--------------------------------------|--------------------------------------------------------------------------|
| Framework       | Vue 3 (`<script setup>`) + Vite      | Já era a stack do projeto original; build rápido, HMR instantâneo.       |
| Linguagem       | TypeScript                           | Necessário para o ganho real da geração de tipos a partir da API (ver abaixo). |
| Estado          | Pinia                                | Store oficial do Vue 3, API simples com Composition API.                |
| Roteamento      | Vue Router                           | Guards de rota para proteger telas autenticadas.                        |
| Tempo real      | `laravel-echo` + `pusher-js`         | Consome o servidor Reverb da API (protocolo compatível com Pusher) para chat e notificações ao vivo. |
| CSS             | **Tailwind CSS v4**                  | Framework utilitário moderno — telas organizadas e consistentes sem escrever CSS customizado nem montar um design system do zero. |
| Testes          | Vitest + @vue/test-utils             | Mesmo runtime do Vite, rápido, sem configuração extra.                  |

## Funcionalidades

- **Autenticação**: login e registro de conta.
- **Dashboard**: indicadores gerais e chamados agrupados por status.
- **Chamados**: listagem paginada com filtros (status, prioridade,
  responsável, categoria, busca), criação, edição e visualização detalhada.
- **Atribuição automática**: opção, ao criar/editar um chamado, de deixar a
  API escolher automaticamente o responsável com menos chamados em aberto.
- **Chat por chamado**: mensagens trocadas entre quem abriu o chamado e o
  responsável, atualizando em tempo real via WebSocket.
- **Notificações**: sino com contador de não lidas, toasts em tempo real
  quando um chamado é criado/atribuído/resolvido/atualizado, e uma lista
  completa com opção de marcar como lida (uma a uma ou todas de uma vez).

## Reduzindo o atrito entre frontend e backend

O maior risco de separar front e back em dois projetos é os dois times (ou a
mesma pessoa, em momentos diferentes) perderem a sincronia sobre o formato
dos dados. A solução adotada aqui:

1. A API publica um contrato **OpenAPI** (`../chamados-api/openapi.yaml`,
   copiado para `contract/openapi.yaml` neste projeto).
2. Este projeto gera tipos TypeScript automaticamente a partir dele:
   ```bash
   npm run generate:types
   ```
   Isso regenera `src/types/api.d.ts`. Os tipos usados em todo o app
   (`src/types/models.ts`) são apenas *aliases* desses tipos gerados — nunca
   redeclarados manualmente.
3. Resultado prático: se um campo de `Ticket` for renomeado ou removido na
   API, o `npm run build` (que roda `vue-tsc` antes do Vite) **quebra no
   frontend imediatamente**, apontando exatamente os arquivos afetados — em
   vez de o problema aparecer silenciosamente em produção.

Rode `npm run generate:types` sempre que `openapi.yaml` mudar no projeto da
API (e copie o arquivo atualizado para `contract/openapi.yaml`).

## Estrutura

```
src/
  api/          # client HTTP tipado + um módulo por recurso (auth, tickets, notificacoes, ...)
  components/   # componentes reutilizáveis (TicketForm, StatusBadge, NotificationBell, ...)
  composables/  # lógica reutilizável (useResponsaveis, useCatalogo)
  router/       # rotas e guards de autenticação
  services/     # cliente WebSocket (echo.ts, conexão com o Reverb da API)
  stores/       # estado global (Pinia) — auth e notifications
  types/        # tipos gerados da API + aliases usados pelo app
  views/        # telas (Auth, Dashboard, Tickets)
```

`TicketForm.vue` é o principal exemplo de componentização: o mesmo
formulário (com suas próprias validações e estado) é reaproveitado nas telas
de criação e edição de chamado, que só cuidam de buscar dados e decidir para
onde navegar depois do envio.

## Autenticação

Token Bearer (Sanctum, ver README da API), guardado em `localStorage` e
mandado em toda chamada via `Authorization: Bearer <token>` (`src/api/client.ts`).
**Trade-off assumido**: um token em `localStorage` é acessível via
JavaScript (risco de XSS), diferente de um cookie `httpOnly`. Optou-se por
isso porque a alternativa (cookies de sessão do Sanctum) exigiria as duas
aplicações compartilharem domínio/subdomínio ou lidar com cookies
cross-site — complexidade desnecessária para o escopo deste desafio. Para
produção real, mitigar com CSP rígida e, se possível, mover para cookie
`httpOnly` com um proxy same-origin na frente das duas aplicações.

O mesmo token também autoriza a conexão WebSocket: `src/services/echo.ts`
lê o token do `localStorage` a cada tentativa de assinatura de canal (em vez
de capturá-lo uma única vez no carregamento da página), então login/logout
refletem no tempo real sem precisar recarregar a aplicação.

## Como rodar

Pré-requisito: a API (`chamados-api`) rodando via Docker (`docker compose up
-d --build` no projeto irmão), que já sobe o backend, o banco, o Reverb
(WebSocket) e o Mailpit.

```bash
cp .env.example .env   # ajuste as variáveis se a API não estiver em localhost:8080
npm install
npm run dev             # http://localhost:5173
```

Variáveis de ambiente (`.env`):

| Variável                | Padrão                    | Descrição                                      |
|---------------------------|----------------------------|--------------------------------------------------|
| `VITE_API_URL`             | `http://localhost:8080/api/v1` | Base da API (com o prefixo `/api/v1`)        |
| `VITE_REVERB_APP_KEY`      | `chamados-key`             | Deve bater com `REVERB_APP_KEY` no `.env` da API |
| `VITE_REVERB_HOST`         | `localhost`                | Host do servidor Reverb                          |
| `VITE_REVERB_PORT`         | `8081`                     | Porta do servidor Reverb                         |
| `VITE_REVERB_SCHEME`       | `http`                     | `http` em dev; `https` atrás de TLS em produção  |

A API precisa liberar CORS para a origem deste frontend — confira
`FRONTEND_URLS` no `.env` da API (já vem com `http://localhost:5173` por
padrão).

## Como testar (roteiro para avaliação)

### 1. Testes automatizados

```bash
npm run test
```

Cobrem: o client HTTP (header de autenticação, tratamento de erro 422,
serialização de filtros), a store de autenticação (login/logout, persistência
do token) e os componentes de badge (status/prioridade). Focados em lógica e
contratos, não em detalhes visuais.

### 2. Build de produção (valida o contrato com a API)

```bash
npm run build   # type-check (vue-tsc) + build (vite) — falha se os tipos da API não baterem
```

Se a API estiver com um contrato diferente do `contract/openapi.yaml` deste
projeto, este comando é o primeiro a acusar o problema.

### 3. Fluxo funcional manual (com a API rodando)

1. Suba a API (`docker compose up -d --build` em `chamados-api`) e este
   frontend (`npm run dev`).
2. Acesse `http://localhost:5173/login` e entre com `admin@codificar.com` /
   `password` (outros usuários de teste estão listados no README da API).
3. No **Dashboard**, confira os indicadores e as colunas por status.
4. Em **Chamados → Novo chamado**, crie um chamado marcando "atribuição
   automática" — confirme que um responsável foi definido automaticamente.
5. Abra o chamado criado e envie uma mensagem no **chat**. Para ver o tempo
   real de fato, abra a mesma tela em duas abas (ou dois navegadores)
   logadas com usuários diferentes — uma mensagem enviada em uma aba deve
   aparecer na outra sem recarregar a página.
6. Marque o chamado como **resolvido** e observe o **sino de notificações**:
   um toast deve aparecer (se você não for o solicitante) e a lista de
   notificações deve atualizar em tempo real.
7. Confira o e-mail correspondente em `http://localhost:8025` (Mailpit da
   API).

## Decisões e trade-offs

- **Sem Vuex/Pinia para chamados**: o estado de chamados vive só nas views
  que precisam dele (`ref` local + composable), sem uma store global.
  Simples o suficiente para o tamanho do domínio; se o app crescer (cache
  entre telas, atualização otimista), migrar para uma store dedicada.
  Notificações têm store própria (`stores/notifications.ts`) porque
  precisam ser lidas de qualquer tela (o sino fica no layout global).
- **Sem biblioteca de formulários** (VeeValidate etc.): os formulários são
  pequenos o bastante para `reactive()` + validação vinda do backend
  (mensagens de erro por campo, exibidas via `FieldError.vue`). Evita uma
  dependência a mais para a equipe manter.
- **Tailwind via classes utilitárias direto no template**, sem componentes de
  design system prontos (ex.: PrimeVue): mantém o bundle pequeno e não exige
  aprender a API de uma lib de componentes para um escopo deste tamanho.
- **`pusher-js` como dependência, mesmo sem usar o Pusher de verdade**: é a
  biblioteca cliente que o `laravel-echo` espera quando o `broadcaster` é
  `'reverb'`, já que o Reverb implementa o mesmo protocolo do Pusher. Não há
  conta nem chave de um serviço terceiro envolvida — tudo aponta para o
  Reverb local da API.