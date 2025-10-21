<template>
  <div>
    <!-- Header con título y botón de nuevo usuario -->
    <VCard class="mb-2 no-padding-card">
      <VCardText class="py-2 px-4">
        <div class="d-flex justify-space-between align-center">
          <div>
            <h2 class="text-h4 mb-1">Gestión de Usuarios</h2>
            <p class="text-body-1 mb-0">Administra los usuarios del sistema</p>
          </div>
          <VBtn 
            color="primary" 
            prepend-icon="mdi-plus"
            @click="crearUsuario"
          >
            Nuevo Usuario
          </VBtn>
        </div>
      </VCardText>
    </VCard>

    <!-- Filtros y búsqueda -->
    <VCard class="mb-2 no-padding-card">
      <VCardText class="py-2 px-4">
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="search"
              label="Buscar usuario"
              placeholder="Buscar por nombre, email..."
              prepend-inner-icon="mdi-magnify"
              clearable
              density="comfortable"
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSelect
              v-model="selectedRole"
              :items="roleOptions"
              label="Filtrar por rol"
              clearable
              density="comfortable"
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Tabla de usuarios -->
    <VCard>
      <VDataTable
        v-model:page="page"
        :headers="headers"
        :items="filteredUsers"
        :loading="loading"
        :items-per-page="itemsPerPage"
        :search="search"
        class="elevation-1"
      >
        <!-- Columna de roles (concatena nombres) -->
        <template #item.roles="{ item }">
          <span>
            {{ Array.isArray(item.roles) && item.roles.length
              ? item.roles.map(r => r?.nombre).filter(Boolean).join(', ')
              : '—' }}
          </span>
        </template>
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
              @click="editarUsuario(item)"
            />
            
            <!-- Activar/Desactivar -->
            <VBtn
              :icon="item.activo ? 'tabler-user-off' : 'tabler-user-check'"
              size="small"
              :color="item.activo ? 'warning' : 'success'"
              variant="text"
              @click="toggleEstadoUsuario(item)"
            />
            
            <!-- Ver detalles -->
            <VBtn
              icon="tabler-eye"
              size="small"
              color="info"
              variant="text"
              @click="verUsuario(item)"
            />
          </div>
        </template>

        <!-- Mensaje cuando no hay datos -->
        <template #no-data>
          <div class="text-center py-6">
            <VIcon size="64" color="grey-lighten-2">mdi-account-off</VIcon>
            <p class="text-h6 mt-4">No hay usuarios registrados</p>
            <p class="text-body-2">Crea tu primer usuario haciendo clic en "Nuevo Usuario"</p>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Modal para crear nuevo usuario -->
    <VDialog 
      v-model="showCreateDialog" 
      max-width="600px"
      persistent
    >
      <VCard>
        <VCardTitle class="headline">
          <span class="text-h5">Crear Nuevo Usuario</span>
        </VCardTitle>
        
        <VCardText>
          <VContainer>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newUser.name"
                  label="Nombre completo"
                  required
                  prepend-inner-icon="mdi-account"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newUser.email"
                  label="Email"
                  type="email"
                  required
                  prepend-inner-icon="mdi-email"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newUser.password"
                  label="Contraseña"
                  type="password"
                  required
                  prepend-inner-icon="mdi-lock"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newUser.fecha_nacimiento"
                  label="Fecha de nacimiento"
                  type="date"
                  prepend-inner-icon="mdi-calendar"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VTextField
                  v-model="newUser.cargo"
                  label="Cargo"
                  prepend-inner-icon="mdi-briefcase"
                />
              </VCol>
              
              <VCol cols="12" md="6">
                <VSelect
                  v-model="newUser.role_id"
                  :items="roles"
                  item-title="nombre"
                  item-value="id"
                  label="Rol"
                  prepend-inner-icon="mdi-account-group"
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
            @click="guardarNuevoUsuario"
          >
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar usuario -->
    <EditUserModal
      :user="usuarioEditando"
      :isOpen="isEditModalOpen"
      @close="isEditModalOpen = false"
      @updated="actualizarUsuario"
    />
  </div>
</template>

