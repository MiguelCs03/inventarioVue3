<template>
  <div style="padding: 2rem; font-size: 2rem; text-align: center;">
    Hola mundo
  </div>
</template>

<script setup>
// Define metadata para la página
definePage({
  meta: {
    // Especificar la sección para el control de acceso
    section: 'dashboard-inicio',
    // Especificar acción y sujeto para CASL
    action: 'read',
    subject: 'dashboard-inicio',
  },
})
</script>
