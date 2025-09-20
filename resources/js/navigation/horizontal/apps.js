export default [
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-smart-home' },
    to: 'dashboard-inicio',
  },
  {
    title: 'Roles y permisos',
    icon: { icon: 'tabler-settings' },
    children: [
      { title: 'Usuarios', to: 'apps-usuarios' },
      { title: 'Roles', to: 'apps-roles' },
    ],
  },
]
