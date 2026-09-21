import { api } from './client'
import type { Notificacao } from '@/types/models'

interface ListaResposta {
  data: Notificacao[]
  meta: { nao_lidas: number; [key: string]: unknown }
}

export const notificacoesApi = {
  listar: (query: { nao_lidas?: boolean; page?: number } = {}) =>
    api.get<ListaResposta>('/notificacoes', query),

  marcarLida: (id: string) =>
    api.post<{ data: Notificacao; meta: { nao_lidas: number } }>(`/notificacoes/${id}/lida`),

  marcarTodasLidas: () =>
    api.post<{ meta: { nao_lidas: number } }>('/notificacoes/marcar-todas-lidas'),
}