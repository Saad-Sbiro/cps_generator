<template>
  <div v-if="loading" class="flex h-48 items-center justify-center">
    <div class="flex flex-col items-center gap-3">
      <svg class="h-8 w-8 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <p class="text-sm text-muted-foreground">Chargement du projet...</p>
    </div>
  </div>

  <div v-else-if="!project" class="flex h-48 flex-col items-center justify-center gap-3">
    <p class="text-muted-foreground">Projet introuvable.</p>
    <NuxtLink to="/projects"><UiButton variant="outline">Retour aux projets</UiButton></NuxtLink>
  </div>

  <div v-else class="space-y-4">

    <div class="flex items-start justify-between">
      <div>
        <div class="flex items-center gap-3">
          <NuxtLink to="/projects" class="text-muted-foreground hover:text-gray-900">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </NuxtLink>
          <h1 class="text-xl font-bold text-gray-900">{{ project.intitule }}</h1>
          <UiBadge variant="outline" class="font-mono">{{ project.reference }}</UiBadge>
          <UiBadge :variant="project.current_user_role === 'owner' ? 'default' : 'secondary'">
            {{ project.current_user_role === 'owner' ? 'propriétaire' : project.current_user_role }}
          </UiBadge>
        </div>
        <p class="mt-1 text-sm text-muted-foreground">
          {{ project.maitre_ouvrage }} · {{ project.lieu }}
        </p>
      </div>
      <div class="flex items-center gap-3 text-right">
        <div>
          <p class="text-xs text-muted-foreground">Total TTC</p>
          <p class="text-lg font-bold text-gray-900">{{ formatCurrency(project.total_ttc) }}</p>
        </div>
      </div>
    </div>


    <UiTabs :tabs="tabs" v-model="activeTab">
      <template #default="{ activeTab: current }">

        <UiCard v-if="current === 'settings'" class="p-6">
          <h3 class="text-base font-semibold mb-5">Paramètres du projet</h3>
          <form @submit.prevent="saveSettings" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <UiLabel>Référence</UiLabel>
                <UiInput v-model="settingsForm.reference" disabled class="bg-muted cursor-not-allowed" />
              </div>
              <div class="space-y-2">
                <UiLabel>Date</UiLabel>
                <UiInput v-model="settingsForm.date_creation" type="date" required />
              </div>
            </div>
            <div class="space-y-2">
              <UiLabel>Nom du projet (Intitulé)</UiLabel>
              <UiInput v-model="settingsForm.intitule" required />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <UiLabel>Maître d'ouvrage</UiLabel>
                <UiInput v-model="settingsForm.maitre_ouvrage" />
              </div>
              <div class="space-y-2">
                <UiLabel>Lieu</UiLabel>
                <UiInput v-model="settingsForm.lieu" />
              </div>
            </div>
            <div class="space-y-2">
              <UiLabel>Objet du marché</UiLabel>
              <UiTextarea v-model="settingsForm.objet_marche" rows="3" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <UiLabel>Taux de TVA (%)</UiLabel>
                <UiInput v-model="settingsForm.taux_tva" type="number" step="0.01" min="0" max="100" />
              </div>
              <div class="space-y-2">
                <UiLabel>Délai d'exécution</UiLabel>
                <UiInput v-model="settingsForm.delai_execution" placeholder="6 mois" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input v-model="settingsForm.inclure_brd_dans_cps" type="checkbox" id="brd_cps_edit" class="h-4 w-4 rounded border-gray-300" />
              <UiLabel for="brd_cps_edit">Inclure le BDP dans le CPS</UiLabel>
            </div>
            <div v-if="settingsSuccess" class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Paramètres enregistrés avec succès !</div>
            <div class="flex justify-end">
              <UiButton type="submit" :disabled="savingSettings">
                {{ savingSettings ? 'Enregistrement...' : 'Enregistrer les modifications' }}
              </UiButton>
            </div>
          </form>
        </UiCard>


        <div v-if="current === 'articles'" class="grid grid-cols-5 gap-4">

          <div class="col-span-2">
            <UiCard>
              <UiCardHeader>
                <div class="flex items-center justify-between">
                  <div>
                    <UiCardTitle class="text-sm">Catalogue d'articles</UiCardTitle>
                    <UiCardDescription>{{ articleMode === 'CPS' ? 'Articles du CPS' : 'Articles du RC' }}</UiCardDescription>
                  </div>
                  <UiButton variant="outline" size="sm" @click="articleMode = articleMode === 'CPS' ? 'RC' : 'CPS'">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    Passer à {{ articleMode === 'CPS' ? 'RC' : 'CPS' }}
                  </UiButton>
                </div>
              </UiCardHeader>
              <UiCardContent class="pt-0">
                <div class="mb-3">
                  <UiInput v-model="articleSearch" placeholder="Rechercher..." class="h-8 text-sm" />
                </div>
                <div v-if="loadingArticles" class="py-4 space-y-3 px-2">
                  <div v-for="i in 5" :key="i" class="h-14 w-full animate-pulse rounded-lg bg-muted"></div>
                </div>
                <div v-else class="max-h-96 overflow-y-auto space-y-1 pr-1">
                  <div
                    v-for="article in filteredLibraryArticles"
                    :key="article.id"
                    class="flex items-center justify-between rounded-lg border p-2.5 hover:bg-muted/50 cursor-pointer"
                    @click="openVariantModal(article)"
                  >
                    <div class="min-w-0">
                      <p class="text-xs font-semibold text-muted-foreground">{{ article.code }}</p>
                      <p class="truncate text-sm font-medium">{{ article.titre }}</p>
                    </div>
                    <UiBadge variant="outline" class="ml-2 shrink-0 text-xs">{{ article.type }}</UiBadge>
                  </div>
                  <div v-if="filteredLibraryArticles.length === 0" class="py-6 text-center text-xs text-muted-foreground">Aucun article trouvé</div>
                </div>
              </UiCardContent>
            </UiCard>
          </div>


          <div class="col-span-3">
            <UiCard>
              <UiCardHeader>
                <div class="flex items-center justify-between">
                  <UiCardTitle class="text-sm">Articles {{ articleMode }} ({{ filteredProjectArticles.length }})</UiCardTitle>
                  <span class="text-xs text-muted-foreground">ordonné par position</span>
                </div>
              </UiCardHeader>
              <UiCardContent class="pt-0">
                <div v-if="filteredProjectArticles.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                  Cliquez sur les articles à gauche pour les ajouter à ce projet.
                </div>
                <div v-else class="max-h-[500px] overflow-y-auto pr-1">
                  <draggable
                    v-model="filteredProjectArticles"
                    item-key="id"
                    class="space-y-2"
                    handle=".drag-handle"
                    @end="onArticleReorder"
                  >
                    <template #item="{ element: pa, index: idx }">
                      <div class="rounded-lg border p-3 bg-white overflow-hidden flex items-start gap-2 group">
                        <div class="drag-handle mt-1 cursor-grab text-gray-300 hover:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity">
                          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                          </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                              <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">{{ idx + 1 }}</span>
                              <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ pa.article?.code }} – {{ pa.article?.titre }}</p>
                                <UiBadge variant="outline" class="text-xs mt-0.5">{{ pa.article?.type }}</UiBadge>
                              </div>
                            </div>
                            <button type="button" @click.stop="removeArticle(pa.id)" class="shrink-0 text-muted-foreground hover:text-destructive">
                              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>
                          <div class="mt-2 text-xs text-muted-foreground bg-muted/50 rounded p-2 font-mono leading-relaxed max-h-24 overflow-y-auto break-words overflow-wrap-anywhere">
                            {{ pa.contenu_final?.substring(0, 200) }}{{ pa.contenu_final?.length > 200 ? '...' : '' }}
                          </div>
                        </div>
                      </div>
                    </template>
                  </draggable>
                </div>
              </UiCardContent>
            </UiCard>
          </div>
        </div>


        <UiDialog :open="showVariantModal" @update:open="showVariantModal = $event" class="sm:max-w-2xl">
          <div class="p-6 bg-white text-gray-900">
            <UiDialogHeader>
              <template #title>Choisir une variante</template>
              <template #description>
                Sélectionnez la variante de l'article "{{ selectedArticleForVariant?.code }}" à ajouter au projet.
              </template>
            </UiDialogHeader>
            <div class="py-6 space-y-4 max-h-[60vh] overflow-y-auto pr-2">
              <div v-if="!selectedArticleForVariant?.variants?.length" class="text-sm text-muted-foreground p-4 bg-muted/50 rounded-lg">
                Cet article n'a pas de variantes spécifiques. Le contenu par défaut sera utilisé.
              </div>
              <div v-else class="space-y-4">
                <div 
                  v-for="variant in selectedArticleForVariant.variants" 
                  :key="variant.id"
                  @click="selectedVariantId = variant.id"
                  class="relative flex flex-col space-y-3 rounded-xl border-2 p-5 cursor-pointer transition-all duration-200"
                  :class="[
                    selectedVariantId === variant.id 
                      ? 'border-primary bg-primary/5 shadow-sm ring-1 ring-primary ring-opacity-50' 
                      : 'border-border bg-white hover:border-primary/40 hover:bg-gray-50'
                  ]"
                >
                  <div class="flex items-start justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-3 min-w-0">

                      <div 
                        class="flex h-5 w-5 items-center justify-center rounded-full border transition-colors shrink-0"
                        :class="selectedVariantId === variant.id ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white'"
                      >
                        <svg v-if="selectedVariantId === variant.id" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <div class="min-w-0">
                        <p class="text-sm font-semibold transition-colors break-words" :class="selectedVariantId === variant.id ? 'text-primary' : 'text-gray-900'">
                          {{ variant.label }}
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <UiBadge v-if="variant.is_default" :variant="selectedVariantId === variant.id ? 'default' : 'secondary'" class="text-[10px] tracking-wide uppercase">Défaut</UiBadge>
                        <button 
                          v-if="!variant.is_default" 
                          type="button" 
                          class="p-1.5 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-md transition-colors z-10"
                          @click.stop.prevent="deleteVariant(variant)"
                        >
                          <svg v-if="deletingVariantId === variant.id" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                          </svg>
                          <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                    </div>
                  </div>
                  
                  <div class="pl-8">
                    <div class="relative rounded-lg bg-white p-4 border border-gray-100 shadow-sm overflow-hidden">
                      <p class="text-sm text-muted-foreground font-mono leading-relaxed break-words" style="overflow-wrap: anywhere;">
                        {{ variant.contenu }}
                      </p>
                    </div>
                  </div>
                  </div>
                </div>
                

              <div v-if="showNewVariantForm" class="mt-2 rounded-xl border-2 border-primary/30 bg-primary/5 p-5 space-y-3 overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">Nouvelle variante</p>
                    <button type="button" @click="showNewVariantForm = false; newVariantForm = { label: '', contenu: '' }" class="text-muted-foreground hover:text-gray-700">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div class="space-y-2">
                    <UiLabel class="text-xs">Libellé *</UiLabel>
                    <UiInput v-model="newVariantForm.label" placeholder="ex. Variante béton armé" class="text-sm" />
                  </div>
                  <div class="space-y-2">
                    <UiLabel class="text-xs">Contenu *</UiLabel>
                    <UiTextarea v-model="newVariantForm.contenu" placeholder="Contenu de la variante d'article..." rows="4" class="text-sm font-mono" />
                  </div>
                  <div class="flex justify-end">
                    <UiButton size="sm" :disabled="creatingVariant || !newVariantForm.label || !newVariantForm.contenu" @click="createNewVariant">
                      <svg v-if="creatingVariant" class="mr-2 h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                      </svg>
                      {{ creatingVariant ? 'Création...' : 'Créer & Sélectionner' }}
                    </UiButton>
                  </div>
                </div>
              </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
              <div>
                <UiButton 
                  v-if="!showNewVariantForm" 
                  type="button" 
                  variant="secondary" 
                  @click="openNewVariantForm"
                >
                  Créer une nouvelle variante
                </UiButton>
              </div>
              <div class="flex items-center gap-3">
                <UiButton type="button" variant="outline" @click="showVariantModal = false">Annuler</UiButton>
                <UiButton type="button" :disabled="addingArticle" @click="confirmAddArticle">
                  <span v-if="addingArticle">Ajout...</span>
                  <span v-else>Ajouter au projet</span>
                </UiButton>
              </div>
            </div>
          </div>
        </UiDialog>


        <div v-if="current === 'brd'" class="space-y-4">
          <div class="flex items-center justify-between">
            <p class="text-sm text-muted-foreground">{{ projectPrix.length }} ligne{{ projectPrix.length !== 1 ? 's' : '' }}</p>
            <UiButton size="sm" @click="showAddPrixModal = true">
              <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Ajouter un prix
            </UiButton>
          </div>
          <UiCard class="overflow-x-auto">
            <div class="min-w-[800px] p-1">
              <UiTable>
              <UiTableHeader>
                <UiTableRow class="hover:bg-transparent">
                  <UiTableHead class="w-12">#</UiTableHead>
                  <UiTableHead>Désignation</UiTableHead>
                  <UiTableHead class="w-20">Unité</UiTableHead>
                  <UiTableHead class="w-24">Quantité</UiTableHead>
                  <UiTableHead class="w-28">Prix unitaire HT</UiTableHead>
                  <UiTableHead class="w-28">Total HT</UiTableHead>
                  <UiTableHead class="w-16"></UiTableHead>
                </UiTableRow>
              </UiTableHeader>
              <UiTableBody>
                <UiTableRow v-if="loadingBrd">
                  <UiTableCell colspan="7" class="py-12">
                    <div class="flex justify-center">
                      <svg class="h-6 w-6 animate-spin text-muted-foreground" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                      </svg>
                    </div>
                  </UiTableCell>
                </UiTableRow>
                <UiTableRow v-else-if="projectPrix.length === 0">
                  <UiTableCell colspan="7" class="py-12 text-center text-muted-foreground">
                    Aucun article ajouté. Cliquez sur "Ajouter un prix" pour sélectionner dans le catalogue.
                  </UiTableCell>
                </UiTableRow>
                <UiTableRow v-for="(pp, idx) in projectPrix" :key="pp.id">
                  <UiTableCell class="text-muted-foreground text-center">{{ idx + 1 }}</UiTableCell>
                  <UiTableCell class="font-medium">{{ pp.prix_catalogue?.designation }}</UiTableCell>
                  <UiTableCell>
                    <UiInput
                      v-model="pp.unite"
                      class="h-8 w-16 text-sm"
                      @change="updatePrix(pp, { unite: pp.unite })"
                    />
                  </UiTableCell>
                  <UiTableCell>
                    <UiInput
                      v-model="pp.quantite"
                      type="number"
                      step="0.01"
                      min="0"
                      class="h-8 w-20 text-sm"
                      @change="updatePrix(pp, { quantite: pp.quantite })"
                    />
                  </UiTableCell>
                  <UiTableCell>
                    <UiInput
                      v-model="pp.prix_unitaire_ht"
                      type="number"
                      step="0.01"
                      min="0"
                      class="h-8 w-24 text-sm"
                      @change="updatePrix(pp, { prix_unitaire_ht: pp.prix_unitaire_ht })"
                    />
                  </UiTableCell>
                  <UiTableCell class="font-semibold">
                    {{ formatCurrency(parseFloat(pp.quantite || 0) * parseFloat(pp.prix_unitaire_ht || 0)) }}
                  </UiTableCell>
                  <UiTableCell>
                    <button @click="removePrix(pp.id)" class="text-muted-foreground hover:text-destructive">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </UiTableCell>
                </UiTableRow>
              </UiTableBody>
            </UiTable>
            </div>
          </UiCard>


          <UiCard v-if="projectPrix.length > 0" class="p-4 overflow-x-auto">
            <div class="flex justify-end gap-4 sm:gap-8 min-w-[300px]">
              <div class="text-right">
                <p class="text-xs text-muted-foreground">Total HT</p>
                <p class="font-semibold">{{ formatCurrency(brdTotalHT) }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-muted-foreground">TVA ({{ project.taux_tva }}%)</p>
                <p class="font-semibold">{{ formatCurrency(brdTotalTVA) }}</p>
              </div>
              <div class="text-right border-l pl-8">
                <p class="text-xs text-muted-foreground">Total TTC</p>
                <p class="text-lg font-bold text-primary">{{ formatCurrency(brdTotalTTC) }}</p>
              </div>
            </div>
          </UiCard>


          <UiDialog :open="showAddPrixModal" @update:open="showAddPrixModal = $event">
            <div class="p-6">
              <UiDialogHeader>
                <template #title>Ajouter un prix</template>
                <template #description>Sélectionnez dans votre catalogue et définissez la quantité</template>
              </UiDialogHeader>
              <div class="mt-4 space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                <div class="space-y-2">
                  <UiLabel>Rechercher le catalogue</UiLabel>
                  <UiInput v-model="prixSearch" placeholder="Rechercher par désignation..." />
                </div>
                <div class="max-h-48 overflow-y-auto rounded-md border divide-y">
                  <div
                    v-for="cat in filteredCatalogue"
                    :key="cat.id"
                    class="flex items-center justify-between p-3 hover:bg-muted/50 cursor-pointer"
                    :class="selectedCatalogueItem?.id === cat.id ? 'bg-primary/5 border-l-2 border-l-primary' : ''"
                    @click="selectedCatalogueItem = cat; prixForm.prix_unitaire_ht = cat.prix_unitaire_ht_defaut"
                  >
                    <div>
                      <p class="text-sm font-medium">{{ cat.designation }}</p>
                      <p class="text-xs text-muted-foreground">{{ cat.categorie }} · {{ cat.unite }}</p>
                    </div>
                    <span class="text-sm font-semibold">{{ formatCurrency(cat.prix_unitaire_ht_defaut) }}</span>
                  </div>
                  <div v-if="filteredCatalogue.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    Aucun article correspondant à votre recherche n'a été trouvé.
                  </div>
                </div>
                <div v-if="selectedCatalogueItem" class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <UiLabel>Quantité</UiLabel>
                    <UiInput v-model="prixForm.quantite" type="number" step="0.01" min="0" />
                  </div>
                  <div class="space-y-2">
                    <UiLabel>Prix unitaire HT</UiLabel>
                    <UiInput v-model="prixForm.prix_unitaire_ht" type="number" step="0.01" min="0" />
                  </div>
                </div>


                <div v-if="showNewPriceForm" class="mt-4 rounded-xl border-2 border-primary/30 bg-primary/5 p-5 space-y-3 overflow-hidden">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">Nouvel article de prix</p>
                    <button type="button" @click="showNewPriceForm = false" class="text-muted-foreground hover:text-gray-700">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 space-y-2">
                      <UiLabel class="text-xs">Désignation *</UiLabel>
                      <UiInput v-model="newPriceForm.designation" placeholder="ex. Béton armé en fondation" class="text-sm" />
                    </div>
                    <div class="space-y-2">
                      <UiLabel class="text-xs">Catégories *</UiLabel>
                      <UiInput v-model="newPriceForm.categorie" placeholder="ex. Gros oeuvre" class="text-sm" />
                    </div>
                    <div class="space-y-2">
                      <UiLabel class="text-xs">Unité *</UiLabel>
                      <UiInput v-model="newPriceForm.unite" placeholder="ex. m3" class="text-sm" />
                    </div>
                    <div class="space-y-2">
                      <UiLabel class="text-xs">Prix unitaire par défaut HT *</UiLabel>
                      <UiInput v-model="newPriceForm.prix_unitaire_ht_defaut" type="number" step="0.01" min="0" class="text-sm" />
                    </div>
                  </div>
                  <div class="flex justify-end pt-2">
                    <UiButton size="sm" :disabled="creatingPrice || !newPriceForm.designation || !newPriceForm.categorie || !newPriceForm.unite || newPriceForm.prix_unitaire_ht_defaut === null" @click="createNewPrice">
                      <svg v-if="creatingPrice" class="mr-2 h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                      </svg>
                      {{ creatingPrice ? 'Création...' : 'Créer & Sélectionner' }}
                    </UiButton>
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-4">
                <div>
                  <UiButton 
                    v-if="!showNewPriceForm" 
                    type="button" 
                    variant="secondary" 
                    @click="openNewPriceForm"
                  >
                    Créer un nouveau prix
                  </UiButton>
                </div>
                <div class="flex gap-3">
                  <UiButton variant="outline" @click="showAddPrixModal = false">Annuler</UiButton>
                  <UiButton :disabled="!selectedCatalogueItem || addingPrix" @click="addPrix">
                    {{ addingPrix ? 'Ajout...' : 'Ajouter au BDP' }}
                  </UiButton>
                </div>
              </div>
            </div>
          </UiDialog>
        </div>


        <div v-if="current === 'collaborators'" class="space-y-4">
          <UiCard v-if="project.current_user_role === 'owner'" class="p-6">
            <h3 class="text-base font-semibold mb-4">Inviter un collaborateur</h3>
            <form @submit.prevent="sendInvite" class="space-y-4">
              <div class="flex gap-3 items-start relative">
                <div class="flex-1 max-w-sm relative">
                  <UiInput 
                    v-model="inviteSearch" 
                    type="text" 
                    placeholder="Rechercher un utilisateur par nom ou e-mail..." 
                    @input="onSearchUsers"
                    @focus="showSearchDropdown = true"
                    class="w-full"
                    autocomplete="off"
                  />

                  <div v-if="showSearchDropdown && (searchingUsers || searchResults.length > 0 || inviteSearch.length >= 2)" class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-auto">
                    <div v-if="searchingUsers" class="p-3 text-sm text-muted-foreground text-center">Recherche...</div>
                    <div v-else-if="inviteSearch.length >= 2 && searchResults.length === 0" class="p-3 text-sm text-muted-foreground text-center">Aucun utilisateur trouvé</div>
                    <div 
                      v-else-if="searchResults.length > 0"
                      v-for="u in searchResults" 
                      :key="u.id"
                      class="px-3 py-2 hover:bg-muted cursor-pointer flex flex-col"
                      @click="selectUserToInvite(u)"
                    >
                      <span class="text-sm font-medium">{{ u.name }}</span>
                      <span class="text-xs text-muted-foreground">{{ u.email }}</span>
                    </div>
                  </div>
                </div>
                
                <select v-model="inviteRole" class="rounded-md border border-input bg-background px-3 h-10 text-sm">
                  <option value="editor">Éditeur</option>
                  <option value="viewer">Lecteur</option>
                </select>
                <UiButton type="submit" :disabled="inviting || !selectedUserToInvite">{{ inviting ? 'Envoi...' : 'Envoyer l\'invitation' }}</UiButton>
              </div>
              <div v-if="selectedUserToInvite" class="flex items-center gap-2 bg-primary/5 rounded-md p-2 w-fit">
                <span class="text-sm font-medium text-primary">Sélectionné: {{ selectedUserToInvite.name }} ({{ selectedUserToInvite.email }})</span>
                <button type="button" @click="clearSelectedUser" class="text-muted-foreground hover:text-destructive">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </form>
            <div v-if="inviteSuccess" class="mt-3 text-sm text-green-600">Invitation envoyée avec succès !</div>
          </UiCard>

          <UiCard v-if="project.current_user_role === 'owner' && pendingInvitations.length > 0">
            <UiCardHeader><UiCardTitle class="text-sm">Invitations en attente</UiCardTitle></UiCardHeader>
            <UiCardContent class="pt-0">
              <div class="divide-y">
                <div v-for="inv in pendingInvitations" :key="inv.id" class="flex items-center justify-between py-3">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 text-sm font-semibold text-orange-600">
                      {{ inv.email.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="text-sm font-medium">{{ inv.email }}</p>
                      <p class="text-xs text-muted-foreground">En attente • Envoyé le {{ formatDate(inv.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <UiBadge variant="outline">{{ inv.role }}</UiBadge>
                    <UiButton
                      variant="ghost"
                      size="sm"
                      class="text-destructive hover:text-destructive"
                      @click="removeInvitation(inv.id)"
                    >
                      Révoquer
                    </UiButton>
                  </div>
                </div>
              </div>
            </UiCardContent>
          </UiCard>

          <UiCard>
            <UiCardHeader><UiCardTitle class="text-sm">Membres de l'équipe</UiCardTitle></UiCardHeader>
            <UiCardContent class="pt-0">
              <div v-if="loadingCollaborators" class="py-8 flex justify-center">
                <svg class="h-6 w-6 animate-spin text-muted-foreground" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
              </div>
              <div v-else class="divide-y">
                <div v-for="member in collaborators" :key="member.id" class="flex items-center justify-between py-3">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                      {{ member.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="text-sm font-medium">{{ member.name }}</p>
                      <p class="text-xs text-muted-foreground">{{ member.email }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <UiBadge :variant="member.role === 'owner' ? 'default' : 'secondary'">{{ member.role }}</UiBadge>
                    <UiButton
                      v-if="!member.is_owner && project.current_user_role === 'owner'"
                      variant="ghost"
                      size="sm"
                      class="text-destructive hover:text-destructive"
                      @click="removeCollaborator(member.id)"
                    >
                      Retirer
                    </UiButton>
                  </div>
                </div>
              </div>
            </UiCardContent>
          </UiCard>
        </div>


        <div v-if="current === 'exports'" class="space-y-4">
          <div class="grid grid-cols-3 gap-4">
            <UiCard class="p-5 flex flex-col items-center gap-3 text-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <p class="font-semibold">Document CPS</p>
                <p class="text-xs text-muted-foreground">Spécifications techniques .docx</p>
              </div>
              <UiButton class="w-full" :disabled="generatingCps" @click="generateDoc('cps')">
                <svg v-if="generatingCps" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ generatingCps ? 'Génération...' : 'Générer CPS' }}
              </UiButton>
            </UiCard>

            <UiCard class="p-5 flex flex-col items-center gap-3 text-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div>
                <p class="font-semibold">Document RC</p>
                <p class="text-xs text-muted-foreground">Règles de consultation .docx</p>
              </div>
              <UiButton class="w-full" variant="secondary" :disabled="generatingRc" @click="generateDoc('rc')">
                <svg v-if="generatingRc" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ generatingRc ? 'Génération...' : 'Générer RC' }}
              </UiButton>
            </UiCard>

            <UiCard class="p-5 flex flex-col items-center gap-3 text-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <p class="font-semibold">BDP Budget</p>
                <p class="text-xs text-muted-foreground">Tableau de prix .xlsx</p>
              </div>
              <UiButton class="w-full" variant="outline" :disabled="generatingBrd" @click="generateDoc('brd')">
                <svg v-if="generatingBrd" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ generatingBrd ? 'Génération...' : 'Générer BDP' }}
              </UiButton>
            </UiCard>
          </div>


          <UiCard>
            <UiCardHeader>
              <UiCardTitle class="text-sm">Historique d'exportation</UiCardTitle>
            </UiCardHeader>
            <UiCardContent class="pt-0">
              <div v-if="exports.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                Aucun document généré pour le moment.
              </div>
              <UiTable v-else>
                <UiTableHeader>
                  <UiTableRow class="hover:bg-transparent">
                    <UiTableHead>Type</UiTableHead>
                    <UiTableHead>Nom du fichier</UiTableHead>
                    <UiTableHead>Généré le</UiTableHead>
                    <UiTableHead class="text-right">Actions</UiTableHead>
                  </UiTableRow>
                </UiTableHeader>
                <UiTableBody>
                  <UiTableRow v-for="exp in exports" :key="exp.id">
                    <UiTableCell>
                      <UiBadge :variant="exp.type?.includes('CPS') ? 'default' : exp.type?.includes('RC') ? 'secondary' : 'success'">
                        {{ exp.type }}
                      </UiBadge>
                    </UiTableCell>
                    <UiTableCell class="font-mono text-xs">{{ exp.filename }}</UiTableCell>
                    <UiTableCell class="text-sm text-muted-foreground">{{ formatDate(exp.created_at) }}</UiTableCell>
                    <UiTableCell class="text-right">
                      <a :href="`http://127.0.0.1:8000/api/exports/${exp.id}/download`" target="_blank">
                        <UiButton size="sm" variant="outline">Télécharger</UiButton>
                      </a>
                    </UiTableCell>
                  </UiTableRow>
                </UiTableBody>
              </UiTable>
            </UiCardContent>
          </UiCard>
        </div>
      </template>
    </UiTabs>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import draggable from 'vuedraggable'
import { useToast } from '~/composables/useToast'
import { useConfirm } from '~/composables/useConfirm'

const { addToast } = useToast()
const { confirm } = useConfirm()

const route = useRoute()
const api = useApi()
const projectId = route.params.id as string

const loading = ref(true)
const loadingArticles = ref(true)
const loadingBrd = ref(true)
const loadingCollaborators = ref(true)
const loadingExports = ref(true)

const project = ref<any>(null)
const projectArticles = ref<any[]>([])
const projectPrix = ref<any[]>([])
const collaborators = ref<any[]>([])
const pendingInvitations = ref<any[]>([])
const exports = ref<any[]>([])
const libraryArticles = ref<any[]>([])
const catalogue = ref<any[]>([])


const activeTab = ref((route.query.tab as string) || 'settings')
watch(activeTab, (newTab) => {
  if (process.client) {
    const url = new URL(window.location.href)
    url.searchParams.set('tab', newTab)
    window.history.replaceState({}, '', url.toString())
  }
})
const tabs = [
  { value: 'settings', label: 'Paramètres' },
  { value: 'articles', label: 'Articles CPS / RC' },
  { value: 'brd', label: 'BDP Budget' },
  { value: 'collaborators', label: 'Collaborateurs' },
  { value: 'exports', label: 'Documents Exportés' },
]


const settingsForm = ref<any>({})
const savingSettings = ref(false)
const settingsSuccess = ref(false)


const articleSearch = ref('')
const articleMode = ref<'CPS' | 'RC'>('CPS')
const showVariantModal = ref(false)
const selectedArticleForVariant = ref<any>(null)
const selectedVariantId = ref<string>('')
const addingArticle = ref(false)
const showNewVariantForm = ref(false)
const creatingVariant = ref(false)
const newVariantForm = ref({ label: '', contenu: '' })

const openNewVariantForm = () => {

  const defaultVariant = selectedArticleForVariant.value?.variants?.find((v: any) => v.is_default)
  newVariantForm.value = {
    label: '',
    contenu: defaultVariant?.contenu || selectedArticleForVariant.value?.contenu || '',
  }
  showNewVariantForm.value = true
}

const createNewVariant = async () => {
  if (!selectedArticleForVariant.value || !newVariantForm.value.label || !newVariantForm.value.contenu) return
  creatingVariant.value = true
  try {
    const variant = await api.post(`/articles/${selectedArticleForVariant.value.id}/variants`, newVariantForm.value) as any

    if (!selectedArticleForVariant.value.variants) selectedArticleForVariant.value.variants = []
    selectedArticleForVariant.value.variants.push(variant)
    selectedVariantId.value = variant.id
    showNewVariantForm.value = false
    newVariantForm.value = { label: '', contenu: '' }
    addToast({ title: 'Variante créée', description: `La variante "${variant.label}" a été créée et sélectionnée.`, variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la création de la variante.', variant: 'destructive' })
  } finally {
    creatingVariant.value = false
  }
}

const deletingVariantId = ref<string | null>(null)
const deleteVariant = async (variant: any) => {
  if (variant.is_default) return
  const isConfirmed = await confirm({
    title: 'Supprimer la variante',
    description: `Êtes-vous sûr de vouloir supprimer "${variant.label}" ? Cette action est irréversible.`,
    confirmText: 'Supprimer la variante',
    cancelText: 'Annuler',
    variant: 'destructive',
  })
  if (!isConfirmed) return
  deletingVariantId.value = variant.id
  try {
    await api.delete(`/variants/${variant.id}`)
    selectedArticleForVariant.value.variants = selectedArticleForVariant.value.variants.filter((v: any) => v.id !== variant.id)
    if (selectedVariantId.value === variant.id) {
       selectedVariantId.value = selectedArticleForVariant.value.variants.find((v: any) => v.is_default)?.id || selectedArticleForVariant.value.variants[0]?.id || ''
    }
    addToast({ title: 'Variante supprimée', description: 'La variante a été supprimée.', variant: 'success' })
  } catch(e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la suppression de la variante.', variant: 'destructive' })
  } finally {
    deletingVariantId.value = null
  }
}

const filteredLibraryArticles = computed(() =>
  libraryArticles.value.filter(a =>
    (articleMode.value === 'CPS' ? a.type?.startsWith('CPS') : a.type === 'RC') &&
    (!articleSearch.value || a.titre.toLowerCase().includes(articleSearch.value.toLowerCase()) || a.code.toLowerCase().includes(articleSearch.value.toLowerCase()))
  )
)
const filteredProjectArticles = computed({
  get: () => projectArticles.value.filter(pa =>
    articleMode.value === 'CPS' ? pa.article?.type?.startsWith('CPS') : pa.article?.type === 'RC'
  ),
  set: (newOrder) => {

    const otherArticles = projectArticles.value.filter(pa =>
      articleMode.value === 'CPS' ? pa.article?.type === 'RC' : pa.article?.type?.startsWith('CPS')
    )
    projectArticles.value = [...newOrder, ...otherArticles]
  }
})


const showAddPrixModal = ref(false)
const prixSearch = ref('')
const selectedCatalogueItem = ref<any>(null)
const addingPrix = ref(false)
const prixForm = ref({ quantite: 1, prix_unitaire_ht: 0 })

const showNewPriceForm = ref(false)
const creatingPrice = ref(false)
const newPriceForm = ref({ designation: '', categorie: '', unite: '', prix_unitaire_ht_defaut: 0 })

const openNewPriceForm = () => {
  newPriceForm.value = {
    designation: prixSearch.value || '',
    categorie: '',
    unite: '',
    prix_unitaire_ht_defaut: 0
  }
  showNewPriceForm.value = true
}

const createNewPrice = async () => {
  creatingPrice.value = true
  try {
    const res = await api.post('/catalogue-articles', newPriceForm.value) as any
    catalogue.value.push(res)
    selectedCatalogueItem.value = res
    prixForm.value.prix_unitaire_ht = res.prix_unitaire_ht_defaut
    showNewPriceForm.value = false
    addToast({ title: 'Prix créé', description: `"${res.designation}" a été créé et sélectionné.`, variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la création du prix dans le catalogue.', variant: 'destructive' })
  } finally {
    creatingPrice.value = false
  }
}

const filteredCatalogue = computed(() =>
  catalogue.value.filter(c => !prixSearch.value || c.designation.toLowerCase().includes(prixSearch.value.toLowerCase()))
)
const brdTotalHT = computed(() => projectPrix.value.reduce((s, pp) => s + parseFloat(pp.quantite || 0) * parseFloat(pp.prix_unitaire_ht || 0), 0))
const brdTotalTVA = computed(() => brdTotalHT.value * (parseFloat(project.value?.taux_tva || 0) / 100))
const brdTotalTTC = computed(() => brdTotalHT.value + brdTotalTVA.value)


const inviteRole = ref('editor')
const inviting = ref(false)
const inviteSuccess = ref(false)


const generatingCps = ref(false)
const generatingRc = ref(false)
const generatingBrd = ref(false)


const formatCurrency = (val: any) =>
  val !== undefined && val !== null ? `${parseFloat(val).toLocaleString('fr-MA', { minimumFractionDigits: 2 })} MAD` : '—'

const formatDate = (d: string) => d ? new Date(d).toLocaleDateString('fr-MA', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'


const loadProject = async () => {
  const res = await api.get(`/projets/${projectId}`)
  project.value = res
  settingsForm.value = { ...res }

  if (settingsForm.value.date_creation) {
    settingsForm.value.date_creation = new Date(settingsForm.value.date_creation).toISOString().split('T')[0]
  }
}

const loadProjectArticles = async () => {
  const proj = await api.get(`/projets/${projectId}/articles`)
  projectArticles.value = Array.isArray(proj) ? proj : []
}

const loadLibraryArticles = async () => {
  const lib = await api.get('/articles')
  libraryArticles.value = Array.isArray(lib) ? lib : []
}

const loadArticles = async () => {
  loadingArticles.value = true
  try { await Promise.all([loadProjectArticles(), loadLibraryArticles()]) } catch(e){}
  loadingArticles.value = false
}

const loadProjectPrix = async () => {
  const prix = await api.get(`/projets/${projectId}/prix`)
  projectPrix.value = Array.isArray(prix) ? prix : []
}

const loadBrd = async () => {
  loadingBrd.value = true
  try {
    const [prix, cat] = await Promise.all([
      api.get(`/projets/${projectId}/prix`),
      api.get('/catalogue-articles'),
    ])
    projectPrix.value = Array.isArray(prix) ? prix : []
    catalogue.value = Array.isArray(cat) ? cat : []
  } catch(e){}
  loadingBrd.value = false
}

const loadCollaborators = async () => {
  loadingCollaborators.value = true
  try {
    const [collabs, invs] = await Promise.all([
      api.get(`/projets/${projectId}/collaborators`),
      api.get(`/projets/${projectId}/invitations`)
    ])
    collaborators.value = Array.isArray(collabs) ? collabs : []
    pendingInvitations.value = Array.isArray(invs) ? invs : []
  } catch(e){}
  loadingCollaborators.value = false
}

const loadExports = async () => {
  loadingExports.value = true
  try {
    const res = await api.get(`/projets/${projectId}/exports`)
    exports.value = Array.isArray(res) ? res : []
  } catch(e){}
  loadingExports.value = false
}

onMounted(async () => {
  try {
    if (route.query.tab) {
      activeTab.value = route.query.tab as string
    }

    await loadProject()
    loading.value = false


    loadArticles()
    loadBrd()
    loadCollaborators()
    loadExports()
  } catch (e) {
    console.error('Project load error:', e)
    loading.value = false
  }
})

const saveSettings = async () => {
  savingSettings.value = true
  settingsSuccess.value = false
  try {
    const payload = { ...settingsForm.value, _method: 'PUT' }
    delete payload.reference
    const res = await api.post(`/projets/${projectId}`, payload)
    project.value = res
    settingsSuccess.value = true
    setTimeout(() => settingsSuccess.value = false, 3000)
  } catch (e) { console.error(e) } finally { savingSettings.value = false }
}

const openVariantModal = (article: any) => {
  selectedArticleForVariant.value = article
  selectedVariantId.value = article.variants?.find((v: any) => v.is_default)?.id || (article.variants?.[0]?.id ?? '')
  showNewVariantForm.value = false
  newVariantForm.value = { label: '', contenu: '' }
  showVariantModal.value = true
}

const confirmAddArticle = async () => {
  if (!selectedArticleForVariant.value) return
  addingArticle.value = true
  try {
    const res = await api.post(`/projets/${projectId}/articles`, {
      article_id: selectedArticleForVariant.value.id,
      article_variant_id: selectedVariantId.value || null,
      ordre: projectArticles.value.length,
    })
    projectArticles.value.push(res as any)
    showVariantModal.value = false
    selectedArticleForVariant.value = null
  } catch (e) { console.error(e) } finally { addingArticle.value = false }
}

const removeArticle = async (id: string) => {
  const backup = [...projectArticles.value]
  projectArticles.value = projectArticles.value.filter(pa => pa.id !== id)
  try {
    await api.delete(`/projets/${projectId}/articles/${id}`)
  } catch (e: any) {
    projectArticles.value = backup
    addToast({ title: 'Erreur', description: 'Échec de la suppression de l\'article : ' + (e?.data?.message || e?.message || 'Erreur inconnue'), variant: 'destructive' })
  }
}

const onArticleReorder = async () => {
  try {
    const articles = projectArticles.value.map((pa, index) => ({
      id: pa.id,
      ordre: index
    }))
    await api.post(`/projets/${projectId}/articles/sync`, { articles })
  } catch (e) { console.error('Failed to sync article order:', e) }
}

const addPrix = async () => {
  if (!selectedCatalogueItem.value) return
  addingPrix.value = true
  try {
    const res = await api.post(`/projets/${projectId}/prix`, {
      prix_catalogue_id: selectedCatalogueItem.value.id,
      quantite: prixForm.value.quantite,
      prix_unitaire_ht: prixForm.value.prix_unitaire_ht,
      ordre: projectPrix.value.length,
      numero_prix: projectPrix.value.length + 1,
    })
    projectPrix.value.push(res as any)
    showAddPrixModal.value = false
    selectedCatalogueItem.value = null
    prixForm.value = { quantite: 1, prix_unitaire_ht: 0 }
    prixSearch.value = ''
  } catch (e) { console.error(e) } finally { addingPrix.value = false }
}

const updatePrix = async (pp: any, updates: any) => {
  Object.assign(pp, updates)
  try {
    await api.post(`/projets/${projectId}/prix/${pp.id}`, { ...updates, _method: 'PUT' })
  } catch (e) { console.error(e) }
}

const removePrix = async (id: string) => {
  const backup = [...projectPrix.value]
  projectPrix.value = projectPrix.value.filter(pp => pp.id !== id)
  try {
    await api.delete(`/projets/${projectId}/prix/${id}`)
  } catch (e) {
    projectPrix.value = backup
    console.error(e)
  }
}


const inviteSearch = ref('')
const searchingUsers = ref(false)
const searchResults = ref<any[]>([])
const showSearchDropdown = ref(false)
const selectedUserToInvite = ref<any>(null)
let searchTimeout: any = null

const onSearchUsers = () => {
  clearSelectedUser()
  if (inviteSearch.value.length < 2) {
    searchResults.value = []
    return
  }
  
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(async () => {
    searchingUsers.value = true
    try {
      const res = await api.get(`/users/search?q=${encodeURIComponent(inviteSearch.value)}`)
      searchResults.value = Array.isArray(res) ? res : []
    } catch (e) {
      console.error(e)
      searchResults.value = []
    } finally {
      searchingUsers.value = false
    }
  }, 300)
}

const selectUserToInvite = (user: any) => {
  selectedUserToInvite.value = user
  inviteSearch.value = user.name
  showSearchDropdown.value = false
}

const clearSelectedUser = () => {
  selectedUserToInvite.value = null
}

const sendInvite = async () => {
  if (!selectedUserToInvite.value) return
  
  inviting.value = true
  inviteSuccess.value = false
  try {
    await api.post(`/projets/${projectId}/invitations`, { 
      email: selectedUserToInvite.value.email, 
      role: inviteRole.value 
    })
    inviteSuccess.value = true
    clearSelectedUser()
    inviteSearch.value = ''
    setTimeout(() => {
      inviteSuccess.value = false
      loadCollaborators()
    }, 2000)
  } catch (e) { console.error(e) } finally { inviting.value = false }
}

const removeCollaborator = async (userId: string) => {
  const confirmed = await confirm({
    title: 'Retirer le collaborateur',
    description: 'Êtes-vous sûr de vouloir retirer ce collaborateur du projet ? Il perdra l\'accès immédiatement.',
    confirmText: 'Retirer',
    variant: 'destructive',
  })
  if (!confirmed) return
  try {
    await api.delete(`/projets/${projectId}/collaborators/${userId}`)
    await loadCollaborators()
    addToast({ title: 'Collaborateur retiré', description: 'Le collaborateur a été retiré de ce projet.', variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec du retrait du collaborateur.', variant: 'destructive' })
  }
}

const removeInvitation = async (invId: string) => {
  const confirmed = await confirm({
    title: 'Révoquer l\'invitation',
    description: 'Êtes-vous sûr de vouloir révoquer cette invitation ? L\'utilisateur ne pourra plus s\'inscrire.',
    confirmText: 'Révoquer',
    variant: 'destructive',
  })
  if (!confirmed) return
  try {
    await api.delete(`/projets/${projectId}/invitations/${invId}`)
    await loadCollaborators()
    addToast({ title: 'Invitation révoquée', description: 'L\'invitation a été révoquée.', variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Erreur', description: e?.data?.message || 'Échec de la révocation de l\'invitation.', variant: 'destructive' })
  }
}

const generateDoc = async (type: 'cps' | 'rc' | 'brd') => {
  if (type === 'cps') generatingCps.value = true
  if (type === 'rc') generatingRc.value = true
  if (type === 'brd') generatingBrd.value = true
  try {
    const res = await api.post(`/projets/${projectId}/exports/${type}`, {})
    await loadExports()
    addToast({ title: 'Succès', description: `${type.toUpperCase()} généré ! Nom du fichier : ${(res as any).filename}`, variant: 'success' })
  } catch (e: any) {
    addToast({ title: 'Échec de la génération', description: e?.data?.message || `Échec de la génération de ${type.toUpperCase()}`, variant: 'destructive' })
  } finally {
    if (type === 'cps') generatingCps.value = false
    generatingRc.value = false
    generatingBrd.value = false
  }
}
</script>
