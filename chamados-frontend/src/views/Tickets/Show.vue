<template>
  <div v-if="loading" class="text-center py-12 text-gray-400">
    Carregando chamado...
  </div>

  <div v-else-if="ticket" class="space-y-6">
    <!-- Cabeçalho do Chamado -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 shadow-lg">
      <div class="flex justify-between items-start">
        <div>
          <h1 class="text-2xl font-bold text-white mb-2">#{{ ticket.id }} - {{ ticket.titulo }}</h1>
          <p class="text-gray-400 text-sm">
            Responsável
            <span class="text-indigo-400">{{ ticket.responsavel?.nome ?? 'Sem responsável' }}</span>
            · criado em {{ formatDate(ticket.created_at) }}
          </p>
        </div>
        <div v-if="ticket.categoria || ticket.tags?.length" class="mt-2 flex flex-wrap gap-2">
          <span
            v-if="ticket.categoria"
            class="rounded-full px-2.5 py-0.5 text-xs font-semibold text-white"
            :style="{ backgroundColor: ticket.categoria.cor }"
          >
            {{ ticket.categoria.nome }}
          </span>
          <span
            v-for="t in ticket.tags"
            :key="t.id"
            class="rounded-full border border-gray-600 px-2.5 py-0.5 text-xs text-gray-300"
          >
            #{{ t.nome }}
          </span>
        </div>
        <div class="flex items-center space-x-2">
          <StatusBadge :status="ticket.status" />
          <PriorityBadge :prioridade="ticket.prioridade" />
          <RouterLink
            :to="{ name: 'chamados.edit', params: { id: ticket.id } }"
            class="ml-2 rounded-md border border-gray-600 px-3 py-1.5 text-sm font-medium text-gray-200 hover:bg-gray-700 transition"
          >
            Editar
          </RouterLink>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t border-gray-700">
        <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Descrição</h3>
        <p class="text-gray-200 whitespace-pre-line">{{ ticket.descricao }}</p>
      </div>
    </div>

    <!-- Área do Chat / Mensagens dentro do Chamado -->
    <div class="bg-gray-800 border border-gray-700 rounded-lg flex flex-col h-[500px] shadow-lg">
      <div class="p-4 border-b border-gray-700 bg-gray-850 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white">Mensagens / Histórico</h2>
        <span class="text-xs text-green-400 flex items-center gap-1">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
          AO VIVO
        </span>
      </div>
      <div
        v-if="ticket.status === 'resolvido' || ticket.status === 'fechado'"
        class="border-b border-yellow-700/50 bg-yellow-900/20 px-4 py-2 text-xs text-yellow-300"
      >
        Este atendimento foi finalizado.
      </div>

      <!-- Lista de Mensagens -->
      <div ref="messagesContainer" class="flex-1 p-4 overflow-y-auto space-y-4">
        <div v-if="loadingMessages" class="text-center text-gray-500 py-8">
          Carregando mensagens...
        </div>

        <div v-else-if="!messages.length" class="text-center text-gray-500 py-8">
          Nenhuma mensagem registrada ainda neste chamado.
        </div>

        <div
          v-for="msg in messages"
          :key="msg.id"
          :class="[
            'flex items-end gap-2 max-w-[80%]',
            msg.user_id === authStore.user?.id ? 'ml-auto flex-row-reverse' : 'mr-auto'
          ]"
        >
          <!-- Avatar com as iniciais do autor -->
          <div
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-500/80 text-xs font-semibold text-white"
            :title="msg.user?.name ?? 'Usuário'"
          >
            {{ getInitials(msg.user?.name) }}
          </div>

          <div
            :class="[
              'flex flex-col rounded-lg p-3 text-sm shadow',
              msg.user_id === authStore.user?.id
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-700 text-gray-100'
            ]"
          >
            <div class="flex justify-between items-center mb-1 gap-4 text-xs opacity-75">
              <span class="font-bold">{{ msg.user?.name ?? 'Usuário' }}</span>
              <span>{{ formatDate(msg.created_at) }}</span>
            </div>
            <p class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
          </div>
        </div>
      </div>

      <!-- Caixa de Envio de Mensagem -->
      <form @submit.prevent="sendMessage" class="p-4 border-t border-gray-700 bg-gray-800 flex gap-2">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Digite sua mensagem para este chamado..."
          class="flex-1 bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500"
          :disabled="sending"
        />
        <button
          type="submit"
          :disabled="sending || !newMessage.trim()"
          class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white px-5 py-2 rounded-lg font-medium transition"
        >
          {{ sending ? 'Enviando...' : 'Enviar' }}
        </button>
      </form>
      <p v-if="sendError" class="px-4 pb-3 text-sm text-red-400">{{ sendError }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import StatusBadge from '@/components/StatusBadge.vue';
import PriorityBadge from '@/components/PriorityBadge.vue';
import { useAuthStore } from '@/stores/auth';
import { echo } from '@/services/echo';
import type { Ticket, Message } from '@/types/models';

import { api, ApiError } from '@/api/client'
import { useNotificationsStore } from '@/stores/notifications';

const notifications = useNotificationsStore();
const sendError = ref('');

const route = useRoute();
const authStore = useAuthStore();

const ticket = ref<Ticket | null>(null);
const messages = ref<Message[]>([]);
const newMessage = ref('');
const loading = ref(true);
const loadingMessages = ref(true);
const sending = ref(false);
const messagesContainer = ref<HTMLDivElement | null>(null);

const ticketId = Number(route.params.id);

function formatDate(dateStr?: string) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('pt-BR');
}

