import { useAbility } from '@casl/vue'

/**
 * Returns ability result if ACL is configured or else just return true
 * We should allow passing string | undefined to can because for admin ability we omit defining action & subject
 *
 * Useful if you don't know if ACL is configured or not
 * Used in @core files to handle absence of ACL without errors
 *
 * @param {string} action CASL Actions // https://casl.js.org/v4/en/guide/intro#basics
 * @param {string} subject CASL Subject // https://casl.js.org/v4/en/guide/intro#basics
 */
export const can = (action, subject) => {
  const vm = getCurrentInstance()
  if (!vm)
    return false
  const localCan = vm.proxy && '$can' in vm.proxy
    
  return localCan ? vm.proxy?.$can(action, subject) : true
}

/**
 * Check if user can view item based on it's ability
 * Based on item's action and subject & Hide group if all of it's children are hidden
 * @param {object} item navigation object item
 */
export const canViewNavMenuGroup = item => {
  const hasAnyVisibleChild = item.children.some(i => can(i.action, i.subject))

  // If subject and action is defined in item => Return based on children visibility (Hide group if no child is visible)
  // Else check for ability using provided subject and action along with checking if has any visible child
  if (!(item.action && item.subject))
    return hasAnyVisibleChild
  
  return can(item.action, item.subject) && hasAnyVisibleChild
}
export const canNavigate = to => {
  let ability
  try {
    ability = useAbility()
  } catch (err) {
    // Ability not provided yet (plugin not registered) - allow navigation by default
    // This prevents runtime errors during app init when plugins register order is not guaranteed.
    console.warn('[casl] ability not provided yet, allowing navigation by default', err)
    return true
  }
  
  // Si la ruta no tiene requisitos específicos de permisos, permitir acceso
  const targetRoute = to.matched[to.matched.length - 1]
  if (!targetRoute?.meta?.action && !targetRoute?.meta?.subject)
    return true
    
  // Get the most specific route (last one in the matched array)
  
  // Si la ruta tiene section, verificar si el usuario tiene permisos para esa sección
  if (targetRoute?.meta?.section) {
    const section = targetRoute.meta.section.toLowerCase()
    // Verificar si el usuario tiene permisos para esta sección
    const userData = useCookie('userData').value || {}
    const allowedSections = (userData.allowedSections || [])
      .map(s => typeof s === 'string' ? s.toLowerCase().trim() : '')
      .filter(s => s)
    
    // También verificar si es admin
    const userRoles = (userData.roles || [])
      .map(r => typeof r === 'string' ? r.toLowerCase().trim() : '')
      .filter(r => r)
    const isAdmin = userRoles.includes('admin')
    
    // Si es admin o tiene la sección permitida
    if (isAdmin || allowedSections.includes(section))
      return true
  }

  // Si tiene action y subject específicos, verificar con CASL
  if (targetRoute?.meta?.action && targetRoute?.meta?.subject)
    return typeof ability.can === 'function' ? ability.can(targetRoute.meta.action, targetRoute.meta.subject) : true

  // Si no tiene ningún permiso específico, verificar si alguna ruta padre permite acceso
  return to.matched.some(route => (typeof ability.can === 'function' ? ability.can(route.meta.action, route.meta.subject) : true))
}
