# Chamados API

API (Laravel 11) do Sistema de Controle de Chamados Internos. Esta aplicação
**não** serve nenhuma tela: ela expõe apenas JSON em `/api/v1/*`, notificações
em tempo real via WebSocket (Laravel Reverb) e e-mails transacionais. A
interface web vive em um projeto separado, `chamados-frontend` (Vue 3 SPA).

> Esta é a segunda versão deste desafio. A primeira era um monólito
> Laravel + Inertia.js (um único deploy, sem API JSON própria). Este README
> explica por que ela foi separada em API + frontend e quais decisões foram
> tomadas no processo — ver seção **"Por que separar em duas aplicações?"**.

## Stack

| Camada             | Tecnologia                                         |
|---------------------|-----------------------------------------------------|
| Framework           | Laravel 11 (PHP 8.3)                                |
| Autenticação        | Laravel Sanctum (tokens Bearer, sem sessão)         |
| Banco               | MySQL 8.4                                           |
| Cache / Lock / Fila | Redis 7                                             |
| Tempo real          | Laravel Reverb (WebSocket próprio, protocolo Pusher)|
| E-mail (dev)        | Mailpit (captura local, sem enviar de verdade)      |
| Infra local         | Docker + Docker Compose                             |
| Testes              | PHPUnit (SQLite em memória, fila `sync`, mail `array`) |
| Contrato de API     | OpenAPI 3 (`openapi.yaml`)                          |

## Por que separar em duas aplicações?

A versão anterior deste projeto argumentava a favor de Inertia.js exatamente
para **evitar** ter que manter uma API JSON própria. Isso continua sendo um
argumento válido para times muito pequenos e um único produto. Como o
requisito atual pede explicitamente API + frontend separados, a separação foi
feita, mas os riscos que a versão Inertia evitava foram endereçados de forma
deliberada, não ignorados:

- **Tipos duplicados entre back e front** → resolvido com um contrato OpenAPI
  (`openapi.yaml`) versionado *neste* repositório como fonte única de
  verdade. O frontend gera tipos TypeScript a partir dele
  (`npm run generate:types` no projeto `chamados-frontend`), então um campo
  renomeado ou removido aqui quebra o *build* do frontend imediatamente, em
  vez de quebrar silenciosamente em produção.
- **Autenticação de API própria** → usamos Laravel Sanctum com tokens Bearer
  (não o modo "SPA cookie" do Sanctum). Escolhido porque as duas aplicações
  podem ser implantadas em domínios/portas totalmente diferentes sem exigir
  configuração de cookies cross-site; o trade-off é que o token fica guardado
  no frontend (ver decisão equivalente no README do frontend).
- **Versionamento da API** → todas as rotas ficam sob `/api/v1`, para poder
  evoluir o contrato no futuro sem quebrar clientes existentes.

## Modelagem e regras de negócio

- **`users`**: conta de login (`role`: `admin`, `agente` ou `usuario`).
- **`responsaveis`**: nome, e-mail, e um `user_id` opcional (1:1) que liga o
  responsável a uma conta de login — é essa ligação que permite o
  responsável acessar o chat e receber notificações/e-mails.
- **`categorias`** e **`tags`**: catálogo simples (nome + cor, no caso da
  categoria) usado para classificar chamados. Tags têm relação N:N com
  chamados (`ticket_tag`).
- **`tickets`** (chamado): título, descrição, prioridade
  (baixa/média/alta/urgente), status
  (`aberto`/`em_andamento`/`resolvido`/`fechado`), `categoria_id` (nullable),
  `responsavel_id` (nullable, aponta para `responsaveis`), `solicitante_id`
  (quem abriu), `aberto_em`.
- **`ticket_messages`**: chat por chamado — usado por quem abriu o chamado e
  pelo responsável (via seu `user_id`) para conversar sobre o caso.
- **Chamado "em aberto"** (para fins de balanceamento de carga) = status
  `aberto` ou `em_andamento`. Ver `Ticket::STATUS_ABERTOS`.
- **Distribuição automática** (`app/Services/TicketAssignmentService.php`):
  escolhe o responsável com menos chamados em aberto, com lock distribuído
  via Redis (`Cache::lock`) para evitar condição de corrida entre duas
  atribuições automáticas simultâneas. Em empate, vence o responsável de
  menor id (determinístico e testável).

