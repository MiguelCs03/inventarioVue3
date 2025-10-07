<script setup>
import AddEditRoleDialog from '@/components/dialogs/AddEditRoleDialog.vue'
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'

const roles = ref([])
const permissions = ref([])
const selectedRoleId = ref(null)
const rolePermissionSet = ref(new Set())

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
  await axios.put(`/api/roles/${selectedRoleId.value}/permissions`, { permission_ids: Array.from(rolePermissionSet.value) })
}

// Abrir/cerrar diálogo de permisos de un rol
const openPermissionsDialog = async (role) => {
  roleForPermissions.value = role
  selectedRoleId.value = role.id
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
  await Promise.all([loadRoles(), loadPermissions()])
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
    <v-dialog v-model="isPermissionsDialogOpen" max-width="800px" persistent>
      <v-card>
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h6">Permisos del rol: {{ roleForPermissions?.nombre }}</span>
          <v-btn icon="tabler-x" variant="text" @click="closePermissionsDialog" />
        </v-card-title>
        <v-card-text>
          <v-table>
            <thead>
              <tr>
                <th>Permiso</th>
                <th>Acción</th>
                <th>Sección</th>
                <th class="text-center">Habilitado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in permissions" :key="p.id">
                <td>{{ p.name }}</td>
                <td>
                  <v-chip size="small" :color="p.action === 'read' ? 'info' : 'success'">
                    {{ p.action }}
                  </v-chip>
                </td>
                <td>{{ p.subject }}</td>
                <td class="text-center">
                  <v-switch
                    :model-value="rolePermissionSet.has(p.id)"
                    @update:model-value="togglePermission(p.id)"
                    color="primary"
                    hide-details
                  />
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="grey" variant="text" @click="closePermissionsDialog">Cancelar</v-btn>
          <v-btn color="primary" @click="async () => { await savePermissions(); closePermissionsDialog() }">Guardar</v-btn>
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
