<script setup>
import axios from 'axios'
import { VForm } from 'vuetify/components/VForm'

// Validation rules
const requiredValidator = value => !!value || 'Este campo es requerido'

const props = defineProps({
  rolePermissions: {
    type: Object,
    required: false,
    default: () => ({
      id: null,
      nombre: '',
      activo: true,
    }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDialogVisible',
  'update:rolePermissions',
  'roleCreated',
  'roleUpdated',
])


// 👉 Form fields
const roleForm = ref({
  id: null,
  nombre: '',
  activo: true,
})

const refPermissionForm = ref()

// watch for rolePermissions changes
watch(() => props.rolePermissions, (newVal) => {
  if (newVal) {
    roleForm.value = {
      id: newVal.id || null,
      nombre: newVal.nombre || '',
      activo: newVal.activo !== undefined ? newVal.activo : true,
    }
  }
}, { immediate: true, deep: true })

const onSubmit = async () => {
  try {
    if (roleForm.value.id) {
      // Editar rol existente
      await axios.put(`/api/roles/${roleForm.value.id}`, {
        nombre: roleForm.value.nombre,
        activo: roleForm.value.activo,
      })
      emit('roleUpdated')
    } else {
      // Crear nuevo rol
      await axios.post('/api/roles', {
        nombre: roleForm.value.nombre,
        activo: roleForm.value.activo,
      })
      emit('roleCreated')
    }
    
    onReset()
  } catch (error) {
    console.error('Error al guardar rol:', error)
  }
}

const onReset = () => {
  emit('update:isDialogVisible', false)
  roleForm.value = {
    id: null,
    nombre: '',
    activo: true,
  }
  refPermissionForm.value?.reset()
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="onReset"
  >
    <!-- 👉 Dialog close btn -->
    <DialogCloseBtn @click="onReset" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          {{ roleForm.id ? 'Editar' : 'Crear' }} Rol
        </h4>
        <p class="text-body-1 text-center mb-6">
          {{ roleForm.id ? 'Modifica los datos del rol' : 'Ingresa los datos del nuevo rol' }}
        </p>

        <!-- 👉 Form -->
        <VForm ref="refPermissionForm" @submit.prevent="onSubmit">
          <!-- 👉 Role name -->
          <AppTextField
            v-model="roleForm.nombre"
            label="Nombre del Rol"
            placeholder="Ej: Administrador, Empleado, etc."
            :rules="[requiredValidator]"
            class="mb-4"
          />

          <!-- 👉 Role status -->
          <div class="mb-6">
            <VCheckbox
              v-model="roleForm.activo"
              label="Rol activo"
              class="mb-0"
            />
          </div>

          <!-- 👉 Actions button -->
          <div class="d-flex align-center justify-center gap-4">
            <VBtn type="submit" :loading="false">
              {{ roleForm.id ? 'Actualizar' : 'Crear' }}
            </VBtn>

            <VBtn
              color="secondary"
              variant="tonal"
              @click="onReset"
            >
              Cancelar
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
// Estilos personalizados si son necesarios
</style>
