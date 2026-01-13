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
    path: '/apps/email/filter/:filter',
    name: 'apps-email-filter',
    component: emailRouteComponent,
    meta: {
      navActiveLink: 'apps-email',
      layoutWrapperClasses: 'layout-content-height-fixed',
    },
  },
  // dashboard-inicio se genera automáticamente desde /pages/dashboard-inicio.vue con definePage
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
  
  // Ruta para clientes
  
  // Ruta para ordenes
 
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
  // La ruta /configuracion-sistema ahora se genera automáticamente desde /pages/configuracion-sistema.vue con definePage
  
]
