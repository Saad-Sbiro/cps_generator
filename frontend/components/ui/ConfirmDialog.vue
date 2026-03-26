<template>
  <UiDialog :open="isOpen" @update:open="handleCancel" z-index-class="z-[100]">
    <div class="p-6">
      <div class="flex items-start gap-4">

        <div 
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
          :class="options.variant === 'destructive' ? 'bg-red-100' : 'bg-primary/10'"
        >
          <svg 
            class="h-5 w-5" 
            :class="options.variant === 'destructive' ? 'text-red-600' : 'text-primary'"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
          >
            <path 
              v-if="options.variant === 'destructive'"
              stroke-linecap="round" stroke-linejoin="round" 
              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" 
            />
            <path 
              v-else
              stroke-linecap="round" stroke-linejoin="round" 
              d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" 
            />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-base font-semibold text-gray-900">{{ options.title }}</h3>
          <p class="mt-1.5 text-sm text-muted-foreground leading-relaxed">{{ options.description }}</p>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <UiButton variant="outline" @click="handleCancel">{{ options.cancelText }}</UiButton>
        <UiButton 
          :variant="options.variant === 'destructive' ? 'destructive' : 'default'" 
          @click="handleConfirm"
        >
          {{ options.confirmText }}
        </UiButton>
      </div>
    </div>
  </UiDialog>
</template>

<script setup lang="ts">
import { useConfirm } from '~/composables/useConfirm'
const { isOpen, options, handleConfirm, handleCancel } = useConfirm()
</script>
