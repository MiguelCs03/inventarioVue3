<template>
  <VContainer fluid>
    <!-- Primera fila: Analytics Web, Ventas Diarias Promedio, Resumen de Ventas -->
    <VRow>
      <!-- Analytics Web -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard class="gradient-card">
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-4">
              <div>
                <h6 class="text-sm text-white mb-1 opacity-75">
                  Analíticas Web
                </h6>
                <h4 class="text-white">
                  Tasa de Conversión Total 28.5%
                </h4>
              </div>
            </div>

            <div class="d-flex justify-space-between mb-6">
              <div>
                <p class="text-white text-sm mb-1 opacity-75">
                  Gastos
                </p>
                <div class="d-flex align-center gap-2">
                  <h5 class="text-white">
                    12h
                  </h5>
                  <span class="text-white text-sm opacity-75">Gastados</span>
                  <h5 class="text-white">
                    18
                  </h5>
                  <span class="text-white text-sm opacity-75">Tamaño Pedido</span>
                </div>
                <div class="d-flex align-center gap-4 mt-2">
                  <div class="d-flex align-center gap-2">
                    <h5 class="text-white">
                      127
                    </h5>
                    <span class="text-white text-sm opacity-75">Pedido</span>
                  </div>
                  <div class="d-flex align-center gap-2">
                    <h5 class="text-white">
                      2.3k
                    </h5>
                    <span class="text-white text-sm opacity-75">Artículos</span>
                  </div>
                </div>
              </div>
              <div class="analytics-3d-sphere">
                <VueApexCharts
                  type="radialBar"
                  :options="analytics3dOptions"
                  :series="[75]"
                  height="150"
                />
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Ventas Diarias Promedio -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h6 class="text-sm mb-1 text-disabled">
              Ventas Diarias Promedio
            </h6>
            <h4 class="text-h4 mb-1">
              Ventas Totales Este Mes
            </h4>
            <h3 class="text-h3 mb-6">
              BS 28,450
            </h3>
            <VueApexCharts
              type="area"
              :options="salesAreaOptions"
              :series="salesAreaSeries"
              height="120"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Resumen de Ventas -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-1">
              <h6 class="text-sm text-disabled">
                Resumen de Ventas
              </h6>
              <span class="text-success text-sm">+18.2%</span>
            </div>
            <h3 class="text-h3 mb-6">
              Bs 42.5k
            </h3>

            <div class="d-flex justify-space-between mb-6">
              <div class="d-flex align-center gap-2">
                <VIcon
                  color="info"
                  size="20"
                >
                  mdi-cart-outline
                </VIcon>
                <span class="text-sm">Pedido</span>
              </div>
              <div class="d-flex align-center gap-2">
                <VIcon
                  color="warning"
                  size="20"
                >
                  mdi-eye-outline
                </VIcon>
                <span class="text-sm">Visitas</span>
              </div>
            </div>

            <div class="d-flex justify-space-between mb-2">
              <div>
                <h4 class="text-h4">
                  62.2%
                </h4>
                <p class="text-sm text-disabled">
                  6,440
                </p>
              </div>
              <div>
                <h4 class="text-h4">
                  25.5%
                </h4>
                <p class="text-sm text-disabled">
                  12,749
                </p>
              </div>
            </div>

            <VProgressLinear
              :model-value="62"
              height="8"
              rounded
              color="info"
              class="mb-2"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Segunda fila: Reportes de Ganancias, Seguimiento de Soporte -->
    <VRow>
      <!-- Reportes de Ganancias -->
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-6">
              <div>
                <h5 class="text-h5 mb-1">
                  Reportes de Ganancias
                </h5>
                <p class="text-sm text-disabled">
                  Resumen Semanal de Ganancias
                </p>
              </div>
            </div>

            <div class="mb-6">
              <div class="d-flex align-center mb-4">
                <h2 class="text-h2 me-2">
                  Bs 468
                </h2>
                <VChip
                  color="success"
                  size="small"
                  variant="tonal"
                >
                  +4.2%
                </VChip>
              </div>
              <p class="text-sm text-disabled">
                Ganaste Bs 48 más en comparación con la semana pasada
              </p>
            </div>

            <VueApexCharts
              type="bar"
              :options="earningsBarOptions"
              :series="earningsBarSeries"
              height="200"
            />

            <VDivider class="my-6" />

            <VRow>
              <VCol
                cols="12"
                sm="4"
              >
                <div class="d-flex align-center gap-2 mb-2">
                  <VAvatar
                    color="primary"
                    variant="tonal"
                    size="40"
                  >
                    <VIcon
                      size="20"
                      icon="mdi-currency-usd"
                    />
                  </VAvatar>
                  <span class="text-sm">Ganancias</span>
                </div>
                <h4 class="text-h4">
                  Bs 545.69
                </h4>
              </VCol>

              <VCol
                cols="12"
                sm="4"
              >
                <div class="d-flex align-center gap-2 mb-2">
                  <VAvatar
                    color="info"
                    variant="tonal"
                    size="40"
                  >
                    <VIcon
                      size="20"
                      icon="mdi-chart-line"
                    />
                  </VAvatar>
                  <span class="text-sm">Ganancia</span>
                </div>
                <h4 class="text-h4">
                  Bs 256.34
                </h4>
              </VCol>

              <VCol
                cols="12"
                sm="4"
              >
                <div class="d-flex align-center gap-2 mb-2">
                  <VAvatar
                    color="error"
                    variant="tonal"
                    size="40"
                  >
                    <VIcon
                      size="20"
                      icon="mdi-alert-circle-outline"
                    />
                  </VAvatar>
                  <span class="text-sm">Gastos</span>
                </div>
                <h4 class="text-h4">
                  Bs 74.19
                </h4>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Seguimiento de Soporte -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">
              Seguimiento de Soporte
            </h5>
            <p class="text-sm text-disabled mb-6">
              Últimos 7 Días
            </p>

            <div class="d-flex justify-center mb-6">
              <VueApexCharts
                type="radialBar"
                :options="supportRadialOptions"
                :series="[85]"
                height="250"
              />
            </div>

            <h2 class="text-h2 text-center mb-2">
              164
            </h2>
            <p class="text-center text-sm text-disabled mb-6">
              Tickets Totales
            </p>

            <div class="d-flex flex-column gap-4">
              <div class="d-flex align-center gap-3">
                <VAvatar
                  color="primary"
                  variant="tonal"
                  size="36"
                >
                  <VIcon
                    size="18"
                    icon="mdi-ticket-outline"
                  />
                </VAvatar>
                <div class="flex-grow-1">
                  <p class="text-sm font-weight-medium mb-0">
                    Nuevos Tickets
                  </p>
                  <p class="text-xs text-disabled mb-0">
                    142
                  </p>
                </div>
              </div>

              <div class="d-flex align-center gap-3">
                <VAvatar
                  color="info"
                  variant="tonal"
                  size="36"
                >
                  <VIcon
                    size="18"
                    icon="mdi-check-circle-outline"
                  />
                </VAvatar>
                <div class="flex-grow-1">
                  <p class="text-sm font-weight-medium mb-0">
                    Tickets Abiertos
                  </p>
                  <p class="text-xs text-disabled mb-0">
                    28
                  </p>
                </div>
              </div>

              <div class="d-flex align-center gap-3">
                <VAvatar
                  color="warning"
                  variant="tonal"
                  size="36"
                >
                  <VIcon
                    size="18"
                    icon="mdi-clock-outline"
                  />
                </VAvatar>
                <div class="flex-grow-1">
                  <p class="text-sm font-weight-medium mb-0">
                    Tiempo de Respuesta
                  </p>
                  <p class="text-xs text-disabled mb-0">
                    1 Día
                  </p>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Tercera fila: Reportes de Ganancias Anuales, Ventas -->
    <VRow>
      <!-- Reportes de Ganancias Anuales -->
      <VCol
        cols="12"
        md="8"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">
              Reportes de Ganancias
            </h5>
            <p class="text-sm text-disabled mb-6">
              Resumen Anual de Ganancias
            </p>

            <VueApexCharts
              type="bar"
              :options="yearlyEarningsOptions"
              :series="yearlyEarningsSeries"
              height="300"
            />
          </VCardText>
        </VCard>
      </VCol>

      <!-- Ventas (Radar) -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">
              Ventas
            </h5>
            <p class="text-sm text-disabled mb-6">
              Últimos 6 Meses
            </p>

            <VueApexCharts
              type="radar"
              :options="salesRadarOptions"
              :series="salesRadarSeries"
              height="280"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Cuarta fila: Ventas por Países, Estado del Proyecto, Proyectos Activos -->
    <VRow>
      <!-- Ventas por Países -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">
              Ventas por Países
            </h5>
            <p class="text-sm text-disabled mb-6">
              Resumen Mensual de Ventas
            </p>

            <div class="d-flex flex-column gap-4">
              <div
                v-for="country in salesByCountry"
                :key="country.name"
                class="d-flex align-center justify-space-between"
              >
                <div class="d-flex align-center gap-3">
                  <VAvatar
                    size="36"
                    :image="country.flag"
                  />
                  <div>
                    <p class="text-sm font-weight-medium mb-0">
                      {{ country.amount }}
                    </p>
                    <p class="text-xs text-disabled mb-0">
                      {{ country.name }}
                    </p>
                  </div>
                </div>
                <VChip
                  :color="country.trend > 0 ? 'success' : 'error'"
                  size="small"
                  variant="tonal"
                >
                  {{ country.trend > 0 ? '+' : '' }}{{ country.trend }}%
                </VChip>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Estado del Proyecto -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <h5 class="text-h5 mb-1">
              Estado del Proyecto
            </h5>
            <p class="text-sm text-disabled mb-6">
              Tus Ganancias
            </p>

            <div class="mb-6">
              <h3 class="text-h3 mb-1">
                Bs 4,3742
              </h3>
              <VChip
                color="success"
                size="small"
                variant="tonal"
              >
                +10.2%
              </VChip>
            </div>

            <VueApexCharts
              type="line"
              :options="projectStatusOptions"
              :series="projectStatusSeries"
              height="150"
            />

            <VDivider class="my-6" />

            <div class="d-flex justify-space-between">
              <div>
                <p class="text-sm text-disabled mb-1">
                  Donaciones
                </p>
                <h5 class="text-h5">
                  Bs 756.26
                </h5>
                <VChip
                  color="error"
                  size="x-small"
                  variant="tonal"
                >
                  -139.34
                </VChip>
              </div>
              <VDivider vertical />
              <div>
                <p class="text-sm text-disabled mb-1">
                  Podcasts
                </p>
                <h5 class="text-h5">
                  Bs 2,207.03
                </h5>
                <VChip
                  color="success"
                  size="x-small"
                  variant="tonal"
                >
                  +576.24
                </VChip>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Productos con Mayor Rotación -->
      <VCol
        cols="12"
        md="4"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between align-center mb-1">
              <h5 class="text-h5">
                Productos con Mayor Rotación
              </h5>
              <VIcon
                color="info"
                size="20"
              >
                mdi-information-outline
              </VIcon>
            </div>
            <p class="text-sm text-disabled mb-6">
              Productos de alta demanda
            </p>

            <div class="d-flex flex-column gap-4">
              <div
                v-for="product in fastMovingProducts"
                :key="product.name"
                class="product-rotation-item"
              >
                <div class="d-flex align-center justify-space-between mb-2">
                  <div class="d-flex align-center gap-3">
                    <VAvatar
                      :color="product.stockLevel === 'critical' ? 'error' : product.stockLevel === 'low' ? 'warning' : 'success'"
                      variant="tonal"
                      size="40"
                    >
                      <VIcon
                        size="20"
                        :icon="product.icon"
                      />
                    </VAvatar>
                    <div>
                      <p class="text-sm font-weight-medium mb-0">
                        {{ product.name }}
                      </p>
                      <div class="d-flex align-center gap-2">
                        <VIcon
                          size="12"
                          color="success"
                        >
                          mdi-trending-up
                        </VIcon>
                        <span class="text-xs text-success">{{ product.dailySales }} ventas/día</span>
                      </div>
                    </div>
                  </div>
                  <VChip
                    :color="product.stockLevel === 'critical' ? 'error' : product.stockLevel === 'low' ? 'warning' : 'success'"
                    size="small"
                    variant="tonal"
                  >
                    {{ product.stock }} unid.
                  </VChip>
                </div>
                <div class="d-flex align-center gap-2">
                  <VProgressLinear
                    :model-value="(product.stock / product.maxStock) * 100"
                    :color="product.stockLevel === 'critical' ? 'error' : product.stockLevel === 'low' ? 'warning' : 'success'"
                    height="6"
                    rounded
                    class="flex-grow-1"
                  />
                  <span class="text-xs text-disabled">{{ Math.round((product.stock / product.dailySales)) }}d</span>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<script setup>
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'

