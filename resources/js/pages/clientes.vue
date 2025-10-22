<template>
  <VRow>
    <VCol cols="12">
      <!-- Header -->
      <VCard class="mb-2">
        <VCardText class="py-2">
          <div class="d-flex justify-space-between align-center">
            <div>
              <h2 class="text-h4 mb-1">Gestión de Clientes</h2>
              <p class="text-body-1 mb-0">Administra los clientes del sistema</p>
            </div>
            <VBtn 
              color="primary" 
              prepend-icon="tabler-plus"
              @click="abrirModalCrear"
            >
              Nuevo Cliente
            </VBtn>
          </div>
        </VCardText>
      </VCard>

      <!-- Filtros -->
      <VCard class="mb-2">
        <VCardText class="py-2">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                v-model="filtros.search"
                label="Buscar cliente"
                placeholder="Buscar por nombre, apellido o email..."
                prepend-inner-icon="tabler-search"
                clearable
                @input="buscarClientes"
              />
            </VCol>
            <VCol cols="12" md="3">
              <VSelect
                v-model="filtros.activo"
                label="Estado"
                :items="estadoOptions"
                clearable
                @update:model-value="buscarClientes"
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
          :items="clientes"
          :items-length="totalClientes"
          :loading="loading"
          class="elevation-1"
          @update:options="actualizarOpciones"
        >
          <!-- Nombre completo -->
          <template #item.nombre_completo="{ item }">
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
                  {{ item.nombre }} {{ item.apellido }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  {{ item.email }}
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
                @click="verCliente(item)"
              />
              <VBtn
                icon="tabler-pencil"
                size="small"
                color="primary"
                variant="text"
                @click="editarCliente(item)"
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
              {{ esEdicion ? 'Editar Cliente' : 'Nuevo Cliente' }}
            </span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="cerrarModal"
            />
          </VCardTitle>
          
          <VCardText>
            <VForm ref="formulario" @submit.prevent="guardarCliente">
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="clienteForm.nombre"
                    label="Nombre *"
                    :rules="validaciones.nombre"
                    required
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="clienteForm.apellido"
                    label="Apellido *"
                    :rules="validaciones.apellido"
                    required
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="clienteForm.email"
                    label="Email *"
                    type="email"
                    :rules="validaciones.email"
                    required
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="clienteForm.telefono"
                    label="Teléfono"
                    :rules="validaciones.telefono"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="clienteForm.direccion"
                    label="Dirección"
                    rows="3"
                  />
                </VCol>
                <VCol cols="12">
                  <VSwitch
                    v-model="clienteForm.activo"
                    label="Cliente activo"
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
              @click="guardarCliente"
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
        <VCard v-if="clienteDetalle">
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">Detalles del Cliente</span>
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
                  {{ obtenerIniciales(clienteDetalle) }}
                </VAvatar>
                <h3 class="mt-4">
                  {{ clienteDetalle.nombre }} {{ clienteDetalle.apellido }}
                </h3>
                <VChip
                  :color="clienteDetalle.activo ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                  class="mt-2"
                >
                  {{ clienteDetalle.activo ? 'ACTIVO' : 'INACTIVO' }}
                </VChip>
              </VCol>
              
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Email</div>
                <div class="text-body-1">{{ clienteDetalle.email }}</div>
              </VCol>
              
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Teléfono</div>
                <div class="text-body-1">{{ clienteDetalle.telefono || 'No especificado' }}</div>
              </VCol>
              
              <VCol cols="12">
                <div class="text-body-2 text-medium-emphasis">Dirección</div>
                <div class="text-body-1">{{ clienteDetalle.direccion || 'No especificada' }}</div>
              </VCol>
              
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Fecha de registro</div>
                <div class="text-body-1">{{ formatearFecha(clienteDetalle.created_at) }}</div>
              </VCol>
              
              <VCol cols="6">
                <div class="text-body-2 text-medium-emphasis">Última actualización</div>
                <div class="text-body-1">{{ formatearFecha(clienteDetalle.updated_at) }}</div>
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
const clientes = ref([])
const totalClientes = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const clienteDetalle = ref(null)

// Filtros
const filtros = reactive({
  search: '',
  activo: null
})

// Formulario
const clienteForm = reactive({
  id: null,
  nombre: '',
  apellido: '',
  email: '',
  telefono: '',
  direccion: '',
  activo: true
})

// Opciones
const estadoOptions = [
  { title: 'Activo', value: true },
  { title: 'Inactivo', value: false }
]

// Headers tabla
const headers = [
  { title: 'Cliente', key: 'nombre_completo', sortable: false },
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
  apellido: [
    v => !!v || 'El apellido es requerido',
    v => (v && v.length <= 100) || 'El apellido debe tener máximo 100 caracteres'
  ],
  email: [
    v => !!v || 'El email es requerido',
    v => /.+@.+\..+/.test(v) || 'El email debe ser válido'
  ],
  telefono: [
    v => !v || v.length <= 20 || 'El teléfono debe tener máximo 20 caracteres'
  ]
}

// Métodos
const cargarClientes = async () => {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: itemsPerPage.value,
      search: filtros.search || undefined,
      activo: filtros.activo !== null ? filtros.activo : undefined
    }

    const response = await axios.get('/api/clientes', { params })
    clientes.value = response.data.data
    totalClientes.value = response.data.total
  } catch (error) {
    console.error('Error al cargar clientes:', error)
  } finally {
    loading.value = false
  }
}

const buscarClientes = () => {
  page.value = 1
  cargarClientes()
}

const actualizarOpciones = (opciones) => {
  page.value = opciones.page
  itemsPerPage.value = opciones.itemsPerPage
  cargarClientes()
}

const abrirModalCrear = () => {
  esEdicion.value = false
  resetearFormulario()
  modalVisible.value = true
}

const editarCliente = (cliente) => {
  esEdicion.value = true
  Object.assign(clienteForm, cliente)
  modalVisible.value = true
}

const verCliente = (cliente) => {
  clienteDetalle.value = cliente
  modalVerVisible.value = true
}

const guardarCliente = async () => {
  const { valid } = await formulario.value.validate()
  if (!valid) return

  guardando.value = true
  try {
    if (esEdicion.value) {
      await axios.put(`/api/clientes/${clienteForm.id}`, clienteForm)
    } else {
      await axios.post('/api/clientes', clienteForm)
    }
    
    cerrarModal()
    cargarClientes()
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al guardar cliente:', error)
    // Aquí podrías mostrar un snackbar de error
  } finally {
    guardando.value = false
  }
}

const toggleEstado = async (cliente) => {
  try {
    const endpoint = cliente.activo ? 'deactivate' : 'activate'
    await axios.put(`/api/clientes/${cliente.id}/${endpoint}`)
    
    cliente.activo = !cliente.activo
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
  Object.assign(clienteForm, {
    id: null,
    nombre: '',
    apellido: '',
    email: '',
    telefono: '',
    direccion: '',
    activo: true
  })
}

const obtenerIniciales = (cliente) => {
  return `${cliente.nombre?.[0] || ''}${cliente.apellido?.[0] || ''}`.toUpperCase()
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
  cargarClientes()
})

// Meta de la página
definePage({
  meta: {
    layout: 'default',
    action: 'read',
    subject: 'Clientes',
  }
})
</script>
