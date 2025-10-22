import axios from 'axios'

// Configurar Axios para enviar cookies en cada petición
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default axios