const theme = useTheme()

// Define metadata para la página
definePage({
  meta: {
    section: 'dashboard-inicio',
    action: 'read',
    subject: 'dashboard-inicio',
  },
})

// Analytics 3D Options
const analytics3dOptions = {
  chart: {
    sparkline: { enabled: true },
  },
  plotOptions: {
    radialBar: {
      hollow: {
        size: '55%',
      },
      dataLabels: {
        name: { show: false },
        value: {
          show: false,
        },
      },
    },
  },
  colors: ['#fff'],
  grid: {
    padding: {
      top: -10,
      bottom: -10,
    },
  },
}

// Sales Area Chart
const salesAreaSeries = [{
  name: 'Ventas',
  data: [280, 320, 290, 350, 310, 370, 340, 390, 360, 410],
}]

const salesAreaOptions = {
  chart: {
    type: 'area',
    sparkline: { enabled: true },
    toolbar: { show: false },
  },
  stroke: {
    curve: 'smooth',
    width: 2,
  },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.5,
      opacityTo: 0.1,
    },
  },
  colors: ['#00D4BD'],
  tooltip: {
    enabled: true,
    theme: 'dark',
    y: {
      formatter: val => `Bs ${val}`,
    },
  },
}

// Earnings Bar Chart
const earningsBarSeries = [{
  name: 'Ganancias',
  data: [90, 120, 55, 100, 80, 125, 175],
}]

