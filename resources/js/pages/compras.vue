<template>
  <VRow>
    <VCol cols="12">
      <!-- Header con título, búsqueda y filtros -->
      <VCard class="mb-2 no-padding-card">
        <VCardText class="py-2 px-4">
          <div class="d-flex justify-space-between align-center flex-wrap ga-4">
            <div>
              <h2 class="text-h4 mb-1">Gestión de Compras</h2>
              <p class="text-body-1 mb-0">Administra las compras a proveedores</p>
            </div>

            <div class="d-flex align-center ga-3">
              <VTextField
                v-model="filtros.search"
                label="Buscar compra"
                placeholder="Buscar por proveedor..."
                prepend-inner-icon="tabler-search"
                clearable
                density="comfortable"
                hide-details
                style="min-width: 220px;"
                @input="buscarCompras"
              />

              <VSelect
                v-model="filtros.estado"
                label="Estado"
                :items="estadoOptions"
                clearable
                density="comfortable"
                hide-details
                style="min-width: 180px;"
                @update:model-value="buscarCompras"
              />

              <VBtn 
                color="success" 
                variant="outlined"
                prepend-icon="tabler-plus"
                @click="abrirModalCrear"
              >
                Nueva Compra
              </VBtn>
            </div>
          </div>
        </VCardText>
      </VCard>

      <!-- Tabla -->
      <VCard>
        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :headers="headers"
          :items="compras"
          :items-length="totalCompras"
          :loading="loading"
          class="elevation-1"
          @update:options="actualizarOpciones"
        >
          <!-- Proveedor -->
          <template #item.proveedor="{ item }">
            <div>
              <div class="text-body-1 font-weight-medium">
                {{ item.proveedor?.nombre }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                {{ item.proveedor?.contacto }}
              </div>
            </div>
          </template>

          <!-- Fecha -->
          <template #item.fecha="{ item }">
            {{ formatearFecha(item.fecha) }}
          </template>

          <!-- Estado -->
          <template #item.estado="{ item }">
            <VChip
              :color="obtenerColorEstado(item.estado)"
              size="small"
              variant="tonal"
            >
              {{ item.estado.toUpperCase() }}
            </VChip>
          </template>

          <!-- Total -->
          <template #item.total="{ item }">
            <div class="text-body-1 font-weight-medium">
              ${{ Number(item.total).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
            </div>
          </template>

          <!-- Acciones -->
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <VBtn
                icon="tabler-eye"
                size="small"
                color="info"
                variant="text"
                @click="verCompra(item)"
              />
            </div>
          </template>
        </VDataTableServer>
      </VCard>

      <!-- Modal Crear/Editar -->
      <VDialog 
        v-model="modalVisible" 
        max-width="900px"
        persistent
      >
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">
              Nueva Compra
            </span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="cerrarModal"
            />
          </VCardTitle>
          
          <VCardText>
            <VForm ref="formulario" @submit.prevent="guardarCompra">
              <VRow>
                <VCol cols="12" md="6">
                  <VSelect
                    v-model="compraForm.proveedor_id"
                    :items="proveedores"
                    item-title="nombre"
                    item-value="id"
                    label="Proveedor *"
                    required
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="compraForm.fecha"
                    label="Fecha *"
                    type="date"
                    required
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="compraForm.observaciones"
                    label="Observaciones"
                    rows="2"
                  />
                </VCol>
                <VCol cols="12">
                  <h4 class="mb-2">Productos</h4>
                  <VBtn
                    color="success"
                    variant="outlined"
                    size="small"
                    prepend-icon="tabler-plus"
                    @click="agregarItem"
                  >
                    Agregar Producto
                  </VBtn>
                  <div v-if="compraForm.items.length === 0" class="text-center py-6">
                    <VIcon size="64" color="grey-lighten-2">tabler-package</VIcon>
                    <p class="text-h6 mt-4">No hay productos en la compra</p>
                    <p class="text-body-2">Agrega productos a la compra</p>
                  </div>
                  <div v-else>
                    <VRow v-for="(item, index) in compraForm.items" :key="index" class="align-center mb-4">
                      <VCol cols="12" md="4">
                        <VSelect
                          v-model="item.producto_id"
                          :items="productos"
                          item-title="nombre"
                          item-value="id"
                          label="Producto *"
                          required
                          @update:model-value="actualizarPrecioProducto(item, item.producto_id)"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model="item.cantidad"
                          label="Cantidad *"
                          type="number"
                          min="1"
                          required
                          @input="calcularSubtotal(item)"
                        />
                      </VCol>
                      <VCol cols="12" md="3">
                        <VTextField
                          v-model="item.precio_unitario"
                          label="Precio Unitario *"
                          type="number"
                          min="0"
                          required
                          @input="calcularSubtotal(item)"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model="item.subtotal"
                          label="Subtotal"
                          type="number"
                          readonly
                        />
                      </VCol>
                      <VCol cols="12" md="1">
                        <VBtn
                          icon="tabler-trash"
                          size="small"
                          color="error"
                          variant="text"
                          @click="eliminarItem(index)"
                        />
                      </VCol>
                    </VRow>
                    <VDivider class="my-4" />
                    <VRow>
                      <VCol cols="12" class="text-end">
                        <h4>Total: ${{ totalCompra.toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}</h4>
                      </VCol>
                    </VRow>
                  </div>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
          
          <VCardActions>
            <VSpacer />
            <VBtn 
              color="error" 
              variant="outlined"
              @click="cerrarModal"
            >
              Cancelar
            </VBtn>
            <VBtn 
              color="success" 
              variant="elevated"
              :loading="guardando"
              :disabled="compraForm.items.length === 0"
              @click="guardarCompra"
            >
              Guardar
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>

      <!-- Modal Ver Detalles -->
      <VDialog 
        v-model="modalVerVisible" 
        max-width="800px"
      >
        <VCard v-if="compraDetalle">
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">Detalles de la Compra #{{ compraDetalle.id }}</span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="modalVerVisible = false"
            />
          </VCardTitle>
          
          <VCardText>
            <VCard class="mb-4" variant="outlined">
              <VCardTitle class="text-h6">Información General</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Proveedor</div>
                    <div class="text-body-1">
                      {{ compraDetalle.proveedor?.nombre }}
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Fecha</div>
                    <div class="text-body-1">{{ formatearFecha(compraDetalle.fecha) }}</div>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Estado</div>
                    <VChip
                      :color="obtenerColorEstado(compraDetalle.estado)"
                      size="small"
                      variant="tonal"
                    >
                      {{ compraDetalle.estado ? compraDetalle.estado.toUpperCase() : '' }}  
                    </VChip>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Total</div>
                    <div class="text-h6">
                      ${{ Number(compraDetalle.total).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                    </div>
                  </VCol>
                  <VCol cols="12" v-if="compraDetalle.observaciones">
                    <div class="text-body-2 text-medium-emphasis">Observaciones</div>
                    <div class="text-body-1">{{ compraDetalle.observaciones }}</div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <VCard variant="outlined">
              <VCardTitle class="text-h6">Productos Comprados</VCardTitle>
              <VCardText>
                <VTable>
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th>Precio Unit.</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in compraDetalle.items" :key="item.id">
                      <td>{{ item.producto?.nombre }}</td>
                      <td>{{ item.cantidad }}</td>
                      <td>${{ Number(item.precio_unitario).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}</td>
                      <td class="font-weight-medium">
                        ${{ Number(item.subtotal).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </VCardText>
            </VCard>
          </VCardText>
        </VCard>
      </VDialog>
    </VCol>
  </VRow>
</template>

<script setup>
import { usePermissions } from '@/composables/usePermissions'
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'

// Permisos
const { hasPermission, can } = usePermissions()

// Estado
const loading = ref(false)
const guardando = ref(false)
const modalVisible = ref(false)
const modalVerVisible = ref(false)
const formulario = ref(null)

// Datos
const compras = ref([])
const totalCompras = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const compraDetalle = ref(null)
const proveedores = ref([])
const productos = ref([])

// Filtros
const filtros = reactive({
  search: '',
  estado: ''
})

// Formulario
const compraForm = reactive({
  proveedor_id: null,
  fecha: new Date().toISOString().split('T')[0],
  observaciones: '',
  items: []
})

// Opciones
const estadoOptions = [
  { title: 'Registrada', value: 'registrada' },
  { title: 'Cancelada', value: 'cancelada' }
]

// Headers tabla
const headers = [
  { title: 'Proveedor', key: 'proveedor', sortable: false },
  { title: 'Fecha', key: 'fecha', sortable: true },
  { title: 'Estado', key: 'estado', sortable: true },
  { title: 'Total', key: 'total', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 100 }
]

// Computed
const totalCompra = computed(() => {
  return compraForm.items.reduce((total, item) => {
    return total + (parseFloat(item.subtotal) || 0)
  }, 0)
})

// Métodos
const cargarCompras = async () => {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: itemsPerPage.value,
      search: filtros.search || undefined,
      estado: filtros.estado || undefined
    }
    const response = await axios.get('/api/compras', { params })
    compras.value = Array.isArray(response.data.data) ? response.data.data : []
    totalCompras.value = response.data.total
  } catch (error) {
    console.error('Error al cargar compras:', error)
  } finally {
    loading.value = false
  }
}

const cargarProveedores = async () => {
  try {
    const response = await axios.get('/api/proveedores', { params: { activo: 1, per_page: 100 } })
    proveedores.value = response.data.data
  } catch (error) {
    console.error('Error al cargar proveedores:', error)
  }
}

const cargarProductos = async () => {
  try {
    const response = await axios.get('/api/productos', { params: { solo_activos: true } })
    productos.value = response.data
  } catch (error) {
    console.error('Error al cargar productos:', error)
  }
}

const buscarCompras = () => {
  page.value = 1
  cargarCompras()
}

const actualizarOpciones = (opciones) => {
  page.value = opciones.page
  itemsPerPage.value = opciones.itemsPerPage
  cargarCompras()
}

const abrirModalCrear = () => {
  resetearFormulario()
  modalVisible.value = true
  cargarProveedores()
  cargarProductos()
}

const verCompra = async (compra) => {
  try {
    const response = await axios.get(`/api/compras/${compra.id}`)
    compraDetalle.value = response.data
    modalVerVisible.value = true
  } catch (error) {
    console.error('Error al cargar detalles:', error)
  }
}

const guardarCompra = async () => {
  const { valid } = await formulario.value.validate()
  if (!valid || compraForm.items.length === 0) return

  guardando.value = true
  try {
    await axios.post('/api/compras', {
      proveedor_id: compraForm.proveedor_id,
      fecha: compraForm.fecha,
      observaciones: compraForm.observaciones,
      items: compraForm.items.map(item => ({
        producto_id: item.producto_id,
        cantidad: item.cantidad,
        precio_unitario: item.precio_unitario
      }))
    })
    cerrarModal()
    cargarCompras()
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al guardar compra:', error)
    // Aquí podrías mostrar un snackbar de error
  } finally {
    guardando.value = false
  }
}

const agregarItem = () => {
  compraForm.items.push({
    producto_id: null,
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0
  })
}

const eliminarItem = (index) => {
  compraForm.items.splice(index, 1)
}

const actualizarPrecioProducto = (item, productoId) => {
  const producto = productos.value.find(p => p.id === productoId)
  if (producto) {
    item.precio_unitario = producto.precio
    calcularSubtotal(item)
  }
}

const calcularSubtotal = (item) => {
  const cantidad = parseFloat(item.cantidad) || 0
  const precio = parseFloat(item.precio_unitario) || 0
  item.subtotal = (cantidad * precio).toFixed(2)
}

const cerrarModal = () => {
  modalVisible.value = false
  resetearFormulario()
  if (formulario.value) {
    formulario.value.resetValidation()
  }
}

const resetearFormulario = () => {
  Object.assign(compraForm, {
    proveedor_id: null,
    fecha: new Date().toISOString().split('T')[0],
    observaciones: '',
    items: []
  })
}

const obtenerColorEstado = (estado) => {
  const colores = {
    registrada: 'primary',
    cancelada: 'error'
  }
  return colores[estado] || 'grey'
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES')
}

// Cargar datos al montar
onMounted(() => {
  cargarCompras()
})

// Meta de la página
definePage({
  meta: {
    layout: 'default',
    action: 'read',
    subject: 'Compras',
  }
})
</script>

<style scoped>
.no-padding-card .v-card-text {
  padding-top: 8px !important;
  padding-bottom: 8px !important;
}

.no-padding-card {
  margin-bottom: 8px !important;
}
</style>
