import { api } from './client'
import type { Responsavel } from '@/types/models'

export const responsaveisApi = {
  listar: () => api.get<{ data: Responsavel[] }>('/responsaveis'),
}
