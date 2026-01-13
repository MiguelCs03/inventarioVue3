<script setup>
import { ref, onMounted } from 'vue'
import { useConfiguracion } from '@/composables/useConfiguracion'
import { useImagenesSistema } from '@/composables/useImagenesSistema'
import SelectorImagenModal from '@/components/SelectorImagenModal.vue'

definePage({
  meta: {
    requiresAuth: true,
    section: 'configuracion-sistema',
    action: 'read',
    subject: 'configuracion-sistema',
    navActiveLink: 'configuracion-sistema',
    layoutWrapperClasses: 'layout-content-height-fixed',
  },
})

const { configuracion, obtenerConfiguracion, actualizarConfiguracion, loading: loadingConfig } = useConfiguracion()
const { imagenesActivas, obtenerTodasActivas } = useImagenesSistema()

const formData = ref({
  nombre_empresa: '',
  titulo_navegador: '',
})

const guardandoConfig = ref(false)
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

// Modal de imágenes
const modalAbierto = ref(false)
const usoSeleccionado = ref('')
const tituloModal = ref('')

const tiposImagenes = [
  {
    uso: 'logo_header',
    titulo: 'Logo de Cabecera',
    descripcion: 'Se muestra en la parte superior izquierda de toda la aplicación',
    icono: 'tabler-photo',
  },
  {
    uso: 'login_ilustracion',
    titulo: 'Ilustración de Login',
    descripcion: 'Imagen grande que aparece en el lado izquierdo de la página de login',
    icono: 'tabler-image',
  },
  {
    uso: 'favicon',
    titulo: 'Favicon',
    descripcion: 'Icono pequeño que aparece en la pestaña del navegador',
    icono: 'tabler-world',
  },
  {
    uso: 'loader',
    titulo: 'Animación de Carga',
    descripcion: 'Se muestra mientras las páginas están cargando',
    icono: 'tabler-loader',
  },
]

onMounted(async () => {
  await cargarDatos()
})

const cargarDatos = async () => {
  try {
    const config = await obtenerConfiguracion()
    formData.value = {
      nombre_empresa: config.nombre_empresa,
      titulo_navegador: config.titulo_navegador,
    }
    await obtenerTodasActivas()
  } catch (error) {
    mostrarMensaje('Error al cargar configuración', 'error')
  }
}

const guardarConfiguracion = async () => {
  guardandoConfig.value = true
  try {
    await actualizarConfiguracion(formData.value)
    mostrarMensaje('Configuración actualizada correctamente', 'success')
    
    // Actualizar el título del navegador en tiempo real
    document.title = formData.value.titulo_navegador
  } catch (error) {
    mostrarMensaje('Error al actualizar configuración', 'error')
  } finally {
    guardandoConfig.value = false
  }
}

const abrirSelectorImagen = (uso, titulo) => {
  usoSeleccionado.value = uso
  tituloModal.value = titulo
  modalAbierto.value = true
}

const cerrarModal = async () => {
  modalAbierto.value = false
  await obtenerTodasActivas()
}

const mostrarMensaje = (mensaje, color = 'success') => {
  snackbarMessage.value = mensaje
  snackbarColor.value = color
  showSnackbar.value = true
}

const getImagenUrl = (uso) => {
  if (!imagenesActivas.value || !imagenesActivas.value[uso]) {
    return null
  }
  return imagenesActivas.value[uso].ruta
}

