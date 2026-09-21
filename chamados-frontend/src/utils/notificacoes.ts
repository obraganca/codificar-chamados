export const ICONES: Record<string, string> = {
  ticket_created: '🆕',
  ticket_assigned: '👤',
  ticket_updated: '✏️',
  ticket_resolved: '✅',
  ticket_message: '💬',
}

const rtf = new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' })

export function tempoRelativo(iso: string): string {
  const seg = Math.round((new Date(iso).getTime() - Date.now()) / 1000)
  const unidades: [Intl.RelativeTimeFormatUnit, number][] = [
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
  ]

  for (const [unidade, s] of unidades) {
    if (Math.abs(seg) >= s) return rtf.format(Math.round(seg / s), unidade)
  }
  return 'agora'
}