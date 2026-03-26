<template>
  <div class="fixed bottom-0 right-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-4 sm:right-4 sm:top-auto sm:flex-col md:max-w-[420px] gap-2 pointer-events-none">
    <TransitionGroup name="toast">
      <div 
        v-for="toast in toasts" 
        :key="toast.id"
        class="group pointer-events-auto relative flex w-full items-center justify-between space-x-2 overflow-hidden rounded-lg border p-4 pr-8 shadow-xl transition-all"
        :class="[
          toast.variant === 'destructive' ? 'border-destructive bg-destructive text-destructive-foreground' : 
          toast.variant === 'success' ? 'border-green-500 bg-green-50 text-green-900 border-l-4 border-l-green-600' :
          'border-border bg-white text-gray-900 border-l-4 border-l-primary'
        ]"
      >
        <div class="grid gap-1">
          <div v-if="toast.title" class="text-sm font-semibold">{{ toast.title }}</div>
          <div v-if="toast.description" class="text-sm opacity-90">{{ toast.description }}</div>
        </div>
        <button 
          @click="removeToast(toast.id!)"
          class="absolute right-2 top-2 rounded-md p-1 opacity-50 transition-opacity hover:opacity-100 focus:opacity-100 focus:outline-none"
          :class="toast.variant === 'destructive' ? 'text-destructive-foreground hover:bg-destructive/80' : 'text-gray-500 hover:bg-gray-100'"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useToast } from '~/composables/useToast'
const { toasts, removeToast } = useToast()
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-10px);
}
</style>
