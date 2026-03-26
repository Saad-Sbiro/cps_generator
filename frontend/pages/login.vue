<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900">Bienvenue</h2>
    <p class="mt-2 text-sm text-muted-foreground">Connectez-vous à votre compte CPS Generator</p>

    <form @submit.prevent="handleLogin" class="mt-8 space-y-5">
      <div class="space-y-2">
        <UiLabel for="email">Adresse e-mail</UiLabel>
        <UiInput
          id="email"
          v-model="email"
          type="email"
          required
          placeholder="you@company.ma"
          autocomplete="email"
        />
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <UiLabel for="password">Mot de passe</UiLabel>
          <NuxtLink to="/forgot-password" class="text-xs text-primary hover:underline">Mot de passe oublié ?</NuxtLink>
        </div>
        <UiInput
          id="password"
          v-model="password"
          type="password"
          required
          placeholder="••••••••"
          autocomplete="current-password"
        />
      </div>

      <div class="flex items-center space-x-2">
        <input 
          id="remember" 
          v-model="remember" 
          type="checkbox" 
          class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" 
        />
        <UiLabel for="remember" class="font-normal">Se souvenir de moi pendant 30 jours</UiLabel>
      </div>

      <div v-if="error" class="rounded-lg bg-destructive/10 px-4 py-3 text-sm text-destructive">
        {{ error }}
      </div>

      <UiButton type="submit" class="w-full" :disabled="loading">
        <svg v-if="loading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
      </UiButton>
    </form>

    <p class="mt-6 text-center text-sm text-muted-foreground">
      Vous n'avez pas de compte ?
      <NuxtLink to="/register" class="font-medium text-primary hover:underline">Créer un compte ici</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({ layout: 'auth', auth: false })

const auth = useAuth()
const router = useRouter()

const email = ref('')
const password = ref('')
const remember = ref(false)
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    const success = await auth.login({ email: email.value, password: password.value, remember: remember.value })
    if (success) router.push('/')
    else error.value = 'E-mail ou mot de passe invalide'
  } catch (err: any) {
    error.value = err?.data?.message || err?.message || 'Échec de la connexion. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script>
