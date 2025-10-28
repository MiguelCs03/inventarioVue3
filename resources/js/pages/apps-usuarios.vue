<template>
  <div>
    <!-- Header con título y botón de nuevo usuario -->
    <VCard class="mb-2 no-padding-card">
      <VCardText class="py-2 px-4">
        <div class="d-flex justify-space-between align-center flex-wrap ga-4">

          <div>
            <h2 class="text-h4 mb-1">Gestión de Usuarios</h2>
            <p class="text-body-1 mb-0">Administra los usuarios del sistema</p>
          </div>

          <div class="d-flex align-center ga-3">
            <!--Buscasdor de usuarios-->
            <VTextField v-model="search" label="Buscar usuario" placeholder="Buscar por nombre, email..."
              prepend-inner-icon="mdi-magnify" clearable density="comfortable" hide-details style="min-width: 220px;" />

            <VSelect v-model="selectedRole" :items="roleOptions" label="Filtrar por rol" clearable density="comfortable"
              hide-details style="min-width: 200px;" />

            <VBtn color="success" variant="outlined" prepend-icon="mdi-plus" @click="crearUsuario">
              Nuevo Usuario
            </VBtn>

          </div>
        </div>
      </VCardText>
    </VCard>



    <!-- Tabla de usuarios -->
    <VCard>
      <VDataTable v-model:page="page" :headers="headers" :items="filteredUsers" :loading="loading"
        :items-per-page="itemsPerPage" :search="search" class="elevation-1">
        <!-- Columna de roles (concatena nombres) -->
        <template #item.roles="{ item }">
          <span>
            {{Array.isArray(item.roles) && item.roles.length
              ? item.roles.map(r => r?.nombre).filter(Boolean).join(', ')
              : '—'}}
          </span>
        </template>
        <!-- Estado activo/inactivo -->
        <template #item.activo="{ item }">
          <VChip :color="item.activo ? 'success' : 'error'" size="small" variant="tonal">
            {{ item.activo ? 'ACTIVO' : 'INACTIVO' }}
          </VChip>
        </template>

        <!-- Botones de acción -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-2">
            <!-- Editar -->
            <VBtn icon="tabler-pencil" size="small" color="primary" variant="text" @click="editarUsuario(item)" />

            <!-- Activar/Desactivar (Candado) -->
            <VBtn :icon="item.activo ? 'tabler-lock' : 'tabler-lock-open'" size="small"
              :color="item.activo ? 'warning' : 'success'" variant="text" @click="toggleEstadoUsuario(item)" />

            <!-- Ver detalles -->
            <VBtn icon="tabler-eye" size="small" color="info" variant="text" @click="verUsuario(item)" />
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
    <VDialog v-model="showCreateDialog" max-width="600px" persistent>
      <VCard>
        <VCardTitle class="headline">
          <span class="text-h5">Crear Nuevo Usuario</span>
        </VCardTitle>

        <VCardText>
          <VContainer>
            <VRow>
              <!-- añadir imagen de perfil -->
              <VCol cols="12" class="d-flex flex-column align-center">
                <VAvatar size="96" class="mb-2">
                  <VImg v-if="newUserPreview" :src="newUserPreview" />
                  <VIcon v-else icon="tabler-user" size="48" />
                </VAvatar>
                <input type="file" accept="image/*" @change="onNewUserAvatarChange" />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="newUser.name" label="Nombre completo" required prepend-inner-icon="mdi-account" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.username" label="Nombre de usuario" placeholder="usuario123"
                  prepend-inner-icon="mdi-at" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.email" label="Email" type="email" required
                  prepend-inner-icon="mdi-email" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.numero" label="Número de celular" placeholder="+591 70123456"
                  prepend-inner-icon="mdi-phone" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.password" label="Contraseña" type="password" required
                  prepend-inner-icon="mdi-lock" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.fecha_nacimiento" label="Fecha de nacimiento" type="date"
                  prepend-inner-icon="mdi-calendar" />
              </VCol>

              <VCol cols="12" md="6">
                <VTextField v-model="newUser.cargo" label="Cargo" prepend-inner-icon="mdi-briefcase" />
              </VCol>

              <VCol cols="12" md="6">
                <VSelect v-model="newUser.role_id" :items="roles" item-title="nombre" item-value="id" label="Rol"
                  prepend-inner-icon="mdi-account-group" />
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="error" variant="outlined" @click="showCreateDialog = false; resetForm()">
            Cancelar
          </VBtn>
          <VBtn color="success" variant="elevated" @click="guardarNuevoUsuario" :loading="savingUser" :disabled="savingUser">
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Modal para editar usuario -->
    <EditUserModal :user="usuarioEditando" :isOpen="isEditModalOpen" @close="isEditModalOpen = false"
      @updated="actualizarUsuario" />

    <!-- Modal Ver Detalles -->
    <VDialog v-model="modalVerVisible" max-width="600px">
      <VCard v-if="usuarioDetalle">
        <VCardTitle class="d-flex justify-space-between align-center">
          <span class="text-h5">Detalles del Usuario</span>
          <VBtn icon="tabler-x" variant="text" @click="modalVerVisible = false" />
        </VCardTitle>

        <VCardText>
          <VRow>
            <VCol cols="12" class="text-center pb-6">
              <VAvatar size="96" :color="!usuarioDetalle.avatar_url ? 'primary' : undefined"
                :variant="!usuarioDetalle.avatar_url ? 'tonal' : undefined">
                <VImg v-if="usuarioDetalle.avatar_url" :src="usuarioDetalle.avatar_url" />
                <span v-else class="text-h4">{{ obtenerIniciales(usuarioDetalle) }}</span>
              </VAvatar>
              <h3 class="mt-4">
                {{ usuarioDetalle.name }}
              </h3>
              <VChip :color="usuarioDetalle.activo ? 'success' : 'error'" size="small" variant="tonal" class="mt-2">
                {{ usuarioDetalle.activo ? 'ACTIVO' : 'INACTIVO' }}
              </VChip>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-mail" size="16" class="me-1" />
                Email
              </div>
              <div class="text-body-1">{{ usuarioDetalle.email }}</div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-at" size="16" class="me-1" />
                Username
              </div>
              <div class="text-body-1">{{ usuarioDetalle.username || 'No especificado' }}</div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-phone" size="16" class="me-1" />
                Número de celular
              </div>
              <div class="text-body-1">{{ usuarioDetalle.numero || 'No especificado' }}</div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-briefcase" size="16" class="me-1" />
                Cargo
              </div>
              <div class="text-body-1">{{ usuarioDetalle.cargo || 'No especificado' }}</div>
            </VCol>

            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-shield" size="16" class="me-1" />
                Roles
              </div>
              <div class="text-body-1">
                <VChip v-for="role in usuarioDetalle.roles" :key="role.id" size="small" color="primary" variant="tonal"
                  class="me-1">
                  {{ role.nombre }}
                </VChip>
                <span v-if="!usuarioDetalle.roles || usuarioDetalle.roles.length === 0">
                  Sin roles asignados
                </span>
              </div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-calendar" size="16" class="me-1" />
                Fecha de nacimiento
              </div>
              <div class="text-body-1">{{ usuarioDetalle.fecha_nacimiento || 'No especificada' }}</div>
            </VCol>

            <VCol cols="6">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-clock" size="16" class="me-1" />
                Fecha de registro
              </div>
              <div class="text-body-1">{{ formatearFecha(usuarioDetalle.created_at) }}</div>
            </VCol>

            <VCol cols="12">
              <div class="text-body-2 text-medium-emphasis mb-1">
                <VIcon icon="tabler-refresh" size="16" class="me-1" />
                Última actualización
              </div>
              <div class="text-body-1">{{ formatearFecha(usuarioDetalle.updated_at) }}</div>
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn color="success" variant="elevated" @click="modalVerVisible = false">
            Cerrar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup>
