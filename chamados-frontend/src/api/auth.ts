import { api } from './client'
import type { User } from '@/types/models'

interface AuthResponse {
  user: User
  token: string
}

export const authApi = {
  login: (email: string, password: string) =>
    api.post<AuthResponse>('/auth/login', { email, password }),

  registrar: (payload: { name: string; email: string; password: string; password_confirmation: string }) =>
    api.post<AuthResponse>('/auth/registrar', payload),

  logout: () => api.post<{ message: string }>('/auth/logout'),

  me: () => api.get<{ user: User }>('/auth/me'),
}
