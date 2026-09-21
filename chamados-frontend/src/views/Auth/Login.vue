<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ApiError } from '@/api/client'
import FieldError from '@/components/FieldError.vue'
import type { ValidationError } from '@/types/models'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = reactive({ email: '', password: '' })
const errors = ref<ValidationError['errors']>()
const enviando = ref(false)

async function aoSubmeter(): Promise<void> {
  errors.value = undefined
  enviando.value = true

  try {
    await auth.login(form.email, form.password)
    const destino = typeof route.query.redirect === 'string' ? route.query.redirect : { name: 'dashboard' }
    router.push(destino)
  } catch (erro) {
    if (erro instanceof ApiError) {
      errors.value = erro.errors
    } else {
      throw erro
    }
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center px-4">
    <div class="card w-full max-w-sm p-8">
      <h1 class="mb-6 text-xl font-semibold text-white">Entrar</h1>

      <form class="space-y-4" @submit.prevent="aoSubmeter">
        <div>
          <label for="email" class="field-label">E-mail</label>
          <input id="email" v-model="form.email" type="email" required class="field-input" />
          <FieldError :mensagens="errors?.email" />
        </div>

        <div>
          <label for="password" class="field-label">Senha</label>
          <input id="password" v-model="form.password" type="password" required class="field-input" />
          <FieldError :mensagens="errors?.password" />
        </div>

        <button type="submit" :disabled="enviando" class="btn-primary w-full">
          {{ enviando ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-400">
        Não tem conta?
        <RouterLink :to="{ name: 'registrar' }" class="font-medium text-indigo-400 hover:underline">
          Registre-se
        </RouterLink>
      </p>
    </div>
  </div>
</template>