const earningsBarOptions = {
  chart: {
    type: 'bar',
    toolbar: { show: false },
    sparkline: { enabled: false },
  },
  plotOptions: {
    bar: {
      borderRadius: 8,
      distributed: false,
      columnWidth: '35%',
    },
  },
  colors: ['#7367F0'],
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    labels: {
      style: {
        colors: theme.current.value.dark ? '#fff' : '#000',
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: theme.current.value.dark ? '#fff' : '#000',
      },
    },
  },
  grid: {
    borderColor: theme.current.value.dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
  },
}

// Support Radial Chart
const supportRadialOptions = {
  chart: {
    sparkline: { enabled: true },
  },
  plotOptions: {
    radialBar: {
      hollow: {
        size: '65%',
      },
      track: {
        background: theme.current.value.dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
      },
      dataLabels: {
        name: {
          show: false,
        },
        value: {
          fontSize: '32px',
          fontWeight: 600,
          color: theme.current.value.dark ? '#fff' : '#000',
          formatter: val => `${val}%`,
        },
      },
    },
  },
  colors: ['#7367F0'],
  stroke: {
    lineCap: 'round',
  },
  tooltip: {
    theme: 'dark',
  },
}

// Yearly Earnings Chart
const yearlyEarningsSeries = [{
  name: 'Pedidos',
  data: [28, 10, 45, 38, 30, 35, 30, 8],
}]

