const emailRouteComponent = () => import('@/pages/apps/email/index.vue')

// 👉 Redirects
export const redirects = [
  // ℹ️ We are redirecting to different pages based on role.
  // NOTE: Role is just for UI purposes. ACL is based on abilities.
  {
    path: '/',
    name: 'index',
    redirect: to => {
      // TODO: Get type from backend
      const userData = useCookie('userData')
      const userRoles = userData.value?.roles || []
      
      if (userRoles.includes('admin'))
        return { name: 'dashboard-inicio' }
      if (userRoles.includes('client'))
        return { name: 'dashboard-inicio' }
      
      return { name: 'login', query: to.query }
    },
  },
  {
    path: '/pages/user-profile',
    name: 'pages-user-profile',
    redirect: () => ({ name: 'pages-user-profile-tab', params: { tab: 'profile' } }),
  },
  {
    path: '/pages/account-settings',
    name: 'pages-account-settings',
    redirect: () => ({ name: 'pages-account-settings-tab', params: { tab: 'account' } }),
  },
]
export const routes = [
  {
    path: '/apps-usuarios',
    name: 'apps-usuarios',
    component: () => import('@/pages/apps-usuarios.vue'),
    meta: {
      section: 'usuarios', // Sección requerida para acceder
    },
  },
  {
    path: '/apps/email/filter/:filter',
    name: 'apps-email-filter',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/dashboard-inicio',
    name: 'dashboard-inicio',
    component: () => import('@/pages/dashboard-inicio.vue'),
  },
  {
    path: '/apps/email/label/:label',
    name: 'apps-email-label',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/proveedores',
    name: 'proveedores',
    component: () => import('@/pages/proveedores.vue'),
    meta: {
      section: 'proveedores', // Sección requerida
      navActiveLink: 'proveedores',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/compras',
    name: 'compras',
    component: () => import('@/pages/compras.vue'),
    meta: {
      section: 'compras', // Sección requerida
      navActiveLink: 'compras',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
]