<script setup>
import EditUserModal from '@/views/apps/user/list/EditUserModal.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Estados reactivos
const loading = ref(false)
const search = ref('')
const selectedRole = ref(null)
const page = ref(1)
const itemsPerPage = ref(10)
const users = ref([])
const roles = ref([])
const showCreateDialog = ref(false)
const isEditModalOpen = ref(false)
const usuarioEditando = ref(null)

// Formulario de nuevo usuario
const newUser = ref({
  name: '',
  email: '',
  password: '',
  fecha_nacimiento: '',
  cargo: '',
  role_id: null
})

// Opciones para el select de roles
const roleOptions = ref([
  { title: 'Todos', value: null },
  { title: 'Administrador', value: 'Administrador' },
  { title: 'Jefe', value: 'Jefe' },
  { title: 'Trabajador', value: 'Trabajador' }
])

// Headers de la tabla
const headers = [
  { title: 'Nombre', key: 'name', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Cargo', key: 'cargo', sortable: true },
  { title: 'Rol', key: 'roles', sortable: false },
  { title: 'Estado', key: 'activo', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 150 }
]

// Usuarios filtrados
const filteredUsers = computed(() => {
  // Asegurar que users.value sea un array
  if (!Array.isArray(users.value)) {
    return []
  }
  
  let filtered = users.value

  if (selectedRole.value) {
    filtered = filtered.filter(user => {
      const userRoles = Array.isArray(user.roles) ? user.roles : []
      const hasRole = userRoles.some(r => r?.nombre === selectedRole.value)
      return hasRole || user.cargo === selectedRole.value
    })
  }

  return filtered
})

// Métodos
const cargarUsuarios = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/users')
    users.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
    users.value = [] // Asegurar que sea array en caso de error
    
  } finally {
    loading.value = false
  }
}

const cargarRoles = async () => {
  try {
    const response = await axios.get('/api/roles')
    roles.value = Array.isArray(response.data) ? response.data : []
  } catch (error) {
    console.error('Error al cargar roles:', error)
    roles.value = []
  }
}

const crearUsuario = () => {
  showCreateDialog.value = true
}

const guardarNuevoUsuario = async () => {
  try {
    // Convertir role_id (único) a arreglo 'roles' esperado por la API
    const payload = {
      name: newUser.value.name,
      email: newUser.value.email,
      password: newUser.value.password,
      fecha_nacimiento: newUser.value.fecha_nacimiento,
      cargo: newUser.value.cargo,
      roles: newUser.value.role_id ? [newUser.value.role_id] : [],
    }

    const response = await axios.post('/api/users', payload)
    users.value.push(response.data)
    showCreateDialog.value = false
    resetForm()
    console.log('Usuario creado exitosamente')
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al crear usuario:', error)
    // Aquí podrías mostrar un snackbar de error
  }
}

const resetForm = () => {
  newUser.value = {
    name: '',
    email: '',
    password: '',
    fecha_nacimiento: '',
    cargo: '',
    role_id: null
  }
}

const editarUsuario = (usuario) => {
  usuarioEditando.value = usuario
  isEditModalOpen.value = true
}

const actualizarUsuario = async () => {
  await cargarUsuarios()
}

const verUsuario = (usuario) => {
  console.log('Ver usuario:', usuario)
  // Aquí mostrar detalles del usuario
}

const toggleEstadoUsuario = async (usuario) => {
  try {
    const endpoint = usuario.activo ? 'deactivate' : 'activate'
    await axios.put(`/api/users/${usuario.id}/${endpoint}`)
    
    // Actualizar estado local
    usuario.activo = !usuario.activo
    
    console.log(`Usuario ${usuario.activo ? 'activado' : 'desactivado'}`)
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al cambiar estado del usuario:', error)
    // Aquí podrías mostrar un snackbar de error
  }
}

// Cargar datos al montar el componente
onMounted(() => {
  cargarUsuarios()
  cargarRoles()
})
</script>

<style scoped>
.elevation-1 {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}
 </style>
<style scoped>
.no-padding-card .v-card-text {
  padding-top: 8px !important;
  padding-bottom: 8px !important;
}
.no-padding-card {
  margin-bottom: 8px !important;
}
</style>
