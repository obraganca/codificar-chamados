import type { components } from './api'

// Aliases amigaveis para os schemas gerados a partir de openapi.yaml. Nunca
// declare esses formatos manualmente em outro lugar do app: se a API mudar
// um campo, `npm run generate:types` atualiza `api.d.ts` e o TypeScript
// aponta exatamente onde o frontend quebrou.
export type User = components['schemas']['User']
export type Responsavel = components['schemas']['Responsavel']
export type TicketBase = components['schemas']['Ticket']
export type TicketInput = components['schemas']['TicketInput']
export type ValidationError = components['schemas']['ValidationError']

export type Status = TicketBase['status']
export type Prioridade = TicketBase['prioridade']

export const STATUSES: Status[] = ['aberto', 'em_andamento', 'resolvido', 'fechado']
export const PRIORIDADES: Prioridade[] = ['baixa', 'media', 'alta']

export const STATUS_LABELS: Record<Status, string> = {
  aberto: 'Aberto',
  em_andamento: 'Em andamento',
  resolvido: 'Resolvido',
  fechado: 'Fechado',
}

export const PRIORIDADE_LABELS: Record<Prioridade, string> = {
  baixa: 'Baixa',
  media: 'Média',
  alta: 'Alta',
}

// Extensão do Ticket para incluir mensagens/histórico de chat do front-end
export interface Message {
  id: number
  ticket_id: number
  user_id: number
  user?: User
  content: string
  created_at: string
}

export interface Ticket extends TicketBase {
  messages?: Message[]
  user_id?: number
  user?: User
}

export interface PaginatedResponse<T> {
  data: T[]
  links: unknown
  meta: {
    current_page: number
    last_page: number
    total: number
    [key: string]: unknown
  }
}

export interface DashboardData {
  colunas: Record<Status, Ticket[]>
  responsaveis: Responsavel[]
  totais: {
    total: number
    em_aberto: number
    alta_prioridade: number
    sem_responsavel: number
  }
}

/**
 * Tipos auto-gerados via openapi-typescript
 */
