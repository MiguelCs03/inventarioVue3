import axios from 'axios'
import { computed, ref } from 'vue'

export const menus = ref([])

// Función para filtrar menús según permisos
export const filteredMenus = computed(() => {
  const userData = useCookie('userData')
  const allowedSections = userData.value?.allowedSections || []
  const isAdmin = userData.value?.roles?.includes('admin') || false

  // Si es admin, mostrar todos los menús
  if (isAdmin) {
    return menus.value
  }

  // Filtrar menús según secciones permitidas
  return menus.value.filter(menu => {
    // Extraer la sección del route (ej: '/compras' -> 'compras')
    const section = menu.to?.replace('/', '') || menu.title.toLowerCase()
    return allowedSections.includes(section)
  }).map(menu => ({
    ...menu,
    // También filtrar submenús si existen
    children: menu.children?.filter(child => {
      const childSection = child.to?.replace('/', '') || child.title.toLowerCase()
      return allowedSections.includes(childSection)
    }) || []
  }))
})

export async function fetchMenus() {
  try {
    const response = await axios.get('/api/menus')
    menus.value = response.data.map(menu => ({
      title: menu.name,
      icon: menu.icon ? { icon: menu.icon } : undefined,
      to: menu.route,
      section: menu.section || menu.route?.replace('/', ''), // Agregar campo section
      children: menu.submenus?.map(sub => ({
        title: sub.name,
        to: sub.route,
        section: sub.section || sub.route?.replace('/', ''),
      })) || [],
    }))
  } catch (error) {
    console.error('Error fetching menus:', error)
  }
}
