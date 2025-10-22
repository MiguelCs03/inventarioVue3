<template>
  <VRow>
    <VCol cols="12">
      <!-- Header -->
      <VCard class="mb-2">
        <VCardText class="py-2">
          <div class="d-flex justify-space-between align-center">
            <div>
              <h2 class="text-h4 mb-1">Gestión de Órdenes</h2>
              <p class="text-body-1 mb-0">Administra las órdenes de compra</p>
            </div>
            <VBtn 
              color="primary" 
              prepend-icon="tabler-plus"
              @click="abrirModalCrear"
            >
              Nueva Orden
            </VBtn>
          </div>
        </VCardText>
      </VCard>

      <!-- Filtros -->
      <VCard class="mb-2">
        <VCardText class="py-2">
          <VRow>
            <VCol cols="12" md="4">
              <VTextField
                v-model="filtros.search"
                label="Buscar por cliente"
                placeholder="Buscar cliente..."
                prepend-inner-icon="tabler-search"
                clearable
                @input="buscarOrdenes"
              />
            </VCol>
            <VCol cols="12" md="3">
              <VSelect
                v-model="filtros.estado"
                label="Estado"
                :items="estadoOptions"
                clearable
                @update:model-value="buscarOrdenes"
              />
            </VCol>
            <VCol cols="12" md="3">
                
            </VCol>
          </VRow>
        </VCardText>
      </VCard>

      <!-- Tabla -->
      <VCard>
        <VDataTableServer
          v-model:items-per-page="itemsPerPage"
          v-model:page="page"
          :headers="headers"
          :items="ordenes"
          :items-length="totalOrdenes"
          :loading="loading"
          class="elevation-1"
          @update:options="actualizarOpciones"
        >
          <!-- Cliente -->
          <template #item.cliente="{ item }">
            <div>
              <div class="text-body-1 font-weight-medium">
                {{ item.cliente?.nombre }} {{ item.cliente?.apellido }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                {{ item.cliente?.email }}
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
                @click="verOrden(item)"
              />
              <VBtn
                icon="tabler-pencil"
                size="small"
                color="primary"
                variant="text"
                @click="editarOrden(item)"
                :disabled="item.estado === 'confirmada'"
              />
              <VBtn
                v-if="item.estado === 'pendiente'"
                icon="tabler-check"
                size="small"
                color="success"
                variant="text"
                @click="abrirModalConfirmar(item)"
              />
            </div>
          </template>
        </VDataTableServer>
      </VCard>

      <!-- Modal Crear/Editar -->
      <VDialog 
        v-model="modalVisible" 
        max-width="1200px"
        persistent
        scrollable
      >
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">
              {{ esEdicion ? 'Editar Orden' : 'Nueva Orden' }}
            </span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="cerrarModal"
            />
          </VCardTitle>
          
          <VCardText>
            <VForm ref="formulario" @submit.prevent="guardarOrden">
              <!-- Datos maestros -->
              <VCard class="mb-6" variant="outlined">
                <VCardTitle class="text-h6">Información General</VCardTitle>
                <VCardText>
                  <VRow>
                    <VCol cols="12" md="6">
                      <VAutocomplete
                        v-model="ordenForm.cliente_id"
                        label="Cliente *"
                        :items="clientes"
                        item-title="nombre_completo"
                        item-value="id"
                        :rules="validaciones.cliente_id"
                        :loading="cargandoClientes"
                        clearable
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model="ordenForm.fecha"
                        label="Fecha *"
                        type="date"
                        :rules="validaciones.fecha"
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VSelect
                        v-model="ordenForm.estado"
                        label="Estado *"
                        :items="estadoOptions"
                        :rules="validaciones.estado"
                        required
                      />
                    </VCol>
                    <VCol cols="12">
                      <VTextarea
                        v-model="ordenForm.observaciones"
                        label="Observaciones"
                        rows="2"
                      />
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Items de la orden -->
              <VCard variant="outlined">
                <VCardTitle class="d-flex justify-space-between align-center">
                  <span class="text-h6">Items de la Orden</span>
                  <VBtn
                    color="primary"
                    size="small"
                    prepend-icon="tabler-plus"
                    @click="agregarItem"
                  >
                    Agregar Item
                  </VBtn>
                </VCardTitle>
                <VCardText>
                  <div v-if="ordenForm.items.length === 0" class="text-center py-6">
                    <VIcon size="64" color="grey-lighten-2">tabler-package</VIcon>
                    <p class="text-h6 mt-4">No hay items en la orden</p>
                    <p class="text-body-2">Agrega productos a la orden</p>
                  </div>

                  <div v-else>
                    <VRow 
                      v-for="(item, index) in ordenForm.items" 
                      :key="index"
                      class="align-center mb-4"
                    >
                      <VCol cols="12" md="4">
                        <VAutocomplete
                          v-model="item.producto_id"
                          label="Producto *"
                          :items="productos"
                          item-title="nombre"
                          item-value="id"
                          :loading="cargandoProductos"
                          @update:model-value="actualizarPrecioProducto(item, $event)"
                        >
                          <template #item="{ props, item: producto }">
                            <VListItem v-bind="props">
                              <VListItemTitle>{{ producto.raw.nombre }}</VListItemTitle>
                              <VListItemSubtitle>
                                ${{ Number(producto.raw.precio).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                              </VListItemSubtitle>
                            </VListItem>
                          </template>
                        </VAutocomplete>
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model="item.cantidad"
                          label="Cantidad *"
                          type="number"
                          min="1"
                          @input="calcularSubtotal(item)"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          v-model="item.precio_unitario"
                          label="Precio Unit. *"
                          type="number"
                          step="0.01"
                          min="0"
                          @input="calcularSubtotal(item)"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
                        <VTextField
                          :model-value="item.subtotal"
                          label="Subtotal"
                          readonly
                          prefix="$"
                        />
                      </VCol>
                      <VCol cols="12" md="2">
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
                    
                    <!-- Total -->
                    <VRow>
                      <VCol cols="12" class="text-end">
                        <div class="text-h6">
                          Total: ${{ totalOrden.toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                        </div>
                      </VCol>
                    </VRow>
                  </div>
                </VCardText>
              </VCard>
            </VForm>
          </VCardText>
          
          <VCardActions>
            <VSpacer />
            <VBtn 
              color="grey" 
              variant="text"
              @click="cerrarModal"
            >
              Cancelar
            </VBtn>
            <VBtn 
              color="primary" 
              variant="elevated"
              :loading="guardando"
              :disabled="ordenForm.items.length === 0"
              @click="guardarOrden"
            >
              {{ esEdicion ? 'Actualizar' : 'Guardar' }}
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>

      <!-- Modal Ver Detalles -->
      <VDialog 
        v-model="modalVerVisible" 
        max-width="800px"
        scrollable
      >
        <VCard v-if="ordenDetalle">
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">Detalles de la Orden #{{ ordenDetalle.id }}</span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="modalVerVisible = false"
            />
          </VCardTitle>
          
          <VCardText>
            <!-- Información general -->
            <VCard class="mb-4" variant="outlined">
              <VCardTitle class="text-h6">Información General</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Cliente</div>
                    <div class="text-body-1">
                      {{ ordenDetalle.cliente?.nombre }} {{ ordenDetalle.cliente?.apellido }}
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Fecha</div>
                    <div class="text-body-1">{{ formatearFecha(ordenDetalle.fecha) }}</div>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Estado</div>
                    <VChip
                      :color="obtenerColorEstado(ordenDetalle.estado)"
                      size="small"
                      variant="tonal"
                    >
                      {{ ordenDetalle.estado.toUpperCase() }}
                    </VChip>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Total</div>
                    <div class="text-h6">
                      ${{ Number(ordenDetalle.total).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                    </div>
                  </VCol>
                  <VCol cols="12" v-if="ordenDetalle.observaciones">
                    <div class="text-body-2 text-medium-emphasis">Observaciones</div>
                    <div class="text-body-1">{{ ordenDetalle.observaciones }}</div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Items -->
            <VCard variant="outlined">
              <VCardTitle class="text-h6">Items de la Orden</VCardTitle>
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
                    <tr v-for="item in ordenDetalle.items" :key="item.id">
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

      <!-- Modal Confirmar Orden -->
      <VDialog 
        v-model="modalConfirmarVisible" 
        max-width="800px"
        persistent
        scrollable
      >
        <VCard v-if="ordenAConfirmar">
          <VCardTitle class="d-flex justify-space-between align-center">
            <span class="text-h5">Confirmar Orden #{{ ordenAConfirmar.id }}</span>
            <VBtn
              icon="tabler-x"
              variant="text"
              @click="cerrarModalConfirmar"
            />
          </VCardTitle>
          
          <VCardText>
            <!-- Resumen de la orden -->
            <VCard class="mb-4" variant="outlined">
              <VCardTitle class="text-h6">Resumen de la Orden</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Cliente</div>
                    <div class="text-body-1">
                      {{ ordenAConfirmar.cliente?.nombre }} {{ ordenAConfirmar.cliente?.apellido }}
                    </div>
                  </VCol>
                  <VCol cols="6">
                    <div class="text-body-2 text-medium-emphasis">Total a Pagar</div>
                    <div class="text-h6 text-primary">
                      ${{ ordenAConfirmar?.total ? Number(ordenAConfirmar.total).toLocaleString('es-ES', { minimumFractionDigits: 2 }) : '0.00' }}
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Métodos de pago -->
            <VCard variant="outlined">
              <VCardTitle class="d-flex justify-space-between align-center">
                <span class="text-h6">Métodos de Pago</span>
                <VBtn
                  color="primary"
                  size="small"
                  prepend-icon="tabler-plus"
                  @click="agregarPago"
                  :disabled="totalPagado >= (ordenAConfirmar?.total || 0)"
                >
                  Agregar Pago
                </VBtn>
              </VCardTitle>
              <VCardText>
                <div v-if="pagosForm.length === 0" class="text-center py-6">
                  <VIcon size="64" color="grey-lighten-2">tabler-credit-card</VIcon>
                  <p class="text-h6 mt-4">No hay métodos de pago</p>
                  <p class="text-body-2">Agrega al menos un método de pago</p>
                </div>

                <div v-else>
                  <VRow 
                    v-for="(pago, index) in pagosForm" 
                    :key="index"
                    class="align-center mb-4"
                  >
                    <VCol cols="12" md="4">
                      <VSelect
                        v-model="pago.metodo_pago_id"
                        label="Método de Pago *"
                        :items="metodosPago"
                        item-title="nombre"
                        item-value="id"
                        :loading="cargandoMetodosPago"
                      />
                    </VCol>
                    <VCol cols="12" md="3">
                      <VTextField
                        v-model="pago.monto"
                        label="Monto *"
                        type="number"
                        step="0.01"
                        min="0.01"
                        :max="ordenAConfirmar.total - pagosForm.filter((p, i) => i !== index).reduce((total, p) => total + (parseFloat(p.monto) || 0), 0)"
                        prefix="$"
                        @input="actualizarTotalPagado"
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="pago.detalle"
                        label="Detalle"
                        placeholder="Ej: N° tarjeta, código QR, etc."
                      />
                    </VCol>
                    <VCol cols="12" md="1">
                      <VBtn
                        icon="tabler-trash"
                        size="small"
                        color="error"
                        variant="text"
                        @click="eliminarPago(index)"
                      />
                    </VCol>
                  </VRow>

                  <VDivider class="my-4" />
                  
                  <!-- Resumen de pagos -->
                  <VRow>
                    <VCol cols="12">
                      <div class="d-flex justify-space-between align-center">
                        <div>
                          <div class="text-body-2 text-medium-emphasis">Total de la Orden</div>
                          <div class="text-h6">
                            ${{ ordenAConfirmar?.total ? Number(ordenAConfirmar.total).toLocaleString('es-ES', { minimumFractionDigits: 2 }) : '0.00' }}
                          </div>
                        </div>
                        <div>
                          <div class="text-body-2 text-medium-emphasis">Total Pagado</div>
                          <div class="text-h6" :class="totalPagado === ordenAConfirmar.total ? 'text-success' : totalPagado > ordenAConfirmar.total ? 'text-error' : 'text-warning'">
                            ${{ totalPagado.toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                          </div>
                        </div>
                        <div>
                          <div class="text-body-2 text-medium-emphasis">Diferencia</div>
                          <div class="text-h6" :class="diferenciaPago === 0 ? 'text-success' : diferenciaPago < 0 ? 'text-error' : 'text-warning'">
                            ${{ Math.abs(diferenciaPago).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}
                            {{ diferenciaPago > 0 ? '(Falta)' : diferenciaPago < 0 ? '(Exceso)' : '(Correcto)' }}
                          </div>
                        </div>
                      </div>
                    </VCol>
                  </VRow>
                </div>
              </VCardText>
            </VCard>
          </VCardText>
          
          <VCardActions>
            <VSpacer />
            <VBtn 
              color="grey" 
              variant="text"
              @click="cerrarModalConfirmar"
            >
              Cancelar
            </VBtn>
            <VBtn 
              color="success" 
              variant="elevated"
              :loading="confirmando"
              :disabled="diferenciaPago !== 0 || pagosForm.length === 0 || pagosForm.some(p => !p.metodo_pago_id || !p.monto || p.monto <= 0)"
              @click="confirmarOrden"
            >
              Confirmar Orden
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>
    </VCol>
  </VRow>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, reactive, ref } from 'vue'

// Configurar la base URL de axios
axios.defaults.baseURL = 'http://localhost:8000'

// Estado
const loading = ref(false)
const guardando = ref(false)
const confirmando = ref(false)
const cargandoClientes = ref(false)
const cargandoProductos = ref(false)
const cargandoMetodosPago = ref(false)
const modalVisible = ref(false)
const modalVerVisible = ref(false)
const modalConfirmarVisible = ref(false)
const esEdicion = ref(false)
const formulario = ref(null)

// Datos
const ordenes = ref([])
const totalOrdenes = ref(0)
const page = ref(1)
const itemsPerPage = ref(10)
const ordenDetalle = ref(null)
const ordenAConfirmar = ref(null)
const clientes = ref([])
const productos = ref([])
const metodosPago = ref([])
const pagosForm = ref([])

// Filtros
const filtros = reactive({
  search: '',
  estado: ''
})

// Formulario
const ordenForm = reactive({
  id: null,
  cliente_id: null,
  fecha: new Date().toISOString().split('T')[0],
  estado: 'pendiente',
  observaciones: '',
  items: []
})

// Opciones
const estadoOptions = [
  { title: 'Pendiente', value: 'pendiente' },
  { title: 'Confirmada', value: 'confirmada' },
  { title: 'Cancelada', value: 'cancelada' }
]

// Headers tabla
const headers = [
  { title: 'Cliente', key: 'cliente', sortable: false },
  { title: 'Fecha', key: 'fecha', sortable: true },
  { title: 'Estado', key: 'estado', sortable: true },
  { title: 'Total', key: 'total', sortable: true },
  { title: 'Acciones', key: 'actions', sortable: false, width: 120 }
]

// Validaciones
const validaciones = {
  cliente_id: [v => !!v || 'El cliente es requerido'],
  fecha: [v => !!v || 'La fecha es requerida'],
  estado: [v => !!v || 'El estado es requerido']
}

// Computed
const totalOrden = computed(() => {
  return ordenForm.items.reduce((total, item) => {
    return total + (parseFloat(item.subtotal) || 0)
  }, 0)
})

const totalPagado = computed(() => {
  return pagosForm.value.reduce((total, pago) => {
    return total + (parseFloat(pago.monto) || 0)
  }, 0)
})

const diferenciaPago = computed(() => {
  if (!ordenAConfirmar.value) return 0
  return (parseFloat(ordenAConfirmar.value.total) || 0) - totalPagado.value
})

const montoMaximo = computed(() => {
  if (!ordenAConfirmar.value) return 0
  return ordenAConfirmar.value.total - totalPagado.value + (pagosForm.value.find(p => p.monto) ? parseFloat(pagosForm.value.find(p => p.monto)?.monto || 0) : 0)
})

// Métodos
const cargarOrdenes = async () => {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: itemsPerPage.value,
      search: filtros.search || undefined,
      estado: filtros.estado || undefined
    }

    const response = await axios.get('/api/ordenes', { params })
    ordenes.value = response.data.data
    totalOrdenes.value = response.data.total
  } catch (error) {
    console.error('Error al cargar órdenes:', error)
  } finally {
    loading.value = false
  }
}

const cargarClientes = async () => {
  cargandoClientes.value = true
  try {
    const response = await axios.get('/api/clientes', { 
      params: { per_page: 100, activo: true } 
    })
    clientes.value = response.data.data.map(cliente => ({
      ...cliente,
      nombre_completo: `${cliente.nombre} ${cliente.apellido}`
    }))
  } catch (error) {
    console.error('Error al cargar clientes:', error)
  } finally {
    cargandoClientes.value = false
  }
}

const cargarProductos = async () => {
  cargandoProductos.value = true
  try {
    const response = await axios.get('/api/productos', { 
      params: { solo_activos: true } 
    })
    productos.value = response.data
  } catch (error) {
    console.error('Error al cargar productos:', error)
  } finally {
    cargandoProductos.value = false
  }
}

const cargarMetodosPago = async () => {
  cargandoMetodosPago.value = true
  try {
    console.log('Cargando métodos de pago...')
    const response = await axios.get('/api/metodos-pago')
    console.log('Métodos de pago recibidos:', response.data)
    metodosPago.value = response.data
  } catch (error) {
    console.error('Error al cargar métodos de pago:', error)
  } finally {
    cargandoMetodosPago.value = false
  }
}

const buscarOrdenes = () => {
  page.value = 1
  cargarOrdenes()
}

const actualizarOpciones = (opciones) => {
  page.value = opciones.page
  itemsPerPage.value = opciones.itemsPerPage
  cargarOrdenes()
}

const abrirModalCrear = () => {
  esEdicion.value = false
  resetearFormulario()
  modalVisible.value = true
  cargarClientes()
  cargarProductos()
}

const editarOrden = (orden) => {
  esEdicion.value = true
  Object.assign(ordenForm, {
    id: orden.id,
    cliente_id: orden.cliente_id,
    fecha: orden.fecha,
    estado: orden.estado,
    observaciones: orden.observaciones,
    items: orden.items.map(item => ({
      producto_id: item.producto_id,
      cantidad: item.cantidad,
      precio_unitario: item.precio_unitario,
      subtotal: item.subtotal
    }))
  })
  modalVisible.value = true
  cargarClientes()
  cargarProductos()
}

const verOrden = async (orden) => {
  try {
    const response = await axios.get(`/api/ordenes/${orden.id}`)
    ordenDetalle.value = response.data
    modalVerVisible.value = true
  } catch (error) {
    console.error('Error al cargar detalles:', error)
  }
}

const abrirModalConfirmar = async (orden) => {
  try {
    console.log('Abriendo modal para confirmar orden:', orden.id)
    
    // Cargar los métodos de pago primero
    await cargarMetodosPago()
    
    // Luego cargar los detalles de la orden
    const response = await axios.get(`/api/ordenes/${orden.id}`)
    console.log('Orden cargada:', response.data)
    
    ordenAConfirmar.value = response.data
    pagosForm.value = []
    modalConfirmarVisible.value = true
  } catch (error) {
    console.error('Error al cargar orden:', error)
  }
}

const cerrarModalConfirmar = () => {
  modalConfirmarVisible.value = false
  ordenAConfirmar.value = null
  pagosForm.value = []
}

const agregarPago = () => {
  if (!ordenAConfirmar.value) return
  const montoRestante = (parseFloat(ordenAConfirmar.value.total) || 0) - pagosForm.value.reduce((total, pago) => total + (parseFloat(pago.monto) || 0), 0)
  pagosForm.value.push({
    metodo_pago_id: null,
    monto: montoRestante > 0 ? montoRestante.toFixed(2) : '',
    detalle: ''
  })
}

const eliminarPago = (index) => {
  pagosForm.value.splice(index, 1)
}

const actualizarTotalPagado = () => {
  // 
}

const confirmarOrden = async () => {
  confirmando.value = true
  try {
    const response = await axios.post(`/api/ordenes/${ordenAConfirmar.value.id}/confirmar`, {
      pagos: pagosForm.value.filter(pago => pago.metodo_pago_id && pago.monto > 0)
    })
    
    cerrarModalConfirmar()
    cargarOrdenes()
    
    // Mostrar mensaje de éxito
    console.log('Orden confirmada exitosamente')
  } catch (error) {
    console.error('Error al confirmar orden:', error)
    // Mostrar mensaje de error
  } finally {
    confirmando.value = false
  }
}

const agregarItem = () => {
  ordenForm.items.push({
    producto_id: null,
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0
  })
}

const eliminarItem = (index) => {
  ordenForm.items.splice(index, 1)
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

const guardarOrden = async () => {
  const { valid } = await formulario.value.validate()
  if (!valid || ordenForm.items.length === 0) return

  guardando.value = true
  try {
    if (esEdicion.value) {
      await axios.put(`/api/ordenes/${ordenForm.id}`, ordenForm)
    } else {
      await axios.post('/api/ordenes', ordenForm)
    }
    
    cerrarModal()
    cargarOrdenes()
    // Aquí podrías mostrar un snackbar de éxito
  } catch (error) {
    console.error('Error al guardar orden:', error)
    // Aquí podrías mostrar un snackbar de error
  } finally {
    guardando.value = false
  }
}

const cerrarModal = () => {
  modalVisible.value = false
  resetearFormulario()
  if (formulario.value) {
    formulario.value.resetValidation()
  }
}

const resetearFormulario = () => {
  Object.assign(ordenForm, {
    id: null,
    cliente_id: null,
    fecha: new Date().toISOString().split('T')[0],
    estado: 'pendiente',
    observaciones: '',
    items: []
  })
}

const obtenerColorEstado = (estado) => {
  const colores = {
    pendiente: 'warning',
    confirmada: 'success',
    cancelada: 'error'
  }
  return colores[estado] || 'grey'
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES')
}

// Cargar datos al montar
onMounted(() => {
  cargarOrdenes()
  cargarMetodosPago()  // Cargar métodos de pago al inicializar
})

// Meta de la página
definePage({
  meta: {
    layout: 'default',
    action: 'read',
    subject: 'Ordenes',
  }
})
</script>
