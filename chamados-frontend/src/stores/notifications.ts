import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificacoesApi } from '@/api/notificacoes'
import type { Notificacao } from '@/types/models'

/** Formato que chega pelo WebSocket (payload de TicketNotification::payload()). */
interface NotificacaoRealtime {
  id: string
  type: Notificacao['tipo']
  ticket_id: number
  title: string
  message: string
  actor: { id: number; name: string } | null
  changes?: Notificacao['mudancas']
}

type EchoInstance = typeof import('@/services/echo').echo

export const useNotificationsStore = defineStore('notifications', () => {
  const itens = ref<Notificacao[]>([])
  const naoLidas = ref(0)
  const toasts = ref<Notificacao[]>([])
  /** Chamado que o usuario esta vendo agora (definido pela tela Show.vue). */
  const ticketAberto = ref<number | null>(null)

  let canal: string | null = null
  let echoRef: EchoInstance | null = null

  async function carregar(): Promise<void> {
    const resposta = await notificacoesApi.listar()
    itens.value = resposta.data
    naoLidas.value = resposta.meta.nao_lidas
  }

  /** Carrega o historico e assina o canal privado do usuario logado. */
  async function iniciar(userId: number): Promise<void> {
    const nome = `App.Models.User.${userId}`
    if (canal === nome) return

    parar()
    canal = nome

    await carregar().catch((e) => console.error('Erro ao carregar notificações:', e))

    // Import dinamico: so abre o WebSocket depois do login.
    const { echo } = await import('@/services/echo')
    if (canal !== nome) return // usuario saiu enquanto carregava

    echoRef = echo
    echo.private(nome).notification((n: NotificacaoRealtime) => receber(n))
  }

  function parar(): void {
    if (canal && echoRef) echoRef.leave(canal)
    canal = null
    echoRef = null
    itens.value = []
    naoLidas.value = 0
    toasts.value = []
  }

  function receber(n: NotificacaoRealtime): void {
    if (itens.value.some((i) => i.id === n.id)) return

    const nova: Notificacao = {
      id: n.id,
      tipo: n.type,
      titulo: n.title,
      mensagem: n.message,
      ticket_id: n.ticket_id,
      autor: n.actor,
      mudancas: n.changes ?? null,
      lida_em: null,
      created_at: new Date().toISOString(),
    }

    // Mensagem do chamado que ja estou vendo: aparece ao vivo no chat, entao
    // conta como lida e nao mostra toast. O atraso existe porque a linha do
    // banco e gravada por outro job da fila e pode ainda nao existir.
    if (nova.tipo === 'ticket_message' && nova.ticket_id === ticketAberto.value) {
      nova.lida_em = new Date().toISOString()
      itens.value.unshift(nova)
      setTimeout(() => {
        notificacoesApi
          .marcarLida(nova.id)
          .then((r) => (naoLidas.value = r.meta.nao_lidas))
          .catch(() => {})
      }, 1500)
      return
    }

    itens.value.unshift(nova)
    naoLidas.value++
    mostrarToast(nova)
  }

  function mostrarToast(n: Notificacao): void {
    toasts.value.push(n)
    setTimeout(() => fecharToast(n.id), 6000)
  }

  function fecharToast(id: string): void {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  async function marcarLida(id: string): Promise<void> {
    const item = itens.value.find((i) => i.id === id)
    if (item && !item.lida_em) {
      item.lida_em = new Date().toISOString()
      naoLidas.value = Math.max(0, naoLidas.value - 1)
    }
    const resposta = await notificacoesApi.marcarLida(id)
    naoLidas.value = resposta.meta.nao_lidas
  }

  async function marcarTodasLidas(): Promise<void> {
    itens.value.forEach((i) => {
      i.lida_em ??= new Date().toISOString()
    })
    naoLidas.value = 0
    await notificacoesApi.marcarTodasLidas()
  }

  return {
    itens,
    naoLidas,
    toasts,
    ticketAberto,
    carregar,
    iniciar,
    parar,
    fecharToast,
    marcarLida,
    marcarTodasLidas,
  }
})