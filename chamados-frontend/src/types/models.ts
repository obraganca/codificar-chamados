import type { components } from './api'

// Aliases amigaveis para os schemas gerados a partir de openapi.yaml. Nunca
// declare esses formatos manualmente em outro lugar do app: se a API mudar
// um campo, `npm run generate:types` atualiza `api.d.ts` e o TypeScript
// aponta exatamente onde o frontend quebrou.
export type User = components['schemas']['User']
export type Responsavel = components['schemas']['Responsavel']
export type Ticket = components['schemas']['Ticket']
export type TicketInput = components['schemas']['TicketInput']
export type ValidationError = components['schemas']['ValidationError']

export type Categoria = components['schemas']['Categoria']
export type Tag = components['schemas']['Tag']
export type Notificacao = components['schemas']['Notificacao']

export type Status = Ticket['status']
export type Prioridade = Ticket['prioridade']

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

export interface Message {
  id: number;
  ticket_id: number;
  user_id: number;
  user?: User;
  message: string;
  created_at: string;
}

