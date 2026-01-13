import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import cargarConfiguracion from '@/plugins/configuracion'

// Configurar Axios para enviar cookies
import '@/plugins/axios'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Create vue app
const app = createApp(App)

// Cargar configuración del sistema ANTES de registrar otros plugins
await cargarConfiguracion()

// Register plugins (load lazily). Await so we can catch import-time errors in console.
await registerPlugins(app)

// Mount vue app
app.mount('#app')

// Ocultar el overlay de carga inicial (si existe) una vez que Vue está montado
try {
	if (typeof document !== 'undefined') {
		const loader = document.getElementById('loading-bg')
		if (loader) {
			// Dar un pequeño delay para asegurar que todo haya rendereado
			setTimeout(() => {
				loader.style.display = 'none'
			}, 80)
		}
	}
} catch (e) {
	// noop
}