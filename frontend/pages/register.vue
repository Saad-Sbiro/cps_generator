<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900">Créer votre compte</h2>
    <p class="mt-2 text-sm text-muted-foreground">Commencez à gérer vos documents d'achat dès aujourd'hui</p>

    <form @submit.prevent="handleRegister" class="mt-8 space-y-4">
      <div class="space-y-2">
        <UiLabel for="name">Nom complet</UiLabel>
        <UiInput id="name" v-model="name" type="text" required placeholder="Ahmed El Mansouri" />
      </div>

      <div class="space-y-2">
        <UiLabel for="email">Adresse e-mail</UiLabel>
        <UiInput id="email" v-model="email" type="email" required placeholder="ahmed@company.ma" />
      </div>

      <div class="space-y-2">
        <UiLabel for="password">Mot de passe</UiLabel>
        <UiInput id="password" v-model="password" type="password" required placeholder="Min. 8 caractères" />
      </div>

      <div class="space-y-2">
        <UiLabel for="password_confirmation">Confirmer le mot de passe</UiLabel>
        <UiInput id="password_confirmation" v-model="password_confirmation" type="password" required placeholder="••••••••" />
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
        {{ loading ? 'Création du compte...' : 'Créer un compte' }}
      </UiButton>
    </form>

    <p class="mt-6 text-center text-sm text-muted-foreground">
      Vous avez déjà un compte ?
      <NuxtLink to="/login" class="font-medium text-primary hover:underline">Se connecter</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({ layout: 'auth', auth: false })

const auth = useAuth()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const remember = ref(false)
const loading = ref(false)
const error = ref('')

const handleRegister = async () => {
  loading.value = true
  error.value = ''

  if (password.value !== password_confirmation.value) {
    error.value = 'Les mots de passe ne correspondent pas'
    loading.value = false
    return
  }

  try {
    const success = await auth.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
      remember: remember.value
    })
    if (success) router.push('/')
  } catch (err: any) {
    if (err?.data?.errors) {
      error.value = Object.values(err.data.errors).flat()[0] as string
    } else {
      error.value = err?.data?.message || 'Échec de l\'inscription. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}
</script>
