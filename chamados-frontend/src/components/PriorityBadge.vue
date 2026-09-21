<template>
  <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', badgeClass]">
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  priority?: 'low' | 'medium' | 'high' | 'urgent' | 'baixa' | 'media' | 'alta'
  prioridade?: 'low' | 'medium' | 'high' | 'urgent' | 'baixa' | 'media' | 'alta'
}>()

// Obtém o valor vindo de qualquer uma das duas props
const activePriority = computed(() => props.prioridade || props.priority || 'baixa')

const labelMap: Record<string, string> = {
  baixa: 'BAIXA',
  low: 'BAIXA',
  media: 'MÉDIA',
  medium: 'MÉDIA',
  alta: 'ALTA',
  high: 'ALTA',
  urgent: 'URGENTE'
}

const classMap: Record<string, string> = {
  baixa: 'bg-gray-700 text-gray-300 border border-gray-600',
  low: 'bg-gray-700 text-gray-300 border border-gray-600',
  media: 'bg-blue-900/60 text-blue-300 border border-blue-700',
  medium: 'bg-blue-900/60 text-blue-300 border border-blue-700',
  alta: 'bg-red-900/60 text-red-300 border border-red-700',
  high: 'bg-red-900/60 text-red-300 border border-red-700',
  urgent: 'bg-red-900/60 text-red-300 border border-red-700',
}

const label = computed(() => labelMap[activePriority.value] || activePriority.value.toUpperCase())
const badgeClass = computed(() => classMap[activePriority.value] || classMap['baixa'])
</script>