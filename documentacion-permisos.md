# Sistema de Permisos y Autorización

## Índice
1. [Arquitectura General](#arquitectura-general)
2. [Base de Datos](#base-de-datos)
3. [Backend (Laravel)](#backend-laravel)
4. [Frontend (Vue 3)](#frontend-vue-3)
5. [Carga Dinámica del Menú](#carga-dinámica-del-menú)
6. [Gestión de Avatares y Fotos de Perfil](#gestión-de-avatares-y-fotos-de-perfil)
7. [Configuración de Página Inicial por Rol](#configuración-de-página-inicial-por-rol)
8. [Recuperación de Contraseña por Email (SMTP Gmail)](#recuperación-de-contraseña-por-email-smtp-gmail)
9. [Flujo Completo](#flujo-completo)
10. [Archivos Clave](#archivos-clave)

---

## Arquitectura General

El sistema implementa **Roles y Permisos** (RBAC - Role-Based Access Control):

- **Roles**: Agrupan permisos (Admin, Jefe, Trabajador)
- **Permisos**: Acciones sobre recursos (ver productos, editar clientes)
- **CASL**: Librería frontend para autorización
- **Middleware**: Protección de rutas backend
- **Composables**: Funciones Vue para verificar permisos

---

## Base de Datos

### Tablas Principales

#### 1. `roles`
**Ubicación**: `database/migrations/2025_09_09_000000_create_roles_table.php`

Almacena roles del sistema (Admin, Jefe, Trabajador).

**Campos importantes**:
- `nombre`: Nombre único del rol
- `tu_inicio`: Ruta personalizada al iniciar sesión
- `activo`: Estado del rol

#### 2. `permissions`
**Ubicación**: `database/migrations/2025_09_11_000000_create_permissions_table.php`

Define permisos individuales.

**Campos importantes**:
- `name`: Identificador único (ej: "ver-productos")
- `action`: Acción CASL (read, create, update, delete, manage)
- `subject`: Recurso (productos, clientes, ordenes)
- `description`: Descripción legible

#### 3. `permission_role` (Tabla Pivote)
**Ubicación**: `database/migrations/2025_09_11_000001_create_permission_role_table.php`

Relación many-to-many: Un rol tiene muchos permisos, un permiso puede estar en muchos roles.

#### 4. Relación con `users`
**Ubicación**: `database/migrations/0001_01_01_000000_create_users_table.php`

Los usuarios tienen `role_id` que apunta a la tabla `roles`

#### 5. `menus`
**Ubicación**: `database/migrations/2025_09_11_000002_create_menus_table.php`

Almacena los menús principales del sistema.

**Campos importantes**:
- `name`: Nombre del menú (ej: "Gestión", "Ventas")
- `icon`: Icono del menú (formato Iconify o MDI)
- `route`: Ruta asociada (opcional si solo es grupo)
- `section`: Sección para verificar permisos
- `is_active`: Estado activo/inactivo
- `order`: Orden de aparición

#### 6. `submenus`
**Ubicación**: `database/migrations/2025_09_11_000003_create_submenus_table.php`

Almacena los submenús (items dentro de cada menú).

**Campos importantes**:
- `menu_id`: FK al menú padre
- `name`: Nombre del submenú (ej: "Productos", "Clientes")
- `route`: Ruta Vue (ej: "/apps/productos")
- `section`: Sección para verificar contra `allowedSections`
- `is_active`: Estado activo/inactivo
- `order`: Orden de aparición

---

## Backend (Laravel)

### Modelos

#### 1. Role Model
**Archivo**: `app/Models/Role.php`

Define relaciones:
- `users()`: Un rol tiene muchos usuarios
- `permissions()`: Relación many-to-many con permisos

#### 2. Permission Model
**Archivo**: `app/Models/Permission.php`

Define relación:
- `roles()`: Relación many-to-many con roles

#### 3. User Model
**Archivo**: `app/Models/User.php` (líneas 70-90)

Métodos clave:
- `roles()`: Relación con el rol del usuario
- `hasRole($roleName)`: Verifica si tiene un rol específico
- `hasPermission($permissionName)`: Busca en sus roles si tiene el permiso

### Middleware

#### CheckPermission
**Archivo**: `app/Http/Middleware/CheckPermission.php`

Protege rutas del backend. Verifica:
1. Si el usuario está autenticado
2. Si tiene el permiso requerido usando `$user->hasPermission()`
3. Retorna 401 (no autenticado) o 403 (sin permisos)

**Uso**: `Route::middleware(['auth', 'permission:editar-productos'])->put(...)`

### AuthController - Login

**Archivo**: `app/Http/Controllers/AuthController.php` (líneas 10-120)

**Proceso crítico del login**:
1. Valida credenciales (email y password)
2. Carga roles y permisos del usuario: `$user->loadMissing('roles.permissions')`
3. Detecta si es admin (roles: admin, administrador, administrator)
4. **Si es admin**: acceso completo a todas las secciones, regla CASL `{action: 'manage', subject: 'all'}`
5. **Si no es admin**: calcula secciones permitidas desde `permission.subject`
6. Genera `userAbilityRules` para CASL
7. Retorna payload con:
   - `accessToken`
   - `userData` (id, name, email, roles, permissions, allowedSections)
   - `userAbilityRules`
   - `homeRoute`

### Ruta de Debug

**Archivo**: `routes/api.php` (líneas 60-95)

Endpoint: `GET /api/user/debug-permissions`

Útil para depurar. Retorna:
- Roles del usuario
- Lista completa de permisos con id, name, action, subject
- Si es admin o no

---

## Frontend (Vue 3)

### CASL - Librería de Autorización

**Archivo**: `resources/js/plugins/casl/ability.js`

Instancia global de CASL que se actualiza al hacer login con las reglas del backend.

### Composable usePermissions

**Archivo**: `resources/js/composables/usePermissions.js`

Composable principal para verificar permisos. Lee datos de cookies (`userData`, `userAbilityRules`).

**Funciones principales**:
- `hasPermission(name)`: Verifica permiso por nombre
- `hasRole(role)`: Verifica rol específico
- `isAdmin`: Computed booleano si es administrador
- `hasAnyPermission([...])`: Al menos uno de la lista
- `hasAllPermissions([...])`: Todos de la lista
- `hasAccessToSection(section)`: Verifica acceso a sección
- `can(action, subject)`: Verifica con formato CASL
- `userPermissions`: Lista de permisos
- `userRoles`: Lista de roles

**Uso típico**:
```javascript
const { hasPermission, isAdmin } = usePermissions()
```

### Helpers de Navegación

**Archivo**: `resources/js/@layouts/plugins/casl.js`

Tres funciones clave:

#### 1. `can(action, subject)`
Verifica habilidad usando CASL en el contexto del componente actual.

#### 2. `canViewNavMenuGroup(item)`
Determina si un grupo de menú debe mostrarse. Verifica:
- Si al menos un hijo es visible
- Si el grupo mismo tiene permisos de acción/subject

#### 3. `canNavigate(to)`
Protege rutas en Vue Router. Verifica:
- Si la ruta tiene `meta.section` y está en `allowedSections`
- Si es admin (acceso total)
- Si tiene action/subject específicos via CASL
- Redirige a `/not-authorized` si no tiene acceso

---

## Carga Dinámica del Menú

El sistema genera el menú de navegación dinámicamente desde la base de datos, filtrando opciones según los permisos del usuario.

### Base de Datos - Tablas de Menú

Ya documentadas en la sección [Base de Datos](#base-de-datos):
- `menus`: Grupos principales del menú
- `submenus`: Items dentro de cada grupo

### Backend - MenuController

**Archivo**: `app/Http/Controllers/MenuController.php`

**Endpoint**: `GET /api/menus`

**Proceso**:
1. Obtiene el usuario autenticado
2. Carga `roles.permissions` del usuario
3. Calcula `allowedSections` desde `permission.subject`
4. Detecta si es admin
5. Consulta BD: `Menu::with('submenus')` donde `is_active = true`
6. Normaliza campos `section` si están vacíos (usa route o name)
7. Retorna array de menús con sus submenús

**Nota**: El endpoint NO filtra por permisos en backend, retorna todos los menús activos. El filtrado se hace en frontend.

### Modelos

#### Menu Model
**Archivo**: `app/Models/Menu.php`

Relación:
- `submenus()`: Un menú tiene muchos submenús

#### Submenu Model
**Archivo**: `app/Models/Submenu.php`

Relación:
- `menu()`: Pertenece a un menú

### Frontend - Store de Menú

**Archivo**: `resources/js/store/menu.js`

Maneja el estado global del menú usando Vue refs y computed.

#### Variables reactivas:
- `menus`: ref([]) - Almacena menús obtenidos de la API

#### Funciones principales:

**1. `fetchMenus()`**
- Hace `GET /api/menus`
- Mapea respuesta a formato Vue Router:
  ```javascript
  {
    title: menu.name,
    icon: { icon: menu.icon },
    to: menu.route,
    section: menu.section, // Para filtrado
    children: [...submenus]
  }
  ```
- Normaliza `section` de menús y submenús
- Asigna `action: 'read'` y `subject: section` a los hijos

**2. `filteredMenus` (computed)**
- Lee `userData` de cookies (contiene `allowedSections`)
- Detecta si es admin → Retorna todos los menús
- Si no es admin:
  - Filtra menús donde `section` está en `allowedSections`
  - O que tengan al menos un hijo visible
  - Filtra también los `children` de cada menú
- Retorna solo menús y submenús permitidos

**Lógica de filtrado**:
```javascript
// Admin: todo visible
if (isAdmin) return menus.value

// No admin: filtrar por secciones
return menus.value.filter(menu => {
  const sectionKey = menu.section || routeKey
  const hasVisibleChildren = menu.children.some(child => 
    allowedSections.includes(child.section)
  )
  return allowedSections.includes(sectionKey) || hasVisibleChildren
}).map(menu => ({
  ...menu,
  children: menu.children.filter(child =>
    allowedSections.includes(child.section)
  )
}))
```

### Frontend - Configuración de Navegación

**Archivo**: `resources/js/navigation/vertical/index.js`

Configuración para menú lateral vertical.

```javascript
import { fetchMenus, filteredMenus } from '@/store/menu'

fetchMenus() // Carga menús al iniciar

export default filteredMenus // Exporta menús filtrados
```

**Archivo**: `resources/js/navigation/horizontal/index.js`

Idéntico al vertical pero para menú horizontal.

### Flujo Completo de Carga del Menú

1. **Inicio de la aplicación**:
   - Se importa `navigation/vertical/index.js`
   - Se ejecuta `fetchMenus()`

2. **fetchMenus() se ejecuta**:
   - Hace `GET /api/menus`
   - Backend retorna todos los menús activos con submenús
   - Frontend mapea a formato Vue Router
   - Almacena en `menus.value`

3. **filteredMenus se calcula**:
   - Lee `userData.allowedSections` de cookies
   - Detecta si es admin
   - Filtra menús y submenús según secciones permitidas
   - Retorna solo lo visible para el usuario

4. **Layout renderiza el menú**:
   - Lee `filteredMenus` (computed)
   - Solo muestra items permitidos
   - Usa `canViewNavMenuGroup()` para grupos con hijos

5. **Actualización reactiva**:
   - Si `userData` cambia (logout, cambio de permisos)
   - `filteredMenus` se recalcula automáticamente
   - Menú se actualiza en tiempo real

### Ejemplo de Datos

**Respuesta de `/api/menus`**:
```json
[
  {
    "id": 1,
    "name": "Gestión",
    "icon": "tabler-settings",
    "route": null,
    "section": "gestion",
    "is_active": true,
    "order": 1,
    "submenus": [
      {
        "id": 1,
        "menu_id": 1,
        "name": "Usuarios",
        "route": "/apps/usuarios",
        "section": "apps-usuarios",
        "is_active": true,
        "order": 1
      },
      {
        "id": 2,
        "menu_id": 1,
        "name": "Productos",
        "route": "/apps/productos",
        "section": "productos",
        "is_active": true,
        "order": 2
      }
    ]
  }
]
```

**Transformado en `menus.value`**:
```javascript
[
  {
    title: "Gestión",
    icon: { icon: "tabler-settings" },
    to: null,
    section: "gestion",
    children: [
      {
        title: "Usuarios",
        to: "/apps/usuarios",
        section: "apps-usuarios",
        action: "read",
        subject: "apps-usuarios"
      },
      {
        title: "Productos",
        to: "/apps/productos",
        section: "productos",
        action: "read",
        subject: "productos"
      }
    ]
  }
]
```

---

## Gestión de Avatares y Fotos de Perfil

El sistema permite a los usuarios subir y actualizar sus fotos de perfil (avatares), las cuales se almacenan en el servidor y se muestran en toda la aplicación.

### Base de Datos

**Tabla: `users`**
- `avatar`: Campo varchar(255) nullable que almacena la ruta relativa de la imagen en `storage/app/public/avatars/`

**Ejemplo de valor**:
```
avatars/xyz123.jpg
```

### Configuración de Storage

Laravel usa el sistema de almacenamiento público para las imágenes de perfil:

**Directorio físico**: `storage/app/public/avatars/`
**Enlace simbólico**: `public/storage` → `storage/app/public`

**Crear enlace simbólico** (si no existe):
```bash
php artisan storage:link
```

### Backend Laravel

#### UserController - uploadAvatar()

**Archivo**: `app/Http/Controllers/UserController.php`

**Endpoint**: `POST /user/avatar`

**Proceso**:
1. Obtiene el usuario autenticado mediante `Auth::user()` (sesión Laravel)
2. Valida que el archivo sea una imagen válida (max 2MB)
3. Almacena la imagen en `storage/app/public/avatars/` usando `store('avatars', 'public')`
4. Elimina la imagen anterior si existe (para evitar acumular archivos)
5. Actualiza el campo `avatar` del usuario en la BD
6. Retorna la URL pública del avatar: `asset('storage/' . $path)`

**Código clave**:
```php
public function uploadAvatar(Request $request)
{
    \Log::info('Upload avatar attempt', [
        'session_id' => $request->session()->getId(),
        'auth_check' => Auth::check(),
        'auth_id' => Auth::id(),
    ]);

    $user = Auth::user(); 
    
    if (!$user) {
        return response()->json([
            'message' => 'No autorizado. Por favor, inicie sesión.'
        ], 401);
    }

    $validated = $request->validate([
        'avatar' => 'required|image|max:2048', 
    ]);

    $path = $request->file('avatar')->store('avatars', 'public');

    // Eliminar imagen anterior
    if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
        \Storage::disk('public')->delete($user->avatar);
    }
    
    $user->avatar = $path;
    $user->save();

    return response()->json([
        'message' => 'Avatar subido exitosamente',
        'avatar_url' => asset('storage/' . $path),
    ]);
}
```

#### UserController - store() y update()

Los métodos para crear y actualizar usuarios también soportan la subida de avatar:

**Validación opcional**:
```php
'avatar' => 'nullable|image|max:2048'
```

**Almacenamiento**:
```php
if ($request->hasFile('avatar')) {
    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
}
```

**Respuesta incluyendo URL**:
```php
$response = $user->toArray();
if (!empty($user->avatar)) {
    $response['avatar_url'] = asset('storage/' . $user->avatar);
}
```

#### AuthController - Login

El login retorna la URL del avatar en el payload de `userData`:

```php
'userData' => [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    // ...
    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
]
```

#### Rutas Web vs API

**Importante**: La ruta de upload de avatar está en `routes/web.php` (no en `api.php`) para aprovechar la autenticación por sesión de Laravel:

**Archivo**: `routes/web.php`

```php
Route::post('/api/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware(['web'])->group(function () {
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar'])
        ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
});
```

**¿Por qué en web.php?**
- El login establece una sesión en Laravel (`Auth::attempt()`)
- La sesión solo funciona con el middleware `web`
- Las rutas en `api.php` son stateless por defecto
- Esto permite usar `Auth::user()` sin necesidad de tokens

**CSRF**: Se excluye el middleware CSRF para esta ruta específica para permitir uploads desde el frontend.

### Frontend Vue 3

#### Configuración de Axios

**Archivo**: `resources/js/plugins/axios.js`

Axios se configura globalmente para enviar cookies de sesión:

```javascript
import axios from 'axios'

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default axios
```

**Importación en main.js**:
```javascript
import '@/plugins/axios'
```

Esto asegura que todas las peticiones de Axios incluyan las cookies de sesión de Laravel.

#### Página de Perfil

**Archivo**: `resources/js/pages/perfil.vue`

**Funcionalidades**:
- Muestra avatar actual del usuario desde `userData.avatar`
- Botón "Cambiar Foto de Perfil" abre modal
- Modal con preview de la imagen seleccionada
- Upload mediante `fetch` con `FormData` y `credentials: 'include'`

**Código clave**:

```vue
<script setup>
const userData = useCookie('userData')
const avatarModal = ref(false)
const avatarFile = ref(null)
const avatarPreview = ref('')
const isAvatarLoading = ref(false)

const openAvatarModal = () => {
  avatarModal.value = true
  avatarFile.value = null
  avatarPreview.value = userData.value?.avatar || ''
}

const onAvatarChange = (event) => {
  const file = event.target.files[0]
  avatarFile.value = file
  if (file) {
    const reader = new FileReader()
    reader.onload = e => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const uploadAvatar = async () => {
  if (!avatarFile.value) return
  isAvatarLoading.value = true
  const formData = new FormData()
  formData.append('avatar', avatarFile.value)
  
  try {
    const response = await fetch('/user/avatar', {
      method: 'POST',
      body: formData,
      credentials: 'include', // Incluir cookies de sesión
    })
    const data = await response.json()
    
    if (response.ok) {
      // Actualizar cookie de userData
      const updatedUserData = {
        ...userData.value,
        avatar: data.avatar_url
      }
      userData.value = updatedUserData
      useCookie('userData').value = updatedUserData
      
      closeAvatarModal()
      alert('Foto de perfil actualizada')
      
      // Recargar para reflejar en toda la app
      window.location.reload()
    } else {
      alert(data.message || 'Error al subir imagen')
    }
  } catch (err) {
    console.error('Error al subir avatar:', err)
    alert('Error de red')
  } finally {
    isAvatarLoading.value = false
  }
}
</script>

<template>
  <!-- Avatar display -->
  <VAvatar size="120" :color="!(userData && userData.avatar) ? 'primary' : undefined">
    <VImg v-if="userData && userData.avatar" :src="userData.avatar" />
    <VIcon v-else icon="tabler-user" size="60" />
  </VAvatar>
  
  <VBtn color="primary" variant="tonal" @click="openAvatarModal">
    Cambiar Foto de Perfil
  </VBtn>
  
  <!-- Modal de upload -->
  <VDialog v-model="avatarModal" max-width="400">
    <VCard>
      <VCardTitle>Subir Foto de Perfil</VCardTitle>
      <VCardText>
        <VAvatar size="120" class="mb-4">
          <VImg v-if="avatarPreview" :src="avatarPreview" />
          <VIcon v-else icon="tabler-user" size="60" />
        </VAvatar>
        <input type="file" accept="image/*" @change="onAvatarChange" />
      </VCardText>
      <VCardActions>
        <VBtn variant="text" @click="closeAvatarModal">Cancelar</VBtn>
        <VBtn color="primary" :loading="isAvatarLoading" 
              @click="uploadAvatar" :disabled="!avatarFile">
          Subir Foto
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
```

**Características importantes**:
- Usa `fetch` en lugar de Axios por mejor soporte de `FormData`
- `credentials: 'include'` asegura que se envíen las cookies de sesión
- Actualiza la cookie `userData` después del upload exitoso
- Recarga la página para que el avatar se actualice en navbar/sidebar

#### Gestión de Usuarios (Admin)

**Archivo**: `resources/js/pages/apps-usuarios.vue`

Los administradores pueden asignar avatares al crear o editar usuarios:

**Modal de creación/edición**:
```vue
<input type="file" accept="image/*" @change="onAvatarChange" />
```

**Envío con FormData**:
```javascript
const formData = new FormData()
formData.append('name', userForm.value.name)
formData.append('email', userForm.value.email)
// ...otros campos
if (userForm.value.avatarFile) {
  formData.append('avatar', userForm.value.avatarFile)
}

await axios.post('/api/users', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
})
```

**Vista de detalle del usuario**:
Muestra el avatar en el modal de detalles con la URL del avatar.

### Flujo Completo de Upload de Avatar

1. **Usuario autenticado** navega a su perfil (`/perfil`)
2. **Click en "Cambiar Foto"** → Abre modal de upload
3. **Selecciona imagen** → Preview se muestra en el modal
4. **Click en "Subir Foto"** → Frontend crea FormData y envía POST a `/user/avatar`
5. **Backend recibe petición**:
   - Verifica sesión con `Auth::user()`
   - Valida que sea imagen (max 2MB)
   - Guarda en `storage/app/public/avatars/`
   - Elimina avatar anterior si existe
   - Actualiza campo `avatar` en BD
   - Retorna URL pública del avatar
6. **Frontend recibe respuesta**:
   - Actualiza cookie `userData` con nueva URL
   - Recarga la página
7. **Avatar actualizado** aparece en perfil, navbar, sidebar y toda la app

### Consideraciones de Seguridad

- **Autenticación requerida**: Solo usuarios logueados pueden subir avatares
- **Validación de tipo**: Solo imágenes (jpg, png, gif, etc.)
- **Límite de tamaño**: Máximo 2MB por imagen
- **Almacenamiento seguro**: Archivos en `storage/`, no directamente accesibles sin el enlace simbólico
- **Limpieza automática**: Se eliminan avatares antiguos al subir uno nuevo
- **URL pública**: Se usa `asset()` para generar URLs accesibles desde el navegador
- **Sesión Laravel**: Usa autenticación de sesión nativa, no tokens

### Troubleshooting

**Problema**: Avatar no se muestra después de subirlo
- **Solución**: Verificar que existe el enlace simbólico: `php artisan storage:link`

**Problema**: Error 401 al subir avatar
- **Solución**: Asegurar que Axios envía cookies (`withCredentials: true`)

**Problema**: Error 419 CSRF
- **Solución**: Verificar que la ruta excluye el middleware CSRF en `web.php`

**Problema**: Avatar no se actualiza en navbar
- **Solución**: El frontend recarga la página automáticamente después del upload

---

## Configuración de Página Inicial por Rol

El sistema permite configurar una página de inicio personalizada para cada rol. Cuando un usuario inicia sesión, es redirigido a la página configurada para su rol principal.

### Base de Datos

**Tabla: `roles`**
- `tu_inicio`: Campo varchar(255) nullable que almacena la ruta inicial del rol

**Valores de ejemplo**:
```
/dashboard-inicio
/ordenes
/productos
/clientes
```

### Backend Laravel

#### AuthController - Login

El login usa el campo `tu_inicio` del rol para determinar la redirección:

**Archivo**: `app/Http/Controllers/AuthController.php`

```php
public function login(Request $request)
{
    // ... validación y Auth::attempt()
    
    if (Auth::attempt($credentials)) {
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        
        $user = Auth::user();
        $user->loadMissing('roles.permissions');
        
        // ... cálculo de permisos y secciones ...
        
        // Determinar homeRoute desde tu_inicio del rol principal o usar default
        $homeRoute = $user->roles->first()?->tu_inicio ?? '/dashboard-inicio';

        return response()->json([
            'accessToken' => $token,
            'userData' => [ /* ... */ ],
            'userAbilityRules' => $userAbilityRules,
            'homeRoute' => $homeRoute, // Ruta personalizada por rol
        ]);
    }
}
```

**Lógica**:
1. Obtiene el primer rol del usuario (`$user->roles->first()`)
2. Lee el campo `tu_inicio` de ese rol
3. Si no tiene `tu_inicio` definido, usa `/dashboard-inicio` como fallback
4. Retorna `homeRoute` en el payload de login

#### PermissionController - syncForRole()

Permite actualizar el campo `tu_inicio` junto con los permisos del rol:

**Archivo**: `app/Http/Controllers/PermissionController.php`

```php
public function syncForRole(Request $request, $roleId)
{
    $data = $request->validate([
        'permission_ids' => 'array',
        'permission_ids.*' => 'integer|exists:permissions,id',
        'tu_inicio' => 'nullable|string|max:255',
    ]);

    $role = Role::findOrFail($roleId);
    $role->permissions()->sync($data['permission_ids'] ?? []);
    
    // Actualizar campo tu_inicio si se envió
    if (isset($data['tu_inicio'])) {
        $role->tu_inicio = $data['tu_inicio'];
        $role->save();
    }

    return response()->json([
        'message' => 'Permisos y configuración del rol actualizados correctamente'
    ]);
}
```

#### Role Model

**Archivo**: `app/Models/Role.php`

El campo `tu_inicio` está incluido en `$fillable`:

```php
protected $fillable = [
    'nombre',
    'tu_inicio',
    'activo',
];
```

### Frontend Vue 3

#### Página de Gestión de Roles

**Archivo**: `resources/js/pages/apps-roles.vue`

Los administradores pueden configurar la vista inicial de cada rol desde el diálogo de permisos.

**Variables reactivas**:
```javascript
const roleTuInicio = ref('') // Vista inicial del rol
const availableRoutes = ref([]) // Rutas disponibles desde menús
```

**Carga de rutas disponibles**:
```javascript
const loadAvailableRoutes = async () => {
  try {
    const { data } = await axios.get('/api/menus')
    const routes = []
    
    data.forEach(menu => {
      if (menu.route) {
        routes.push({ 
          title: menu.name, 
          value: menu.route 
        })
      }
      
      if (menu.submenus && menu.submenus.length > 0) {
        menu.submenus.forEach(submenu => {
          if (submenu.route) {
            routes.push({ 
              title: `${menu.name} - ${submenu.name}`, 
              value: submenu.route 
            })
          }
        })
      }
    })
    
    availableRoutes.value = routes
  } catch (error) {
    console.error('Error cargando rutas:', error)
    // Fallback
    availableRoutes.value = [
      { title: 'Dashboard', value: '/dashboard-inicio' },
      { title: 'Perfil', value: '/perfil' },
    ]
  }
}
```

**Cargar vista inicial del rol**:
```javascript
const openPermissionsDialog = async (role) => {
  roleForPermissions.value = role
  selectedRoleId.value = role.id
  roleTuInicio.value = role.tu_inicio || '/dashboard-inicio'
  
  if (!permissions.value.length) await loadPermissions()
  await loadRolePermissions(role.id)
  isPermissionsDialogOpen.value = true
}
```

**Guardar configuración**:
```javascript
const savePermissions = async () => {
  await axios.put(`/api/roles/${selectedRoleId.value}/permissions`, { 
    permission_ids: Array.from(rolePermissionSet.value),
    tu_inicio: roleTuInicio.value // Guardar vista inicial
  })
  await loadRoles() // Recargar roles
}
```

**Template del selector**:
```vue
<v-card variant="outlined" class="mb-4">
  <v-card-text class="pa-3">
    <div class="d-flex align-center gap-2 mb-2">
      <v-icon icon="tabler-home" size="18" color="primary" />
      <span class="text-subtitle-2 font-weight-medium">Vista Inicial</span>
    </div>
    <v-select
      v-model="roleTuInicio"
      :items="availableRoutes"
      density="compact"
      variant="outlined"
      hide-details
      placeholder="Selecciona la vista inicial"
    />
  </v-card-text>
</v-card>
```

**Características del selector**:
- Carga rutas dinámicamente desde la tabla `menus` y `submenus`
- Muestra menús principales: "Dashboard", "Productos", etc.
- Muestra submenús con formato: "Gestión - Usuarios", "Apps - Roles"
- Se actualiza automáticamente si se agregan nuevos menús en la BD
- Fallback a rutas básicas si falla la carga

#### Login y Redirección

**Archivo**: `resources/js/pages/login.vue`

Después del login exitoso, el usuario es redirigido a la ruta configurada:

```javascript
const login = async () => {
  try {
    const response = await axios.post('/api/login', {
      email: credentials.value.email,
      password: credentials.value.password,
    })

    const { accessToken, userData, userAbilityRules, homeRoute } = response.data

    useCookie('userAbilityRules').value = userAbilityRules
    ability.update(userAbilityRules)
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    // Redirigir a homeRoute del rol o query param 'to'
    await nextTick(() => {
      const target = route.query.to 
        ? String(route.query.to) 
        : (homeRoute ?? '/')
      router.replace(target)
    })
  } catch (err) {
    console.error('Error de login:', err)
    errors.value = { email: 'Credenciales incorrectas' }
  }
}
```

**Lógica de redirección**:
1. Prioriza query param `to` (ej: redirigir después de timeout de sesión)
2. Si no hay `to`, usa `homeRoute` del payload (configurado por rol)
3. Si no hay `homeRoute`, usa `/` como fallback final

### Flujo Completo de Configuración

1. **Admin abre página de Roles** (`/apps-roles`)
2. **Click en icono de permisos** (escudo) de un rol → Abre diálogo
3. **Selector "Vista Inicial"** carga todas las rutas disponibles desde menús de la BD
4. **Admin selecciona ruta** (ej: `/ordenes` para rol "Ventas")
5. **Click en "Guardar"** → Frontend envía `tu_inicio: '/ordenes'` al backend
6. **Backend actualiza** campo `tu_inicio` en tabla `roles`
7. **Usuarios con ese rol** ahora inician sesión y son redirigidos a `/ordenes`

### Flujo de Login con Ruta Personalizada

1. **Usuario ingresa credenciales** y hace login
2. **Backend valida** y carga el rol del usuario
3. **Backend lee** `tu_inicio` del rol (ej: `/productos`)
4. **Backend retorna** `homeRoute: '/productos'` en el payload
5. **Frontend recibe** el payload y extrae `homeRoute`
6. **Vue Router redirige** a `/productos` automáticamente
7. **Usuario ve** la página configurada para su rol

### Casos de Uso

**Rol Admin**:
- `tu_inicio`: `/dashboard-inicio`
- Ve estadísticas y resumen general al iniciar sesión

**Rol Ventas**:
- `tu_inicio`: `/ordenes`
- Accede directamente a la gestión de órdenes

**Rol Bodega**:
- `tu_inicio`: `/productos`
- Ve el inventario de productos inmediatamente

**Rol Cajero**:
- `tu_inicio`: `/clientes`
- Gestiona clientes y ventas desde el inicio

### Consideraciones

- **Formato de ruta**: Debe empezar con `/` (ej: `/dashboard-inicio`)
- **Rutas válidas**: Solo rutas que existan en Vue Router
- **Permisos**: El usuario debe tener permisos para acceder a la ruta configurada
- **Fallback**: Si `tu_inicio` es null o inválido, usa `/dashboard-inicio`
- **Reactivo**: Los cambios se aplican en el próximo login, no en sesiones activas
- **Admin siempre puede**: Configurar cualquier ruta para cualquier rol

### Troubleshooting

**Problema**: Usuario sigue yendo a `/dashboard-inicio` después de cambiar `tu_inicio`
- **Solución**: El cambio solo aplica en nuevos logins. Cerrar sesión y volver a iniciar.

**Problema**: Selector de rutas aparece vacío
- **Solución**: Verificar que la API `/api/menus` retorna datos correctamente

**Problema**: Error 404 al iniciar sesión
- **Solución**: La ruta configurada en `tu_inicio` no existe en Vue Router. Configurar una ruta válida.

---

## Recuperación de Contraseña por Email (SMTP Gmail)

El sistema permite a los usuarios recuperar su contraseña mediante un enlace enviado por email usando SMTP de Gmail.

### Base de Datos

**Tabla: `password_reset_tokens`**
- `email` (primary key): Email del usuario que solicita el reset
- `token`: Token único generado para validar la solicitud
- `created_at`: Timestamp de creación (expira en 60 minutos según config)

### Configuración SMTP en .env

Para usar Gmail como servidor SMTP, configurar las siguientes variables:

```env
# Configuración de correo SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Intecruz Sistema"

# URL del frontend para construir el enlace de reset
APP_FRONTEND_URL=http://localhost:5173
```

**Nota importante sobre Gmail:**
- No usar la contraseña normal de Gmail
- Generar una **App Password** desde la configuración de seguridad de Google
- Pasos: Google Account → Security → 2-Step Verification → App Passwords
- Usar la contraseña de 16 caracteres generada en `MAIL_PASSWORD`

### Backend Laravel

#### config/mail.php
Configuración del mailer SMTP que lee las variables del .env:
- Define el mailer por defecto (`smtp`)
- Configura host, port, username, password, encryption
- Define dirección y nombre del remitente (`from`)

#### config/auth.php
Configuración de password reset:
```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,      // Token expira en 60 minutos
        'throttle' => 60,    // Espera 60 segundos entre solicitudes
    ],
],
```

#### routes/api.php
Dos endpoints para el proceso:

**POST /api/forgot-password**
- Recibe: `{ email: "user@example.com" }`
- Valida que el email exista
- Genera token y guarda en `password_reset_tokens`
- Envía email con enlace de reset usando `Password::sendResetLink()`
- Responde siempre con éxito (para evitar enumeración de usuarios)

**POST /api/reset-password**
- Recibe: `{ email, token, password, password_confirmation }`
- Valida token, email, y contraseña (mínimo 6 caracteres, confirmación coincide)
- Usa `Password::reset()` para cambiar la contraseña
- Hashea la nueva contraseña con `bcrypt()`
- Elimina el token usado
- Responde con éxito o error

#### app/Models/User.php
Método sobrescrito para personalizar la notificación:
```php
public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token, $this->email));
}
```

#### app/Notifications/ResetPasswordNotification.php
Notificación personalizada que:
- Recibe `$token` y `$email` en el constructor
- Construye URL del frontend: `APP_FRONTEND_URL/reset-password?token={token}&email={email}`
- Usa vista personalizada Blade para el email: `emails.miformato`
- Define asunto: "Notificación de restablecimiento de contraseña"

#### resources/views/emails/miformato.blade.php
Template HTML del email con estilos inline:
- Logo de la empresa (imagen externa)
- Título: "Restablecimiento de contraseña"
- Texto explicativo en español
- Botón grande con enlace al reset: `{{ $url }}`
- Aviso: "Si no solicitaste el restablecimiento, puedes ignorar este mensaje"
- Footer con copyright

### Frontend Vue 3

#### resources/js/pages/forgot-password.vue
Página para solicitar el reset:
- Campo de entrada: email
- Botón "Enviar enlace"
- Envía POST a `/api/forgot-password` con axios
- Muestra toast de éxito: "se ha enviado un enlace para restablecer la contraseña"
- Layout: `blank` (sin sidebar/navbar)
- Meta: `unauthenticatedOnly: true`

#### resources/js/pages/reset-password.vue
Página para establecer nueva contraseña:
- Extrae `token` y `email` de query params (viene del enlace del email)
- Formulario con campos:
  - Email (prellenado, readonly o disabled)
  - Token (prellenado, hidden)
  - Nueva contraseña (con toggle visibility)
  - Confirmar contraseña (con toggle visibility)
- Validaciones frontend:
  - Verifica que contraseñas coincidan
  - Mínimo 6 caracteres
- Envía POST a `/api/reset-password` con fetch
- Si éxito: mensaje de éxito y redirige a `/login` después de 2 segundos
- Si error: muestra mensaje de error (token expirado, inválido, etc.)

### Flujo Completo de Recuperación de Contraseña

1. **Usuario olvida contraseña** → Hace clic en "¿Olvidaste tu contraseña?" en login
2. **Página forgot-password** → Ingresa su email y envía formulario
3. **Backend recibe solicitud** → POST `/api/forgot-password`
4. **Laravel genera token** → Crea registro en `password_reset_tokens`
5. **Backend envía email** → Usa Gmail SMTP para enviar notificación
6. **Usuario recibe email** → Abre y hace clic en botón "Restablecer contraseña"
7. **Redirige a reset-password** → Abre `http://localhost:5173/reset-password?token=xxx&email=yyy`
8. **Página reset-password** → Usuario ingresa nueva contraseña y confirma
9. **Backend valida y actualiza** → POST `/api/reset-password` verifica token, actualiza password
10. **Redirige a login** → Usuario puede iniciar sesión con nueva contraseña

### Ejemplo de Email Recibido

```
De: Intecruz Sistema <tu-email@gmail.com>
Para: usuario@example.com
Asunto: Notificación de restablecimiento de contraseña

[Logo Intecruz]

Restablecimiento de contraseña

Hola,

Recibiste este correo porque se solicitó un restablecimiento 
de contraseña para tu cuenta en Intecruz.

┌──────────────────────────────────┐
│    [Restablecer contraseña]      │  ← Botón azul grande
└──────────────────────────────────┘

Si no solicitaste el restablecimiento, puedes ignorar este mensaje.

© 2025 Intecruz. Todos los derechos reservados.
```

### Consideraciones de Seguridad

- **Token expira en 60 minutos**: Configurado en `config/auth.php`
- **Throttling**: Solo 1 solicitud por minuto por email
- **Respuesta genérica**: Siempre responde igual (éxito) para evitar enumeración de usuarios
- **Token único**: Se genera hash único para cada solicitud
- **Token de un solo uso**: Se elimina automáticamente al usarse
- **HTTPS recomendado**: En producción usar TLS/SSL
- **App Password**: Gmail requiere contraseña de aplicación (2FA activado)

---

## Flujo Completo

### Autenticación (Login)

1. Usuario ingresa email y password → POST `/api/login`
2. `AuthController` valida credenciales
3. Backend carga `roles.permissions` del usuario
4. Backend detecta si es admin o calcula secciones permitidas
5. Backend retorna: `accessToken`, `userData`, `userAbilityRules`
6. Frontend guarda en cookies y actualiza CASL
7. Usuario redirigido a `homeRoute`

### Autorización en Backend

1. Request a endpoint protegido
2. Middleware `auth` verifica token
3. Middleware `permission:nombre` verifica permiso
4. `User::hasPermission()` busca en roles → permissions
5. Continúa o retorna 403 Forbidden

### Autorización en Frontend (Navegación)

1. Usuario navega a ruta (ej: `/apps/productos`)
2. Vue Router guard llama `canNavigate(to)`
3. Verifica `meta.section` en `allowedSections` o si es admin
4. Permite navegación o redirige a `/not-authorized`

### Autorización en Frontend (Componentes)

1. Componente importa `usePermissions()`
2. Usa `hasPermission()`, `hasRole()`, `can()`, etc.
3. Condiciona renderizado con `v-if`

---

## Uso en Componentes

### Ejemplo 1: Ocultar botón según permiso

```vue
<script setup>
const { hasPermission } = usePermissions()
</script>

<template>
  <VBtn v-if="hasPermission('crear-productos')" @click="crearProducto">
    Nuevo Producto
  </VBtn>
</template>
```

### Ejemplo 2: Verificar múltiples permisos

```vue
<template>
  <!-- Al menos uno -->
  <div v-if="hasAnyPermission(['ver-reportes', 'generar-reportes'])">
    Reportes
  </div>
  
  <!-- Todos -->
  <div v-if="hasAllPermissions(['ver-productos', 'editar-productos'])">
    Gestión completa
  </div>
</template>
```

### Ejemplo 3: Proteger ruta

**Archivo**: `resources/js/plugins/1.router/additional-routes.js`

```javascript
{
  path: '/apps/productos',
  name: 'apps-productos',
  component: () => import('@/pages/apps/producto/ProductoPage.vue'),
  meta: {
    section: 'productos',  // Se verifica contra allowedSections
    action: 'read',
    subject: 'productos',
  }
}
```
---

## Archivos Clave

### Backend - Permisos
| Archivo | Descripción |
|---------|-------------|
| `app/Models/Role.php` | Modelo de roles con relaciones y campo tu_inicio |
| `app/Models/Permission.php` | Modelo de permisos |
| `app/Models/User.php` | Métodos hasRole() y hasPermission(), campo avatar |
| `app/Http/Middleware/CheckPermission.php` | Middleware de protección |
| `app/Http/Controllers/AuthController.php` | Login y generación de reglas, homeRoute desde tu_inicio |
| `app/Http/Controllers/UserController.php` | CRUD de usuarios, uploadAvatar() |
| `app/Http/Controllers/PermissionController.php` | Gestión de permisos, syncForRole() con tu_inicio |
| `database/migrations/*_create_roles_table.php` | Tabla roles con campo tu_inicio |
| `database/migrations/*_create_permissions_table.php` | Tabla permissions |
| `database/migrations/*_create_permission_role_table.php` | Tabla pivote |
| `database/migrations/*_create_users_table.php` | Tabla users con campo avatar |

### Backend - Menú
| Archivo | Descripción |
|---------|-------------|
| `app/Models/Menu.php` | Modelo de menús con relación hasMany(Submenu) |
| `app/Models/Submenu.php` | Modelo de submenús con belongsTo(Menu) |
| `app/Http/Controllers/MenuController.php` | Endpoint GET /api/menus |
| `database/migrations/*_create_menus_table.php` | Tabla menus |
| `database/migrations/*_create_submenus_table.php` | Tabla submenus |

### Backend - Recuperación de Contraseña
| Archivo | Descripción |
|---------|-------------|
| `routes/api.php` | Endpoints POST /api/forgot-password y /api/reset-password |
| `app/Notifications/ResetPasswordNotification.php` | Notificación personalizada con enlace al frontend |
| `app/Models/User.php` | Método sendPasswordResetNotification() |
| `resources/views/emails/miformato.blade.php` | Template HTML del email de reset |
| `config/mail.php` | Configuración SMTP (Gmail) |
| `config/auth.php` | Configuración de tokens (expiración, throttle) |
| `database/migrations/*_create_users_table.php` | Incluye tabla password_reset_tokens |
| `.env` | Variables MAIL_* y APP_FRONTEND_URL |

### Backend - Avatares
| Archivo | Descripción |
|---------|-------------|
| `routes/web.php` | Ruta POST /user/avatar y POST /api/login (con sesión) |
| `app/Http/Controllers/UserController.php` | Método uploadAvatar(), store() y update() con soporte de avatar |
| `storage/app/public/avatars/` | Directorio de almacenamiento de avatares |
| `public/storage` | Enlace simbólico a storage/app/public |

### Frontend - Permisos
| Archivo | Descripción |
|---------|-------------|
| `resources/js/composables/usePermissions.js` | Composable principal |
| `resources/js/plugins/casl/ability.js` | Instancia CASL |
| `resources/js/@layouts/plugins/casl.js` | Helpers de navegación |
| `resources/js/plugins/1.router/guards.js` | Guards Vue Router |

### Frontend - Menú
| Archivo | Descripción |
|---------|-------------|
| `resources/js/store/menu.js` | Store de menús con fetchMenus() y filteredMenus |
| `resources/js/navigation/vertical/index.js` | Configuración de navegación vertical |

### Frontend - Recuperación de Contraseña
| Archivo | Descripción |
|---------|-------------|
| `resources/js/pages/forgot-password.vue` | Página para solicitar reset de contraseña |
| `resources/js/pages/reset-password.vue` | Página para establecer nueva contraseña |

### Frontend - Avatares y Configuración de Roles
| Archivo | Descripción |
|---------|-------------|
| `resources/js/pages/perfil.vue` | Página de perfil con upload de avatar |
| `resources/js/pages/apps-usuarios.vue` | Gestión de usuarios con asignación de avatar |
| `resources/js/pages/apps-roles.vue` | Gestión de roles con selector de vista inicial (tu_inicio) |
| `resources/js/plugins/axios.js` | Configuración de Axios con withCredentials para sesión |
| `resources/js/main.js` | Importación de configuración de Axios |
| `resources/js/pages/login.vue` | Login con redirección a homeRoute personalizado |

---

## Notas Importantes

### Permisos
- **Admin**: Roles "admin", "administrador", "administrator" tienen acceso total (`manage all`)
- **Secciones permitidas**: Se generan automáticamente desde `permission.subject`
- **CASL**: Para verificaciones complejas tipo `can('update', 'productos')`
- **Cookies**: Persisten `userData` y `userAbilityRules` entre recargas
- **Guards**: Vue Router protege rutas antes de navegación
- **Debug**: Endpoint `/api/user/debug-permissions` para depurar permisos del usuario actual

### Menú Dinámico
- **Filtrado frontend**: El backend retorna todos los menús activos, el filtrado por permisos se hace en Vue
- **Reactivo**: `filteredMenus` es un computed que se actualiza automáticamente al cambiar `userData`
- **Sección clave**: El campo `section` en menús/submenús debe coincidir con las `allowedSections` del usuario
- **Normalización**: Si `section` está vacío, se usa `route` o `name` como fallback
- **Admin**: Ve todos los menús sin filtrado
- **Orden**: Los menús y submenús se ordenan por el campo `order`

### Avatares y Fotos de Perfil
- **Almacenamiento**: Archivos guardados en `storage/app/public/avatars/`
- **Enlace simbólico**: Debe existir `public/storage` → `storage/app/public` (ejecutar `php artisan storage:link`)
- **Validación**: Solo imágenes, máximo 2MB
- **URL pública**: Generada con `asset('storage/avatars/xyz.jpg')`
- **Limpieza automática**: Se eliminan avatares antiguos al subir uno nuevo
- **Autenticación**: Usa sesión de Laravel (`Auth::user()`), no tokens
- **Rutas web**: La ruta de upload está en `web.php` para aprovechar sesiones
- **Axios configurado**: `withCredentials: true` para enviar cookies de sesión
- **Actualización reactiva**: Se recarga la página después del upload para reflejar cambios

### Configuración de Página Inicial por Rol
- **Campo BD**: `tu_inicio` en tabla `roles` almacena la ruta inicial
- **Formato**: Debe empezar con `/` (ej: `/dashboard-inicio`, `/productos`)
- **Carga dinámica**: Las rutas disponibles se cargan desde la tabla `menus` y `submenus`
- **Selector inteligente**: Muestra "Menú - Submenú" para rutas anidadas
- **Fallback**: Si `tu_inicio` es null, usa `/dashboard-inicio` por defecto
- **Aplicación**: Solo en nuevos logins, no afecta sesiones activas
- **Validación**: El usuario debe tener permisos para acceder a la ruta configurada
- **Admin**: Puede configurar cualquier ruta para cualquier rol

### Recuperación de Contraseña
- **Gmail App Password**: Obligatorio usar contraseña de aplicación (no la contraseña normal)
- **Expiración de token**: 60 minutos (configurable en `config/auth.php`)
- **Throttling**: Máximo 1 solicitud por minuto por email
- **Seguridad**: Respuesta genérica para evitar enumeración de usuarios
- **Token único**: Cada token se elimina automáticamente tras su uso
- **URL frontend**: Debe configurarse `APP_FRONTEND_URL` correctamente en `.env`
- **Template personalizado**: Email con estilos inline y branding de Intecruz
- **SMTP Gmail**: Requiere puerto 587 con TLS para conexión segura

---

**Fecha**: 21 de octubre de 2025  
**Proyecto**: Sistema Inventario Vue3 + Laravel  
**Incluye**: Sistema de Permisos (RBAC), Carga Dinámica de Menú, Gestión de Avatares, Configuración de Página Inicial por Rol y Recuperación de Contraseña por Email
