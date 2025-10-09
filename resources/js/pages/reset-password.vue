<script setup>
import { ref } from 'vue'
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})


// Campos requeridos para el reset: email, token, nueva contraseña y confirmación
const form = ref({
  email: '',
  token: '',
  newPassword: '',
  confirmPassword: '',
})

const loading = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

// Si el token/email viene por query string, extraerlo
import { useRoute, useRouter } from 'vue-router'
const route = useRoute()
const router = useRouter()
if (route.query.token) form.value.token = route.query.token
if (route.query.email) form.value.email = route.query.email

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Enviar formulario a la API
async function onSubmit() {
  errorMsg.value = ''
  successMsg.value = ''
  loading.value = true
  try {
    // Validación simple en frontend
    if (!form.value.email || !form.value.token) {
      errorMsg.value = 'Faltan datos para restablecer la contraseña.'
      loading.value = false
      return
    }
    if (form.value.newPassword !== form.value.confirmPassword) {
      errorMsg.value = 'Las contraseñas no coinciden.'
      loading.value = false
      return
    }
    // Enviar petición
    const res = await fetch('/api/reset-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: form.value.email,
        token: form.value.token,
        password: form.value.newPassword,
        password_confirmation: form.value.confirmPassword,
      }),
    })
    const data = await res.json()
    if (res.ok) {
      successMsg.value = 'Contraseña restablecida correctamente. Ahora puedes iniciar sesión.'
      // Opcional: redirigir al login después de unos segundos
      setTimeout(() => router.push({ name: 'pages-authentication-login-v1' }), 2000)
    } else {
      errorMsg.value = data.message || 'Error al restablecer la contraseña.'
    }
  } catch (e) {
    errorMsg.value = 'Error de red o del servidor.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/">
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            Restablecer contraseña 🔒
          </h4>
          <p class="mb-0">
            Tu nueva contraseña debe ser diferente a las contraseñas usadas anteriormente
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="onSubmit">
            <VRow>

              <!-- Email (oculto si ya viene en query) -->
              <VCol cols="12" v-if="!form.email">
                <AppTextField
                  v-model="form.email"
                  label="Correo electrónico"
                  placeholder="tucorreo@ejemplo.com"
                  autocomplete="email"
                  type="email"
                  required
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.newPassword"
                  autofocus
                  label="Nueva contraseña"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.confirmPassword"
                  label="Confirmar contraseña"
                  autocomplete="new-password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <!-- Mensajes de error o éxito -->
              <VCol cols="12" v-if="errorMsg">
                <div class="text-error text-center">{{ errorMsg }}</div>
              </VCol>
              <VCol cols="12" v-if="successMsg">
                <div class="text-success text-center">{{ successMsg }}</div>
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="loading"
                  :disabled="loading"
                >
                  Establecer nueva contraseña
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'pages-authentication-login-v1' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>Volver al inicio de sesión</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
