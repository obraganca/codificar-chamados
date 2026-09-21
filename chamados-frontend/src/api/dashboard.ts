import { api } from './client'
import type { DashboardData } from '@/types/models'

export const dashboardApi = {
  obter: () => api.get<DashboardData>('/dashboard'),
}
