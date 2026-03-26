<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">Modèles de clauses administratives réutilisables pour les documents CPS et RC</p>
      <UiButton size="sm" @click="openCreateArticleModal">
        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Créer un nouvel article
      </UiButton>
    </div>


    <UiCard class="p-4">
      <div class="flex gap-3">
        <UiInput v-model="search" placeholder="Rechercher par titre ou code..." class="max-w-xs" />
        <div class="flex gap-2">
          <button
            v-for="type in articleTypes"
            :key="type.value"
            :class="[
              'rounded-full px-3 py-1.5 text-xs font-medium transition-colors border',
              typeFilter === type.value
                ? 'bg-primary text-primary-foreground border-primary'
                : 'bg-background text-muted-foreground border-border hover:border-primary hover:text-primary'
            ]"
            @click="typeFilter = typeFilter === type.value ? '' : type.value"
          >
            {{ type.label }}
          </button>
        </div>
      </div>
    </UiCard>


    <div v-if="loading" class="grid grid-cols-1 gap-3">
      <div v-for="i in 5" :key="i" class="h-24 animate-pulse rounded-lg bg-gray-100" />
    </div>

    <div v-else-if="filteredArticles.length === 0" class="py-16 text-center text-muted-foreground">
      <p class="font-medium">Aucun article trouvé</p>
      <p class="text-sm mt-1">Essayez d'ajuster vos filtres</p>
    </div>

    <div v-else class="space-y-3">
      <UiCard
        v-for="article in filteredArticles"
        :key="article.id"
        class="p-4 cursor-pointer hover:border-primary/50 transition-colors"
        @click="toggleArticle(article.id)"
      >
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="font-mono text-xs font-bold text-primary">{{ article.code }}</span>
              <UiBadge :variant="typeVariant(article.type)" class="text-xs">{{ article.type }}</UiBadge>
              <span class="text-xs text-muted-foreground">{{ article.variants?.length || 0 }} variant{{ article.variants?.length !== 1 ? 's' : '' }}</span>
            </div>
            <p class="font-semibold text-gray-900">{{ article.titre }}</p>
            <p v-if="article.default_variant?.contenu" class="mt-1 text-sm text-muted-foreground line-clamp-2">
              {{ article.default_variant.contenu }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <button 
              type="button" 
              class="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-md transition-colors"
              title="Supprimer l'article"
              @click.stop="deleteArticle(article)"
            >
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
            <svg
              class="h-4 w-4 shrink-0 text-muted-foreground transition-transform"
              :class="expandedIds.includes(article.id) ? 'rotate-180' : ''"
              fill="none" viewBox="0 0 24 24" stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </div>


        <div v-if="expandedIds.includes(article.id)" class="mt-4 border-t pt-4">
          <p class="text-xs font-semibold text-muted-foreground mb-3">VARIANTES</p>
          <div v-if="!article.variants || article.variants.length === 0" class="text-sm text-muted-foreground">Aucune variante disponible.</div>
          <div v-else class="space-y-2">
            <div
              v-for="variant in article.variants"
              :key="variant.id"
              class="rounded-lg border p-3"
              :class="variant.is_default ? 'border-primary/30 bg-primary/5' : ''"
            >
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium flex items-center gap-2">
                  {{ variant.label || 'Par défaut' }}
                  <UiBadge v-if="variant.is_default" variant="default" class="text-xs">par défaut</UiBadge>
                </p>
                <button 
                  v-if="!variant.is_default" 
                  type="button" 
                  class="p-1 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-md transition-colors"
                  @click.stop="deleteVariant(article, variant)"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <p class="text-xs text-muted-foreground font-mono leading-relaxed whitespace-pre-wrap break-words" style="overflow-wrap: anywhere;">{{ variant.contenu?.substring(0, 300) }}{{ variant.contenu?.length > 300 ? '...' : '' }}</p>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <UiButton variant="outline" size="sm" @click.stop="openVariantModal(article)">
              <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter une variante
            </UiButton>
          </div>
        </div>
      </UiCard>
    </div>


    <UiDialog :open="showVariantModal" @update:open="showVariantModal = $event">
      <div class="p-6">
        <UiDialogHeader>
          <template #title>Ajouter une variante d'article</template>
          <template #description>Créer une nouvelle version de "{{ selectedArticle?.titre }}"</template>
        </UiDialogHeader>
        <form @submit.prevent="saveVariant" class="mt-4 space-y-4">
          <div class="space-y-2">
            <UiLabel>Libellé / Titre *</UiLabel>
            <UiInput v-model="variantForm.label" required placeholder="ex. Forfaitaire Alternative..." />
          </div>
          <div class="space-y-2">
            <UiLabel>Contenu *</UiLabel>
            <UiTextarea v-model="variantForm.contenu" required rows="6" placeholder="Le texte complet de cette variante..." />
          </div>
          <div v-if="variantSuccess" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Variante créée avec succès !</div>
          <div v-if="variantError" class="rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ variantError }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <UiButton type="button" variant="outline" @click="showVariantModal = false">Annuler</UiButton>
            <UiButton type="submit" :disabled="savingVariant">
              {{ savingVariant ? 'Enregistrement...' : 'Créer la variante' }}
            </UiButton>
          </div>
        </form>
      </div>
    </UiDialog>


    <UiDialog :open="showCreateArticleModal" @update:open="showCreateArticleModal = $event">
      <div class="p-6">
        <UiDialogHeader>
          <template #title>Créer un nouvel article</template>
          <template #description>Ajouter un nouvel article au catalogue</template>
        </UiDialogHeader>
        <form @submit.prevent="saveArticle" class="mt-4 space-y-4 max-h-[70vh] overflow-y-auto pr-2">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <UiLabel>Code (ex. Article 1) *</UiLabel>
              <UiInput v-model="articleForm.code" required placeholder="ex. Article 1" />
            </div>
            <div class="space-y-2">
              <UiLabel>Type *</UiLabel>
              <select v-model="articleForm.type" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                <option value="" disabled>Sélectionnez un type...</option>
                <option v-for="type in articleTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
              </select>
            </div>
          </div>
          <div class="space-y-2">
            <UiLabel>Titre *</UiLabel>
            <UiInput v-model="articleForm.titre" required placeholder="ex. Objet du marché" />
          </div>
          <div class="space-y-2">
            <UiLabel>Contenu par défaut *</UiLabel>
            <UiTextarea v-model="articleForm.contenu" required rows="6" placeholder="Le contenu textuel par défaut..." class="font-mono text-sm" />
          </div>
          <div v-if="articleSuccess" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Article créé avec succès !</div>
          <div v-if="articleError" class="rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ articleError }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <UiButton type="button" variant="outline" @click="showCreateArticleModal = false">Annuler</UiButton>
            <UiButton type="submit" :disabled="savingArticle">
              {{ savingArticle ? 'Création...' : 'Créer l\'article' }}
            </UiButton>
          </div>
        </form>
      </div>
    </UiDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const api = useApi()
const loading = ref(true)
const articles = ref<any[]>([])
const search = ref('')
const typeFilter = ref('')
const expandedIds = ref<string[]>([])

const showVariantModal = ref(false)
const selectedArticle = ref<any>(null)
const savingVariant = ref(false)
const variantForm = ref({ label: '', contenu: '' })

const articleTypes = [
  { value: 'CPS_ADMIN', label: 'CPS Admin' },
  { value: 'CPS_FIN', label: 'CPS Financier' },
  { value: 'CPS_TECH_COMMUNE', label: 'CPS Tech' },
  { value: 'RC', label: 'RC' },
]

const typeVariant = (type: string): any => {
  const map: Record<string, string> = {
    CPS_ADMIN: 'default', CPS_FIN: 'warning', CPS_TECH_COMMUNE: 'info', RC: 'secondary'
  }
  return map[type] || 'outline'
}

const filteredArticles = computed(() =>
  articles.value.filter(a =>
    (!typeFilter.value || a.type === typeFilter.value) &&
    (!search.value ||
      a.titre?.toLowerCase().includes(search.value.toLowerCase()) ||
      a.code?.toLowerCase().includes(search.value.toLowerCase()))
  )
)

const toggleArticle = (id: string) => {
  if (expandedIds.value.includes(id)) {
    expandedIds.value = expandedIds.value.filter(x => x !== id)
  } else {
    expandedIds.value.push(id)
  }
}

const fetchArticles = async () => {
  loading.value = true
  try {
    const res = await api.get('/articles')
    articles.value = Array.isArray(res) ? res : (res as any)?.data ?? []
  } catch (e) { console.error(e) } finally { loading.value = false }
}

onMounted(fetchArticles)

const variantSuccess = ref(false)
const variantError = ref('')

const openVariantModal = (article: any) => {
  selectedArticle.value = article
  variantSuccess.value = false
  variantError.value = ''
  variantForm.value = { 
    label: '', 
    contenu: article.variants?.find((v:any) => v.is_default)?.contenu || '' 
  }
  showVariantModal.value = true
}

const saveVariant = async () => {
  if (!selectedArticle.value) return
  savingVariant.value = true
  variantSuccess.value = false
  variantError.value = ''
  try {
    await api.post(`/articles/${selectedArticle.value.id}/variants`, variantForm.value)
    variantSuccess.value = true
    setTimeout(() => {
      showVariantModal.value = false
      variantSuccess.value = false
    }, 1500)
    await fetchArticles()
  } catch (e: any) {
    console.error('Failed to save variant:', e)
    variantError.value = e?.data?.message || 'Échec de la création de la variante'
  } finally {
    savingVariant.value = false
  }
}

const { confirm } = useConfirm()
const deleteVariant = async (article: any, variant: any) => {
  if (variant.is_default) return
  const isConfirmed = await confirm({
    title: 'Supprimer la variante',
    description: `Êtes-vous sûr de vouloir supprimer "${variant.label}" ? Cette action est irréversible.`,
    confirmText: 'Supprimer la variante',
    cancelText: 'Annuler',
    variant: 'destructive',
  })
  if (!isConfirmed) return
  try {
    await api.delete(`/variants/${variant.id}`)
    article.variants = article.variants.filter((v: any) => v.id !== variant.id)
    addToast({ title: 'Variante supprimée', description: 'La variante a été supprimée.', variant: 'success' })
  } catch(e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la suppression de la variante.', variant: 'destructive' })
  }
}

const deleteArticle = async (article: any) => {
  const isConfirmed = await confirm({
    title: 'Supprimer l\'article',
    description: `Êtes-vous sûr de vouloir supprimer "${article.titre}" ? Cette action est irréversible.`,
    confirmText: 'Supprimer l\'article',
    cancelText: 'Annuler',
    variant: 'destructive',
  })
  if (!isConfirmed) return
  try {
    await api.delete(`/articles/${article.id}`)
    articles.value = articles.value.filter((a: any) => a.id !== article.id)
    addToast({ title: 'Article supprimé', description: 'L\'article a été supprimé.', variant: 'success' })
  } catch(e: any) {
    if (e.response?.status !== 403 && e.response?.status !== 401) {
      addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la suppression de l\'article.', variant: 'destructive' })
    }
  }
}


const { addToast } = useToast()
const showCreateArticleModal = ref(false)
const savingArticle = ref(false)
const articleSuccess = ref(false)
const articleError = ref('')
const articleForm = ref({
  code: '',
  titre: '',
  type: '',
  contenu: ''
})

const openCreateArticleModal = () => {
  articleSuccess.value = false
  articleError.value = ''
  articleForm.value = {
    code: '',
    titre: '',
    type: '',
    contenu: ''
  }
  showCreateArticleModal.value = true
}

const saveArticle = async () => {
  savingArticle.value = true
  articleSuccess.value = false
  articleError.value = ''
  try {
    await api.post('/articles', articleForm.value)
    articleSuccess.value = true
    addToast({ title: 'Article créé', description: `L'article "${articleForm.value.code}" a été créé.`, variant: 'success' })
    setTimeout(() => {
      showCreateArticleModal.value = false
      articleSuccess.value = false
    }, 1500)
    await fetchArticles()
  } catch (e: any) {
    console.error('Failed to create article:', e)
    articleError.value = e?.data?.message || 'Échec de la création de l\'article'
  } finally {
    savingArticle.value = false
  }
}
</script>
