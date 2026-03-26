<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900">Créer un nouveau mot de passe</h2>
    <p class="mt-2 text-sm text-muted-foreground">Votre nouveau mot de passe doit être différent de vos mots de passe précédents.</p>

    <div v-if="success" class="mt-8 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
      Mot de passe réinitialisé avec succès. Vous pouvez maintenant <NuxtLink to="/login" class="font-medium underline">vous connecter</NuxtLink>.
    </div>

    <form v-else @submit.prevent="handleReset" class="mt-8 space-y-5">
      <div class="space-y-2">
        <UiLabel for="password">Nouveau mot de passe</UiLabel>
        <UiInput
          id="password"
          v-model="password"
          type="password"
          required
          placeholder="••••••••"
        />
      </div>

      <div class="space-y-2">
        <UiLabel for="password_confirmation">Confirmer le mot de passe</UiLabel>
        <UiInput
          id="password_confirmation"
          v-model="password_confirmation"
          type="password"
          required
          placeholder="••••••••"
        />
      </div>

      <div v-if="error" class="rounded-lg bg-destructive/10 px-4 py-3 text-sm text-destructive">
        {{ error }}
      </div>

      <UiButton type="submit" class="w-full" :disabled="loading">
        <svg v-if="loading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ loading ? 'Réinitialisation...' : 'Réinitialiser le mot de passe' }}
      </UiButton>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({ layout: 'auth', auth: false })

const auth = useAuth()
const route = useRoute()
const router = useRouter()

const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)

const handleReset = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const token = route.query.token as string
    const email = route.query.email as string
    
    if (!token || !email) {
      error.value = 'Jeton invalide ou manquant.'
      loading.value = false
      return
    }

    await auth.resetPassword({
      token,
      email,
      password: password.value,
      password_confirmation: password_confirmation.value
    })
    
    success.value = true
    setTimeout(() => {
      router.push('/login')
    }, 3000)
    
  } catch (err: any) {
    error.value = err?.data?.message || err?.message || 'Échec de la réinitialisation du mot de passe. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!route.query.token || !route.query.email) {
    error.value = 'Lien de réinitialisation de mot de passe invalide.'
  }
})
</script>
