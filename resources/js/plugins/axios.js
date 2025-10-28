import axios from 'axios'

// Plugin para configurar axios de forma segura sin ejecutar código en import-time
export default function () {
  // Configurar Axios para enviar cookies en cada petición
  axios.defaults.withCredentials = true
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
}
