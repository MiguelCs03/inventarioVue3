import { canNavigate } from '@layouts/plugins/casl'

export const setupGuards = router => {
  // 👉 router.beforeEach
  // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
  router.beforeEach(to => {
    /*
         * If it's a public route, continue navigation. This kind of pages are allowed to visited by login & non-login users. Basically, without any restrictions.
         * Examples of public routes are, 404, under maintenance, etc.
         */
    if (to.meta.public)
      return

    /**
         * Check if user is logged in by checking if token & user data exists in local storage
         * Feel free to update this logic to suit your needs
         */
    const userCookie = useCookie('userData').value
    const tokenCookie = useCookie('accessToken').value
    const isLoggedIn = !!(userCookie && tokenCookie)

    // Debug: basic route and auth state
    try {
      console.debug('[Guard] to=', {
        name: to.name,
        path: to.path,
        meta: to.meta,
      }, 'isLoggedIn=', isLoggedIn)
    } catch (e) {
      // no-op
    }

    /*
          If user is logged in and is trying to access login like page, redirect to home
          else allow visiting the page
          (WARN: Don't allow executing further by return statement because next code will check for permissions)
         */
    if (to.meta.unauthenticatedOnly) {
      if (isLoggedIn)
        return '/'
      else
        return undefined
    }

    // Verificar si el usuario no está logueado
    if (!isLoggedIn) {
      return {
        name: 'login',
        query: {
          ...to.query,
          to: to.fullPath !== '/' ? to.path : undefined,
        },
      }
    }

    // Verificar permisos por sección si la ruta tiene una sección definida
    if (to.meta.section && isLoggedIn) {
      const userData = useCookie('userData').value
      const allowedSections = (userData?.allowedSections || [])
        .filter(s => typeof s === 'string')
        .map(s => s.trim())
      const allowedSectionsNorm = allowedSections.map(s => s.toLowerCase())
      const requiredSection = String(to.meta.section).trim().toLowerCase()

      // Verificar si es admin de múltiples formas (sin asumir idioma)
      const userRoles = (userData?.roles || [])
        .filter(r => typeof r === 'string')
        .map(r => r.trim().toLowerCase())
  const isAdmin = userRoles.includes('admin')

      try {
        console.debug('[Guard] section-check', {
          route: to.name,
          requiredSection,
          userSections: allowedSections,
          userSectionsNorm: allowedSectionsNorm,
          userRoles,
          isAdmin,
        })
      } catch (e) { /* no-op */ }

      // Si no es admin y no tiene acceso a la sección, redirigir
      if (!isAdmin && !allowedSectionsNorm.includes(requiredSection)) {
        console.warn('[Guard] blocked by section check -> not-authorized')
        return { name: 'not-authorized' }
      }
    }

    // Verificación existente con CASL
  const can = canNavigate(to)
  try { console.debug('[Guard] canNavigate=', can, 'matched=', to.matched.length) } catch (e) {}
  if (!can && to.matched.length) {
      /* eslint-disable indent */
            return isLoggedIn
                ? { name: 'not-authorized' }
                : {
                    name: 'login',
                    query: {
                        ...to.query,
                        to: to.fullPath !== '/' ? to.path : undefined,
                    },
                }
            /* eslint-enable indent */
    }
  })
}
