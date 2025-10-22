<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Get user data from cookie
const userData = useCookie('userData')

// Form data for profile
const profileForm = ref({
  fullName: userData.value?.fullName || '',
  username: userData.value?.username || '',
  email: userData.value?.email || '',
  phone: userData.value?.phone || '',
  address: userData.value?.address || '',
})

// Form data for password change
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

// Loading states
const isProfileLoading = ref(false)
const isPasswordLoading = ref(false)

// Validation rules
const rules = {
  required: value => !!value || 'Este campo es requerido',
  email: value => {
    const pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
    return pattern.test(value) || 'Ingrese un email válido'
  },
  minPassword: value => value.length >= 6 || 'La contraseña debe tener al menos 6 caracteres',
  confirmPassword: value => value === passwordForm.value.newPassword || 'Las contraseñas no coinciden'
}

// Save profile data
const saveProfile = async () => {
  try {
    isProfileLoading.value = true

    // API call to update profile
    const response = await $fetch('/api/profile', {
      method: 'PUT',
      body: profileForm.value,
      headers: {
        'Authorization': `Bearer ${useCookie('accessToken').value}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    // Update cookie with new data
    userData.value = {
      ...userData.value,
      ...response.user
    }

    // Show success message
    console.log('Perfil actualizado exitosamente')

  } catch (error) {
    console.error('Error al actualizar perfil:', error)
  } finally {
    isProfileLoading.value = false
  }
}

// Change password
const changePassword = async () => {
  try {
    isPasswordLoading.value = true

    const accessToken = useCookie('accessToken').value

    // API call to change password
    const response = await fetch('/api/profile/password', {
      method: 'PUT',
      body: JSON.stringify({
        email: userData.value?.email,
        current_password: passwordForm.value.currentPassword,
        new_password: passwordForm.value.newPassword,
        new_password_confirmation: passwordForm.value.confirmPassword
      }),
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.errors?.current_password?.[0] || 'Error al cambiar la contraseña')
    }

    // Reset form
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
    }

    // Show success message using Vuetify snackbar or alert
    alert('Contraseña actualizada exitosamente')

  } catch (error) {
    alert(error.message || 'Error al cambiar la contraseña')
    console.error('Error al cambiar contraseña:', error)
  } finally {
    isPasswordLoading.value = false
  }
}
const avatarModal = ref(false)
const avatarFile = ref(null)
const avatarPreview = ref('')
const isAvatarLoading = ref(false)

const openAvatarModal = () => {
  avatarModal.value = true
  avatarFile.value = null
  avatarPreview.value = userData.value?.avatar || ''
}
const closeAvatarModal = () => {
  avatarModal.value = false
  avatarFile.value = null
  avatarPreview.value = ''
}
const onAvatarChange = (event) => {
  const file = event.target.files[0]
  avatarFile.value = file
  if (file) {
    const reader = new FileReader()
    reader.onload = e => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else {
    avatarPreview.value = userData.value?.avatar || ''
  }
}
const uploadAvatar = async () => {
  if (!avatarFile.value) return
  isAvatarLoading.value = true
  const formData = new FormData()
  formData.append('avatar', avatarFile.value)
  
  try {
    const response = await fetch('/user/avatar', {
      method: 'POST',
      body: formData,
      credentials: 'include', // Incluir cookies de sesión
    })
    const data = await response.json()
    if (response.ok) {
      // Actualizar la cookie de userData con la nueva URL del avatar
      const updatedUserData = {
        ...userData.value,
        avatar: data.avatar_url
      }
      userData.value = updatedUserData
      useCookie('userData').value = updatedUserData
      
      closeAvatarModal()
      alert('Foto de perfil actualizada')
      
      // Recargar la página para reflejar cambios en toda la app
      window.location.reload()
    } else {
      alert(data.message || `Error al subir imagen (HTTP ${response.status})`)
    }
  } catch (err) {
    console.error('Error al subir avatar:', err)
    alert('Error de red')
  } finally {
    isAvatarLoading.value = false
  }
}

// Meta for page
definePage({
  meta: {
    layout: 'default',
  },
})
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h4 class="text-h4 font-weight-medium mb-1">
          Mi Perfil
        </h4>
        <p class="text-body-1 mb-0">
          Actualiza tu información personal y configuración de seguridad
        </p>
      </div>
    </div>

    <VRow>
      <VCol cols="12" md="8">
        <!-- Profile Information Card -->
        <VCard class="mb-6">
          <VCardItem>
            <VCardTitle>Información Personal</VCardTitle>
            <VCardSubtitle>Actualiza tus datos personales</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VForm @submit.prevent="saveProfile">
              <VRow>
                <!-- seccion de foto de perfil -->
                <VCol cols="12" class="d-flex justify-center mb-4">
                  <VAvatar size="120" :color="!(userData && userData.avatar) ? 'primary' : undefined"
                    :variant="!(userData && userData.avatar) ? 'tonal' : undefined">
                    <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
                    <VIcon v-else icon="tabler-user" size="60" />
                  </VAvatar>
                </VCol>
                <VCol cols="12" class="d-flex justify-center mb-2">
                  <VBtn color="primary" variant="tonal" @click="openAvatarModal">
                    Cambiar Foto de Perfil
                  </VBtn>
                </VCol>

                <!-- Full Name -->
                <VCol cols="12" md="6">
                  <VTextField v-model="profileForm.fullName" label="Nombre Completo" :rules="[rules.required]"
                    prepend-inner-icon="tabler-user" />
                </VCol>

                <!-- Username -->
                <VCol cols="12" md="6">
                  <VTextField v-model="profileForm.username" label="Nombre de Usuario" :rules="[rules.required]"
                    prepend-inner-icon="tabler-at" />
                </VCol>

                <!-- Email -->
                <VCol cols="12" md="6">
                  <VTextField v-model="profileForm.email" label="Correo Electrónico" type="email"
                    :rules="[rules.required, rules.email]" prepend-inner-icon="tabler-mail" />
                </VCol>

                <!-- Phone -->
                <VCol cols="12" md="6">
                  <VTextField v-model="profileForm.phone" label="Teléfono" prepend-inner-icon="tabler-phone" />
                </VCol>

                <!-- Address -->
                <VCol cols="12">
                  <VTextField v-model="profileForm.address" label="Dirección" prepend-inner-icon="tabler-map-pin" />
                </VCol>

                <!-- Save Button -->
                <VCol cols="12" class="d-flex justify-end gap-3">
                  <VBtn color="secondary" variant="tonal" @click="router.go(-1)">
                    Cancelar
                  </VBtn>
                  <VBtn type="submit" :loading="isProfileLoading">
                    Guardar Cambios
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>

        <!-- Password Change Card -->
        <VCard>
          <VCardItem>
            <VCardTitle>Cambiar Contraseña</VCardTitle>
            <VCardSubtitle>Actualiza tu contraseña para mantener tu cuenta segura</VCardSubtitle>
          </VCardItem>

          <VCardText>
            <VForm @submit.prevent="changePassword">
              <VRow>
                <!-- Current Password -->
                <VCol cols="12">
                  <VTextField v-model="passwordForm.currentPassword" label="Contraseña Actual" type="password"
                    :rules="[rules.required]" prepend-inner-icon="tabler-lock" />
                </VCol>

                <!-- New Password -->
                <VCol cols="12" md="6">
                  <VTextField v-model="passwordForm.newPassword" label="Nueva Contraseña" type="password"
                    :rules="[rules.required, rules.minPassword]" prepend-inner-icon="tabler-lock" />
                </VCol>

                <!-- Confirm Password -->
                <VCol cols="12" md="6">
                  <VTextField v-model="passwordForm.confirmPassword" label="Confirmar Nueva Contraseña" type="password"
                    :rules="[rules.required, rules.confirmPassword]" prepend-inner-icon="tabler-lock-check" />
                </VCol>

                <!-- Change Password Button -->
                <VCol cols="12" class="d-flex justify-end">
                  <VBtn type="submit" color="warning" :loading="isPasswordLoading">
                    Cambiar Contraseña
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Sidebar with User Info -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardItem>
            <VCardTitle>Información de la Cuenta</VCardTitle>
          </VCardItem>

          <VCardText>
            <div class="d-flex align-center gap-3 mb-4">
              <VAvatar size="60" :color="!(userData && userData.avatar) ? 'primary' : undefined"
                :variant="!(userData && userData.avatar) ? 'tonal' : undefined">
                <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
                <VIcon v-else icon="tabler-user" />
              </VAvatar>
              <div>
                <h6 class="text-h6 font-weight-medium mb-1">
                  {{ userData?.fullName || userData?.username }}
                </h6>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  {{ userData?.role }}
                </p>
              </div>
            </div>

            <VDivider class="mb-4" />

            <!-- Account Details -->
            <div class="mb-3">
              <h6 class="text-subtitle-1 font-weight-medium mb-2">
                Detalles de la Cuenta
              </h6>

              <div class="d-flex align-center gap-2 mb-2">
                <VIcon icon="tabler-mail" size="16" />
                <span class="text-body-2">{{ userData?.email || 'No especificado' }}</span>
              </div>

              <div class="d-flex align-center gap-2 mb-2">
                <VIcon icon="tabler-user-circle" size="16" />
                <span class="text-body-2">{{ userData?.username || 'No especificado' }}</span>
              </div>

              <div class="d-flex align-center gap-2">
                <VIcon icon="tabler-shield-check" size="16" />
                <span class="text-body-2">{{ userData?.role || 'Usuario' }}</span>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <!-- Modal para subir imagen -->>
    <VDialog v-model="avatarModal" max-width="400">
      <VCard>
        <VCardTitle class="text-h6">Subir Foto de Perfil</VCardTitle>
        <VCardText>
          <div class="d-flex flex-column align-center">
            <VAvatar size="120" class="mb-4">
              <VImg v-if="avatarPreview" :src="avatarPreview" />
              <VIcon v-else icon="tabler-user" size="60" />
            </VAvatar>
            <input type="file" accept="image/*" @change="onAvatarChange" />
          </div>
        </VCardText>
        <VCardActions class="justify-end">
          <VBtn variant="text" @click="closeAvatarModal">Cancelar</VBtn>
          <VBtn color="primary" :loading="isAvatarLoading" @click="uploadAvatar" :disabled="!avatarFile">Subir Foto
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

  </div>
</template>

<style scoped>
.v-avatar {
  border: 2px solid rgb(var(--v-theme-surface));
}
</style>