export interface paths {
    "/auth/registrar": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        get?: never;
        put?: never;
        /** Cria uma conta e retorna um token de acesso */
        post: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody: {
                content: {
                    "application/json": {
                        name: string;
                        /** Format: email */
                        email: string;
                        /** Format: password */
                        password: string;
                        /** Format: password */
                        password_confirmation: string;
                    };
                };
            };
            responses: {
                /** @description Conta criada */
                201: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            user?: components["schemas"]["User"];
                            token?: string;
                        };
                    };
                };
                /** @description Erro de validacao */
                422: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": components["schemas"]["ValidationError"];
                    };
                };
            };
        };
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/auth/login": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        get?: never;
        put?: never;
        /** Autentica e retorna um token de acesso */
        post: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody: {
                content: {
                    "application/json": {
                        /** Format: email */
                        email: string;
                        /** Format: password */
                        password: string;
                    };
                };
            };
            responses: {
                /** @description Login realizado */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            user?: components["schemas"]["User"];
                            token?: string;
                        };
                    };
                };
                /** @description Credenciais invalidas */
                422: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": components["schemas"]["ValidationError"];
                    };
                };
            };
        };
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/auth/logout": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        get?: never;
        put?: never;
        /** Revoga o token atual */
        post: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description Logout realizado */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content?: never;
                };
                /** @description Nao autenticado */
                401: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content?: never;
                };
            };
        };
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/auth/me": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        /** Retorna o usuario autenticado */
        get: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description OK */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            user?: components["schemas"]["User"];
                        };
                    };
                };
                /** @description Nao autenticado */
                401: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content?: never;
                };
            };
        };
        put?: never;
        post?: never;
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/dashboard": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        /** Indicadores e chamados agrupados por status */
        get: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description OK */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            colunas?: {
                                [key: string]: components["schemas"]["Ticket"][];
                            };
                            responsaveis?: components["schemas"]["Responsavel"][];
                            totais?: {
                                total?: number;
                                em_aberto?: number;
                                alta_prioridade?: number;
                                sem_responsavel?: number;
                            };
                        };
                    };
                };
            };
        };
        put?: never;
        post?: never;
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/responsaveis": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        /** Lista responsaveis com a carga atual de chamados em aberto */
        get: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description OK */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            data?: components["schemas"]["Responsavel"][];
                        };
                    };
                };
            };
        };
        put?: never;
        post?: never;
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/chamados": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        /** Lista chamados (paginado e filtravel) */
        get: {
            parameters: {
                query?: {
                    status?: string;
                    prioridade?: string;
                    responsavel_id?: number;
                    busca?: string;
                    page?: number;
                };
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description OK */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            data?: components["schemas"]["Ticket"][];
                            links?: Record<string, never>;
                            meta?: Record<string, never>;
                        };
                    };
                };
            };
        };
        put?: never;
        /** Cria um chamado */
        post: {
            parameters: {
                query?: never;
                header?: never;
                path?: never;
                cookie?: never;
            };
            requestBody: {
                content: {
                    "application/json": components["schemas"]["TicketInput"];
                };
            };
            responses: {
                /** @description Criado */
                201: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            data?: components["schemas"]["Ticket"];
                        };
                    };
                };
                /** @description Erro de validacao */
                422: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": components["schemas"]["ValidationError"];
                    };
                };
            };
        };
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
    "/chamados/{id}": {
        parameters: {
            query?: never;
            header?: never;
            path?: never;
            cookie?: never;
        };
        /** Exibe um chamado */
        get: {
            parameters: {
                query?: never;
                header?: never;
                path: {
                    id: number;
                };
                cookie?: never;
            };
            requestBody?: never;
            responses: {
                /** @description OK */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            data?: components["schemas"]["Ticket"];
                        };
                    };
                };
                /** @description Nao encontrado */
                404: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content?: never;
                };
            };
        };
        /** Atualiza um chamado */
        put: {
            parameters: {
                query?: never;
                header?: never;
                path: {
                    id: number;
                };
                cookie?: never;
            };
            requestBody: {
                content: {
                    "application/json": components["schemas"]["TicketInput"];
                };
            };
            responses: {
                /** @description Atualizado */
                200: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": {
                            data?: components["schemas"]["Ticket"];
                        };
                    };
                };
                /** @description Erro de validacao */
                422: {
                    headers: {
                        [name: string]: unknown;
                    };
                    content: {
                        "application/json": components["schemas"]["ValidationError"];
                    };
                };
            };
        };
        post?: never;
        delete?: never;
        options?: never;
        head?: never;
        patch?: never;
        trace?: never;
    };
}
export type webhooks = Record<string, never>;
export interface components {
    schemas: {
        User: {
            id: number;
            name: string;
            /** Format: email */
            email: string;
        };
        Responsavel: {
            id: number;
            nome: string;
            /** Format: email */
            email: string;
            chamados_abertos_count?: number | null;
        };
        Ticket: {
            id: number;
            titulo: string;
            descricao: string;
            /** @enum {string} */
            prioridade: "baixa" | "media" | "alta";
            /** @enum {string} */
            status: "aberto" | "em_andamento" | "resolvido" | "fechado";
            /** Format: date-time */
            aberto_em?: string;
            /** Format: date-time */
            created_at?: string;
            /** Format: date-time */
            updated_at?: string;
            responsavel_id?: number | null;
            responsavel?: components["schemas"]["Responsavel"] | null;
        };
        TicketInput: {
            titulo: string;
            descricao: string;
            /** @enum {string} */
            prioridade: "baixa" | "media" | "alta";
            /** @enum {string} */
            status: "aberto" | "em_andamento" | "resolvido" | "fechado";
            responsavel_id?: number | null;
            /** @description Se true, ignora responsavel_id e atribui automaticamente quem tem menos chamados em aberto. */
            atribuicao_automatica?: boolean;
        };
        ValidationError: {
            message?: string;
            errors?: {
                [key: string]: string[];
            };
        };
    };
    responses: never;
    parameters: never;
    requestBodies: never;
    headers: never;
    pathItems: never;
}
export type $defs = Record<string, never>;
export type operations = Record<string, never>;