import EditUserModal from '@/views/apps/user/list/EditUserModal.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

// Estados reactivos
const loading = ref(false)
const savingUser = ref(false)
const search = ref('')
const selectedRole = ref(null)
const page = ref(1)
const itemsPerPage = ref(10)
const users = ref([])
const roles = ref([])
const showCreateDialog = ref(false)
const isEditModalOpen = ref(false)
const usuarioEditando = ref(null)
const modalVerVisible = ref(false)
const usuarioDetalle = ref(null)

// Formulario de nuevo usuario
const newUser = ref({
  name: '',
  username: '',
  email: '',
  numero: '',
  password: '',
  fecha_nacimiento: '',
  cargo: '',
  role_id: null,
  avatarFile: null,
})
const newUserPreview = ref('')

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
  if (savingUser.value) return // Evitar doble clic
  
  savingUser.value = true
  try {
    // Enviar como multipart/form-data para incluir imagen
    const form = new FormData()
    form.append('name', newUser.value.name)
    if (newUser.value.username)
      form.append('username', newUser.value.username)
    form.append('email', newUser.value.email)
    if (newUser.value.numero)
      form.append('numero', newUser.value.numero)
    form.append('password', newUser.value.password)
    if (newUser.value.fecha_nacimiento)
      form.append('fecha_nacimiento', newUser.value.fecha_nacimiento)
    if (newUser.value.cargo)
      form.append('cargo', newUser.value.cargo)
    const roles = newUser.value.role_id ? [newUser.value.role_id] : []
    roles.forEach(r => form.append('roles[]', r))
    if (newUser.value.avatarFile)
      form.append('avatar', newUser.value.avatarFile)

    const response = await axios.post('/api/users', form, {
      headers: { 
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data'
      },
    })
    
    users.value.push(response.data)
    showCreateDialog.value = false
    resetForm()
    console.log('Usuario creado exitosamente')
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al crear usuario:', error)
    
    // Mostrar errores de validación si existen
    if (error.response?.data?.errors) {
      console.error('Errores de validación:', error.response.data.errors)
    }
    
    // Si el error es 422 y el usuario ya existe, recargar la lista
    if (error.response?.status === 422) {
      console.warn('Error de validación, recargando usuarios...')
      await cargarUsuarios()
    }
    // Aquí podrías mostrar un snackbar de error
  } finally {
    savingUser.value = false
  }
}