## Notificações, e-mail e tempo real

Toda mudança relevante em um chamado passa pelo `TicketObserver`, que
detecta o que mudou (`getDirty()`) e dispara os eventos `TicketCreated` /
`TicketUpdated`. Os listeners correspondentes (`app/Listeners/*`) então:

1. Gravam uma notificação no banco (canal `database`, tabela
   `notifications`) — alimenta o sininho de notificações no frontend.
2. Fazem *broadcast* via Reverb (canal privado `App.Models.User.{id}` para
   notificações pessoais, `tickets.chat.{ticketId}` para o chat) — chega em
   tempo real em quem estiver com a tela aberta, sem precisar recarregar.
3. Enviam um e-mail (Mailable correspondente em `app/Mail/*`), capturado
   pelo Mailpit em desenvolvimento.

Eventos cobertos: chamado criado, chamado atribuído a um responsável,
chamado resolvido, chamado atualizado (campos genéricos) e nova mensagem no
chat. Quem executou a ação (`atorId`) não recebe notificação da própria
ação.

**Importante para quem for mexer no código**: `SendTicketUpdatedNotifications`
e os Mailables usam `ShouldQueue`, ou seja, o envio passa pelo worker de fila
(`queue-worker`), um processo PHP de longa duração. Alterações em código só
valem para jobs novos depois que o worker for reiniciado
(`docker compose restart queue-worker`).

## Autenticação

- `POST /api/v1/auth/registrar` e `POST /api/v1/auth/login` retornam
  `{ user, token }`. O frontend envia o token em
  `Authorization: Bearer <token>` em todas as chamadas subsequentes.
- `POST /api/v1/auth/logout` revoga apenas o token da requisição atual (não
  desloga o usuário de outros dispositivos/abas).
- Tokens expiram em 7 dias (`config/sanctum.php`).
- Usuários de teste (via seeder), todos com senha `password`:

  | E-mail                        | Papel   | Observação                          |
  |--------------------------------|---------|---------------------------------------|
  | `admin@codificar.com`          | admin   | Acessa qualquer chamado               |
  | `ana.souza@codificar.com`      | agente  | Também é `responsavel` (recebe chamados atribuídos) |
  | `bruno.lima@codificar.com`     | agente  | Idem                                  |
  | `carla.mendes@codificar.com`   | agente  | Idem                                  |
  | `maria@codificar.com`          | usuario | Solicitante de exemplo nos chamados seed |

## Endpoints principais

Contrato completo em [`openapi.yaml`](./openapi.yaml). Resumo:

| Método | Rota                                        | Descrição                                        |
|--------|----------------------------------------------|----------------------------------------------------|
| POST   | `/api/v1/auth/registrar`                      | Cria conta, retorna token                          |
| POST   | `/api/v1/auth/login`                          | Login, retorna token                               |
| POST   | `/api/v1/auth/logout`                         | Revoga o token atual                               |
| GET    | `/api/v1/auth/me`                             | Usuário autenticado                                |
| GET    | `/api/v1/dashboard`                           | Indicadores + chamados agrupados por status        |
| GET    | `/api/v1/responsaveis`                        | Lista com carga de chamados em aberto              |
| GET    | `/api/v1/categorias`                          | Catálogo de categorias                             |
| GET    | `/api/v1/tags`                                | Catálogo de tags                                   |
| GET    | `/api/v1/chamados`                            | Lista paginada, filtros por status/prioridade/responsável/categoria/busca |
| POST   | `/api/v1/chamados`                            | Cria (com `atribuicao_automatica: true` opcional)  |
| GET    | `/api/v1/chamados/{id}`                       | Exibe                                              |
| PUT    | `/api/v1/chamados/{id}`                       | Atualiza                                           |
| GET    | `/api/v1/chamados/{id}/messages`              | Lista mensagens do chat do chamado                 |
| POST   | `/api/v1/chamados/{id}/messages`              | Envia mensagem no chat do chamado                  |
| GET    | `/api/v1/notificacoes`                        | Lista notificações (`?nao_lidas=1` filtra)         |
| POST   | `/api/v1/notificacoes/{id}/lida`              | Marca uma notificação como lida                    |
| POST   | `/api/v1/notificacoes/marcar-todas-lidas`     | Marca todas como lidas                             |

