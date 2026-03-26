<template>
  <div class="space-y-6">

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <UiCard v-for="stat in stats" :key="stat.label" class="p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-muted-foreground">{{ stat.label }}</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">
              <span v-if="loading">—</span>
              <span v-else>{{ stat.value }}</span>
            </p>
          </div>
          <div :class="`flex h-12 w-12 items-center justify-center rounded-xl ${stat.bgColor}`">
            <span v-html="stat.icon" :class="`h-6 w-6 ${stat.iconColor} [&>svg]:h-full [&>svg]:w-full`"></span>
          </div>
        </div>
        <p class="mt-2 text-xs text-muted-foreground">{{ stat.sub }}</p>
      </UiCard>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

      <UiCard>
        <UiCardHeader>
          <div class="flex items-center justify-between">
            <UiCardTitle class="text-base">Projets Récents</UiCardTitle>
            <NuxtLink to="/projects" class="text-xs text-primary hover:underline">Voir tout →</NuxtLink>
          </div>
        </UiCardHeader>
        <UiCardContent class="pt-0">
          <div v-if="loading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="h-12 animate-pulse rounded-lg bg-gray-100" />
          </div>
          <div v-else-if="recentProjects.length === 0" class="py-8 text-center text-sm text-muted-foreground">
            Aucun projet pour le moment.
            <NuxtLink to="/projects" class="block mt-1 text-primary hover:underline">Créer votre premier projet →</NuxtLink>
          </div>
          <div v-else class="divide-y">
            <div v-for="project in recentProjects" :key="project.id" class="flex items-center justify-between py-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-gray-900">{{ project.intitule }}</p>
                <p class="text-xs text-muted-foreground">Ref: {{ project.reference }}</p>
              </div>
              <div class="ml-4 flex items-center gap-2">
                <UiBadge variant="outline" class="text-xs">{{ project.taux_tva }}% TVA</UiBadge>
                <NuxtLink :to="`/projects/${project.id}`">
                  <UiButton variant="ghost" size="sm" class="h-7 px-2 text-xs">Ouvrir</UiButton>
                </NuxtLink>
              </div>
            </div>
          </div>
        </UiCardContent>
      </UiCard>


      <UiCard>
        <UiCardHeader>
          <UiCardTitle class="text-base">Actions Rapides</UiCardTitle>
          <UiCardDescription>Commencez votre prochaine tâche</UiCardDescription>
        </UiCardHeader>
        <UiCardContent class="pt-0 space-y-3">
          <NuxtLink to="/projects">
            <div class="flex items-center gap-4 rounded-lg border p-4 hover:bg-muted/50 transition-colors cursor-pointer">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium">Nouveau Projet</p>
                <p class="text-xs text-muted-foreground">Créer un nouveau projet d'achat</p>
              </div>
            </div>
          </NuxtLink>

          <NuxtLink to="/catalogue">
            <div class="flex items-center gap-4 rounded-lg border p-4 hover:bg-muted/50 transition-colors cursor-pointer">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium">Gérer le Catalogue</p>
                <p class="text-xs text-muted-foreground">Ajouter ou modifier des articles de prix</p>
              </div>
            </div>
          </NuxtLink>

          <NuxtLink to="/articles">
            <div class="flex items-center gap-4 rounded-lg border p-4 hover:bg-muted/50 transition-colors cursor-pointer">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium">Catalogue d'Articles</p>
                <p class="text-xs text-muted-foreground">Parcourir les modèles d'articles CPS & RC</p>
              </div>
            </div>
          </NuxtLink>
        </UiCardContent>
      </UiCard>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

const api = useApi()
const loading = ref(true)
const projectsData = ref<any[]>([])
const catalogueCount = ref(0)

const recentProjects = computed(() => projectsData.value.slice(0, 5))


const FolderIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>`
const DocIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`
const BookIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`

const stats = computed(() => [
  { label: 'Projets Actifs', value: projectsData.value.length, sub: 'Possédés et partagés', icon: FolderIcon, bgColor: 'bg-primary/10', iconColor: 'text-primary' },
  { label: 'Articles du Catalogue', value: catalogueCount.value, sub: 'Articles de prix réutilisables', icon: BookIcon, bgColor: 'bg-green-100', iconColor: 'text-green-600' },
  { label: 'Types de Documents', value: 3, sub: 'Exportations CPS, RC, BDP', icon: DocIcon, bgColor: 'bg-amber-100', iconColor: 'text-amber-600' },
])

onMounted(async () => {
  try {
    const [projectsRes, catRes] = await Promise.all([
      api.get('/projets').catch(() => []),
      api.get('/catalogue-articles').catch(() => []),
    ])
    projectsData.value = Array.isArray(projectsRes) ? projectsRes : (projectsRes as any)?.data ?? []
    const catData = Array.isArray(catRes) ? catRes : (catRes as any)?.data ?? []
    catalogueCount.value = catData.length
  } catch (e) {
    console.error('Dashboard load error:', e)
  } finally {
    loading.value = false
  }
})
</script>
