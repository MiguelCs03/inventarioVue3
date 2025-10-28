import { filteredMenus } from '@/store/menu'

// Nota: Ya no invocamos fetchMenus en tiempo de importación para evitar
// peticiones HTTP mientras se registran plugins (causaba recursión en axios
// en ciertos escenarios). Ahora el fetch se hace desde el layout en onMounted.

export default filteredMenus
