import type { ValidationError } from '@/types/models'

const BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1'

/**
 * Erro lancado para qualquer resposta HTTP de erro. Guarda o status e (quando
 * existir) o corpo de validacao {message, errors} devolvido pelo Laravel,
 * para que telas de formulario possam mostrar o erro por campo.
 */
export class ApiError extends Error {
  public readonly status: number
  public readonly errors?: ValidationError['errors']

  constructor(message: string, status: number, errors?: ValidationError['errors']) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }
}

let authToken: string | null = null

/** Chamado pela auth store ao logar/deslogar; mantem o client desacoplado do Pinia. */
export function setAuthToken(token: string | null): void {
  authToken = token
}

type RequestOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE'
  body?: unknown
  query?: Record<string, string | number | boolean | undefined | null>
}

function buildUrl(path: string, query?: RequestOptions['query']): string {
  const url = new URL(BASE_URL.replace(/\/$/, '') + path)

  for (const [key, value] of Object.entries(query ?? {})) {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, String(value))
    }
  }

  return url.toString()
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const response = await fetch(buildUrl(path, options.query), {
    method: options.method ?? 'GET',
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...(authToken ? { Authorization: `Bearer ${authToken}` } : {}),
    },
    body: options.body ? JSON.stringify(options.body) : undefined,
  })

  if (response.status === 204) {
    return undefined as T
  }

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    throw new ApiError(
      data?.message ?? 'Ocorreu um erro inesperado.',
      response.status,
      data?.errors
    )
  }

  return data as T
}

export const api = {
  get: <T>(path: string, query?: RequestOptions['query']) => request<T>(path, { method: 'GET', query }),
  post: <T>(path: string, body?: unknown) => request<T>(path, { method: 'POST', body }),
  put: <T>(path: string, body?: unknown) => request<T>(path, { method: 'PUT', body }),
}
