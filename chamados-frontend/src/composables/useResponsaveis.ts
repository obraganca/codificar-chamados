import { ref } from 'vue'
import { responsaveisApi } from '@/api/responsaveis'
import type { Responsavel } from '@/types/models'

export function useResponsaveis() {
  const responsaveis = ref<Responsavel[]>([])
  const carregando = ref(false)

  async function carregar(): Promise<void> {
    carregando.value = true
    try {
      const resposta = await responsaveisApi.listar()
      responsaveis.value = resposta.data
    } finally {
      carregando.value = false
    }
  }

  return { responsaveis, carregando, carregar }
}
