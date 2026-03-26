<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r bg-white">
      <!-- Logo -->
      <div class="flex h-16 items-center gap-3 border-b px-6">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
          <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold text-gray-900">CPS Generator</p>
          <p class="text-xs text-muted-foreground">Suite d'Achats</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 px-3 py-4">
        <NuxtLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
          :class="isActive(item.to)
            ? 'bg-primary/10 text-primary'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
        >
          <span v-html="item.icon" class="h-5 w-5 shrink-0 [&>svg]:h-full [&>svg]:w-full"></span>
          {{ item.label }}
        </NuxtLink>
      </nav>

      <!-- User footer -->
      <div class="border-t">
        <div class="p-3 flex items-center gap-2">
          <NuxtLink
            to="/invitations"
            class="flex-1 group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="isActive('/invitations')
              ? 'bg-primary/10 text-primary'
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
          >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
            Boîte de réception
            <span v-if="pendingCount > 0" class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">{{ pendingCount }}</span>
          </NuxtLink>
          <NuxtLink
            to="/settings"
            class="shrink-0 flex h-10 w-10 items-center justify-center rounded-lg transition-colors"
            :class="isActive('/settings')
              ? 'bg-primary/10 text-primary'
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
            title="Paramètres"
          >
            <span v-html="SettingsIcon" class="h-5 w-5 shrink-0 [&>svg]:h-full [&>svg]:w-full"></span>
          </NuxtLink>
        </div>
        <div class="border-t p-4 flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
            {{ userInitial }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-gray-900">{{ user?.name || 'Chargement...' }}</p>
            <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
          </div>
          <button @click="handleLogout" title="Déconnexion" class="rounded p-1 text-gray-400 hover:text-gray-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="ml-64 flex flex-1 flex-col overflow-hidden">
      <!-- Top header -->
      <header class="flex h-16 items-center justify-between border-b bg-white px-6">
        <div>
          <h1 class="text-lg font-semibold text-gray-900">{{ pageTitle }}</h1>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-sm text-muted-foreground">{{ currentDate }}</span>
        </div>
      </header>

      <!-- Page content -->
      <div class="flex-1 overflow-y-auto p-6 relative">
        <slot />
        <UiToastProvider />
        <UiConfirmDialog />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const { user, fetchUser, logout } = useAuth()
const route = useRoute()
const router = useRouter()
const api = useApi()

const pendingCount = ref(0)
let countInterval: any = null

const fetchPendingCount = async () => {
  if (!user.value) return
  try {
    const res = await api.get('/projet-invitations')
    pendingCount.value = Array.isArray(res) ? res.length : 0
  } catch (e) {
    // silently fail
  }
}

onMounted(async () => {
  if (!user.value) await fetchUser()
  if (user.value) {
    await fetchPendingCount()
    countInterval = setInterval(fetchPendingCount, 30000) // update every 30s
  }
})

onUnmounted(() => {
  if (countInterval) clearInterval(countInterval)
})

const handleLogout = async () => {
  await logout()
  router.push('/login')
}

const userInitial = computed(() =>
  user.value?.name ? user.value.name.charAt(0).toUpperCase() : '?'
)

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/': 'Tableau de bord',
    '/projects': 'Projets',
    '/catalogue': 'Catalogue de Prix',
    '/articles': 'Catalogue d\'articles',
    '/settings': 'Paramètres',
  }
  const path = route.path
  if (path.startsWith('/projects/')) return 'Espace Projet'
  return titles[path] || 'CPS Generator'
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-MA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

const isActive = (to: string) => {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}

// Modern outline SVG strings
const LayoutDashboardIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>`
const FolderIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>`
const BookIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`
const DocumentIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`

const SettingsIcon = `<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`

const navItems = [
  { to: '/', label: 'Tableau de bord', icon: LayoutDashboardIcon },
  { to: '/projects', label: 'Projets', icon: FolderIcon },
  { to: '/catalogue', label: 'Catalogue de Prix', icon: BookIcon },
  { to: '/articles', label: 'Catalogue d\'articles', icon: DocumentIcon },
]
</script>
