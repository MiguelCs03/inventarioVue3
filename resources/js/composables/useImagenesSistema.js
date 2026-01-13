import { ref } from 'vue'
import axios from 'axios'

export function useImagenesSistema() {
  const imagenes = ref([])
  const imagenActiva = ref(null)
  const imagenesActivas = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const configuracion = ref(null)

  // Obtener todas las imágenes de un tipo
  const obtenerImagenes = async (uso) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/api/imagenes-sistema/${uso}`)
      imagenes.value = response.data.data
      configuracion.value = response.data.configuracion
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener imágenes'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Obtener imagen activa de un tipo
  const obtenerImagenActiva = async (uso) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/api/imagenes-sistema/${uso}/activa`)
      imagenActiva.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener imagen activa'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Obtener todas las imágenes activas (sin auth - para login)
  const obtenerTodasActivas = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/api/imagenes-sistema/activas')
      imagenesActivas.value = response.data.data
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al obtener imágenes activas'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Subir nueva imagen
  const subirImagen = async (uso, archivo) => {
    loading.value = true
    error.value = null
    try {
      const formData = new FormData()
      formData.append('uso', uso)
      formData.append('imagen', archivo)

  const response = await axios.post(`/api/imagenes-sistema/${uso}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.response?.data?.errors?.imagen?.[0] || 'Error al subir imagen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Activar una imagen
  const activarImagen = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put(`/api/imagenes-sistema/${id}/activar`)

      // Recargar la página para que se muestre la animación de carga inicial
      // y se apliquen los cambios en todo el frontend.
      try {
        if (typeof window !== 'undefined') {
          // small delay so any toast can appear before reload
          setTimeout(() => {
            window.location.reload()
          }, 250)
        }
      } catch (e) {
        // noop
      }

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al activar imagen'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Eliminar imagen
  const eliminarImagen = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await axios.delete(`/api/imagenes-sistema/${id}`)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar imagen'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    imagenes,
    imagenActiva,
    imagenesActivas,
    loading,
    error,
    configuracion,
    obtenerImagenes,
    obtenerImagenActiva,
    obtenerTodasActivas,
    subirImagen,
    activarImagen,
    eliminarImagen,
  }
}