Todas exceto registro/login exigem `Authorization: Bearer <token>`. O acesso
ao chat de um chamado (`GET`/`POST /messages`) é restrito a quem participa
dele (solicitante, responsável atual) e a administradores —
ver `Ticket::podeSerAcessadoPor()`.

## Como rodar

### Docker (recomendado)

```bash
cp .env.example .env
docker compose up -d --build
```

Isso sobe cinco serviços:

| Serviço        | Função                                              | Porta local |
|-----------------|------------------------------------------------------|-------------|
| `app`           | API HTTP (roda migrations + seed no start)            | `8080`      |
| `mysql`         | Banco de dados                                        | `3316`      |
| `redis`         | Cache, lock de atribuição automática e fila           | `6379`      |
| `queue-worker`  | Processa e-mails, notificações e broadcast (assíncrono)| —           |
| `reverb`        | Servidor WebSocket (tempo real)                        | `8081`      |
| `mailpit`       | Captura e-mails de desenvolvimento (não envia de verdade)| `8025` (UI), `1025` (SMTP) |

O serviço `app` já roda `composer install`, `migrate --force` e
`db:seed --force` automaticamente. A API fica em `http://localhost:8080`.

### Local (sem Docker)

Pré-requisitos: PHP 8.3+, Composer, MySQL, Redis rodando localmente.

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan reverb:start          # em um terminal separado, para tempo real
php artisan queue:work redis      # em outro terminal, para e-mails/notificações
php artisan serve                 # http://localhost:8000
```

Sem Docker, o Mailpit precisa ser instalado à parte (ou troque
`MAIL_MAILER` para `log` no `.env`, e os e-mails vão para
`storage/logs/laravel.log` em vez de uma interface web).

## Como testar (roteiro para avaliação)

### 1. Testes automatizados

```bash
docker compose exec app php artisan test
# ou, sem Docker:
php artisan test
```

Usam SQLite em memória, fila `sync` e mail `array` (`phpunit.xml`), sem
depender de MySQL/Redis/Mailpit rodando. Cobertura focada em: distribuição
automática (regra mais sensível do domínio), autenticação/token, CRUD de
chamados e suas validações, dashboard.

### 2. Fluxo funcional manual (via API)

Com os containers no ar (`docker compose up -d`):

```bash
# 1. Login com um usuário seed
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@codificar.com","password":"password"}'
# guarde o "token" retornado

TOKEN="cole_o_token_aqui"

# 2. Ver o dashboard
curl http://localhost:8080/api/v1/dashboard -H "Authorization: Bearer $TOKEN"

# 3. Criar um chamado com atribuição automática
curl -X POST http://localhost:8080/api/v1/chamados \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"titulo":"Teste do avaliador","descricao":"Verificando o fluxo","prioridade":"alta","atribuicao_automatica":true}'

# 4. Marcar como resolvido (troque {id} pelo id retornado acima)
curl -X PUT http://localhost:8080/api/v1/chamados/{id} \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"status":"resolvido"}'
```

### 3. Conferir e-mail e tempo real

- Abra `http://localhost:8025` (Mailpit): deve aparecer o e-mail de "chamado
  criado" e, se o ator for diferente do solicitante, o de "chamado
  resolvido"/"chamado atribuído".
- Rode `docker compose logs queue-worker -f` durante os passos acima para
  ver os jobs de notificação/e-mail sendo processados em tempo real.
- O canal de broadcast pode ser inspecionado com qualquer cliente Pusher
  apontando para `ws://localhost:8081`, usando `REVERB_APP_KEY` do `.env`
  (`chamados-key`) — mas a forma mais simples de ver isso funcionando é
  rodar o frontend (`chamados-frontend`) e observar o sino de notificações
  e o chat do chamado atualizarem sem recarregar a página.

### 3.1 Como verificar se os e-mails estão sendo enviados (ou não)

E-mails são despachados de forma assíncrona pelo `queue-worker`
(Mailables e Notifications usam `ShouldQueue`). Isso significa que uma
resposta HTTP `200`/`201` da API **não garante** que o e-mail saiu — só
garante que o job foi colocado na fila. Para confirmar de verdade, siga
esta ordem:

