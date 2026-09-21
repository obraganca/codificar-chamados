<template>
  <div class="min-h-screen bg-gray-900 text-gray-100 flex flex-col">
    <header class="bg-gray-800 border-b border-gray-700 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <router-link to="/dashboard" class="text-xl font-bold text-indigo-400 hover:text-indigo-300">
            Sistema de Chamados
          </router-link>
          <nav class="flex space-x-2">
            <router-link
              :to="{ name: 'chamados.index' }"
              class="px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white"
            >
              Chamados
            </router-link>
          </nav>
        </div>

        <div class="flex items-center space-x-3">
          <NotificationBell />
          <span class="text-sm text-gray-400">{{ authStore.user?.name }}</span>
          <button
            @click="logout"
            class="px-3 py-1.5 bg-red-600/80 hover:bg-red-600 text-white rounded text-sm transition"
          >
            Sair
          </button>
        </div>
      </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
      <slot />
    </main>

    <NotificationToasts />
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationsStore } from '@/stores/notifications';
import NotificationBell from '@/components/NotificationBell.vue';
import NotificationToasts from '@/components/NotificationToasts.vue';

const authStore = useAuthStore();
const notifications = useNotificationsStore();
const router = useRouter();

// Assina o canal privado do usuario assim que ele estiver carregado.
watch(
  () => authStore.user?.id,
  (id) => {
    if (id) notifications.iniciar(id);
    else notifications.parar();
  },
  { immediate: true }
);

onBeforeUnmount(() => notifications.parar());

async function logout() {
  notifications.parar();
  await authStore.logout(); // aguarda limpar o token antes de navegar
  router.push('/login');
}
</script>