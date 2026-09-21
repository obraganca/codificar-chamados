<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import { tempoRelativo, ICONES } from '@/utils/notificacoes'
import type { Notificacao } from '@/types/models'

const store = useNotificationsStore()
const router = useRouter()

const aberto = ref(false)
const raiz = ref<HTMLElement | null>(null)

function fecharAoClicarFora(e: MouseEvent): void {
  if (raiz.value && !raiz.value.contains(e.target as Node)) aberto.value = false
}

onMounted(() => document.addEventListener('click', fecharAoClicarFora))
onBeforeUnmount(() => document.removeEventListener('click', fecharAoClicarFora))

function abrir(n: Notificacao): void {
  if (!n.lida_em) store.marcarLida(n.id).catch(() => {})
  aberto.value = false
  if (n.ticket_id) router.push({ name: 'chamados.show', params: { id: n.ticket_id } })
}
</script>

<template>
  <div ref="raiz" class="relative">
    <button
      type="button"
      class="relative rounded-full p-2 text-gray-300 transition hover:bg-gray-700 hover:text-white"
      aria-label="Notificações"
      @click="aberto = !aberto"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
        />
      </svg>
      <span
        v-if="store.naoLidas"
        class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white"
      >
        {{ store.naoLidas > 99 ? '99+' : store.naoLidas }}
      </span>
    </button>

    <div
      v-if="aberto"
      class="absolute right-0 z-50 mt-2 w-96 max-w-[90vw] overflow-hidden rounded-lg border border-gray-700 bg-gray-800 shadow-xl"
    >
      <div class="flex items-center justify-between border-b border-gray-700 px-4 py-2">
        <h3 class="text-sm font-semibold text-white">Notificações</h3>
        <button
          type="button"
          class="text-xs text-indigo-400 hover:text-indigo-300 disabled:opacity-40"
          :disabled="!store.naoLidas"
          @click="store.marcarTodasLidas()"
        >
          Marcar todas como lidas
        </button>
      </div>

      <ul class="max-h-96 divide-y divide-gray-700 overflow-y-auto">
        <li v-if="!store.itens.length" class="p-6 text-center text-sm text-gray-500">Nenhuma notificação.</li>

        <li v-for="n in store.itens" :key="n.id">
          <button
            type="button"
            class="flex w-full items-start gap-3 px-4 py-3 text-left transition hover:bg-gray-700/50"
            :class="{ 'bg-indigo-500/5': !n.lida_em }"
            @click="abrir(n)"
          >
            <span class="mt-0.5 text-lg leading-none">{{ ICONES[n.tipo] ?? '🔔' }}</span>
            <span class="min-w-0 flex-1">
              <span class="flex items-center justify-between gap-2">
                <span class="truncate text-sm font-medium" :class="n.lida_em ? 'text-gray-400' : 'text-white'">
                  {{ n.titulo }}
                </span>
                <span v-if="!n.lida_em" class="h-2 w-2 shrink-0 rounded-full bg-indigo-500" />
              </span>
              <span class="mt-0.5 line-clamp-2 block text-xs text-gray-400">{{ n.mensagem }}</span>
              <span class="mt-1 block text-[11px] text-gray-500">{{ tempoRelativo(n.created_at) }}</span>
            </span>
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>