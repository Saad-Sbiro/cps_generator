<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Left brand panel -->
    <div class="hidden lg:flex lg:w-1/2 lg:flex-col lg:justify-between bg-primary p-12">
      <div>
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-white">CPS Generator</span>
        </div>
      </div>

      <div>
        <h2 class="text-3xl font-bold text-white leading-tight">
          Générez des documents de marchés publics marocains en quelques minutes
        </h2>
        <p class="mt-4 text-primary-foreground/70 text-base">
          Génération automatisée de documents CPS, RC et BDP pour les marchés publics marocains. Conçu pour les ingénieurs et les acheteurs publics.
        </p>
        <div class="mt-8 flex gap-6">
          <div>
            <p class="text-2xl font-bold text-white">400+</p>
            <p class="text-sm text-primary-foreground/60">Institutions</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">10k+</p>
            <p class="text-sm text-primary-foreground/60">Documents générés</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-white">3</p>
            <p class="text-sm text-primary-foreground/60">Types de documents</p>
          </div>
        </div>
      </div>

      <div class="flex gap-4 text-xs text-primary-foreground/50">
        <span>© 2026 CPS Generator</span>
        <span>·</span>
        <span>Confidentiel</span>
      </div>
    </div>

    <!-- Right form panel -->
    <div class="flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2">
      <div class="w-full max-w-sm">
        <div class="mb-8 lg:hidden flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <span class="font-bold text-gray-900">CPS Generator</span>
        </div>
        <slot />
      </div>
    </div>
  </div>
</template>
