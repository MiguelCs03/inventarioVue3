/**
 * This is helper function to register plugins like a nuxt
 * To register a plugin just export a const function `defineVuePlugin` that takes `app` as argument and call `app.use`
 * For Scanning plugins it will include all files in `src/plugins` and `src/plugins/**\/index.ts`
 *
 *
 * @param {App} app Vue app instance
 *
 * @example
 * ```ts
 * // File: src/plugins/vuetify/index.ts
 *
 * import type { App } from 'vue'
 * import { createVuetify } from 'vuetify'
 *
 * const vuetify = createVuetify({ ... })
 *
 * export default function (app: App) {
 *   app.use(vuetify)
 * }
 * ```
 *
 * All you have to do is use this helper function in `main.ts` file like below:
 * ```ts
 * // File: src/main.ts
 * import { registerPlugins } from '@core/utils/plugins'
 * import { createApp } from 'vue'
 * import App from '@/App.vue'
 *
 * // Create vue app
 * const app = createApp(App)
 *
 * // Register plugins
 * registerPlugins(app) // [!code focus]
 *
 * // Mount vue app
 * app.mount('#app')
 * ```
 */
export const registerPlugins = async (app, options = {}) => {
  // Load plugin modules lazily to avoid eager evaluation side-effects
  // (some plugins may execute code at module evaluation time and cause errors/recursion).
  const modules = import.meta.glob(['../../plugins/*.{ts,js}', '../../plugins/*/index.{ts,js}'])
  const importPaths = Object.keys(modules).sort()

  const { verbose = true, skip = [] } = options

  for (const path of importPaths) {
    // runtime skip patterns
    if (path.includes('fake-api') || skip.some(s => path.includes(s))) {
      if (verbose) console.info(`[plugins] Skipping plugin import ${path}`)
      continue
    }

    if (verbose) console.info(`[plugins] Importing plugin ${path}`)

    try {
      // each modules[path] is a function that returns a promise resolving to the module
      const pluginImportModule = await modules[path]()

      if (pluginImportModule && typeof pluginImportModule.default === 'function') {
        try {
          if (verbose) console.info(`[plugins] Executing plugin ${path}`)
          pluginImportModule.default(app)
        } catch (err) {
          console.error(`[plugins] Error while executing plugin ${path}`, err)
          // don't stop the whole registration; continue to next plugin
        }
      } else {
        if (verbose) console.info(`[plugins] No default export in ${path}`)
      }
    } catch (err) {
      console.error(`[plugins] Error while importing plugin ${path}`, err)
      // If import itself throws (circular import / eval-time error) we still continue so
      // we can observe which plugins were already imported.
    }
  }
}