const yearlyEarningsOptions = {
  chart: {
    type: 'bar',
    toolbar: { show: false },
  },
  plotOptions: {
    bar: {
      borderRadius: 8,
      columnWidth: '40%',
    },
  },
  colors: ['#7367F0'],
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
    labels: {
      style: {
        colors: theme.current.value.dark ? '#fff' : '#000',
      },
    },
  },
  yaxis: {
    labels: {
      formatter: val => `${val}k`,
      style: {
        colors: theme.current.value.dark ? '#fff' : '#000',
      },
    },
  },
  grid: {
    borderColor: theme.current.value.dark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
  },
}

// Sales Radar Chart
const salesRadarSeries = [{
  name: 'Ventas',
  data: [80, 50, 90, 40, 100, 60],
}, {
  name: 'Visitas',
  data: [70, 75, 85, 55, 90, 70],
}]

const salesRadarOptions = {
  chart: {
    type: 'radar',
    toolbar: { show: false },
  },
  colors: ['#7367F0', '#00CFE8'],
  stroke: {
    width: 2,
  },
  fill: {
    opacity: 0.2,
  },
  markers: {
    size: 4,
  },
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
  },
  yaxis: {
    show: false,
  },
  legend: {
    labels: {
      colors: theme.current.value.dark ? '#fff' : '#000',
    },
  },
  tooltip: {
    theme: 'dark',
  },
}

// Project Status Line Chart
const projectStatusSeries = [{
  name: 'Ganancias',
  data: [350, 380, 360, 400, 380, 420, 400, 440],
}]

