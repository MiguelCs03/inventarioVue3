import axios from 'axios'
import { computed, ref } from 'vue'

export const menus = ref([])

// Función para filtrar menús según permisos
export const filteredMenus = computed(() => {
  const userData = useCookie('userData').value
  // Normalizar allowedSections para asegurar que sean strings y convertidos a minúsculas
  const allowedSections = ((userData?.allowedSections || [])
    .filter(s => s) // Filtrar valores vacíos
    .map(s => typeof s === 'string' ? s.trim().toLowerCase() : '')
    .filter(s => s)) // Filtrar strings vacíos después de trim
  
  // Verificar si es admin (múltiples variantes)
  const userRoles = (userData?.roles || []).map(r => typeof r === 'string' ? r.toLowerCase().trim() : '')
  const isAdmin = userRoles.includes('admin') || userRoles.includes('administrador')
  
  // Registrar en consola para depuración
  console.debug('[Menu] Filtering menu:', { 
    allowedSections, 
    isAdmin, 
    userRoles
  })

  // Si es admin, mostrar todos los menús
  if (isAdmin) {
    console.debug('[Menu] Admin user - showing all menus')
    return menus.value
  }

  // Filtrar menús según secciones permitidas
  return menus.value.filter(menu => {
    // Extraer la sección: usar menu.section si existe, sino limpiar el route
    const routeKey = (menu.to || '').toString().replace(/^\/+/, '') // Eliminar todos los / iniciales
    const sectionKey = (menu.section || routeKey || menu.title || '').toLowerCase().trim()
    
    // Si tiene hijos, mostrar si al menos un hijo es visible
    const hasVisibleChildren = menu.children && menu.children.some(child => {
      const childRouteKey = (child.to || '').toString().replace(/^\/+/, '')
      const childSectionKey = (child.section || childRouteKey || child.title || '').toLowerCase().trim()
      return allowedSections.includes(childSectionKey)
    })

    console.debug(`[Menu] Checking menu "${menu.title}":`, { 
      sectionKey, 
      isAllowed: allowedSections.includes(sectionKey),
      hasVisibleChildren
    })
    
    // Mostrar el menú si tiene permiso directo o si tiene hijos visibles
    return allowedSections.includes(sectionKey) || hasVisibleChildren
  }).map(menu => ({
    ...menu,
    // También filtrar submenús para mostrar solo los permitidos
    children: (menu.children || []).filter(child => {
      const childRouteKey = (child.to || '').toString().replace(/^\/+/, '')
      const childSectionKey = (child.section || childRouteKey || child.title || '').toLowerCase().trim()
      const isChildAllowed = allowedSections.includes(childSectionKey)
      
      console.debug(`[Menu] Checking submenu "${child.title}":`, {
        childSectionKey,
        isAllowed: isChildAllowed
      })
      
      return isChildAllowed
    }) || []
  }))
})

export async function fetchMenus() {
  try {
    console.debug('[Menu] Fetching menus from API...')
    const response = await axios.get('/api/menus')
    
  menus.value = response.data.map(menu => {
      // Normalizar la sección del menú principal
      const menuRoute = menu.route || ''
      const menuSection = menu.section || menuRoute.replace(/^\/+/, '') || menu.name.toLowerCase()
      
      return {
        title: menu.name,
        icon: menu.icon ? { icon: menu.icon } : undefined,
        to: menuRoute,
        section: menuSection,
        // Importante: NO establecer action/subject en grupos para que canViewNavMenuGroup
        // se base solo en la visibilidad de los hijos.
        children: menu.submenus?.map(sub => {
          // Normalizar la sección de los submenús
          const subRoute = sub.route || ''
          const subSection = sub.section || subRoute.replace(/^\/+/, '') || sub.name.toLowerCase()
          
          return {
            title: sub.name,
            to: subRoute,
            section: subSection,
            // Los hijos (links) sí llevan action/subject para CASL
            action: 'read',
            subject: subSection,
          }
        }) || [],
      }
    })
    
    console.debug('[Menu] Menus fetched:', menus.value)
  } catch (error) {
    console.error('Error fetching menus:', error)
  }
}
