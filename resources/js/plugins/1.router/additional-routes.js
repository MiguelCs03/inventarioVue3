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
      section: 'apps-usuarios', // Sección requerida para acceder
      action: 'read',
      subject: 'apps-usuarios',
    },
  },
  {
    path: '/apps-roles',
    name: 'apps-roles',
    component: () => import('@/pages/apps-roles.vue'),
    meta: {
      section: 'apps-roles',
      action: 'read',
      subject: 'apps-roles',
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
    meta: {
      section: 'dashboard-inicio', // Agregar sección
      action: 'read',
      subject: 'dashboard-inicio',
    },
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
      action: 'read',
      subject: 'proveedores',
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
      action: 'read',
      subject: 'compras',
      navActiveLink: 'compras',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/perfil',
    name: 'perfil',
    component: () => import('@/pages/perfil.vue'),
    meta: {
      section: 'perfil', 
      action: 'read',
      subject: 'perfil',
    },
  },
  // Ruta para clientes
  {
    path: '/clientes',
    name: 'clientes',
    component: () => import('@/pages/clientes.vue'),
    meta: {
      section: 'clientes',
      action: 'read',
      subject: 'clientes',
      navActiveLink: 'clientes',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  // Ruta para ordenes
  {
    path: '/ordenes',
    name: 'ordenes',
    component: () => import('@/pages/ordenes.vue'),
    meta: {
      section: 'ordenes',
      action: 'read',
      subject: 'ordenes',
      navActiveLink: 'ordenes',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  // Rutas para gestión de grupos, marcas, subgrupos y unidades
  {
    path: '/apps/grupos',
    name: 'apps-grupos',
    component: () => import('@/pages/apps/grupo/GrupoPage.vue'),
    meta: {
      section: 'grupos',
      action: 'read',
      subject: 'grupos',
      navActiveLink: 'grupos',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/apps/marcas',
    name: 'apps-marcas',
    component: () => import('@/pages/apps/marca/MarcaPage.vue'),
    meta: {
      section: 'marcas',
      action: 'read',
      subject: 'marcas',
      navActiveLink: 'marcas',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/apps/subgrupos',
    name: 'apps-subgrupos',
    component: () => import('@/pages/apps/subgrupo/SubGrupoPage.vue'),
    meta: {
      section: 'subgrupos',
      action: 'read',
      subject: 'subgrupos',
      navActiveLink: 'subgrupos',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  {
    path: '/apps/unidades',
    name: 'apps-unidades',
    component: () => import('@/pages/apps/unidad/UnidadPage.vue'),
    meta: {
      section: 'unidades',
      action: 'read',
      subject: 'unidades',
      navActiveLink: 'unidades',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  
]