1. **O job chegou a rodar?**
   ```bash
   docker compose logs queue-worker --tail 50 -f
   ```
   Cada notificação aparece como `RUNNING` e depois `DONE` (sucesso) ou
   `FAIL` (erro). Se nada aparecer, o worker pode estar parado —
   confira com `docker compose ps` se o container `queue-worker` está
   `Up`.

2. **Teve falha? Veja a exceção completa.**
   ```bash
   docker compose exec app php artisan queue:failed
   ```
   Lista os jobs que falharam, com UUID e horário. Para ver a *stack
   trace* completa de um deles (a causa real do erro fica na primeira
   linha, ex.: `View not found`, `Undefined variable`, erro de conexão
   SMTP):
   ```bash
   docker compose exec app php artisan tinker --execute="echo DB::table('failed_jobs')->latest('failed_at')->first()->exception;" | head -20
   ```

3. **O e-mail realmente chegou no Mailpit?**
   Abra `http://localhost:8025` — se o job apareceu como `DONE` no passo 1
   mas nada aparece aqui, o problema é de configuração de SMTP
   (`MAIL_HOST`/`MAIL_PORT` no `.env`), não de código. Confirme que
   `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit` e `MAIL_PORT=1025` — usar
   `localhost` no lugar do nome do serviço Docker (`mailpit`) é o erro
   mais comum aqui, já que os containers se enxergam pelo nome do
   serviço, não por `localhost`.

4. **Depois de corrigir algo no código**, sempre reinicie o worker antes
   de testar de novo — ele é um processo PHP de longa duração e não relê
   arquivos alterados sozinho:
   ```bash
   docker compose restart queue-worker
   docker compose exec app php artisan queue:retry all   # reprocessa os que falharam
   ```

5. **Teste isolado, sem depender de um fluxo completo da aplicação**:
   ```bash
   docker compose exec app php artisan tinker
   ```
   ```php
   Mail::raw('Teste', fn ($m) => $m->to('teste@exemplo.com')->subject('Teste'));
   ```
   Se isso aparecer no Mailpit, o transporte SMTP está OK e qualquer
   problema restante está nas Mailables/Notifications da aplicação
   (view faltando, variável errada, etc.), não na infraestrutura.

### 4. Contrato da API

O arquivo [`openapi.yaml`](./openapi.yaml) pode ser importado em qualquer
cliente (Insomnia, Postman, Swagger UI) para explorar todos os endpoints,
parâmetros e schemas de resposta.

## Decisões e trade-offs (transparência)

- **Sanctum com tokens Bearer, não cookies de sessão**: mais simples para
  duas aplicações em domínios/portas diferentes (sem CSRF/cookie cross-site
  para configurar), ao custo de o frontend precisar guardar o token
  manualmente. Ver contrapartida no README do frontend.
- **Sem exclusão de chamados** (`destroy`): mantido igual à versão anterior —
  o domínio de "chamados" normalmente não apaga histórico, apenas fecha.
- **Sem CRUD de responsáveis/categorias/tags**: fora do escopo pedido;
  endpoints de leitura existem só para alimentar selects e o dashboard.
- **`docker-compose` usa `php artisan serve`, não nginx + php-fpm**: uma peça
  a menos de infraestrutura para uma equipe pequena manter em
  desenvolvimento. Para produção, trocar o `command` do serviço `app` por
  `php-fpm` atrás de um nginx (ou Laravel Octane) é a recomendação.
- **OpenAPI escrito à mão, não gerado por annotations**: para um domínio deste
  tamanho, manter um `openapi.yaml` direto é mais simples e mais confiável do
  que anotações espalhadas pelos controllers; se a API crescer muito, vale
  migrar para geração automática (ex.: pacote `dedoc/scramble`).
- **Reverb em vez de Pusher/Ably**: servidor WebSocket próprio, sem
  dependência de um serviço terceiro pago, mantendo o protocolo compatível
  com Pusher (o frontend usa `laravel-echo` + `pusher-js` normalmente,
  apontando para o Reverb local).

## Bibliotecas de terceiros

- `laravel/sanctum` — autenticação por token.
- `laravel/reverb` — servidor WebSocket para notificações e chat em tempo real.
- `predis/predis` — client Redis (lock da distribuição automática, fila, cache).