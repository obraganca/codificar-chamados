import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import { router } from './router'
import { useAuthStore } from './stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Restaura a sessao (se houver token salvo) antes de montar, para o router
// guard ja saber se o usuario esta autenticado na primeira navegacao.
const auth = useAuthStore()
auth.restaurarSessao().finally(() => {
  app.mount('#app')
})
