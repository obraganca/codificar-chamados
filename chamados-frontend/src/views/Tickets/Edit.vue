<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ticketsApi } from '@/api/tickets'
import { useResponsaveis } from '@/composables/useResponsaveis'
import { useCatalogo } from '@/composables/useCatalogo'
import TicketForm from '@/components/TicketForm.vue'
import { ApiError } from '@/api/client'
import type { Ticket, TicketInput, ValidationError } from '@/types/models'

const props = defineProps<{ id: string }>()
const router = useRouter()
const { responsaveis, carregar } = useResponsaveis()
const { categorias, tags, carregar: carregarCatalogo } = useCatalogo()

const chamado = ref<Ticket | null>(null)
const carregando = ref(true)
const enviando = ref(false)
const errors = ref<ValidationError['errors']>()

onMounted(async () => {
  carregar()
  carregarCatalogo()
  try {
    const resposta = await ticketsApi.exibir(props.id)
    chamado.value = resposta.data
  } finally {
    carregando.value = false
  }
})

async function atualizar(payload: TicketInput): Promise<void> {
  errors.value = undefined
  enviando.value = true

  try {
    const { data } = await ticketsApi.atualizar(props.id, payload)
    router.push({ name: 'chamados.show', params: { id: data.id } })
  } catch (erro) {
    if (erro instanceof ApiError) {
      errors.value = erro.errors
    } else {
      throw erro
    }
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="mx-auto max-w-2xl space-y-4">
    <h1 class="text-xl font-semibold text-white">Editar chamado</h1>
    <div v-if="carregando" class="text-sm text-gray-400">Carregando...</div>
    <div v-else class="card p-6">
      <TicketForm
        :chamado="chamado"
        :responsaveis="responsaveis"
        :categorias="categorias"
        :tags="tags"
        :enviando="enviando"
        :errors="errors"
        @submit="atualizar"
      />
    </div>
  </div>
</template>