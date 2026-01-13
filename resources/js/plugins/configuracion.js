import { empresaTitulo } from '@themeConfig'
import axios from 'axios'

// Función para cargar la configuración
async function cargarConfiguracion() {
  try {
    const response = await axios.get('/api/configuracion/publica')
    const config = response.data.data
    
    if (config) {
  // Actualizar el nombre de la empresa de forma reactiva
  empresaTitulo.value = config.nombre_empresa
      
      // Actualizar título del navegador
      document.title = config.titulo_navegador
    }
  } catch (error) {
    console.error('Error al cargar configuración del sistema:', error)
  }
}

// Exportar la función directamente
export default cargarConfiguracion