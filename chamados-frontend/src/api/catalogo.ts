import { api } from './client'
import type { Categoria, Tag } from '@/types/models'

export const catalogoApi = {
  categorias: () => api.get<{ data: Categoria[] }>('/categorias'),

  tags: () => api.get<{ data: Tag[] }>('/tags'),
}