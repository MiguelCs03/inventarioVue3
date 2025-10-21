<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import AppSearch from '@/components/AppSearch.vue'

const unidades = ref([])
const loading = ref(false)
const showForm = ref(false)
const editItem = ref(null)

const form = ref({ codigo: '', descripcion: '', activo: true })
const saving = ref(false)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const confirmDelete = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

const populateForm = v => {
  if (v) form.value = { codigo: v.codigo ?? '', descripcion: v.descripcion ?? '', activo: !!v.activo }
  else form.value = { codigo: '', descripcion: '', activo: true }
}

const items = computed(() => {
  const val = unidades.value
  if (Array.isArray(val)) return val
  if (val && Array.isArray(val.data)) return val.data
  
  return []
})

// Search
const searchQuery = ref('')
const filterEstado = ref(null)

const filteredItems = computed(() => {
  let result = items.value
  
  // Filtro por estado
  if (filterEstado.value !== null) {
    result = result.filter(i => !!i.activo === filterEstado.value)
  }
  
  // Filtro por búsqueda
  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (q) {
    result = result.filter(i => (i.codigo || '').toString().toLowerCase().includes(q) || (i.descripcion || '').toString().toLowerCase().includes(q))
  }
  
  return result
})

const headers = [
  { title: 'ID', key: 'id_unidad' },
  { title: 'Código', key: 'codigo' },
  { title: 'Descripción', key: 'descripcion' },
  { title: 'Estado', key: 'activo' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const fetchUnidades = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/unidades')

    unidades.value = res.data
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error cargando unidades'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally { loading.value = false }
}

const openCreate = () => { editItem.value = null; populateForm(null); showForm.value = true }
const openEdit = item => { editItem.value = { ...item }; populateForm(item); showForm.value = true }

const save = async () => {
  if (!form.value.descripcion || String(form.value.descripcion).trim() === '') {
    snackbarMessage.value = 'La descripción es requerida'
    snackbarColor.value = 'error'
    snackbar.value = true
    
    return
  }
  saving.value = true
  try {
    if (editItem.value && editItem.value.id_unidad) {
      await axios.put(`/api/unidades/${editItem.value.id_unidad}`, form.value)
    } else {
      await axios.post('/api/unidades', form.value)
    }
    fetchUnidades()
    showForm.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error guardando unidad'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally { saving.value = false }
}

const remove = async item => { deletingItem.value = item; confirmDelete.value = true }

const confirmDeleteAction = async () => {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    await axios.delete(`/api/unidades/${deletingItem.value.id_unidad}`)
    snackbarMessage.value = 'Unidad eliminada'
    snackbarColor.value = 'success'
    snackbar.value = true
    fetchUnidades()
    confirmDelete.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error eliminando unidad'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally { deleting.value = false; deletingItem.value = null }
}

const toggleActive = async item => {
  const original = item.activo
  const newVal = !original
  try { item.activo = newVal; await axios.put(`/api/unidades/${item.id_unidad}`, { activo: newVal ? 1 : 0 }) }
  catch (err) { console.error(err); item.activo = original; snackbarMessage.value = 'Error al cambiar estado'; snackbarColor.value = 'error'; snackbar.value = true }
}

onMounted(() => { fetchUnidades() })
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex justify-end mb-3">
        <VBtn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreate"
        >
          Nuevo Unidad
        </VBtn>
      </div>

      <AppCard title="Unidades">
        <template #default>
          <div v-if="loading">
            Cargando...
          </div>
          <div v-else>
            <VRow class="mb-4">
              <VCol
                cols="12"
                md="8"
              >
                <AppSearch
                  v-model:query="searchQuery"
                  :items="items"
                  keys="['codigo', 'descripcion']"
                  @select="openEdit"
                />
              </VCol>
              <VCol
                cols="12"
                md="4"
              >
                <VSelect
                  v-model="filterEstado"
                  :items="[
                    { title: 'Todos', value: null },
                    { title: 'Activos', value: true },
                    { title: 'Inactivos', value: false }
                  ]"
                  label="Estado"
                  density="comfortable"
                  clearable
                />
              </VCol>
            </VRow>
            <VDataTable
              :headers="headers"
              :items="filteredItems"
              :items-per-page="10"
            >
              <template #item.activo="{ item }">
                <VChip :color="item.activo ? 'success' : 'error'">
                  {{ item.activo ? 'Activo' : 'Inactivo' }}
                </VChip>
              </template>
              <template #item.actions="{ item }">
                <div class="d-flex gap-2">
                  <VBtn
                    :icon="item.activo ? 'tabler-lock-open' : 'tabler-lock'"
                    size="small"
                    variant="text"
                    color="secondary"
                    @click="toggleActive(item)"
                    :title="item.activo ? 'Desactivar' : 'Activar'"
                  />
                  <VBtn
                    icon="tabler-pencil"
                    size="small"
                    color="primary"
                    variant="text"
                    @click="openEdit(item)"
                  />
                  <VBtn
                    icon="tabler-trash"
                    size="small"
                    color="error"
                    variant="text"
                    @click="remove(item)"
                  />
                </div>
              </template>
            </VDataTable>
          </div>
        </template>
      </AppCard>

      <VDialog
        v-model="showForm"
        persistent
        max-width="600px"
      >
        <VCard class="elevation-12">
          <VCardTitle class="text-h5 bg-primary text-white pa-4">
            <VIcon
              icon="mdi-scale-balance"
              class="me-2"
            />
            {{ editItem ? 'Editar Unidad' : 'Nueva Unidad' }}
          </VCardTitle>
          
          <VDivider />
          
          <VForm @submit.prevent="save">
            <VCardText class="pa-6">
              <VTextField
                v-model="form.codigo"
                label="Código"
                variant="outlined"
                prepend-inner-icon="mdi-barcode"
                class="mb-4"
              />
              <VTextField
                v-model="form.descripcion"
                label="Descripción"
                required
                variant="outlined"
                prepend-inner-icon="mdi-text"
                class="mb-4"
              />
              <VSwitch
                v-model="form.activo"
                label="Estado"
                color="primary"
              />
              <VChip
                :color="form.activo ? 'success' : 'error'"
                size="small"
                class="mt-2"
              >
                {{ form.activo ? 'Activo' : 'Inactivo' }}
              </VChip>
            </VCardText>

            <VDivider />

            <VCardActions class="pa-4">
              <VSpacer />
              <VBtn
                variant="text"
                @click="showForm=false"
              >
                Cancelar
              </VBtn>
              <VBtn
                type="submit"
                color="primary"
                variant="elevated"
                :loading="saving"
              >
                Guardar
              </VBtn>
            </VCardActions>
          </VForm>
        </VCard>
      </VDialog>

      <VDialog
        v-model="confirmDelete"
        persistent
        max-width="400px"
      >
        <AppCard
          title="Confirmar eliminación"
          no-padding
        >
          <VCardText>¿Eliminar unidad: <strong>{{ deletingItem?.descripcion }}</strong>?</VCardText>
          <VCardActions>
            <VBtn
              color="error"
              :loading="deleting"
              @click="confirmDeleteAction"
            >
              Eliminar
            </VBtn>
            <VBtn
              text
              @click="confirmDelete=false"
            >
              Cancelar
            </VBtn>
          </VCardActions>
        </AppCard>
      </VDialog>

      <VSnackbar
        v-model="snackbar"
        :color="snackbarColor"
        timeout="4000"
      >
        {{ snackbarMessage }}
      </VSnackbar>
    </VCol>
  </VRow>
</template>