const getImagenInfo = (uso) => {
  if (!imagenesActivas.value || !imagenesActivas.value[uso]) {
    return { nombre: 'Sin imagen', tamaño: '', dimensiones: '' }
  }
  const img = imagenesActivas.value[uso]
  return {
    nombre: img.nombre_archivo,
    tamaño: `${img.tamaño_kb} KB`,
    dimensiones: `${img.ancho}x${img.alto}`,
  }
}
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <h2 class="text-h4 mb-2">
           Configuración del Sistema
        </h2>
        <p class="text-body-1 text-medium-emphasis mb-6">
          Personaliza el nombre, título e imágenes de tu sistema
        </p>
      </VCol>
    </VRow>

    <!-- Configuración de Texto -->
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardItem>
            <VCardTitle> Información de la Empresa</VCardTitle>
            <VCardSubtitle>Configura el nombre y título que aparecerán en el sistema</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="formData.nombre_empresa"
                  label="Nombre de la Empresa"
                  placeholder="Intecruz"
                  prepend-inner-icon="tabler-building"
                  :disabled="loadingConfig"
                />
              </VCol>

              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="formData.titulo_navegador"
                  label="Título del Navegador"
                  placeholder="Intecruz - Sistema de Inventario"
                  prepend-inner-icon="tabler-browser"
                  :disabled="loadingConfig"
                />
              </VCol>

              <VCol cols="12">
                <div class="d-flex justify-end">
                  <VBtn
                    color="primary"
                    :loading="guardandoConfig"
                    :disabled="loadingConfig"
                    @click="guardarConfiguracion"
                  >
                    <VIcon
                      start
                      icon="tabler-device-floppy"
                    />
                    Guardar Cambios
                  </VBtn>
                </div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Imágenes del Sistema -->
    <VRow class="mt-6">
      <VCol cols="12">
        <h3 class="text-h5 mb-4">
           Imágenes del Sistema
        </h3>
      </VCol>

      <VCol
        v-for="tipo in tiposImagenes"
        :key="tipo.uso"
        cols="12"
        md="6"
      >
        <VCard class="h-100">
          <VCardItem>
            <template #prepend>
              <VIcon
                :icon="tipo.icono"
                size="24"
                color="primary"
              />
            </template>
            <VCardTitle>{{ tipo.titulo }}</VCardTitle>
            <VCardSubtitle>{{ tipo.descripcion }}</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <div class="d-flex flex-column align-center">
              <!-- Vista previa de la imagen -->
              <div
                class="imagen-preview mb-4"
                :class="{ 'imagen-placeholder': !getImagenUrl(tipo.uso) }"
              >
                <img
                  v-if="getImagenUrl(tipo.uso)"
                  :src="`/storage/${getImagenUrl(tipo.uso)}`"
                  :alt="tipo.titulo"
                  class="imagen-actual"
                >
                <div
                  v-else
                  class="d-flex flex-column align-center justify-center h-100"
                >
                  <VIcon
                    :icon="tipo.icono"
                    size="48"
                    color="grey"
                  />
                  <span class="text-caption text-medium-emphasis mt-2">Sin imagen</span>
                </div>
              </div>

              <!-- Info de la imagen -->
              <div class="text-center mb-3">
                <div class="text-body-2 font-weight-medium">
                  {{ getImagenInfo(tipo.uso).nombre }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ getImagenInfo(tipo.uso).tamaño }}
                  <span v-if="getImagenInfo(tipo.uso).dimensiones">
                    • {{ getImagenInfo(tipo.uso).dimensiones }}
                  </span>
                </div>
              </div>

              <!-- Botón cambiar -->
              <VBtn
                color="primary"
                variant="tonal"
                @click="abrirSelectorImagen(tipo.uso, tipo.titulo)"
              >
                <VIcon
                  start
                  icon="tabler-refresh"
                />
                Cambiar
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Modal de Selector de Imágenes -->
    <SelectorImagenModal
      v-model="modalAbierto"
      :uso="usoSeleccionado"
      :titulo="tituloModal"
      @cerrar="cerrarModal"
      @actualizado="cerrarModal"
    />

    <!-- Snackbar -->
    <VSnackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      location="top"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </div>
</template>

<style scoped>
.imagen-preview {
  width: 200px;
  height: 150px;
  border: 2px dashed rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.imagen-placeholder {
  background-color: rgba(var(--v-theme-surface), 0.5);
}

.imagen-actual {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>