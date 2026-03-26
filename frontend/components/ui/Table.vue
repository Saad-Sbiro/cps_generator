<template>
  <div :class="cn('relative w-full overflow-auto', $attrs.class ?? '')" v-bind="$attrs">
    <table class="w-full caption-bottom text-sm">
      <slot />
    </table>
  </div>
</template>
<script setup lang="ts">
import { cn } from '~/lib/utils'
defineOptions({ inheritAttrs: false })
</script>
