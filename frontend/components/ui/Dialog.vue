<template>
  <Teleport to="body">
    <Transition name="dialog-fade">
      <div v-if="open" :class="cn('fixed inset-0 flex items-center justify-center', zIndexClass || 'z-50')">
        <div class="fixed inset-0 bg-black/50" @click="$emit('update:open', false)" />
        <div
          :class="cn(
            'relative w-full max-w-lg rounded-lg border bg-background shadow-lg',
            $attrs.class ?? ''
          )"
          v-bind="$attrs"
        >
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { cn } from '~/lib/utils'
defineOptions({ inheritAttrs: false })
defineProps<{ open: boolean, zIndexClass?: string }>()
defineEmits(['update:open'])
</script>

<style scoped>
.dialog-fade-enter-active, .dialog-fade-leave-active { transition: opacity 0.2s; }
.dialog-fade-enter-from, .dialog-fade-leave-to { opacity: 0; }
</style>
