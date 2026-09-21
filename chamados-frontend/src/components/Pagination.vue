<script setup lang="ts">
const props = defineProps<{
  paginaAtual: number
  ultimaPagina: number
}>()

const emit = defineEmits<{ mudarPagina: [pagina: number] }>()

function irPara(pagina: number): void {
  if (pagina >= 1 && pagina <= props.ultimaPagina) {
    emit('mudarPagina', pagina)
  }
}
</script>

<template>
  <div v-if="ultimaPagina > 1" class="flex items-center justify-center gap-3 text-sm">
    <button type="button" class="btn-secondary" :disabled="paginaAtual <= 1" @click="irPara(paginaAtual - 1)">
      Anterior
    </button>
    <span class="text-gray-400">Página {{ paginaAtual }} de {{ ultimaPagina }}</span>
    <button type="button" class="btn-secondary" :disabled="paginaAtual >= ultimaPagina" @click="irPara(paginaAtual + 1)">
      Próxima
    </button>
  </div>
</template>