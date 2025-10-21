<template>
  <VRow>
    <VCol cols="12">
      <!-- Header -->
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex justify-space-between align-center">
            <div>
              <h2 class="text-h4 mb-2">Gestión de Proveedores</h2>
              <p class="text-body-1 mb-0">Administra los proveedores del sistema</p>
            </div>
            <VBtn 
              color="primary" 
              prepend-icon="tabler-plus"
              @click="abrirModalCrear"
            >
              Nuevo Proveedor
            </VBtn>
          </div>
        </VCardText>
      </VCard>

      <!-- Filtros -->
      <VCard class="mb-6">
        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                v-model="filtros.search"
                label="Buscar proveedor"
                placeholder="Buscar por nombre, contacto, email..."
                prepend-inner-icon="tabler-search"
                clearable
                @input="buscarProveedores"
              />
            </VCol>
            <VCol cols="12" md="3">
              <VSelect
                v-model="filtros.activo"
                label="Estado"
                :items="estadoOptions"
                clearable
                @update:model-value="buscarProveedores"
              />
            </VCol>
            <VCol cols="12" md="3">
              
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- Tabla -->
      <VCard>
        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :headers="headers"
          :items="proveedores"
          :items-length="totalProveedores"
          :loading="loading"
          class="elevation-1"
          @update:options="actualizarOpciones"
        >
          <!-- Nombre -->
          <template #item.nombre="{ item }">
            <div class="d-flex align-center">
              <VAvatar
                size="40"
                color="primary"
                variant="tonal"
                class="me-3"
              >
                {{ obtenerIniciales(item) }}
              </VAvatar>
              <div>
                <div class="text-body-1 font-weight-medium">
                  {{ item.nombre }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ item.contacto }}
                </div>
              </div>
            </div>
          </template>

          <!-- Estado -->
          <template #item.activo="{ item }">
            <VChip
              :color="item.activo ? 'success' : 'error'"
              size="small"
              variant="tonal"
            >
              {{ item.activo ? 'ACTIVO' : 'INACTIVO' }}
            </VChip>
          </template>

          <!-- Acciones -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <VBtn
                icon="tabler-eye"
                size="small"
                color="info"
                variant="text"
                @click="verProveedor(item)"
              />
              <VBtn
                icon="tabler-pencil"
                size="small"
                color="primary"
                variant="text"
                @click="editarProveedor(item)"
              />
              <VBtn
                :icon="item.activo ? 'tabler-user-off' : 'tabler-user-check'"
                size="small"
                :color="item.activo ? 'warning' : 'success'"
                variant="text"
                @click="toggleEstado(item)"
              />
            </div>
          </template>
        </VDataTableServer>
      </VCard>

      <!-- Modal Crear/Editar -->
      <VDialog 
        v-model="modalVisible" 
        max-width="800px"
        persistent
      >
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">
              {{ esEdicion ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
            </span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="cerrarModal"
            />
          </VCardTitle>
          
          <VCardText>
            <VForm ref="formulario" @submit.prevent="guardarProveedor">
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="proveedorForm.nombre"
                    label="Nombre *"
                    :rules="validaciones.nombre"
                    required
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="proveedorForm.contacto"
                    label="Contacto"
                    :rules="validaciones.contacto"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="proveedorForm.email"
                    label="Email"
                    type="email"
                    :rules="validaciones.email"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="proveedorForm.telefono"
                    label="Teléfono"
                    :rules="validaciones.telefono"
                  />
                </VCol>
                <VCol cols="12">
                  <VSwitch
                    v-model="proveedorForm.activo"
                    label="Proveedor activo"
                    inset
                  />
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
          
          <VCardActions>
            <VSpacer />
            <VBtn 
              color="grey" 
              variant="text"
              @click="cerrarModal"
            >
              Cancelar
            </VBtn>
            <VBtn 
              color="primary" 
              variant="elevated"
              :loading="guardando"
              @click="guardarProveedor"
            >
              {{ esEdicion ? 'Actualizar' : 'Guardar' }}
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>

      <!-- Modal Ver Detalles -->
      <VDialog 
        v-model="modalVerVisible" 
        max-width="600px"
      >
        <VCard v-if="proveedorDetalle">
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">Detalles del Proveedor</span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="modalVerVisible = false"
            />
          </VCardTitle>
          
          <VCardText>
            <VRow>
              <VCol cols="12" class="text-center pb-6">
                <VAvatar
                  size="80"
                  color="primary"
                  variant="tonal"
                >
                  {{ obtenerIniciales(proveedorDetalle) }}
                </VAvatar>
                <h3 class="mt-4">
                  {{ proveedorDetalle.nombre }}
                </h3>
                <VChip
                  :color="proveedorDetalle.activo ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                  class="mt-2"
                >
                  {{ proveedorDetalle.activo ? 'ACTIVO' : 'INACTIVO' }}
                </VChip>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Contacto</div>
                <div class="text-body-1">{{ proveedorDetalle.contacto || 'No especificado' }}</div>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Email</div>
                <div class="text-body-1">{{ proveedorDetalle.email || 'No especificado' }}</div>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Teléfono</div>
                <div class="text-body-1">{{ proveedorDetalle.telefono || 'No especificado' }}</div>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Fecha de registro</div>
                <div class="text-body-1">{{ formatearFecha(proveedorDetalle.created_at) }}</div>
              </VCol>
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Última actualización</div>
                <div class="text-body-1">{{ formatearFecha(proveedorDetalle.updated_at) }}</div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VDialog>
    </VCol>
  </VRow>
</template>

<script setup>
import axios from 'axios'
import { onMounted, reactive, ref } from 'vue'

// Estado
const loading = ref(false)
const guardando = ref(false)
const modalVisible = ref(false)
const modalVerVisible = ref(false)
const esEdicion = ref(false)
const formulario = ref(null)

// Datos
const proveedores = ref([])
const totalProveedores = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const proveedorDetalle = ref(null)

// Filtros
const filtros = reactive({
  search: '',
  activo: null
})

// Formulario
const proveedorForm = reactive({
  id: null,
  nombre: '',
  contacto: '',
  email: '',
  telefono: '',
  activo: true
})

// Opciones
const estadoOptions = [
  { title: 'Activo', value: true },
  { title: 'Inactivo', value: false }
]

// Headers tabla
const headers = [
  { title: 'Proveedor', key: 'nombre', sortable: false },
  { title: 'Contacto', key: 'contacto', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Teléfono', key: 'telefono', sortable: true },
  { title: 'Estado', key: 'activo', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 150 }
]

// Validaciones
const validaciones = {
  nombre: [
    v => !!v || 'El nombre es requerido',
    v => (v && v.length <= 100) || 'El nombre debe tener máximo 100 caracteres'
  ],
  contacto: [
    v => !v || v.length <= 100 || 'El contacto debe tener máximo 100 caracteres'
  ],
  email: [
    v => !v || /.+@.+\..+/.test(v) || 'El email debe ser válido'
  ],
  telefono: [
    v => !v || v.length <= 20 || 'El teléfono debe tener máximo 20 caracteres'
  ]
}

// Métodos
const cargarProveedores = async () => {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: itemsPerPage.value,
      search: filtros.search || undefined,
      activo: filtros.activo !== null ? filtros.activo : undefined
    }

    const response = await axios.get('/api/proveedores', { params })
    proveedores.value = response.data.data
    totalProveedores.value = response.data.total
  } catch (error) {
    console.error('Error al cargar proveedores:', error)
  } finally {
    loading.value = false
  }
}

const buscarProveedores = () => {
  page.value = 1
  cargarProveedores()
}

const actualizarOpciones = (opciones) => {
  page.value = opciones.page
  itemsPerPage.value = opciones.itemsPerPage
  cargarProveedores()
}

const abrirModalCrear = () => {
  esEdicion.value = false
  resetearFormulario()
  modalVisible.value = true
}

const editarProveedor = (proveedor) => {
  esEdicion.value = true
  Object.assign(proveedorForm, proveedor)
  modalVisible.value = true
}

const verProveedor = (proveedor) => {
  proveedorDetalle.value = proveedor
  modalVerVisible.value = true
}

const guardarProveedor = async () => {
  const { valid } = await formulario.value.validate()
  if (!valid) return

  guardando.value = true
  try {
    if (esEdicion.value) {
      await axios.put(`/api/proveedores/${proveedorForm.id}`, proveedorForm)
    } else {
      await axios.post('/api/proveedores', proveedorForm)
    }
    
    cerrarModal()
    cargarProveedores()
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al guardar proveedor:', error)
    // Aquí podrías mostrar un snackbar de error
  } finally {
    guardando.value = false
  }
}

const toggleEstado = async (proveedor) => {
  try {
    const endpoint = proveedor.activo ? 'deactivate' : 'activate'
    await axios.put(`/api/proveedores/${proveedor.id}/${endpoint}`)
    proveedor.activo = !proveedor.activo
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al cambiar estado:', error)
    // Aquí podrías mostrar un snackbar de error
  }
}

const cerrarModal = () => {
  modalVisible.value = false
  resetearFormulario()
  if (formulario.value) {
    formulario.value.resetValidation()
  }
}

const resetearFormulario = () => {
  Object.assign(proveedorForm, {
    id: null,
    nombre: '',
    contacto: '',
    email: '',
    telefono: '',
    activo: true
  })
}

const obtenerIniciales = (proveedor) => {
  return `${proveedor.nombre?.[0] || ''}${proveedor.contacto?.[0] || ''}`.toUpperCase()
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Cargar datos al montar
onMounted(() => {
  cargarProveedores()
})

// Meta de la página
definePage({
  meta: {
    layout: 'default',
    action: 'read',
    subject: 'Proveedores',
  }
})
</script>
