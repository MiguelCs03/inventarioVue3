<template>
  <VRow class="fill-height">
    <VCol class="d-flex align-center justify-center">
      <div class="text-center">
        <h1>Vista de rol: Empleado</h1>
        <p>Bienvenido. Esta es una página de prueba solo para usuarios con rol empleado.</p>
      </div>
    </VCol>
  </VRow>
</template>

<script setup>
definePage({
  meta: {
    layout: 'default',
    action: 'read',
    subject: 'Dashboard',
  }
})
</script>
