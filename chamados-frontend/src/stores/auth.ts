import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { authApi } from '@/api/auth'
import { setAuthToken, ApiError } from '@/api/client'
import type { User } from '@/types/models'

export const TOKEN_STORAGE_KEY = 'chamados:token'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem(TOKEN_STORAGE_KEY))
  const carregando = ref(false)

  if (token.value) {
    setAuthToken(token.value)
  }

  function definirSessao(novoUsuario: User, novoToken: string): void {
    user.value = novoUsuario
    token.value = novoToken
    localStorage.setItem(TOKEN_STORAGE_KEY, novoToken)
    setAuthToken(novoToken)
  }

  function limparSessao(): void {
    user.value = null
    token.value = null
    localStorage.removeItem(TOKEN_STORAGE_KEY)
    setAuthToken(null)
  }

  async function login(email: string, password: string): Promise<void> {
    carregando.value = true
    try {
      const resposta = await authApi.login(email, password)
      definirSessao(resposta.user, resposta.token)
    } finally {
      carregando.value = false
    }
  }

  async function registrar(payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }): Promise<void> {
    carregando.value = true
    try {
      const resposta = await authApi.registrar(payload)
      definirSessao(resposta.user, resposta.token)
    } finally {
      carregando.value = false
    }
  }

  async function logout(): Promise<void> {
    try {
      await authApi.logout()
    } catch {
      // Mesmo se a chamada falhar (ex.: token ja expirado), seguimos e
      // limpamos o estado local: do ponto de vista do usuario, ele saiu.
    } finally {
      limparSessao()
    }
  }

  /** Restaura a sessao a partir do token salvo, ao carregar a aplicacao. */
  async function restaurarSessao(): Promise<void> {
    if (!token.value) return

    try {
      const resposta = await authApi.me()
      user.value = resposta.user
    } catch (erro) {
      if (erro instanceof ApiError && erro.status === 401) {
        limparSessao()
      }
    }
  }

  const estaAutenticado = computed(() => token.value !== null)

  return {
    user,
    token,
    carregando,
    estaAutenticado,
    login,
    registrar,
    logout,
    restaurarSessao,
  }
})
