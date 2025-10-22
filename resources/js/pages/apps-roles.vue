<script setup>
import AddEditRoleDialog from '@/components/dialogs/AddEditRoleDialog.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const roles = ref([])
const permissions = ref([])
const selectedRoleId = ref(null)
const rolePermissionSet = ref(new Set())
const roleTuInicio = ref('') // Vista inicial del rol (campo tu_inicio en BD)
const availableRoutes = ref([]) // Rutas disponibles desde menús

// Estados para gestión de roles
const isAddRoleDialogVisible = ref(false)
const isEditRoleDialogVisible = ref(false)
const editingRole = ref(null)

// Diálogo de permisos por rol
const isPermissionsDialogOpen = ref(false)
const roleForPermissions = ref(null) // objeto rol seleccionado

const selectedRole = computed(() => roles.value.find(r => r.id === selectedRoleId.value))

const loadRoles = async () => {
  const { data } = await axios.get('/api/roles')
  roles.value = data
  if (!selectedRoleId.value && roles.value.length)
    selectedRoleId.value = roles.value[0].id
}

const loadPermissions = async () => {
  const { data } = await axios.get('/api/permissions')
  permissions.value = data
}

const loadAvailableRoutes = async () => {
  try {
    const { data } = await axios.get('/api/menus')
    // Crear lista de rutas desde los menús y submenús
    const routes = []
    
    data.forEach(menu => {
      // Agregar el menú principal si tiene ruta
      if (menu.route) {
        routes.push({ 
          title: menu.name, 
          value: menu.route 
        })
      }
      
      // Agregar submenús si existen
      if (menu.submenus && menu.submenus.length > 0) {
        menu.submenus.forEach(submenu => {
          if (submenu.route) {
            routes.push({ 
              title: `${menu.name} - ${submenu.name}`, 
              value: submenu.route 
            })
          }
        })
      }
    })
    
    availableRoutes.value = routes
    console.log('Rutas cargadas:', routes) // Debug
  } catch (error) {
    console.error('Error cargando rutas:', error)
    // Fallback a rutas por defecto si falla
    availableRoutes.value = [
      { title: 'Dashboard', value: '/dashboard-inicio' },
      { title: 'Perfil', value: '/perfil' },
    ]
  }
}

const loadRolePermissions = async (roleId) => {
  if (!roleId) return
  const { data } = await axios.get(`/api/roles/${roleId}/permissions`)
  rolePermissionSet.value = new Set(data.permissions)
}

const togglePermission = (permId) => {
  if (rolePermissionSet.value.has(permId))
    rolePermissionSet.value.delete(permId)
  else
    rolePermissionSet.value.add(permId)
}

const savePermissions = async () => {
  await axios.put(`/api/roles/${selectedRoleId.value}/permissions`, { 
    permission_ids: Array.from(rolePermissionSet.value),
    tu_inicio: roleTuInicio.value
  })
  await loadRoles() // Recargar roles para actualizar
}

// Abrir/cerrar diálogo de permisos de un rol
const openPermissionsDialog = async (role) => {
  roleForPermissions.value = role
  selectedRoleId.value = role.id
  roleTuInicio.value = role.tu_inicio || '/dashboard-inicio' // Cargar vista inicial
  // Asegurar permisos cargados y permisos del rol
  if (!permissions.value.length) await loadPermissions()
  await loadRolePermissions(role.id)
  isPermissionsDialogOpen.value = true
}
const closePermissionsDialog = () => {
  isPermissionsDialogOpen.value = false
  roleForPermissions.value = null
}

// Funciones para gestión de roles
const editRole = (role) => {
  editingRole.value = { ...role }
  isEditRoleDialogVisible.value = true
}

const deleteRole = async (roleId) => {
  if (confirm('¿Estás seguro de eliminar este rol?')) {
    await axios.delete(`/api/roles/${roleId}`)
    await loadRoles()
    if (selectedRoleId.value === roleId) {
      selectedRoleId.value = roles.value[0]?.id || null
      if (selectedRoleId.value) {
        await loadRolePermissions(selectedRoleId.value)
      }
    }
  }
}

const onRoleCreated = () => {
  loadRoles()
  isAddRoleDialogVisible.value = false
}

const onRoleUpdated = () => {
  loadRoles()
  isEditRoleDialogVisible.value = false
  editingRole.value = null
}

onMounted(async () => {
  await Promise.all([loadRoles(), loadPermissions(), loadAvailableRoutes()])
  await loadRolePermissions(selectedRoleId.value)
})
</script>

