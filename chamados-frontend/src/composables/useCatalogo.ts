import { ref } from 'vue'
import { catalogoApi } from '@/api/catalogo'
import type { Categoria, Tag } from '@/types/models'

export function useCatalogo() {
  const categorias = ref<Categoria[]>([])
  const tags = ref<Tag[]>([])

  async function carregar(): Promise<void> {
    const [c, t] = await Promise.all([catalogoApi.categorias(), catalogoApi.tags()])
    categorias.value = c.data
    tags.value = t.data
  }

  return { categorias, tags, carregar }
}