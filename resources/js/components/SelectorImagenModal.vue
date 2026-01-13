<script setup>
import { ref, watch, computed } from 'vue'
import { useImagenesSistema } from '@/composables/useImagenesSistema'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true,
  },
  uso: {
    type: String,
    required: true,
  },
  titulo: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue', 'cerrar', 'actualizado'])

const {
  imagenes,
  loading,
  error,
  configuracion,
  obtenerImagenes,
  subirImagen,
  activarImagen,
  eliminarImagen,
} = useImagenesSistema()

const mostrarDialogoSubir = ref(false)
const archivoSeleccionado = ref(null)
const previsualizacion = ref(null)
const inputFile = ref(null)
const loadingAccion = ref(false)

const showSnackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

// Dialogo de confirmación para eliminar
const dialogoEliminar = ref(false)
const imagenAEliminar = ref(null)

const internalValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

// Cargar imágenes cuando se abre el modal
watch(() => props.modelValue, async (newVal) => {
  if (newVal && props.uso) {
    await cargarImagenes()
  }
})

const cargarImagenes = async () => {
  try {
    await obtenerImagenes(props.uso)
  } catch (err) {
    mostrarMensaje('Error al cargar imágenes', 'error')
  }
}

const cerrar = () => {
  internalValue.value = false
  emit('cerrar')
}

const abrirDialogoSubir = () => {
  mostrarDialogoSubir.value = true
  archivoSeleccionado.value = null
  previsualizacion.value = null
}

const onFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    archivoSeleccionado.value = file
    
    // Crear preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previsualizacion.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const triggerFileInput = () => {
  inputFile.value.click()
}

const subirNuevaImagen = async () => {
  if (!archivoSeleccionado.value) {
    mostrarMensaje('Por favor selecciona una imagen', 'warning')
    return
  }

  loadingAccion.value = true
  try {
    await subirImagen(props.uso, archivoSeleccionado.value)
    mostrarMensaje('Imagen subida correctamente', 'success')
    mostrarDialogoSubir.value = false
    await cargarImagenes()
  } catch (err) {
    mostrarMensaje(error.value || 'Error al subir imagen', 'error')
  } finally {
    loadingAccion.value = false
  }
}

const activar = async (id) => {
  loadingAccion.value = true
  try {
    await activarImagen(id)
    mostrarMensaje('Imagen activada correctamente', 'success')
    await cargarImagenes()
    emit('actualizado')
  } catch (err) {
    mostrarMensaje('Error al activar imagen', 'error')
  } finally {
    loadingAccion.value = false
  }
}

const confirmarEliminar = (imagen) => {
  imagenAEliminar.value = imagen
  dialogoEliminar.value = true
}

const eliminar = async () => {
  if (!imagenAEliminar.value) return

  loadingAccion.value = true
  try {
    await eliminarImagen(imagenAEliminar.value.id)
    mostrarMensaje('Imagen eliminada correctamente', 'success')
    dialogoEliminar.value = false
    imagenAEliminar.value = null
    await cargarImagenes()
  } catch (err) {
    mostrarMensaje(error.value || 'Error al eliminar imagen', 'error')
  } finally {
    loadingAccion.value = false
  }
}

const mostrarMensaje = (mensaje, color = 'success') => {
  snackbarMessage.value = mensaje
  snackbarColor.value = color
  showSnackbar.value = true
}

const formatBytes = (kb) => {
  if (kb < 1024) return `${kb} KB`
  return `${(kb / 1024).toFixed(2)} MB`
}

const puedeSubirMas = computed(() => {
  if (!configuracion.value) return true
  return imagenes.value.length < configuracion.value.max_imagenes
})
</script>

