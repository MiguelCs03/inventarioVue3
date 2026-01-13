<script setup>
import AuthProvider from '@/views/pages/authentication/AuthProvider.vue'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { VForm } from 'vuetify/components/VForm'
import { useConfiguracion } from '@/composables/useConfiguracion'
import { useImagenesSistema } from '@/composables/useImagenesSistema'

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const isPasswordVisible = ref(false)
const route = useRoute()
const router = useRouter()
const ability = useAbility()

const errors = ref({
  email: undefined,
  password: undefined,
})

const refVForm = ref()

const credentials = ref({
  email: 'admin@demo.com',
  password: 'admin',
})

const rememberMe = ref(false)

import axios from 'axios'
import { fetchMenus } from '@/store/menu'

// Cargar configuración de la BD
const { configuracion, obtenerConfiguracionPublica } = useConfiguracion()
// Cargar imágenes activas públicas (login, logo, favicon, loader)
const { imagenesActivas, obtenerTodasActivas } = useImagenesSistema()

const nombreEmpresa = computed(() => configuracion.value?.nombre_empresa || themeConfig.app.title)

onMounted(async () => {
  // Obtener configuración pública y las imágenes activas
  await Promise.all([obtenerConfiguracionPublica(), obtenerTodasActivas()])

  // Actualizar título del navegador
  if (configuracion.value?.titulo_navegador) {
    document.title = configuracion.value.titulo_navegador
  }
})

// Si existe una imagen activa subida para la ilustración de login, usarla
const loginIllustration = computed(() => {
  const activo = imagenesActivas.value?.login_ilustracion
  if (activo && activo.ruta) return `/storage/${activo.ruta}`
  return authThemeImg
})

// Logo pequeño que aparece en el header del login (usar `logo_header` si existe)
const smallLogoSrc = computed(() => {
  const img = imagenesActivas.value?.logo_header
  return img && img.ruta ? `/storage/${img.ruta}` : null
})

const login = async () => {
  try {
    const response = await axios.post('/api/login', {
      email: credentials.value.email,
      password: credentials.value.password,
    })

    const { accessToken, userData, userAbilityRules, homeRoute } = response.data

    useCookie('userAbilityRules').value = userAbilityRules
    ability.update(userAbilityRules)
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    // Cargar menús antes de redirigir para que los guards tengan la info necesaria
    await fetchMenus()

    // Redirect to `to` query if exist or redirect to index route
    // Usar nextTick y luego un pequeño delay para asegurar que el router esté listo
    await nextTick()
    
    const target = route.query.to ? String(route.query.to) : (homeRoute ?? '/')
    
    // Usar push en lugar de replace para que el router resuelva correctamente la ruta
    router.push(target)
  } catch (err) {
    console.error('Error de login:', err)
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors || { email: 'Credenciales incorrectas' }
    } else {
      errors.value = { email: 'Error de conexión' }
    }
  }
}

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid)
      login()
  })
}
</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <template v-if="smallLogoSrc">
        <img :src="smallLogoSrc" alt="logo" style="max-height:36px;" />
      </template>
      <template v-else>
        <VNodeRenderer :nodes="themeConfig.app.logo" />
      </template>
      <h1 class="auth-title">
        {{ nombreEmpresa }}
      </h1>
    </div>
  </RouterLink>

  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 6.25rem;"
        >
          <VImg
            max-width="613"
            :src="loginIllustration"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <img
          class="auth-footer-mask"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Bienvenido a  <span class="text-capitalize"> {{ nombreEmpresa }} </span>! 👋🏻
          </h4>
        
        </VCardText>
        <VCardText>
          <VAlert
            color="primary"
            variant="tonal"
          >
            <p class="text-sm mb-2">
              Admin Email: <strong>admin@demo.com</strong> / Pass: <strong>admin</strong>
            </p>
            <p class="text-sm mb-0">
              Client Email: <strong>client@demo.com</strong> / Pass: <strong>client</strong>
            </p>
          </VAlert>
        </VCardText>
        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- email o username -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.email"
                  label="Email o Username"
                  placeholder="johndoe@email.com o usuario123"
                  type="text"
                  autofocus
                  :rules="[requiredValidator]"
                  :error-messages="errors.email"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="Password"
                  placeholder="············"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox
                    v-model="rememberMe"
                    label="Recuerdame"
                  />
                  <RouterLink
                    class="text-primary ms-2 mb-1"
                    :to="{ name: 'forgot-password' }"
                  >
                    ¿Olvidaste tu contraseña?
                  </RouterLink>
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  Login
                </VBtn>
              </VCol>

              <!-- create account -->
              <VCol
                cols="12"
                class="text-center"
              >
                <span>New on our platform?</span>
                <RouterLink
                  class="text-primary ms-1"
                  :to="{ name: 'register' }"
                >
                  Create an account
                </RouterLink>
              </VCol>
              <VCol
                cols="12"
                class="d-flex align-center"
              >
                <VDivider />
                <span class="mx-4">or</span>
                <VDivider />
              </VCol>

              <!-- auth providers -->
              <VCol
                cols="12"
                class="text-center"
              >
                <AuthProvider />
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>