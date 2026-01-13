import { ref } from 'vue'
import axios from 'axios'

export function useConfiguracion() {
  const configuracion = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Obtener configuración actual (con auth)
  const obtenerConfiguracion = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/api/configuracion')
      configuracion.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener configuración'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Obtener configuración pública (sin auth - para login)
  const obtenerConfiguracionPublica = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/api/configuracion/publica')
      configuracion.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener configuración'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Actualizar configuración
  const actualizarConfiguracion = async (datos) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put('/api/configuracion', datos)
      configuracion.value = response.data.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar configuración'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    configuracion,
    loading,
    error,
    obtenerConfiguracion,
    obtenerConfiguracionPublica,
    actualizarConfiguracion,
  }
}