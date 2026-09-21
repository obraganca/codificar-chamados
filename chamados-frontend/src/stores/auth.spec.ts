import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

vi.mock('@/api/auth', () => ({
  authApi: {
    login: vi.fn(),
    registrar: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
  },
}))

import { authApi } from '@/api/auth'
import { useAuthStore } from './auth'

describe('auth store', () => {
  beforeEach(() => {
    localStorage.clear()
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('comeca deslogado quando nao ha token salvo', () => {
    const auth = useAuthStore()
    expect(auth.estaAutenticado).toBe(false)
    expect(auth.user).toBeNull()
  })

  it('guarda usuario e token apos login bem sucedido', async () => {
    vi.mocked(authApi.login).mockResolvedValue({
      user: { id: 1, name: 'Ana', email: 'ana@codificar.com' },
      token: 'token-123',
    })

    const auth = useAuthStore()
    await auth.login('ana@codificar.com', 'password')

    expect(auth.estaAutenticado).toBe(true)
    expect(auth.user?.name).toBe('Ana')
    expect(localStorage.getItem('chamados:token')).toBe('token-123')
  })

  it('limpa a sessao ao fazer logout mesmo se a chamada de rede falhar', async () => {
    vi.mocked(authApi.login).mockResolvedValue({
      user: { id: 1, name: 'Ana', email: 'ana@codificar.com' },
      token: 'token-123',
    })
    vi.mocked(authApi.logout).mockRejectedValue(new Error('rede fora'))

    const auth = useAuthStore()
    await auth.login('ana@codificar.com', 'password')
    await auth.logout()

    expect(auth.estaAutenticado).toBe(false)
    expect(auth.user).toBeNull()
    expect(localStorage.getItem('chamados:token')).toBeNull()
  })
})
