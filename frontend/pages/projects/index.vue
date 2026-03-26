<template>
  <div class="space-y-4">

    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-muted-foreground">{{ projects.length }} projet{{ projects.length !== 1 ? 's' : '' }}</p>
      </div>
      <UiButton @click="showCreateModal = true">
        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nouveau Projet
      </UiButton>
    </div>


    <UiCard class="p-4">
      <UiInput v-model="search" placeholder="Rechercher par nom ou référence..." class="max-w-sm" />
    </UiCard>


    <UiCard class="overflow-x-auto">
      <div class="min-w-[800px] p-1">
        <UiTable>
          <UiTableHeader>
          <UiTableRow class="hover:bg-transparent">
            <UiTableHead>Référence</UiTableHead>
            <UiTableHead>Nom du Projet</UiTableHead>
            <UiTableHead>Maître d'ouvrage</UiTableHead>
            <UiTableHead>TVA</UiTableHead>
            <UiTableHead>Rôle</UiTableHead>
            <UiTableHead>Total HT</UiTableHead>
            <UiTableHead class="text-right w-12"></UiTableHead>
          </UiTableRow>
        </UiTableHeader>
        <UiTableBody>
          <template v-if="loading">
            <UiTableRow v-for="i in 4" :key="i">
              <UiTableCell v-for="j in 7" :key="j">
                <div class="h-4 animate-pulse rounded bg-gray-100" />
              </UiTableCell>
            </UiTableRow>
          </template>
          <template v-else-if="filteredProjects.length === 0">
            <UiTableRow>
              <UiTableCell colspan="7" class="py-12 text-center text-muted-foreground">
                <div class="flex flex-col items-center gap-2">
                  <svg class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h4.586a1 1 0 01.707.293L12 7h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                  </svg>
                  <p>{{ search ? 'Aucun projet ne correspond à votre recherche' : 'Aucun projet pour le moment' }}</p>
                  <UiButton v-if="!search" variant="outline" size="sm" @click="showCreateModal = true">Créer votre premier projet</UiButton>
                </div>
              </UiTableCell>
            </UiTableRow>
          </template>
          <template v-else>
            <UiTableRow v-for="project in filteredProjects" :key="project.id">
              <UiTableCell class="font-mono text-xs text-muted-foreground">{{ project.reference }}</UiTableCell>
              <UiTableCell class="font-medium">
                <NuxtLink :to="`/projects/${project.id}`" class="hover:text-primary transition-colors">
                  {{ project.intitule }}
                </NuxtLink>
              </UiTableCell>
              <UiTableCell class="text-sm text-muted-foreground">{{ project.maitre_ouvrage || '—' }}</UiTableCell>
              <UiTableCell>
                <UiBadge variant="outline">{{ project.taux_tva }}%</UiBadge>
              </UiTableCell>
              <UiTableCell>
                <UiBadge :variant="project.current_user_role === 'owner' ? 'default' : 'secondary'">
                  {{ project.current_user_role === 'owner' ? 'propriétaire' : project.current_user_role }}
                </UiBadge>
              </UiTableCell>
              <UiTableCell class="font-medium">
                {{ formatCurrency(project.total_ht) }}
              </UiTableCell>
              <UiTableCell class="text-right">
                <div class="flex items-center justify-end gap-1">
                  <UiButton variant="outline" size="sm" @click="navigateTo(`/projects/${project.id}`)">
                    Ouvrir
                  </UiButton>
                  <UiDropdownMenu>
                    <UiDropdownMenuTrigger as-child>
                      <UiButton variant="ghost" size="icon" class="h-8 w-8 p-0">
                        <span class="sr-only">Ouvrir le menu</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </UiButton>
                    </UiDropdownMenuTrigger>
                    <UiDropdownMenuContent align="end">
                      <UiDropdownMenuItem @click="navigateTo(`/projects/${project.id}?tab=settings`)">
                        Renommer le projet
                      </UiDropdownMenuItem>
                      <UiDropdownMenuItem @click="navigateTo(`/projects/${project.id}?tab=collaborators`)">
                        Ajouter un collaborateur
                      </UiDropdownMenuItem>
                      <UiDropdownMenuSeparator />
                      <UiDropdownMenuItem class="text-destructive focus:text-destructive" @click="deleteProject(project.id)">
                        Supprimer le projet
                      </UiDropdownMenuItem>
                    </UiDropdownMenuContent>
                  </UiDropdownMenu>
                </div>
              </UiTableCell>
            </UiTableRow>
          </template>
        </UiTableBody>
        </UiTable>
      </div>
    </UiCard>


    <UiDialog :open="showCreateModal" @update:open="showCreateModal = $event">
      <div class="p-6">
        <UiDialogHeader>
          <template #title>Créer un nouveau projet</template>
          <template #description>Remplissez les détails du projet. Tous les champs obligatoires doivent être remplis.</template>
        </UiDialogHeader>
        <form @submit.prevent="createProject" class="mt-4 space-y-4 max-h-[70vh] overflow-y-auto pr-1">
          <div class="space-y-2">
            <UiLabel>Date *</UiLabel>
            <UiInput v-model="form.date_creation" type="date" required />
          </div>
          <div class="space-y-2">
            <UiLabel>Nom du projet (Intitulé) *</UiLabel>
            <UiInput v-model="form.intitule" placeholder="Construction d'un terrain de football" required />
          </div>
          <div class="space-y-2">
            <UiLabel>Maître d'ouvrage</UiLabel>
            <UiInput v-model="form.maitre_ouvrage" placeholder="Commune d'Agadir" />
          </div>
          <div class="space-y-2">
            <UiLabel>Lieu</UiLabel>
            <UiInput v-model="form.lieu" placeholder="Agadir, Souss-Massa" />
          </div>
          <div class="space-y-2">
            <UiLabel>Objet du marché</UiLabel>
            <UiTextarea v-model="form.objet_marche" placeholder="Description du marché..." rows="2" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <UiLabel>Taux de TVA (%) *</UiLabel>
              <UiInput v-model="form.taux_tva" type="number" step="0.01" min="0" max="100" required />
            </div>
            <div class="space-y-2">
              <UiLabel>Délai d'exécution</UiLabel>
              <UiInput v-model="form.delai_execution" placeholder="6 mois" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.inclure_brd_dans_cps" type="checkbox" id="brd_cps" class="h-4 w-4 rounded border-gray-300" />
            <UiLabel for="brd_cps">Inclure le BDP dans le document CPS</UiLabel>
          </div>
          <div v-if="createError" class="rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ createError }}</div>
          <div class="flex justify-end gap-3 pt-2">
            <UiButton type="button" variant="outline" @click="showCreateModal = false">Annuler</UiButton>
            <UiButton type="submit" :disabled="creating">{{ creating ? 'Création...' : 'Créer le projet' }}</UiButton>
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
const projects = ref<any[]>([])
const search = ref('')
const showCreateModal = ref(false)
const creating = ref(false)
const createError = ref('')