/** Ex.: "Thayllon Almeida" -> "TA". Cai para "?" se não houver nome. */
function getInitials(name?: string): string {
  if (!name?.trim()) return '?';
  const partes = name.trim().split(/\s+/);
  const primeira = partes[0]?.[0] ?? '';
  const ultima = partes.length > 1 ? partes[partes.length - 1][0] : '';
  return (primeira + ultima).toUpperCase();
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
}

async function fetchTicket() {
  try {
    const response = await api.get<{ data: Ticket }>(`/chamados/${ticketId}`);
    ticket.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar chamado:', error);
  } finally {
    loading.value = false;
  }
}

/** Busca o historico de mensagens - usado no load inicial/refresh da pagina. */
async function fetchMessages() {
  loadingMessages.value = true;
  try {
    const response = await api.get<{ data: Message[] }>(`/chamados/${ticketId}/messages`);
    messages.value = response.data;
    scrollToBottom();
  } catch (error) {
    console.error('Erro ao carregar mensagens:', error);
  } finally {
    loadingMessages.value = false;
  }
}

async function sendMessage() {
  if (!newMessage.value.trim() || sending.value) return;

  sending.value = true;
  sendError.value = '';
  try {
    const response = await api.post<{ data: Message }>(`/chamados/${ticketId}/messages`, {
      message: newMessage.value,
    });

    if (response.data && !messages.value.some(m => m.id === response.data.id)) {
      messages.value.push(response.data);
      scrollToBottom();
    }
    newMessage.value = '';
  } catch (error) {
    sendError.value = error instanceof ApiError ? error.message : 'Não foi possível enviar a mensagem.';
  } finally {
    sending.value = false;
  }
}

onMounted(async () => {
  // Avisa o sino que estou vendo este chamado (mensagens dele viram "lidas" ao vivo).
  notifications.ticketAberto = ticketId;

  await Promise.all([fetchTicket(), fetchMessages()]);

  // Canal privado do chamado: agora quem emite estes eventos e o backend
  // (MessageSent / TicketUpdated), via Reverb.
  echo.private(`tickets.chat.${ticketId}`)
    .listen('.MessageSent', (e: { message: Message }) => {
      if (!messages.value.some(m => m.id === e.message.id)) {
        messages.value.push(e.message);
        scrollToBottom();
      }
    })
    .listen('.TicketUpdated', (e: { ticket: Ticket }) => {
      if (ticket.value) Object.assign(ticket.value, e.ticket);
    });
});

onUnmounted(() => {
  notifications.ticketAberto = null;
  echo.leave(`tickets.chat.${ticketId}`);
});
</script>