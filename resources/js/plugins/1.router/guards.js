import { canNavigate } from '@layouts/plugins/casl'
import { fetchMenus, menus } from '@/store/menu'

export const setupGuards = router => {
  // 👉 router.beforeEach
  // Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
  router.beforeEach(async to => {
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

    // Si la ruta no tiene nombre (no está registrada), permitir que continúe
    // para que Vue Router la resuelva; esto puede pasar durante la primera navegación
    // si se usa router.replace con path string antes de que las rutas estén completamente cargadas
    if (!to.name && to.path) {
      console.debug('[Guard] Route without name, allowing navigation to resolve:', to.path)
      return undefined
    }

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
      // Evitar redirección circular: si ya estamos en login, no redirigir
      if (to.name === 'login') {
        return undefined
      }
      
      return {
        name: 'login',
        query: {
          ...to.query,
          to: to.fullPath !== '/' ? to.path : undefined,
        },
      }
    }

    // Si estamos logueados y aún no existen menús cargados, cargarlos antes de validar permisos
    if (isLoggedIn) {
      try {
        if (!Array.isArray(menus.value) || menus.value.length === 0) {
          await fetchMenus()
        }
      } catch (e) {
        console.warn('[Guard] Failed to preload menus before navigation', e)
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
      // Evitar redirección circular
      if (!isLoggedIn && to.name === 'login') {
        return undefined
      }
      if (isLoggedIn && to.name === 'not-authorized') {
        return undefined
      }
      
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
