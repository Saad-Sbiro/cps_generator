<template>
  <div class="p-6 pt-0 flex flex-col gap-2 text-center sm:text-left">
    <h2 class="text-lg font-semibold leading-none tracking-tight"><slot name="title" /></h2>
    <p v-if="$slots.description" class="text-sm text-muted-foreground"><slot name="description" /></p>
  </div>
</template>
