<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'
import { ICONES } from '@/utils/notificacoes'
import type { Notificacao } from '@/types/models'

const store = useNotificationsStore()
const router = useRouter()

function abrir(n: Notificacao): void {
  store.fecharToast(n.id)
  store.marcarLida(n.id).catch(() => {})
  if (n.ticket_id) router.push({ name: 'chamados.show', params: { id: n.ticket_id } })
}
</script>

<template>
  <div class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-80 max-w-[90vw] flex-col gap-2">
    <div
      v-for="n in store.toasts"
      :key="n.id"
      class="pointer-events-auto flex cursor-pointer items-start gap-3 rounded-lg border border-gray-600 bg-gray-800 p-3 shadow-xl transition hover:border-indigo-500"
      @click="abrir(n)"
    >
      <span class="text-lg leading-none">{{ ICONES[n.tipo] ?? '🔔' }}</span>
      <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-white">{{ n.titulo }}</p>
        <p class="mt-0.5 line-clamp-2 text-xs text-gray-300">{{ n.mensagem }}</p>
      </div>
      <button
        type="button"
        class="text-gray-500 hover:text-white"
        aria-label="Fechar"
        @click.stop="store.fecharToast(n.id)"
      >
        ✕
      </button>
    </div>
  </div>
</template>