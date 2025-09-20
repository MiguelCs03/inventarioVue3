<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Estados reactivos
const loading = ref(false)
const search = ref('')
const page = ref(1)
const itemsPerPage = ref(10)
const roles = ref([])
const showCreateDialog = ref(false)

// Formulario de nuevo rol
const newRole = ref({
  nombre: '',
  activo: true
})

// Headers de la tabla
const headers = [
  { title: 'Nombre', key: 'nombre', sortable: true },
  { title: 'Estado', key: 'activo', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 150 }
]

// Roles filtrados
const filteredRoles = computed(() => {
  // Asegurar que roles.value sea un array
  if (!Array.isArray(roles.value)) {
    return []
  }
  
  let filtered = roles.value

  if (search.value) {
    filtered = filtered.filter(role => 
      role.nombre.toLowerCase().includes(search.value.toLowerCase())
    )
  }

  return filtered
})

// Métodos
const cargarRoles = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/roles')
    roles.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    console.error('Error al cargar roles:', error)
    roles.value = [] // Asegurar que sea array en caso de error
    // Aquí podrías mostrar un snackbar de error
  } finally {
    loading.value = false
  }
}

const crearRol = () => {
  showCreateDialog.value = true
}

const guardarNuevoRol = async () => {
  try {
    const response = await axios.post('/api/roles', newRole.value)
    roles.value.push(response.data)
    showCreateDialog.value = false
    resetForm()
    console.log('Rol creado exitosamente')
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al crear rol:', error)
    // Aquí podrías mostrar un snackbar de error
  }
}

const editarRol = (rol) => {
  console.log('Editar rol:', rol)
  // Aquí abrir modal de edición
}

const verRol = (rol) => {
  console.log('Ver rol:', rol)
  // Aquí abrir modal o página de detalles del rol
}

const toggleEstadoRol = async (rol) => {
  try {
    const endpoint = rol.activo ? 'deactivate' : 'activate'
    await axios.put(`/api/roles/${rol.id}/${endpoint}`)
    
    // Actualizar estado local
    rol.activo = !rol.activo
    
    console.log(`Rol ${rol.activo ? 'activado' : 'desactivado'}`)
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al cambiar estado del rol:', error)
    // Aquí podrías mostrar un snackbar de error
  }
}

const resetForm = () => {
  newRole.value = {
    nombre: '',
    activo: true
  }
}

// Cargar datos al montar el componente
onMounted(() => {
  cargarRoles()
})
</script>

<template>
  <div>
    <!-- Header con título y botón de nuevo rol -->
    <VCard class="mb-6">
      <VCardText>
        <div class="d-flex justify-space-between align-center">
          <div>
            <h2 class="text-h4 mb-2">Gestión de Roles</h2>
            <p class="text-body-1 mb-0">Administra los roles del sistema</p>
          </div>
          <VBtn 
            color="primary" 
            prepend-icon="mdi-plus"
            @click="crearRol"
          >
            Nuevo Rol
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Filtros y búsqueda -->
    <VCard class="mb-6">
      <VCardText>
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="search"
              label="Buscar rol"
              placeholder="Buscar por nombre..."
              prepend-inner-icon="mdi-magnify"
              clearable
              density="comfortable"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Tabla de roles -->
    <VCard>
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredRoles"
        :loading="loading"
        :items-per-page="itemsPerPage"
        :search="search"
        class="elevation-1"
      >
        <!-- Estado activo/inactivo -->
        <template #item.activo="{ item }">
          <VChip
            :color="item.activo ? 'success' : 'error'"
            size="small"
            variant="tonal"
          >
            {{ item.activo ? 'ACTIVO' : 'INACTIVO' }}
          </VChip>
        </template>

        <!-- Botones de acción -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-2">
            <!-- Editar -->
            <VBtn
              icon="tabler-pencil"
              size="small"
              color="primary"
              variant="text"
              @click="editarRol(item)"
            />
            
            <!-- Activar/Desactivar -->
            <VBtn
              :icon="item.activo ? 'tabler-user-off' : 'tabler-user-check'"
              size="small"
              :color="item.activo ? 'warning' : 'success'"
              variant="text"
              @click="toggleEstadoRol(item)"
            />
            
            <!-- Ver detalles -->
            <VBtn
              icon="tabler-eye"
              size="small"
              color="info"
              variant="text"
              @click="verRol(item)"
            />
          </div>
        </template>

        <!-- Mensaje cuando no hay datos -->
        <template #no-data>
          <div class="text-center py-6">
            <VIcon size="64" color="grey-lighten-2">mdi-account-off</VIcon>
            <p class="text-h6 mt-4">No hay roles registrados</p>
            <p class="text-body-2">Crea tu primer rol haciendo clic en "Nuevo Rol"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear nuevo rol -->
    <VDialog 
      v-model="showCreateDialog" 
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle class="headline">
          <span class="text-h5">Crear Nuevo Rol</span>
        </VCardTitle>
        
        <VCardText>
          <VContainer>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="newRole.nombre"
                  label="Nombre del rol"
                  required
                  prepend-inner-icon="mdi-account-group"
                />
              </VCol>
              
              <VCol cols="12">
                <VSwitch
                  v-model="newRole.activo"
                  label="Activo"
                  inset
                />
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>
        
        <VCardActions>
          <VSpacer />
          <VBtn 
            color="grey" 
            variant="text"
            @click="showCreateDialog = false; resetForm()"
          >
            Cancelar
          </VBtn>
          <VBtn 
            color="primary" 
            variant="elevated"
            @click="guardarNuevoRol"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.elevation-1 {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}
</style>
