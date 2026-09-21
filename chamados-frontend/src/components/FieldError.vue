<script setup lang="ts">
defineProps<{ mensagens?: string[] }>()
</script>

<template>
  <p v-if="mensagens?.length" class="mt-1 text-sm text-red-400">
    {{ mensagens[0] }}
  </p>
</template>