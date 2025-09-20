import { computed } from 'vue'

export function usePermissions() {
  // Obtener datos del usuario de las cookies
  const userData = useCookie('userData')
  const userAbilityRules = useCookie('userAbilityRules')

  // Verificar si el usuario tiene un permiso específico
  const hasPermission = (permission) => {
    const userPermissions = userData.value?.permissions || []
    return userPermissions.includes(permission)
  }

  // Verificar si el usuario tiene un rol específico
  const hasRole = (role) => {
    const userRoles = userData.value?.roles || []
    return userRoles.includes(role)
  }

  // Verificar si el usuario es admin
  const isAdmin = computed(() => {
    return hasRole('admin')
  })

  // Verificar múltiples permisos (al menos uno)
  const hasAnyPermission = (permissions) => {
    return permissions.some(permission => hasPermission(permission))
  }

  // Verificar múltiples permisos (todos)
  const hasAllPermissions = (permissions) => {
    return permissions.every(permission => hasPermission(permission))
  }

  // Verificar si el usuario tiene acceso a una sección específica
  const hasAccessToSection = (section) => {
    const allowedSections = userData.value?.allowedSections || []
    return isAdmin.value || allowedSections.includes(section)
  }

  // Obtener todas las secciones permitidas
  const getAllowedSections = computed(() => {
    return userData.value?.allowedSections || []
  })

  // Verificar si el usuario puede realizar una acción sobre un subject (usando ability rules)
  const can = (action, subject) => {
    const rules = userAbilityRules.value || []
    return rules.some(rule => 
      (rule.action === action || rule.action === 'manage') && 
      (rule.subject === subject || rule.subject === 'all')
    )
  }

  return {
    hasPermission,
    hasRole,
    isAdmin,
    hasAnyPermission,
    hasAllPermissions,
    hasAccessToSection,
    getAllowedSections,
    can,
    userData: computed(() => userData.value),
    userPermissions: computed(() => userData.value?.permissions || []),
    userRoles: computed(() => userData.value?.roles || [])
  }
}