<template>
  <VDialog
    v-model="internalValue"
    max-width="900"
    persistent
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between">
        <div class="d-flex align-center gap-2">
          <VIcon icon="tabler-photo" />
          <span>{{ titulo }}</span>
        </div>
        <VBtn
          icon
          variant="text"
          size="small"
          @click="cerrar"
        >
          <VIcon icon="tabler-x" />
        </VBtn>
      </VCardTitle>

      <VDivider />

      <VCardText class="pt-4">
        <!-- Información -->
        <div class="mb-4 d-flex align-center justify-space-between">
          <div>
            <span class="text-body-2">
              Historial de imágenes ({{ imagenes.length }} guardadas)
            </span>
          </div>
          <VChip
            v-if="configuracion"
            size="small"
            color="info"
          >
            Máximo: {{ configuracion.max_imagenes }}
          </VChip>
        </div>

        <!-- Grid de imágenes -->
        <VRow v-if="!loading">
          <!-- Imágenes existentes -->
          <VCol
            v-for="imagen in imagenes"
            :key="imagen.id"
            cols="12"
            sm="6"
            md="4"
          >
            <VCard
              :class="{ 'border-primary': imagen.es_activa }"
              class="h-100"
              :elevation="imagen.es_activa ? 4 : 1"
            >
              <div class="imagen-container">
                <img
                  :src="`/storage/${imagen.ruta}`"
                  :alt="imagen.nombre_archivo"
                  class="imagen-grid"
                >
                <VChip
                  v-if="imagen.es_activa"
                  color="success"
                  size="small"
                  class="chip-activa"
                >
                  <VIcon
                    start
                    icon="tabler-check"
                  />
                  ACTIVA
                </VChip>
              </div>

              <VCardText>
                <div class="text-body-2 font-weight-medium mb-1 text-truncate">
                  {{ imagen.nombre_archivo }}
                </div>
                <div class="text-caption text-medium-emphasis">
                  {{ formatBytes(imagen.tamaño_kb) }} • {{ imagen.ancho }}x{{ imagen.alto }}
                </div>
              </VCardText>

              <VCardActions>
                <VBtn
                  v-if="!imagen.es_activa"
                  size="small"
                  color="primary"
                  variant="tonal"
                  :loading="loadingAccion"
                  @click="activar(imagen.id)"
                >
                  <VIcon
                    start
                    icon="tabler-check"
                  />
                  Usar
                </VBtn>
                <VChip
                  v-else
                  size="small"
                  color="success"
                  variant="tonal"
                >
                  En uso
                </VChip>

                <VSpacer />

                <VBtn
                  v-if="!imagen.es_activa"
                  size="small"
                  color="error"
                  variant="text"
                  icon
                  :loading="loadingAccion"
                  @click="confirmarEliminar(imagen)"
                >
                  <VIcon icon="tabler-trash" />
                </VBtn>
              </VCardActions>
            </VCard>
          </VCol>

          <!-- Botón para subir nueva -->
          <VCol
            v-if="puedeSubirMas"
            cols="12"
            sm="6"
            md="4"
          >
            <VCard
              class="h-100 d-flex align-center justify-center cursor-pointer hover-card"
              @click="abrirDialogoSubir"
            >
              <VCardText class="text-center">
                <VIcon
                  icon="tabler-plus"
                  size="48"
                  color="primary"
                />
                <div class="text-body-1 font-weight-medium mt-2">
                  Subir nueva imagen
                </div>
                <div class="text-caption text-medium-emphasis">
                  Click para agregar
                </div>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Mensaje límite alcanzado -->
          <VCol
            v-else
            cols="12"
          >
            <VAlert
              type="warning"
              variant="tonal"
            >
              Has alcanzado el límite de {{ configuracion?.max_imagenes }} imágenes. Elimina una para subir otra.
            </VAlert>
          </VCol>
        </VRow>

        <!-- Loading -->
        <div
          v-if="loading"
          class="text-center py-8"
        >
          <VProgressCircular
            indeterminate
            color="primary"
          />
        </div>

        <!-- Sin imágenes -->
        <VAlert
          v-if="!loading && imagenes.length === 0"
          type="info"
          variant="tonal"
        >
          No hay imágenes guardadas. Sube tu primera imagen haciendo click en el botón de arriba.
        </VAlert>

        <!-- Info de requisitos -->
        <VAlert
          v-if="configuracion"
          type="info"
          variant="tonal"
          class="mt-4"
        >
          <div class="text-caption">
            <strong>📋 Requisitos:</strong><br>
            • Formatos: {{ configuracion.formatos.join(', ').toUpperCase() }}<br>
            • Tamaño máximo: {{ configuracion.tamaño_max_mb }} MB<br>
            • Dimensiones recomendadas: {{ configuracion.ancho_recomendado }}x{{ configuracion.alto_recomendado }} px
          </div>
        </VAlert>
      </VCardText>

      <VDivider />

      <VCardActions>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="text"
          @click="cerrar"
        >
          Cerrar
        </VBtn>
      </VCardActions>
    </VCard>

    <!-- Dialog para subir imagen -->
    <VDialog
      v-model="mostrarDialogoSubir"
      max-width="600"
    >
      <VCard>
        <VCardTitle>
          ➕ Subir Nueva Imagen
        </VCardTitle>

        <VDivider />

        <VCardText>
          <!-- Input oculto -->
          <input
            ref="inputFile"
            type="file"
            accept="image/*"
            style="display: none"
            @change="onFileChange"
          >

          <!-- Área de arrastrar o click -->
          <div
            class="upload-area mb-4"
            @click="triggerFileInput"
          >
            <VIcon
              icon="tabler-cloud-upload"
              size="48"
              color="primary"
            />
            <div class="text-body-1 mt-2">
              📁 Arrastra la imagen aquí
            </div>
            <div class="text-body-2 text-medium-emphasis">
              o
            </div>
            <VBtn
              color="primary"
              variant="tonal"
              size="small"
            >
              Seleccionar archivo
            </VBtn>
          </div>

          <!-- Vista previa -->
          <div
            v-if="previsualizacion"
            class="preview-container"
          >
            <div class="text-subtitle-2 mb-2">
              🖼️ Vista Previa
            </div>
            <div class="preview-image-wrapper">
              <img
                :src="previsualizacion"
                alt="Preview"
                class="preview-image"
              >
            </div>
            <div
              v-if="archivoSeleccionado"
              class="text-caption text-center mt-2"
            >
              {{ archivoSeleccionado.name }} • {{ (archivoSeleccionado.size / 1024).toFixed(2) }} KB
            </div>
          </div>
        </VCardText>

        <VDivider />

        <VCardActions>
          <VBtn
            variant="text"
            @click="mostrarDialogoSubir = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="primary"
            :disabled="!archivoSeleccionado"
            :loading="loadingAccion"
            @click="subirNuevaImagen"
          >
            <VIcon
              start
              icon="tabler-upload"
            />
            Subir y Usar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Dialog de confirmación para eliminar -->
    <VDialog
      v-model="dialogoEliminar"
      max-width="400"
    >
      <VCard>
        <VCardTitle>
          ⚠️ Confirmar Eliminación
        </VCardTitle>

        <VCardText>
          ¿Estás seguro de eliminar esta imagen?
          <br>
          <strong>{{ imagenAEliminar?.nombre_archivo }}</strong>
          <br><br>
          Esta acción no se puede deshacer.
        </VCardText>

        <VCardActions>
          <VBtn
            variant="text"
            @click="dialogoEliminar = false"
          >
            Cancelar
          </VBtn>
          <VSpacer />
          <VBtn
            color="error"
            :loading="loadingAccion"
            @click="eliminar"
          >
            Eliminar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar -->
    <VSnackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      location="top"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </VDialog>
</template>

<style scoped>
.imagen-container {
  position: relative;
  width: 100%;
  height: 180px;
  overflow: hidden;
  background-color: rgba(var(--v-theme-surface), 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.imagen-grid {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.chip-activa {
  position: absolute;
  top: 8px;
  right: 8px;
}

.upload-area {
  border: 2px dashed rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
}

.upload-area:hover {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.preview-container {
  margin-top: 20px;
}

.preview-image-wrapper {
  border: 2px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  padding: 12px;
  display: flex;
  justify-content: center;
  background-color: rgba(var(--v-theme-surface), 0.5);
}

.preview-image {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
}

.hover-card {
  transition: all 0.3s;
  min-height: 280px;
}

.hover-card:hover {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.cursor-pointer {
  cursor: pointer;
}
</style>