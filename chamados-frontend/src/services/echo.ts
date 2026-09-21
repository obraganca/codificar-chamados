
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { TOKEN_STORAGE_KEY } from '@/stores/auth';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

window.Pusher = Pusher;

// VITE_API_URL ja inclui o prefixo /api/v1 (ex.: http://localhost:8080/api/v1),
// entao nao repetimos "/api/v1" aqui de novo - so completamos com /broadcasting/auth.
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api/v1';

// Precisa ser IDENTICA a TOKEN_STORAGE_KEY em src/stores/auth.ts. Ideal:
// exportar essa constante do auth.ts e importar aqui, em vez de duplicar a
// string em dois arquivos - foi exatamente essa duplicacao (aqui estava
// 'token', la e 'chamados:token') que causou o Bearer null.

export const echo = new Echo({
  broadcaster: 'reverb', // ou 'pusher' de acordo com o seu backend Laravel
  key: import.meta.env.VITE_REVERB_APP_KEY || import.meta.env.VITE_PUSHER_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],

  // Autorizador customizado em vez de "authEndpoint" + "auth.headers" fixos:
  // o header Authorization e montado NA HORA de cada tentativa de assinatura
  // de canal privado, lendo o token mais recente do localStorage. Com
  // "auth.headers" estatico (como estava antes), o valor do token e
  // capturado uma unica vez, no momento em que este modulo e importado -
  // se o usuario nao estava logado ainda nesse instante, ou fez login de
  // novo depois (token novo), o header antigo (ou vazio) ficava "preso" ate
  // um reload completo da pagina.
  authorizer: (channel: { name: string }) => {
    return {
      authorize: (
        socketId: string,
        callback: (error: boolean, data: unknown) => void
      ) => {
        fetch(`${API_URL}/broadcasting/auth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem(TOKEN_STORAGE_KEY)}`,
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channel.name,
          }),
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Falha ao autorizar canal: ${response.status}`);
            }
            return response.json();
          })
          .then((data) => callback(false, data))
          .catch((error) => callback(true, error));
      },
    };
  },
});