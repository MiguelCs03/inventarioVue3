<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import AppSearch from '@/components/AppSearch.vue'

const subgrupos = ref([])
const loading = ref(false)
const showForm = ref(false)
const editItem = ref(null)

// form state
const form = ref({ descripcion: '', id_grupo: null, activo: true })
const saving = ref(false)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const confirmDelete = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

const grupos = ref([])

const populateForm = v => {
  if (v) form.value = { descripcion: v.descripcion ?? '', activo: !!v.activo, id_grupo: v.id_grupo ?? null }
  else form.value = { descripcion: '', activo: true, id_grupo: null }
}

const items = computed(() => {
  const val = subgrupos.value
  if (Array.isArray(val)) return val
  if (val && Array.isArray(val.data)) return val.data
  
  return []
})

// Search
const searchQuery = ref('')
const filterEstado = ref(null)
const filterGrupo = ref(null)

const filteredItems = computed(() => {
  let result = items.value
  
  // Filtro por estado
  if (filterEstado.value !== null) {
    result = result.filter(i => !!i.activo === filterEstado.value)
  }
  
  // Filtro por grupo
  if (filterGrupo.value) {
    result = result.filter(i => i.id_grupo === filterGrupo.value)
  }
  
  // Filtro por búsqueda
  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (q) {
    result = result.filter(i => (i.descripcion || '').toString().toLowerCase().includes(q) || (i.grupo?.descripcion || '').toString().toLowerCase().includes(q))
  }
  
  return result
})

const headers = [
  { title: 'ID', key: 'id_subgrupo' },
  { title: 'Descripción', key: 'descripcion' },
  { title: 'Grupo', key: 'grupo' },
  { title: 'Estado', key: 'activo' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const fetchGrupos = async () => {
  try {
    const res = await axios.get('/api/grupos')

    grupos.value = Array.isArray(res.data) ? res.data : res.data.data ?? []
  } catch (err) {
    console.error(err)
  }
}

const fetchSubGrupos = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/subgrupos')

    subgrupos.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editItem.value = null
  populateForm(null)
  showForm.value = true
}

const openEdit = item => {
  editItem.value = { ...item }
  populateForm(item)
  showForm.value = true
}

const save = async () => {
  if (!form.value.descripcion || String(form.value.descripcion).trim() === '') {
    snackbarMessage.value = 'La descripción es requerida'
    snackbarColor.value = 'error'
    snackbar.value = true
    
    return
  }
  if (!form.value.id_grupo) {
    snackbarMessage.value = 'Seleccione un Grupo'
    snackbarColor.value = 'error'
    snackbar.value = true
    
    return
  }

  saving.value = true
  try {
    if (editItem.value && editItem.value.id_subgrupo) {
      await axios.put(`/api/subgrupos/${editItem.value.id_subgrupo}`, form.value)
    } else {
      await axios.post('/api/subgrupos', form.value)
    }
    fetchSubGrupos()
    showForm.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error guardando subgrupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally {
    saving.value = false
  }
}

const remove = async item => {
  deletingItem.value = item
  confirmDelete.value = true
}

const confirmDeleteAction = async () => {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    await axios.delete(`/api/subgrupos/${deletingItem.value.id_subgrupo}`)
    snackbarMessage.value = 'SubGrupo eliminado'
    snackbarColor.value = 'success'
    snackbar.value = true
    fetchSubGrupos()
    confirmDelete.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error eliminando subgrupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally {
    deleting.value = false
    deletingItem.value = null
  }
}

const toggleActive = async item => {
  const original = item.activo
  const newVal = !original
  try {
    item.activo = newVal
    await axios.put(`/api/subgrupos/${item.id_subgrupo}`, { activo: newVal ? 1 : 0 })
  } catch (err) {
    console.error(err)
    item.activo = original
    snackbarMessage.value = 'Error al cambiar el estado del subgrupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  }
}

onMounted(() => {
  fetchGrupos()
  fetchSubGrupos()
})
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
          Nuevo SubGrupo
        </VBtn>
      </div>

      <AppCard title="SubGrupos">
        <template #default>
          <div v-if="loading">
            Cargando...
          </div>
          <div v-else>
            <VRow class="mb-4">
              <VCol
                cols="12"
                md="6"
              >
                <AppSearch
                  v-model:query="searchQuery"
                  :items="items"
                  keys="['descripcion']"
                  @select="openEdit"
                />
              </VCol>
              <VCol
                cols="12"
                md="3"
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
              <VCol
                cols="12"
                md="3"
              >
                <VSelect
                  v-model="filterGrupo"
                  :items="grupos"
                  item-title="descripcion"
                  item-value="id_grupo"
                  label="Grupo"
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
              <template #item.grupo="{ item }">
                <span>{{ item.grupo ? item.grupo.descripcion : '-' }}</span>
              </template>

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
              icon="mdi-folder-multiple"
              class="me-2"
            />
            {{ editItem ? 'Editar SubGrupo' : 'Nuevo SubGrupo' }}
          </VCardTitle>
          
          <VDivider />
          
          <VForm @submit.prevent="save">
            <VCardText class="pa-6">
              <VTextField
                v-model="form.descripcion"
                label="Descripción"
                required
                variant="outlined"
                prepend-inner-icon="mdi-text"
                class="mb-4"
              />
              <VSelect
                v-model="form.id_grupo"
                :items="grupos"
                item-title="descripcion"
                item-value="id_grupo"
                label="Grupo"
                required
                variant="outlined"
                prepend-inner-icon="mdi-folder"
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
          <VCardText>¿Eliminar subgrupo: <strong>{{ deletingItem?.descripcion }}</strong>?</VCardText>
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
