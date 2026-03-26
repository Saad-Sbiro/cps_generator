<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900">Réinitialiser votre mot de passe</h2>
    <p class="mt-2 text-sm text-muted-foreground">Nous vous enverrons un e-mail avec un lien pour réinitialiser votre mot de passe.</p>

    <div v-if="success" class="mt-8 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
      Vérifiez votre e-mail pour un lien de réinitialisation. S'il n'apparaît pas dans quelques minutes, vérifiez vos spams.
    </div>

    <form v-else @submit.prevent="handleForgot" class="mt-8 space-y-5">
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

      <div v-if="error" class="rounded-lg bg-destructive/10 px-4 py-3 text-sm text-destructive">
        {{ error }}
      </div>

      <UiButton type="submit" class="w-full" :disabled="loading">
        <svg v-if="loading" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        {{ loading ? 'Envoi du lien...' : 'Envoyer le lien de réinitialisation' }}
      </UiButton>
    </form>

    <p class="mt-6 text-center text-sm text-muted-foreground">
      Retour à
      <NuxtLink to="/login" class="font-medium text-primary hover:underline">Se connecter</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({ layout: 'auth', auth: false })

const auth = useAuth()
const email = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)

const handleForgot = async () => {
  loading.value = true
  error.value = ''
  success.value = false
  try {
    await auth.forgotPassword(email.value)
    success.value = true
  } catch (err: any) {
    error.value = err?.data?.message || err?.message || 'Échec de l\'envoi du lien de réinitialisation. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script>