const today = new Date().toISOString().split('T')[0]
const form = ref({
  intitule: '',
  date_creation: today,
  taux_tva: 20,
  inclure_brd_dans_cps: false,
  maitre_ouvrage: '',
  objet_marche: '',
  lieu: '',
  delai_execution: '',
})

const filteredProjects = computed(() =>
  projects.value.filter(p =>
    !search.value ||
    p.intitule?.toLowerCase().includes(search.value.toLowerCase()) ||
    p.reference?.toLowerCase().includes(search.value.toLowerCase())
  )
)

const formatCurrency = (val: any) =>
  val ? `${parseFloat(val).toLocaleString('fr-MA', { minimumFractionDigits: 2 })} MAD` : '—'

const fetchProjects = async () => {
  loading.value = true
  try {
    const res = await api.get('/projets')
    projects.value = Array.isArray(res) ? res : (res as any)?.data ?? []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const createProject = async () => {
  creating.value = true
  createError.value = ''
  try {
    await api.post('/projets', form.value)
    showCreateModal.value = false
    Object.assign(form.value, { intitule: '', maitre_ouvrage: '', objet_marche: '', lieu: '', delai_execution: '' })
    await fetchProjects()
  } catch (err: any) {
    if (err?.data?.errors) {
      createError.value = Object.values(err.data.errors).flat()[0] as string
    } else {
      createError.value = err?.data?.message || 'Échec de la création du projet'
    }
  } finally {
    creating.value = false
  }
}

const deleteProject = async (id: string) => {
  const confirmed = await confirm({
    title: 'Supprimer le projet',
    description: 'Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible et toutes les données associées seront définitivement supprimées.',
    confirmText: 'Supprimer',
    variant: 'destructive',
  })
  if (!confirmed) return
  try {
    await api.delete(`/projets/${id}`)
    await fetchProjects()
    addToast({ title: 'Projet supprimé', description: 'Le projet a été définitivement supprimé.', variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la suppression du projet.', variant: 'destructive' })
  }
}

onMounted(fetchProjects)
</script>