const resetForm = () => {
  newUser.value = {
    name: '',
    username: '',
    email: '',
    numero: '',
    password: '',
    fecha_nacimiento: '',
    cargo: '',
    role_id: null,
    avatarFile: null,
  }
  newUserPreview.value = ''
}

const editarUsuario = (usuario) => {
  usuarioEditando.value = usuario
  isEditModalOpen.value = true
}

const actualizarUsuario = async () => {
  await cargarUsuarios()
}

const verUsuario = (usuario) => {
  usuarioDetalle.value = usuario
  modalVerVisible.value = true
}

const obtenerIniciales = (usuario) => {
  if (!usuario || !usuario.name) return '?'
  const nombres = usuario.name.trim().split(' ')
  if (nombres.length === 1) return nombres[0].charAt(0).toUpperCase()
  return (nombres[0].charAt(0) + nombres[nombres.length - 1].charAt(0)).toUpperCase()
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'No disponible'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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

const onNewUserAvatarChange = e => {
  const file = e.target.files?.[0]
  newUser.value.avatarFile = file || null
  if (file) {
    const reader = new FileReader()
    reader.onload = ev => { newUserPreview.value = ev.target.result }
    reader.readAsDataURL(file)
  } else {
    newUserPreview.value = ''
  }
}
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
