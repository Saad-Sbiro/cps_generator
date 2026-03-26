<template>
  <div :class="cn('shrink-0 bg-border', orientation === 'vertical' ? 'h-full w-[1px]' : 'h-[1px] w-full', $attrs.class ?? '')" v-bind="$attrs" />
</template>
<script setup lang="ts">
import { cn } from '~/lib/utils'
defineOptions({ inheritAttrs: false })
withDefaults(defineProps<{ orientation?: 'horizontal' | 'vertical' }>(), { orientation: 'horizontal' })
</script>
