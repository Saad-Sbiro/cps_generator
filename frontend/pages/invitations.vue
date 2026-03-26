<template>
  <div class="space-y-4 max-w-5xl mx-auto">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Boîte de réception</h2>
      <p class="text-sm text-muted-foreground">Gérer vos invitations et alertes de projet</p>
    </div>


    <UiTabs :tabs="inboxTabs" default-value="invitations" @update:model-value="activeTab = $event">
      <template #default="{ activeTab: current }">

        <div v-if="current === 'invitations'" class="mt-4">
          <div v-if="loadingInvitations" class="grid grid-cols-1 gap-3">
            <div v-for="i in 3" :key="i" class="h-24 animate-pulse rounded-lg bg-gray-100" />
          </div>
          <div v-else-if="invitations.length === 0" class="py-16 text-center text-muted-foreground bg-white border rounded-lg">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 mb-4">
              <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <p class="font-medium">Aucune invitation en attente</p>
            <p class="text-sm mt-1">Vous êtes à jour !</p>
          </div>
          <div v-else class="space-y-3">
            <UiCard v-for="inv in invitations" :key="inv.id" class="p-5">
              <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                  <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                  </div>
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <h3 class="font-semibold text-gray-900">{{ inv.projet?.intitule }}</h3>
                      <UiBadge variant="outline" class="text-xs">{{ inv.role }}</UiBadge>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">
                      Invité par <span class="font-medium text-gray-900">{{ inv.invited_by?.name }}</span> 
                      ({{ inv.invited_by?.email }})
                    </p>
                    <p class="text-xs text-muted-foreground">{{ formatDate(inv.created_at) }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <UiButton variant="outline" class="text-destructive hover:bg-destructive/10 hover:text-destructive" :disabled="processing === inv.id" @click="reject(inv.id)">
                    Refuser
                  </UiButton>
                  <UiButton :disabled="processing === inv.id" @click="accept(inv.id)">
                    <svg v-if="processing === inv.id" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Accepter l'invitation
                  </UiButton>
                </div>
              </div>
            </UiCard>
          </div>
        </div>


        <div v-if="current === 'alerts'" class="mt-4 space-y-4">
          <div class="flex justify-end pr-2">
            <UiButton v-if="unreadCount > 0" variant="ghost" size="sm" @click="markAllRead" class="text-xs -mr-2">
              <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
              Tout marquer comme lu
            </UiButton>
          </div>
          <div v-if="loadingAlerts" class="grid grid-cols-1 gap-3">
            <div v-for="i in 3" :key="i" class="h-16 animate-pulse rounded-lg bg-gray-100" />
          </div>
          <div v-else-if="notifications.length === 0" class="py-16 text-center text-muted-foreground bg-white border rounded-lg">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 mb-4">
              <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <p class="font-medium">Aucune alerte pour l'instant</p>
          </div>
          <div v-else class="space-y-3">
            <UiCard 
              v-for="notif in notifications" 
              :key="notif.id" 
              class="p-4 transition-colors"
              :class="!notif.read_at ? 'bg-primary/5 border-primary/20' : 'bg-white'"
            >
              <div class="flex items-start justify-between">
                <div class="flex gap-3">
                  <div class="mt-0.5">
                    <span v-if="!notif.read_at" class="flex h-2.5 w-2.5 rounded-full bg-primary mt-1.5"></span>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 leading-snug">{{ notif.data?.message || 'Nouvelle notification' }}</p>
                    <p class="text-xs text-muted-foreground mt-1">{{ formatDate(notif.created_at) }}</p>
                  </div>
                </div>
                <UiButton v-if="!notif.read_at" variant="ghost" size="sm" @click="markRead(notif.id)" class="text-xs h-8">
                  Marquer comme lu
                </UiButton>
              </div>
            </UiCard>
          </div>
        </div>
      </template>
    </UiTabs>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '~/composables/useToast'
import { useConfirm } from '~/composables/useConfirm'

const api = useApi()
const router = useRouter()
const { addToast } = useToast()
const { confirm } = useConfirm()

const activeTab = ref('invitations')
const inboxTabs = computed(() => [
  { value: 'invitations', label: `Invitations (${invitations.value.length})` },
  { value: 'alerts', label: `Alertes (${unreadCount.value > 0 ? unreadCount.value : '0'})` },
])

const loadingInvitations = ref(true)
const invitations = ref<any[]>([])
const processing = ref<string | null>(null)

const loadingAlerts = ref(true)
const notifications = ref<any[]>([])
const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

const formatDate = (d: string) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('fr-FR', {
    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}

const fetchInvitations = async () => {
  loadingInvitations.value = true
  try {
    const res = await api.get('/projet-invitations')
    invitations.value = Array.isArray(res) ? res : []
  } catch (e) {
    console.error(e)
  } finally {
    loadingInvitations.value = false
  }
}

const fetchNotifications = async () => {
  loadingAlerts.value = true
  try {
    const res: any = await api.get('/notifications')
    notifications.value = res.all || []
  } catch (e) {
    console.error(e)
  } finally {
    loadingAlerts.value = false
  }
}

onMounted(() => {
  fetchInvitations()
  fetchNotifications()
})

const accept = async (id: string) => {
  processing.value = id
  try {
    const res = await api.post(`/projet-invitations/${id}/accept`, {})
    if (res && (res as any).projet?.id) {
      router.push(`/projects/${(res as any).projet.id}`)
    } else {
      await fetchInvitations()
    }
  } catch (e) {
    console.error(e)
  } finally {
    processing.value = null
  }
}

const reject = async (id: string) => {
  const confirmed = await confirm({
    title: 'Refuser l\'invitation',
    description: 'Êtes-vous sûr de vouloir refuser cette invitation ? Vous ne pourrez pas rejoindre ce projet à moins d\'y être à nouveau invité.',
    confirmText: 'Refuser',
    variant: 'destructive',
  })
  if (!confirmed) return
  processing.value = id
  try {
    await api.delete(`/projet-invitations/${id}/reject`)
    await fetchInvitations()
    addToast({ title: 'Invitation refusée', description: 'L\'invitation a été refusée.', variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec du refus de l\'invitation.', variant: 'destructive' })
  } finally {
    processing.value = null
  }
}

const markRead = async (id: string) => {
  const notif = notifications.value.find(n => n.id === id)
  if (notif) notif.read_at = new Date().toISOString()
  try {
    await api.post(`/notifications/${id}/read`, {})
  } catch (e) {
    console.error(e)
  }
}

const markAllRead = async () => {
  notifications.value.forEach(n => {
    if (!n.read_at) n.read_at = new Date().toISOString()
  })
  try {
    await api.post('/notifications/read-all', {})
  } catch (e) {
    console.error(e)
  }
}
</script>
