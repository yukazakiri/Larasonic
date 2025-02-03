<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: Number,
    default: 0
  }
})

const percentage = computed(() => {
  return Math.min(Math.max(props.value, 0), 100)
})
</script>

<template>
  <div class="relative h-4 w-full overflow-hidden rounded-full bg-secondary">
    <div
      class="h-full w-full flex-1 bg-primary transition-all"
      :style="{ transform: `translateX(-${100 - percentage}%)` }"
    />
  </div>
</template>