<template>
  <div class="pa-6">
    <!-- Header con botón para crear rol -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h4 class="text-h4 mb-2">Gestión de Roles</h4>
        <p class="text-body-1">Administra roles y asigna permisos por secciones</p>
      </div>
      <v-btn 
        color="primary" 
        @click="isAddRoleDialogVisible = true"
        prepend-icon="tabler-plus"
      >
        Crear Rol
      </v-btn>
    </div>

    <!-- Sección de gestión de roles existentes -->
    <v-card class="mb-6">
      <v-card-title>Roles Existentes</v-card-title>
      <v-card-text>
        <v-row>
          <v-col 
            v-for="role in roles" 
            :key="role.id" 
            cols="12" 
            sm="6" 
            md="4"
          >
            <v-card variant="outlined">
              <v-card-text>
                <div class="d-flex justify-space-between align-center">
                  <div>
                    <h6 class="text-h6 mb-1">{{ role.nombre }}</h6>
                    <p class="text-caption text-medium-emphasis mb-0">
                      Estado: {{ role.activo ? 'Activo' : 'Inactivo' }}
                    </p>
                  </div>
                  <div>
                    <v-tooltip text="Permisos">
                      <template #activator="{ props }">
                        <v-btn
                          v-bind="props"
                          icon="tabler-shield"
                          size="small"
                          variant="text"
                          color="primary"
                          @click="openPermissionsDialog(role)"
                        />
                      </template>
                    </v-tooltip>
                    <v-btn
                      icon="tabler-edit"
                      size="small"
                      variant="text"
                      @click="editRole(role)"
                    />
                    <v-btn
                      icon="tabler-trash"
                      size="small"
                      variant="text"
                      color="error"
                      @click="deleteRole(role.id)"
                    />
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Diálogo de permisos por rol -->
    <v-dialog v-model="isPermissionsDialogOpen" max-width="750px" persistent>
      <v-card>
        <!-- Header destacado con rol -->
        <v-card-title class="pa-4 bg-primary">
          <div class="d-flex justify-space-between align-center">
            <div class="d-flex align-center gap-2">
              <v-icon icon="tabler-shield-check" size="28" color="white" />
              <span class="text-h6 text-white font-weight-bold">{{ roleForPermissions?.nombre }}</span>
            </div>
            <v-btn icon="tabler-x" variant="text" color="white" size="small" @click="closePermissionsDialog" />
          </div>
        </v-card-title>

        <v-card-text class="pa-4">
          <!-- Selector de vista inicial - compacto -->
          <v-card variant="outlined" class="mb-4">
            <v-card-text class="pa-3">
              <div class="d-flex align-center gap-2 mb-2">
                <v-icon icon="tabler-home" size="18" color="primary" />
                <span class="text-subtitle-2 font-weight-medium">Vista Inicial</span>
              </div>
              <v-select
                v-model="roleTuInicio"
                :items="availableRoutes"
                density="compact"
                variant="outlined"
                hide-details
                placeholder="Selecciona la vista inicial"
              />
            </v-card-text>
          </v-card>

          <!-- Tabla de permisos sin columna Acción -->
          <div class="text-subtitle-2 font-weight-medium mb-2">Permisos de Acceso</div>
          <v-table density="compact" class="permissions-table">
            <thead>
              <tr>
                <th>PERMISO</th>
                <th>SECCIÓN</th>
                <th class="text-center" style="width: 100px;">HABILITADO</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in permissions" :key="p.id">
                <td>{{ p.name }}</td>
                <td>
                  <v-chip size="small" variant="tonal">{{ p.subject }}</v-chip>
                </td>
                <td class="text-center">
                  <v-switch
                    :model-value="rolePermissionSet.has(p.id)"
                    @update:model-value="togglePermission(p.id)"
                    color="primary"
                    hide-details
                    density="compact"
                  />
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>

        <v-divider />

        <v-card-actions class="pa-3">
          <v-spacer />
          <v-btn color="grey" variant="text" size="small" @click="closePermissionsDialog">Cancelar</v-btn>
          <v-btn color="primary" size="small" @click="async () => { await savePermissions(); closePermissionsDialog() }">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialogs -->
    <AddEditRoleDialog 
      v-model:is-dialog-visible="isAddRoleDialogVisible"
      @role-created="onRoleCreated"
    />

    <AddEditRoleDialog 
      v-model:is-dialog-visible="isEditRoleDialogVisible"
      :role-permissions="editingRole"
      @role-updated="onRoleUpdated"
    />
  </div>
</template>

<style scoped>
.permissions-table {
  border: 1px solid rgba(var(--v-border-color), 0.12);
  border-radius: 6px;
}

.permissions-table thead {
  background-color: rgba(var(--v-theme-surface-variant), 0.4);
}

.permissions-table thead th {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.permissions-table tbody tr:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}
</style>
