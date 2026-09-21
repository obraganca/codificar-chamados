<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ticketsApi } from '@/api/tickets'
import { useResponsaveis } from '@/composables/useResponsaveis'
import { useCatalogo } from '@/composables/useCatalogo'
import TicketForm from '@/components/TicketForm.vue'
import { ApiError } from '@/api/client'
import type { TicketInput, ValidationError } from '@/types/models'

const router = useRouter()
const { responsaveis, carregar } = useResponsaveis()
const { categorias, tags, carregar: carregarCatalogo } = useCatalogo()

const enviando = ref(false)
const errors = ref<ValidationError['errors']>()

onMounted(() => {
  carregar()
  carregarCatalogo()
})

async function criar(payload: TicketInput): Promise<void> {
  errors.value = undefined
  enviando.value = true

  try {
    const { data } = await ticketsApi.criar(payload)
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
    <h1 class="text-xl font-semibold text-white">Novo chamado</h1>
    <div class="card p-6">
      <TicketForm
        :responsaveis="responsaveis"
        :categorias="categorias"
        :tags="tags"
        :enviando="enviando"
        :errors="errors"
        @submit="criar"
      />
    </div>
  </div>
</template>