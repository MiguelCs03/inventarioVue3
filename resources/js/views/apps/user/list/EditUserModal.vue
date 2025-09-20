<template>
  <VDialog :model-value="isOpen" @update:model-value="val => { if (!val) emit('close') }" max-width="500">
    <VCard>
      <VCardTitle>Editar Usuario</VCardTitle>
      <VCardText>
        <VForm ref="formRef" @submit.prevent="onSubmit">
          <VTextField v-model="form.name" label="Nombre" required />
          <VTextField v-model="form.email" label="Email" type="email" required />
          <VTextField v-model="form.password" label="Contraseña (dejar vacío para no cambiar)" type="password" />
          <VTextField v-model="form.cargo" label="Cargo" />
          <VTextField v-model="form.fecha_nacimiento" label="Fecha de nacimiento" type="date" />
          <VSelect v-model="form.role_id" :items="roles" item-title="nombre" item-value="id" label="Rol" />
          <VSwitch v-model="form.activo" label="Activo" />
          <VBtn type="submit" color="primary" block>Guardar cambios</VBtn>
        </VForm>
      </VCardText>
      <VCardActions>
        <VBtn text @click="close">Cancelar</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
  user: Object,
  isOpen: Boolean,
})
const emit = defineEmits(['close', 'updated'])

const form = ref({
  name: '',
  email: '',
  password: '',
  cargo: '',
  fecha_nacimiento: '',
  role_id: null,
  activo: true,
})
const roles = ref([])
const formRef = ref()

watch(() => props.user, (user) => {
  if (user) {
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      cargo: user.cargo,
      fecha_nacimiento: user.fecha_nacimiento,
      role_id: user.role_id || (user.role ? user.role.id : null),
      activo: user.activo !== false,
    }
  }
}, { immediate: true })

onMounted(async () => {
  const { data } = await axios.get('/api/roles')
  roles.value = data
})

const onSubmit = async () => {
  if (!formRef.value.validate()) return
  try {
    await axios.put(`/api/users/${props.user.id}`, form.value)
    emit('updated')
    close()
  } catch (e) {
    // Manejo de errores
    alert('Error al actualizar usuario')
  }
}

function close() {
  emit('close')
}
</script>
