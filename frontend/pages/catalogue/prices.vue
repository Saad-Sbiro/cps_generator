<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold tracking-tight">Catalogue de Prix</h2>
        <p class="text-sm text-muted-foreground">Gérer les prix unitaires globaux pour les estimations d'achats</p>
      </div>
      <UiButton>
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


    <UiCard>
      <UiTable>
        <UiTableHeader>
          <UiTableRow>
            <UiTableHead>Catégorie</UiTableHead>
            <UiTableHead>Désignation</UiTableHead>
            <UiTableHead>Unité</UiTableHead>
            <UiTableHead>Prix de base HT</UiTableHead>
            <UiTableHead class="text-right">Historique</UiTableHead>
          </UiTableRow>
        </UiTableHeader>
        <UiTableBody>
          <template v-if="loading">
            <UiTableRow v-for="i in 5" :key="i">
              <UiTableCell v-for="j in 5" :key="j"><div class="h-4 animate-pulse rounded bg-gray-100" /></UiTableCell>
            </UiTableRow>
          </template>
          <template v-else-if="filteredItems.length === 0">
            <UiTableRow>
              <UiTableCell colspan="5" class="py-12 text-center text-muted-foreground">
                Aucun prix disponible ou correspondant à votre recherche.
              </UiTableCell>
            </UiTableRow>
          </template>
          <template v-else>
            <UiTableRow v-for="item in filteredItems" :key="item.id">
              <UiTableCell>
                <UiBadge variant="outline">{{ item.categorie || 'Général' }}</UiBadge>
              </UiTableCell>
              <UiTableCell class="font-medium">
                {{ item.designation }}
                <div v-if="item.description_technique" class="text-xs text-muted-foreground font-normal min-w-48 truncate max-w-sm">
                  {{ item.description_technique }}
                </div>
              </UiTableCell>
              <UiTableCell>{{ item.unite }}</UiTableCell>
              <UiTableCell class="font-bold">
                {{ formatCurrency(item.prix_unitaire_ht_defaut) }}
              </UiTableCell>
              <UiTableCell class="text-right">
                <UiButton variant="ghost" size="sm" class="text-xs">
                  Voir les tendances
                </UiButton>
              </UiTableCell>
            </UiTableRow>
          </template>
        </UiTableBody>
      </UiTable>
    </UiCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const api = useApi()
const loading = ref(true)
const items = ref<any[]>([])
const search = ref('')
const categoryFilter = ref('')

const categories = computed(() => [...new Set(items.value.map(i => i.categorie).filter(Boolean))].sort())

const filteredItems = computed(() =>
  items.value.filter(i =>
    (!search.value || i.designation?.toLowerCase().includes(search.value.toLowerCase())) &&
    (!categoryFilter.value || i.categorie === categoryFilter.value)
  )
)

const formatCurrency = (val: any) =>
  val ? `${parseFloat(val).toLocaleString('fr-MA', { minimumFractionDigits: 2 })} MAD` : '—'

const fetchPrices = async () => {
  loading.value = true
  try {
    const res = await api.get('/catalogue-articles')
    items.value = Array.isArray(res) ? res : (res as any)?.data ?? []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPrices)
</script>
