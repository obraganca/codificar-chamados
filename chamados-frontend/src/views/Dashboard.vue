<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { dashboardApi } from '@/api/dashboard'
import StatusBadge from '@/components/StatusBadge.vue'
import PriorityBadge from '@/components/PriorityBadge.vue'
import { STATUSES } from '@/types/models'
import type { DashboardData } from '@/types/models'

// Declarando a reatividade corretamente
const dados = ref<DashboardData | null>(null)
const carregando = ref(true)

onMounted(async () => {
  try {
    // Atribuindo valor utilizando .value
    dados.value = await dashboardApi.obter()
    console.log(dados.value)
  } catch (error) {
    console.error('Erro ao carregar o dashboard:', error)
  } finally {
    carregando.value = false
  }
})
</script>

<template>
  <div v-if="carregando" class="text-sm text-gray-400">Carregando...</div>

  <!-- Acesso direto à propriedade no template (sem .value) -->
  <div v-else-if="dados" class="space-y-8">
    <!-- Métricas Superiores -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
        <p class="text-xs font-medium uppercase text-gray-400">Total</p>
        <p class="mt-1 text-2xl font-semibold text-gray-100">{{ dados.totais.total }}</p>
      </div>
      <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
        <p class="text-xs font-medium uppercase text-gray-400">Em aberto</p>
        <p class="mt-1 text-2xl font-semibold text-gray-100">{{ dados.totais.em_aberto }}</p>
      </div>
      <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
        <p class="text-xs font-medium uppercase text-gray-400">Alta prioridade</p>
        <p class="mt-1 text-2xl font-semibold text-red-400">{{ dados.totais.alta_prioridade }}</p>
      </div>
      <div class="rounded-lg border border-gray-700 bg-gray-800 p-4">
        <p class="text-xs font-medium uppercase text-gray-400">Sem responsável</p>
        <p class="mt-1 text-2xl font-semibold text-gray-100">{{ dados.totais.sem_responsavel }}</p>
      </div>
    </div>

    <!-- Colunas por Status e Carga de Trabalho -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_260px]">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="status in STATUSES" :key="status" class="rounded-lg border border-gray-700 bg-gray-800">
          <div class="flex items-center justify-between border-b border-gray-700 px-3 py-2">
            <StatusBadge :status="status" />
            <span class="text-xs text-gray-400">{{ dados.colunas[status]?.length ?? 0 }}</span>
          </div>
          <ul class="max-h-96 space-y-2 overflow-y-auto p-3">
            <li v-if="!dados.colunas[status]?.length" class="text-xs text-gray-500">Nenhum chamado</li>
            <li v-for="chamado in dados.colunas[status]" :key="chamado.id">
              <RouterLink
                :to="{ name: 'chamados.show', params: { id: chamado.id } }"
                class="block rounded-md border border-gray-700 bg-gray-900/50 p-2 text-sm hover:border-gray-500 transition"
              >
                <p class="truncate font-medium text-gray-200">{{ chamado.titulo }}</p>
                <div class="mt-1 flex items-center justify-between">
                  <PriorityBadge :prioridade="chamado.prioridade" />
                  <span class="truncate text-xs text-gray-400">{{ chamado.responsavel?.nome ?? 'Sem responsável' }}</span>
                </div>
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>

      <!-- Card Lateral de Responsáveis -->
      <aside class="rounded-lg border border-gray-700 bg-gray-800 p-4">
        <h2 class="mb-3 text-sm font-semibold text-gray-300">Carga por responsável</h2>
        <ul class="space-y-2">
          <li
            v-for="responsavel in dados.responsaveis"
            :key="responsavel.id"
            :class="[
              'flex items-center justify-between text-sm p-1.5 rounded',
              responsavel.chamados_abertos_count ? 'bg-gray-900/40' : ''
            ]"
          >
            <span class="text-gray-300">{{ responsavel.nome }}</span>
            <span class="rounded-full bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-300">
              {{ responsavel.chamados_abertos_count ?? 0 }}
            </span>
          </li>
        </ul>
      </aside>
    </div>
  </div>
</template>