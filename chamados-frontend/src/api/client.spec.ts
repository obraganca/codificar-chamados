import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { api, ApiError, setAuthToken } from './client'

describe('api client', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  afterEach(() => {
    setAuthToken(null)
    vi.unstubAllGlobals()
  })

  it('envia o header Authorization quando ha um token definido', async () => {
    setAuthToken('meu-token')
    vi.mocked(fetch).mockResolvedValue(
      new Response(JSON.stringify({ ok: true }), { status: 200 })
    )

    await api.get('/qualquer-coisa')

    const [, init] = vi.mocked(fetch).mock.calls[0]
    const headers = init?.headers as Record<string, string>
    expect(headers.Authorization).toBe('Bearer meu-token')
  })

  it('nao envia o header Authorization sem token', async () => {
    vi.mocked(fetch).mockResolvedValue(new Response(JSON.stringify({}), { status: 200 }))

    await api.get('/qualquer-coisa')

    const [, init] = vi.mocked(fetch).mock.calls[0]
    const headers = init?.headers as Record<string, string>
    expect(headers.Authorization).toBeUndefined()
  })

  it('lanca ApiError com status e erros de validacao em respostas 422', async () => {
    vi.mocked(fetch).mockResolvedValue(
      new Response(
        JSON.stringify({ message: 'Dados invalidos.', errors: { titulo: ['O título é obrigatório.'] } }),
        { status: 422 }
      )
    )

    await expect(api.post('/chamados', {})).rejects.toMatchObject({
      status: 422,
      errors: { titulo: ['O título é obrigatório.'] },
    })
  })

  it('inclui filtros como query string, ignorando valores vazios', async () => {
    vi.mocked(fetch).mockResolvedValue(new Response(JSON.stringify({}), { status: 200 }))

    await api.get('/chamados', { status: 'aberto', busca: '' })

    const [url] = vi.mocked(fetch).mock.calls[0]
    expect(String(url)).toContain('status=aberto')
    expect(String(url)).not.toContain('busca=')
  })
})

describe('ApiError', () => {
  it('guarda mensagem, status e erros', () => {
    const erro = new ApiError('falhou', 422, { email: ['invalido'] })
    expect(erro.message).toBe('falhou')
    expect(erro.status).toBe(422)
    expect(erro.errors).toEqual({ email: ['invalido'] })
  })
})
