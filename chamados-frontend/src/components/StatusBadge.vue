<template>
  <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', classes[status]]">
    {{ labels[status] }}
  </span>
</template>

<script setup lang="ts">
defineProps<{ status: 'aberto' | 'em_andamento' | 'resolvido' | 'fechado' }>();

const labels = {
  aberto: 'Aberto',
  em_andamento: 'Em andamento',
  resolvido: 'Resolvido',
  fechado: 'Fechado'
};

const classes = {
  aberto: 'bg-green-900/60 text-green-300 border border-green-700',
  em_andamento: 'bg-yellow-900/60 text-yellow-300 border border-yellow-700',
  resolvido: 'bg-blue-900/60 text-blue-300 border border-blue-700',
  fechado: 'bg-gray-700 text-gray-400 border border-gray-600'
};
</script>