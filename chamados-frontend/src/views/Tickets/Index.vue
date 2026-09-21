<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import { ticketsApi } from '@/api/tickets'
import { useResponsaveis } from '@/composables/useResponsaveis'
import StatusBadge from '@/components/StatusBadge.vue'
import PriorityBadge from '@/components/PriorityBadge.vue'
import Pagination from '@/components/Pagination.vue'
import { PRIORIDADES, PRIORIDADE_LABELS, STATUSES, STATUS_LABELS } from '@/types/models'
import type { PaginatedResponse, Ticket } from '@/types/models'

const { responsaveis, carregar: carregarResponsaveis } = useResponsaveis()

const chamados = ref<Ticket[]>([])
const meta = ref<PaginatedResponse<Ticket>['meta'] | null>(null)
const carregando = ref(true)

const filtros = reactive({
  status: '',
  prioridade: '',
  responsavel_id: '',
  busca: '',
  page: 1,
})

async function carregarChamados(): Promise<void> {
  carregando.value = true
  try {
    const resposta = await ticketsApi.listar(filtros)
    chamados.value = resposta.data
    meta.value = resposta.meta
  } finally {
    carregando.value = false
  }
}

watch(
  () => [filtros.status, filtros.prioridade, filtros.responsavel_id, filtros.busca],
  () => {
    filtros.page = 1
    carregarChamados()
  }
)

watch(() => filtros.page, carregarChamados)

onMounted(() => {
  carregarResponsaveis()
  carregarChamados()
})
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-white">Chamados</h1>
      <RouterLink :to="{ name: 'chamados.create' }" class="btn-primary">Novo chamado</RouterLink>
    </div>

    <div class="card grid grid-cols-1 gap-3 p-4 sm:grid-cols-4">
      <input
        v-model="filtros.busca"
        type="text"
        placeholder="Buscar por título ou descrição"
        class="field-input mt-0 sm:col-span-2"
      />
      <select v-model="filtros.status" class="field-input mt-0">
        <option value="">Todos os status</option>
        <option v-for="s in STATUSES" :key="s" :value="s">{{ STATUS_LABELS[s] }}</option>
      </select>
      <select v-model="filtros.prioridade" class="field-input mt-0">
        <option value="">Todas as prioridades</option>
        <option v-for="p in PRIORIDADES" :key="p" :value="p">{{ PRIORIDADE_LABELS[p] }}</option>
      </select>
      <select v-model="filtros.responsavel_id" class="field-input mt-0 sm:col-span-4">
        <option value="">Todos os responsáveis</option>
        <option v-for="r in responsaveis" :key="r.id" :value="r.id">{{ r.nome }}</option>
      </select>
    </div>

    <div v-if="carregando" class="text-sm text-gray-400">Carregando...</div>

    <div v-else class="card overflow-hidden">
      <table class="min-w-full divide-y divide-gray-700 text-sm">
        <thead class="bg-gray-900/60 text-left text-xs font-medium uppercase text-gray-400">
          <tr>
            <th class="px-4 py-2">Título</th>
            <th class="px-4 py-2">Categoria</th>
            <th class="px-4 py-2">Prioridade</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Responsável</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
          <tr v-if="!chamados.length">
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Nenhum chamado encontrado.</td>
          </tr>
          <tr
            v-for="chamado in chamados"
            :key="chamado.id"
            class="cursor-pointer transition hover:bg-gray-700/40"
            @click="$router.push({ name: 'chamados.show', params: { id: chamado.id } })"
          >
            <td class="px-4 py-3 font-medium text-gray-100">{{ chamado.titulo }}</td>
            <td class="px-4 py-3">
              <span
                v-if="chamado.categoria"
                class="rounded-full px-2.5 py-0.5 text-xs font-semibold text-white"
                :style="{ backgroundColor: chamado.categoria.cor }"
              >
                {{ chamado.categoria.nome }}
              </span>
              <span v-else class="text-gray-500">—</span>
            </td>
            <td class="px-4 py-3"><PriorityBadge :prioridade="chamado.prioridade" /></td>
            <td class="px-4 py-3"><StatusBadge :status="chamado.status" /></td>
            <td class="px-4 py-3 text-gray-400">{{ chamado.responsavel?.nome ?? 'Sem responsável' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination
      v-if="meta"
      :pagina-atual="meta.current_page"
      :ultima-pagina="meta.last_page"
      @mudar-pagina="(pagina) => (filtros.page = pagina)"
    />
  </div>
</template>