const projectStatusOptions = {
  chart: {
    type: 'line',
    toolbar: { show: false },
    sparkline: { enabled: false },
  },
  stroke: {
    curve: 'smooth',
    width: 3,
  },
  colors: ['#FF9F43'],
  xaxis: {
    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago'],
    labels: {
      show: false,
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    show: false,
  },
  grid: {
    show: false,
  },
  tooltip: {
    enabled: true,
    y: {
      formatter: val => `Bs ${val}`,
    },
  },
}

// Sales by Country Data
const salesByCountry = [
  {
    name: 'Estados Unidos',
    amount: 'BS 8,567k',
    trend: 25.8,
    flag: 'https://flagcdn.com/w40/us.png',
  },
  {
    name: 'Brasil',
    amount: 'Bs 2,415k',
    trend: -6.2,
    flag: 'https://flagcdn.com/w40/br.png',
  },
  {
    name: 'India',
    amount: 'BS 865k',
    trend: 12.4,
    flag: 'https://flagcdn.com/w40/in.png',
  },
  {
    name: 'Australia',
    amount: 'BS 745k',
    trend: -11.9,
    flag: 'https://flagcdn.com/w40/au.png',
  },
  {
    name: 'Francia',
    amount: 'BS 45k',
    trend: 16.2,
    flag: 'https://flagcdn.com/w40/fr.png',
  },
]

// Fast Moving Products Data (con ref reactivo de Vue 3)
import { ref, computed } from 'vue'

const fastMovingProducts = ref([
  {
    name: 'iPhone 14 Pro',
    dailySales: 45,
    stock: 23,
    maxStock: 100,
    stockLevel: 'critical',
    icon: 'mdi-cellphone',
  },
  {
    name: 'MacBook Air M2',
    dailySales: 28,
    stock: 67,
    maxStock: 120,
    stockLevel: 'low',
    icon: 'mdi-laptop',
  },
  {
    name: 'AirPods Pro 2',
    dailySales: 52,
    stock: 145,
    maxStock: 200,
    stockLevel: 'healthy',
    icon: 'mdi-headphones',
  },
  {
    name: 'iPad Pro 12.9"',
    dailySales: 18,
    stock: 89,
    maxStock: 150,
    stockLevel: 'healthy',
    icon: 'mdi-tablet',
  },
  {
    name: 'Apple Watch Ultra',
    dailySales: 31,
    stock: 42,
    maxStock: 80,
    stockLevel: 'low',
    icon: 'mdi-watch',
  },
])
</script>

<style scoped>
.gradient-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.analytics-3d-sphere {
  position: relative;
}

.text-sm {
  font-size: 0.875rem;
}

.text-xs {
  font-size: 0.75rem;
}

.opacity-75 {
  opacity: 0.75;
}

/* Mejorar contraste de labels de gráficas en MODO OSCURO */
:deep(.v-theme--dark .apexcharts-text) {
  fill: rgba(255, 255, 255, 0.85) !important;
}

:deep(.v-theme--dark .apexcharts-yaxis-label),
:deep(.v-theme--dark .apexcharts-xaxis-label) {
  fill: rgba(255, 255, 255, 0.85) !important;
}

/* Mejorar contraste de labels de gráficas en MODO CLARO */
:deep(.v-theme--light .apexcharts-text) {
  fill: rgba(0, 0, 0, 0.75) !important;
}

:deep(.v-theme--light .apexcharts-yaxis-label),
:deep(.v-theme--light .apexcharts-xaxis-label) {
  fill: rgba(0, 0, 0, 0.75) !important;
}
</style>

<style>
/* ========================================
   TOOLTIPS DE APEXCHARTS - TEMA OSCURO 
   ======================================== */
.v-theme--dark .apexcharts-tooltip {
  background: #2c3e50 !important;
  border: 1px solid #34495e !important;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3) !important;
}

.v-theme--dark .apexcharts-tooltip-title {
  background: #34495e !important;
  border-bottom: 1px solid #4a5f7f !important;
  color: #ffffff !important;
  font-weight: 600 !important;
}

.v-theme--dark .apexcharts-tooltip-text,
.v-theme--dark .apexcharts-tooltip-text-y-label,
.v-theme--dark .apexcharts-tooltip-text-y-value,
.v-theme--dark .apexcharts-tooltip-series-group {
  color: #ffffff !important;
}

/* ========================================
   TOOLTIPS DE APEXCHARTS - TEMA CLARO
   ======================================== */
.v-theme--light .apexcharts-tooltip {
  background: #ffffff !important;
  border: 1px solid #e0e0e0 !important;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15) !important;
}

.v-theme--light .apexcharts-tooltip-title {
  background: #f5f5f5 !important;
  border-bottom: 1px solid #e0e0e0 !important;
  color: #2c3e50 !important;
  font-weight: 600 !important;
}

.v-theme--light .apexcharts-tooltip-text,
.v-theme--light .apexcharts-tooltip-text-y-label,
.v-theme--light .apexcharts-tooltip-text-y-value,
.v-theme--light .apexcharts-tooltip-series-group {
  color: #2c3e50 !important;
}

/* Marcador de color del tooltip */
.apexcharts-tooltip-marker {
  margin-right: 8px !important;
}

/* ========================================
   GRID DE GRÁFICAS
   ======================================== */
.v-theme--dark .apexcharts-gridline {
  stroke: rgba(255, 255, 255, 0.1) !important;
}

.v-theme--light .apexcharts-gridline {
  stroke: rgba(0, 0, 0, 0.1) !important;
}
</style>
