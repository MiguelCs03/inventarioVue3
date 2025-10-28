import { menus } from '@/store/menu'

// Nota: Evitamos llamar a fetchMenus aquí para no disparar axios durante el import.
// El layout horizontal hará el fetch en onMounted.

export default menus
