<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">{{ filteredItems.length }} article{{ filteredItems.length !== 1 ? 's' : '' }}</p>
      <UiButton @click="openCreateModal">
        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Ajouter un prix
      </UiButton>
    </div>


    <UiCard class="p-4">
      <div class="flex gap-3">
        <UiInput v-model="search" placeholder="Rechercher par désignation..." class="max-w-xs" />
        <select v-model="categoryFilter" class="rounded-md border border-input bg-background px-3 py-2 text-sm">
          <option value="">Toutes les catégories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>
    </UiCard>


    <UiCard class="overflow-x-auto">
      <div class="min-w-[800px] p-1">
        <UiTable>
          <UiTableHeader>
          <UiTableRow class="hover:bg-transparent">
            <UiTableHead>Désignation</UiTableHead>
            <UiTableHead>Catégorie</UiTableHead>
            <UiTableHead>Unité</UiTableHead>
            <UiTableHead>Prix par défaut HT</UiTableHead>
            <UiTableHead>Type</UiTableHead>
            <UiTableHead class="text-right">Actions</UiTableHead>
          </UiTableRow>
        </UiTableHeader>
        <UiTableBody>
          <template v-if="loading">
            <UiTableRow v-for="i in 5" :key="i">
              <UiTableCell v-for="j in 6" :key="j"><div class="h-4 animate-pulse rounded bg-gray-100" /></UiTableCell>
            </UiTableRow>
          </template>
          <template v-else-if="filteredItems.length === 0">
            <UiTableRow>
              <UiTableCell colspan="6" class="py-12 text-center text-muted-foreground">
                {{ search || categoryFilter ? 'Aucun article ne correspond à vos filtres' : 'Aucun prix pour le moment' }}
              </UiTableCell>
            </UiTableRow>
          </template>
          <template v-else>
            <UiTableRow v-for="item in filteredItems" :key="item.id">
              <UiTableCell>
                <p class="font-medium">{{ item.designation }}</p>
                <p v-if="item.description_technique" class="mt-0.5 text-xs text-muted-foreground truncate max-w-xs">{{ item.description_technique }}</p>
              </UiTableCell>
              <UiTableCell>
                <UiBadge variant="outline">{{ item.categorie || 'Général' }}</UiBadge>
              </UiTableCell>
              <UiTableCell class="text-sm">{{ item.unite }}</UiTableCell>
              <UiTableCell class="font-medium">{{ formatCurrency(item.prix_unitaire_ht_defaut) }}</UiTableCell>
              <UiTableCell>
                <UiBadge :variant="item.type_poste === 'forfait' ? 'secondary' : 'info'">{{ item.type_poste || 'quantitatif' }}</UiBadge>
              </UiTableCell>
              <UiTableCell class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <UiButton variant="outline" size="sm" @click="openEditModal(item)">Modifier</UiButton>
                  <UiButton variant="ghost" size="sm" class="text-destructive hover:text-destructive" @click="deleteItem(item.id)">Supprimer</UiButton>
                </div>
              </UiTableCell>
            </UiTableRow>
          </template>
        </UiTableBody>
      </UiTable>
      </div>
    </UiCard>


    <UiDialog :open="showModal" @update:open="showModal = $event">
      <div class="p-6">
        <UiDialogHeader>
          <template #title>{{ editMode ? 'Modifier l\'article de prix' : 'Ajouter un article de prix' }}</template>
          <template #description>{{ editMode ? 'Mettez à jour les détails ci-dessous.' : 'Créer un nouvel article de prix réutilisable.' }}</template>
        </UiDialogHeader>
        <form @submit.prevent="saveItem" class="mt-4 space-y-4">
          <div class="space-y-2">
            <UiLabel>Désignation *</UiLabel>
            <UiInput v-model="form.designation" required placeholder="Fouille en masse" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <UiLabel>Catégorie</UiLabel>
              <UiInput v-model="form.categorie" placeholder="Terrassements" />
            </div>
            <div class="space-y-2">
              <UiLabel>Unité *</UiLabel>
              <UiInput v-model="form.unite" required placeholder="m³" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <UiLabel>Prix unitaire par défaut HT</UiLabel>
              <UiInput v-model="form.prix_unitaire_ht_defaut" type="number" step="0.01" min="0" placeholder="0.00" />
            </div>
            <div class="space-y-2">
              <UiLabel>Type</UiLabel>
              <select v-model="form.type_poste" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option value="quantitatif">Quantitatif</option>
                <option value="forfait">Forfait</option>
              </select>
            </div>
          </div>
          <div class="space-y-2">
            <UiLabel>Description technique</UiLabel>
            <UiTextarea v-model="form.description_technique" rows="3" placeholder="Spécifications techniques pour le document CPS..." />
          </div>
          <div v-if="formError" class="rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ formError }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <UiButton type="button" variant="outline" @click="showModal = false">Annuler</UiButton>
            <UiButton type="submit" :disabled="saving">{{ saving ? 'Enregistrement...' : editMode ? 'Enregistrer les modifications' : 'Créer le prix' }}</UiButton>
          </div>
        </form>
      </div>
    </UiDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useConfirm } from '~/composables/useConfirm'

