<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ApiError } from '@/api/client'
import FieldError from '@/components/FieldError.vue'
import type { ValidationError } from '@/types/models'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const errors = ref<ValidationError['errors']>()
const enviando = ref(false)

async function aoSubmeter(): Promise<void> {
  errors.value = undefined
  enviando.value = true

  try {
    await auth.registrar(form)
    router.push({ name: 'dashboard' })
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
      <h1 class="mb-6 text-xl font-semibold text-white">Criar conta</h1>

      <form class="space-y-4" @submit.prevent="aoSubmeter">
        <div>
          <label for="name" class="field-label">Nome</label>
          <input id="name" v-model="form.name" type="text" required class="field-input" />
          <FieldError :mensagens="errors?.name" />
        </div>

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

        <div>
          <label for="password_confirmation" class="field-label">Confirmar senha</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            class="field-input"
          />
        </div>

        <button type="submit" :disabled="enviando" class="btn-primary w-full">
          {{ enviando ? 'Criando...' : 'Criar conta' }}
        </button>
      </form>

      <p class="mt-4 text-center text-sm text-gray-400">
        Já tem conta?
        <RouterLink :to="{ name: 'login' }" class="font-medium text-indigo-400 hover:underline">Entrar</RouterLink>
      </p>
    </div>
  </div>
</template>