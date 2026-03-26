<template>
  <div class="w-full">

    <div :class="cn('inline-flex h-10 items-center justify-start rounded-md bg-muted p-1 text-muted-foreground w-full', listClass)">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        :class="cn(
          'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
          activeTab === tab.value ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/50'
        )"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="mt-2">
      <slot :active-tab="activeTab" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { cn } from '~/lib/utils'

const props = withDefaults(defineProps<{
  tabs: { value: string; label: string }[]
  modelValue?: string
  listClass?: string
}>(), {
  modelValue: undefined,
  listClass: '',
})

const emit = defineEmits(['update:modelValue'])
const activeTab = ref(props.modelValue ?? props.tabs[0]?.value ?? '')

watch(() => props.modelValue, (v) => { if (v) activeTab.value = v })
watch(activeTab, (v) => emit('update:modelValue', v))
</script>
