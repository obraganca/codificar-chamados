import { api } from './client'
import type { PaginatedResponse, Ticket, TicketInput } from '@/types/models'

export interface ListarChamadosFiltros {
  status?: string
  prioridade?: string
  responsavel_id?: number | string
  busca?: string
  page?: number
  [key: string]: string | number | boolean | undefined | null
}

export const ticketsApi = {
  listar: (filtros: ListarChamadosFiltros = {}) =>
    api.get<PaginatedResponse<Ticket>>('/chamados', filtros),

  exibir: (id: number | string) => api.get<{ data: Ticket }>(`/chamados/${id}`),

  criar: (payload: TicketInput) => api.post<{ data: Ticket }>('/chamados', payload),

  atualizar: (id: number | string, payload: TicketInput) =>
    api.put<{ data: Ticket }>(`/chamados/${id}`, payload),
}
