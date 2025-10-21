<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import AppSearch from '@/components/AppSearch.vue'

const grupos = ref([])
const loading = ref(false)
const showForm = ref(false)
const editItem = ref(null)

// form state (moved from FormGrupo.vue to keep single page)
const form = ref({ descripcion: '', activo: true })
const saving = ref(false)
const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const confirmDelete = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

// when editItem changes populate form
const populateForm = v => {
  if (v) form.value = { descripcion: v.descripcion ?? '', activo: !!v.activo }
  else form.value = { descripcion: '', activo: true }
}


// computed safe items for the table: always return an array
const items = computed(() => {
  const val = grupos.value
  if (Array.isArray(val)) return val

  // Laravel paginated responses often have `data` field
  if (val && Array.isArray(val.data)) return val.data

  // fallback: empty array
  return []
})

// Search
const searchQuery = ref('')
const filterEstado = ref(null) // null = todos, true = activos, false = inactivos

const filteredItems = computed(() => {
  let result = items.value
  
  // Filtro por estado
  if (filterEstado.value !== null) {
    result = result.filter(i => !!i.activo === filterEstado.value)
  }
  
  // Filtro por búsqueda
  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (q) {
    result = result.filter(i => (i.descripcion || '').toString().toLowerCase().includes(q) || String(i.id_grupo).includes(q))
  }
  
  return result
})

const headers = [
  { title: 'ID', key: 'id_grupo' },
  { title: 'Descripción', key: 'descripcion' },
  { title: 'Estado', key: 'activo' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

const fetchGrupos = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/grupos')


    // Normalize: backend may return an array or an object (paginated)
    console.debug('fetchGrupos response:', res.data)
    grupos.value = res.data
  } catch (err) {
    console.error(err)

    // manejar error (toast)
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  console.debug('openCreate called - before', { showForm: showForm.value })
  editItem.value = null
  populateForm(null)
  showForm.value = true
  console.debug('openCreate finished - after', { showForm: showForm.value })
}

const openEdit = item => {
  console.debug('openEdit called', item)
  editItem.value = { ...item }
  populateForm(item)
  showForm.value = true
  console.debug('openEdit finished - showForm', { showForm: showForm.value })
}

const onSaved = () => {
  showForm.value = false
  fetchGrupos()
}

const save = async () => {
  // basic validation
  if (!form.value.descripcion || String(form.value.descripcion).trim() === '') {
    snackbarMessage.value = 'La descripción es requerida'
    snackbarColor.value = 'error'
    snackbar.value = true
    
    return
  }

  saving.value = true
  try {
    if (editItem.value && editItem.value.id_grupo) {
      await axios.put(`/api/grupos/${editItem.value.id_grupo}`, form.value)
    } else {
      await axios.post('/api/grupos', form.value)
    }

    // refresh
    fetchGrupos()
    showForm.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error guardando grupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally {
    saving.value = false
  }
}

const remove = async item => {
  // open confirm dialog
  deletingItem.value = item
  confirmDelete.value = true
}

const confirmDeleteAction = async () => {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    await axios.delete(`/api/grupos/${deletingItem.value.id_grupo}`)
    snackbarMessage.value = 'Grupo eliminado'
    snackbarColor.value = 'success'
    snackbar.value = true
    fetchGrupos()
    confirmDelete.value = false
  } catch (err) {
    console.error(err)
    snackbarMessage.value = 'Error eliminando grupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  } finally {
    deleting.value = false
    deletingItem.value = null
  }
}

const toggleActive = async item => {
  // optimistic UI update
  const original = item.activo
  const newVal = !original
  try {
    item.activo = newVal
    await axios.put(`/api/grupos/${item.id_grupo}`, { activo: newVal ? 1 : 0 })
  } catch (err) {
    console.error(err)

    // revert on error
    item.activo = original
    snackbarMessage.value = 'Error al cambiar el estado del grupo'
    snackbarColor.value = 'error'
    snackbar.value = true
  }
}

onMounted(fetchGrupos)
</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- Visible toolbar with create button (not inside card header) -->
      <div class="d-flex justify-end mb-3">
        <VBtn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openCreate"
        >
          Nuevo Grupo
        </VBtn>
      </div>

      <AppCard title="Grupos">
        <template #default>
          <div v-if="loading">
            Cargando...
          </div>

          <div>
            <div v-if="!loading">
              <VRow class="mb-4">
                <VCol
                  cols="12"
                  md="8"
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
                  md="4"
                >
                  <VSelect
                    v-model="filterEstado"
                    :items="[
                      { title: 'Todos', value: null },
                      { title: 'Activos', value: true },
                      { title: 'Inactivos', value: false }
                    ]"
                    label="Filtrar por estado"
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
                    <!-- Habilitar/Deshabilitar (icon-only) -->
                    <VBtn
                      :icon="item.activo ? 'tabler-lock-open' : 'tabler-lock'"
                      size="small"
                      variant="text"
                      color="secondary"
                      @click="toggleActive(item)"
                      :title="item.activo ? 'Desactivar' : 'Activar'"
                    />
                    <!-- Editar (icon-only) -->
                    <VBtn
                      icon="tabler-pencil"
                      size="small"
                      color="primary"
                      variant="text"
                      @click="openEdit(item)"
                    />

                    <!-- Eliminar (icon-only) -->
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
              icon="mdi-folder"
              class="me-2"
            />
            {{ editItem ? 'Editar Grupo' : 'Nuevo Grupo' }}
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
              />
              <VSwitch
                v-model="form.activo"
                label="Estado"
                color="primary"
                class="mt-4"
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

      <!-- Confirm delete dialog -->
      <VDialog
        v-model="confirmDelete"
        persistent
        max-width="400px"
      >
        <AppCard
          title="Confirmar eliminación"
          no-padding
        >
          <VCardText>¿Eliminar grupo: <strong>{{ deletingItem?.descripcion }}</strong>?</VCardText>
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

      <!-- Snackbar -->
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
