<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Paramètres</h1>
        <p class="text-sm text-muted-foreground">Gérer vos préférences d'application</p>
      </div>
    </div>

    <UiTabs :tabs="settingsTabs" :default-value="activeTab" @update:model-value="activeTab = $event">
      <template #default="{ activeTab: current }">

        <UiCard v-if="current === 'preferences'" class="p-6">
          <h3 class="text-base font-semibold mb-6">Préférences de l'application</h3>
          <div class="space-y-6 max-w-2xl">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium">Langue par défaut</p>
                  <p class="text-xs text-muted-foreground">Sélectionnez votre langue préférée pour l'interface</p>
                </div>
                <select v-model="prefsForm.language" class="rounded-md border border-input bg-background px-3 h-9 text-sm w-40">
                  <option value="en">Français</option>
                  <option value="fr">English</option>
                  <option value="ar">العربية</option>
                </select>
              </div>

              <div class="flex items-center justify-between pt-4 border-t">
                <div>
                  <p class="text-sm font-medium">Mode sombre</p>
                  <p class="text-xs text-muted-foreground">Basculer entre les thèmes clair et sombre</p>
                </div>
                <UiSwitch v-model="prefsForm.darkMode" />
              </div>
            </div>

            <div v-if="prefsSaved" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Préférences mises à jour avec succès !</div>
            <div class="flex justify-end pt-6 border-t">
              <UiButton @click="savePrefs" :disabled="savingPrefs">
                {{ savingPrefs ? 'Enregistrement...' : 'Enregistrer les préférences' }}
              </UiButton>
            </div>
          </div>
        </UiCard>


        <UiCard v-if="current === 'defaults'" class="p-6">
          <h3 class="text-base font-semibold mb-6">Valeurs par défaut du projet</h3>
          <p class="text-sm text-muted-foreground mb-6">Ces valeurs seront pré-remplies lors de la création de nouveaux projets (stockées localement)</p>
          
          <div class="space-y-6 max-w-2xl">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <UiLabel>Taux de TVA par défaut (%)</UiLabel>
                <UiInput v-model="defaultsForm.tva" type="number" step="0.01" />
              </div>
              <div class="space-y-2">
                <UiLabel>Délai d'exécution par défaut</UiLabel>
                <UiInput v-model="defaultsForm.delay" placeholder="ex. 6 mois" />
              </div>
            </div>

            <div class="space-y-2">
              <UiLabel>Lieu par défaut</UiLabel>
              <UiInput v-model="defaultsForm.location" placeholder="ex. Casablanca" />
            </div>

            <div v-if="defaultsSaved" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Valeurs par défaut optimisées !</div>
            <div class="flex justify-end pt-6 border-t">
              <UiButton @click="saveDefaults" :disabled="savingDefaults">
                {{ savingDefaults ? 'Enregistrement...' : 'Enregistrer' }}
              </UiButton>
            </div>
          </div>
        </UiCard>
      </template>
    </UiTabs>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useDark, useStorage } from '@vueuse/core'

const isDark = useDark()

const activeTab = ref('preferences')

const settingsTabs = [
  { value: 'preferences', label: 'Préférences' },
  { value: 'defaults', label: 'Valeurs par défaut du projet' },
]

const prefsForm = ref({
  language: 'en',
  darkMode: isDark.value
})

watch(() => prefsForm.value.darkMode, (newVal) => {
  isDark.value = newVal
})

const defaultsForm = useStorage('cps_project_defaults', {
  tva: 20,
  delay: '6 mois',
  location: ''
})

const savingPrefs = ref(false)
const prefsSaved = ref(false)

const savePrefs = async () => {
  savingPrefs.value = true
  prefsSaved.value = false
  await new Promise(r => setTimeout(r, 500))
  prefsSaved.value = true
  savingPrefs.value = false
  setTimeout(() => prefsSaved.value = false, 3000)
}

const savingDefaults = ref(false)
const defaultsSaved = ref(false)

const saveDefaults = async () => {
  savingDefaults.value = true
  defaultsSaved.value = false
  await new Promise(r => setTimeout(r, 500))
  defaultsSaved.value = true
  savingDefaults.value = false
  setTimeout(() => defaultsSaved.value = false, 3000)
}
</script>