const api = useApi()
const { addToast } = useToast()
const { confirm } = useConfirm()
const loading = ref(true)
const items = ref<any[]>([])
const search = ref('')
const categoryFilter = ref('')
const showModal = ref(false)
const editMode = ref(false)
const saving = ref(false)
const formError = ref('')

const defaultForm = () => ({
  designation: '', categorie: '', unite: '', prix_unitaire_ht_defaut: 0,
  type_poste: 'quantitatif', description_technique: '',
})
const form = ref(defaultForm())
const editId = ref<string | null>(null)

const categories = computed(() => [...new Set(items.value.map(i => i.categorie).filter(Boolean))].sort())
const filteredItems = computed(() =>
  items.value.filter(i =>
    (!search.value || i.designation?.toLowerCase().includes(search.value.toLowerCase())) &&
    (!categoryFilter.value || i.categorie === categoryFilter.value)
  )
)

const formatCurrency = (val: any) =>
  val ? `${parseFloat(val).toLocaleString('fr-MA', { minimumFractionDigits: 2 })} MAD` : '—'

const fetchItems = async () => {
  loading.value = true
  try {
    const res = await api.get('/catalogue-articles')
    items.value = Array.isArray(res) ? res : (res as any)?.data ?? []
  } catch (e) { console.error(e) } finally { loading.value = false }
}

const openCreateModal = () => {
  editMode.value = false
  editId.value = null
  form.value = defaultForm()
  formError.value = ''
  showModal.value = true
}

const openEditModal = (item: any) => {
  editMode.value = true
  editId.value = item.id
  form.value = { ...item }
  formError.value = ''
  showModal.value = true
}

const saveItem = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (editMode.value && editId.value) {
      await api.post(`/catalogue-articles/${editId.value}`, { ...form.value, _method: 'PUT' })
    } else {
      await api.post('/catalogue-articles', form.value)
    }
    showModal.value = false
    await fetchItems()
  } catch (err: any) {
    formError.value = err?.data?.message || 'Échec de l\'enregistrement de l\'article'
  } finally { saving.value = false }
}

const deleteItem = async (id: string) => {
  const confirmed = await confirm({
    title: 'Supprimer l\'article de prix',
    description: 'Êtes-vous sûr de vouloir supprimer cet article du catalogue ? Il sera retiré de tous les futurs projets.',
    confirmText: 'Supprimer',
    variant: 'destructive',
  })
  if (!confirmed) return
  try {
    await api.delete(`/catalogue-articles/${id}`)
    await fetchItems()
    addToast({ title: 'Article supprimé', description: 'L\'article du catalogue a été supprimé.', variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la suppression de l\'article.', variant: 'destructive' })
  }
}

onMounted(fetchItems)
</script>
