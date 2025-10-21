<script setup>
import { ref, watch } from 'vue'

// lightweight debounce helper to avoid adding lodash-es as a dependency
function debounce(fn, wait = 250) {
  let t
  return (...args) => {
    clearTimeout(t)
    t = setTimeout(() => fn(...args), wait)
  }
}

const props = defineProps({
  items: { type: Array, default: () => [] },
  keys: { type: Array, default: () => ['descripcion'] },
  placeholder: { type: String, default: 'Buscar...' },
  minChars: { type: Number, default: 1 },
})

const emits = defineEmits(['update:query', 'select'])

const query = ref('')
const open = ref(false)
const matches = ref([])

const doSearch = debounce(() => {
  const q = String(query.value || '').trim().toLowerCase()
  if (q.length < props.minChars) {
    matches.value = []
    open.value = false
    emits('update:query', q)
    
    return
  }

  const results = []
  for (const item of props.items) {
    for (const k of props.keys) {
      const v = (item?.[k] ?? '') + ''
      if (v.toLowerCase().includes(q)) { results.push(item); break }
    }
  }
  matches.value = results.slice(0, 10)
  open.value = matches.value.length > 0
  emits('update:query', q)
}, 250)

watch(query, () => doSearch())

const select = item => {
  query.value = ''
  open.value = false
  matches.value = []
  emits('select', item)
}
</script>

<template>
  <div
    class="app-search"
    style="min-width:220px;"
  >
    <VTextField
      v-model="query"
      :placeholder="placeholder"
      density="compact"
      clearable
      hide-details
    />

    <VMenu
      v-model="open"
      close-on-content-click="false"
      activator="parent"
      offset-y
    >
      <VList>
        <VListItem
          v-for="(m, idx) in matches"
          :key="idx"
          @click="select(m)"
        >
          <VListItemTitle>{{ m.descripcion ?? m.codigo ?? m.nombre ?? '—' }}</VListItemTitle>
        </VListItem>
        <VListItem v-if="matches.length === 0">
          <VListItemTitle>No hay resultados</VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </div>
</template>
