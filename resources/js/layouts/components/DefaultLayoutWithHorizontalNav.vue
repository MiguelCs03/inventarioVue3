<script setup>
import navItems from '@/navigation/horizontal'
import { themeConfig } from '@themeConfig'
import { onMounted, computed } from 'vue'
import { useImagenesSistema } from '@/composables/useImagenesSistema'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import NavBarI18n from '@core/components/I18n.vue'
import { HorizontalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
// imágenes del sistema
const { imagenesActivas, obtenerTodasActivas } = useImagenesSistema()

onMounted(async () => {
  try {
    await obtenerTodasActivas()
  } catch (e) {
    console.warn('No se pudieron cargar las imágenes activas para el nav horizontal', e)
  }
})

const logoHeaderSrc = computed(() => {
  const img = imagenesActivas.value?.logo_header
  return img && img.ruta ? `/storage/${img.ruta}` : null
})
</script>

<template>
  <HorizontalNavLayout :nav-items="navItems">
    <!--  navbar -->
    <template #navbar>
      <RouterLink
        to="/"
        class="app-logo d-flex align-center gap-x-3"
      >
        <!-- Preferir logo subido en DB (logo_header) sobre el logo por defecto -->
        <template v-if="logoHeaderSrc">
          <img :src="logoHeaderSrc" alt="logo" style="max-height:36px;" />
        </template>
        <template v-else>
          <VNodeRenderer :nodes="themeConfig.app.logo" />
        </template>

        <h1 class="app-title font-weight-bold leading-normal text-xl text-capitalize">
          {{ themeConfig.app.title }}
        </h1>
      </RouterLink>
      <VSpacer />

      <!-- Hidden: Search -->
      <template v-if="false">
        <NavSearchBar trigger-btn-class="ms-lg-n3" />
      </template>

      <!-- Hidden: Language Switcher -->
      <template v-if="false">
        <NavBarI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
        />
      </template>

      <NavbarThemeSwitcher />
      <!-- Hidden: Shortcuts & Notifications -->
      <template v-if="false">
        <NavbarShortcuts />
        <NavBarNotifications class="me-2" />
      </template>
      <UserProfile />
    </template>

    <!--  Pages -->
    <slot />

    <!--  Footer -->
    <template #footer>
      <Footer />
    </template>

    <!--  Customizer -->
    <TheCustomizer />
  </HorizontalNavLayout>
</template>
