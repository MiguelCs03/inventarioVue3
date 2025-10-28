import { useI18n } from 'vue-i18n'
import { createVuetify } from 'vuetify'
import { VBtn } from 'vuetify/components/VBtn'
import { createVueI18nAdapter } from 'vuetify/locale/adapters/vue-i18n'
import defaults from './defaults'
import { icons } from './icons'
import { staticPrimaryColor, staticPrimaryDarkenColor, themes } from './theme'
import { getI18n } from '@/plugins/i18n/index'
import { themeConfig } from '@themeConfig'

// Styles
import { cookieRef } from '@/@layouts/stores/config'
import '@core-scss/template/libs/vuetify/index.scss'
import 'vuetify/styles'

export default function (app) {
  const cookieThemeValues = {
    defaultTheme: resolveVuetifyTheme(themeConfig.app.theme),
    themes: {
      light: {
        colors: {
          'primary': cookieRef('lightThemePrimaryColor', staticPrimaryColor).value,
          'primary-darken-1': cookieRef('lightThemePrimaryDarkenColor', staticPrimaryDarkenColor).value,
        },
      },
      dark: {
        colors: {
          'primary': cookieRef('darkThemePrimaryColor', staticPrimaryColor).value,
          'primary-darken-1': cookieRef('darkThemePrimaryDarkenColor', staticPrimaryDarkenColor).value,
        },
      },
    },
  }

  // Avoid deepMerge which can run into recursion with reactive/circular values.
  // Build a safe merged theme object by shallow-merging the color overrides only.
  const optionTheme = {
    defaultTheme: cookieThemeValues.defaultTheme,
    themes: {
      light: {
        ...themes.light,
        colors: {
          ...themes.light.colors,
          ...(cookieThemeValues.themes?.light?.colors || {}),
        },
        variables: { ...(themes.light.variables || {}) },
      },
      dark: {
        ...themes.dark,
        colors: {
          ...themes.dark.colors,
          ...(cookieThemeValues.themes?.dark?.colors || {}),
        },
        variables: { ...(themes.dark.variables || {}) },
      },
    },
  }

  const vuetify = createVuetify({
    aliases: {
      IconBtn: VBtn,
    },
    defaults,
    icons,
    theme: optionTheme,
    locale: {
      adapter: createVueI18nAdapter({ i18n: getI18n(), useI18n }),
    },
  })

  app.use(vuetify)
}
