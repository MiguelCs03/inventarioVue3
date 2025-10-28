import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'

// Configurar Axios para enviar cookies
import '@/plugins/axios'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Create vue app
const app = createApp(App)


// Register plugins (load lazily). Await so we can catch import-time errors in console.
await registerPlugins(app)

// Mount vue app
app.mount('#app')
