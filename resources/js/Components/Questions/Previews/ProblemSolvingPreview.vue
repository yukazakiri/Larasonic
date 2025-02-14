<script setup>
defineProps({
  question: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="space-y-4">
    <div class="p-4 bg-muted/30 rounded-lg">
      <p class="text-sm font-mono whitespace-pre-wrap">{{ question.options?.problem }}</p>
    </div>
    <div class="border-2 border-dashed rounded-lg p-4">
      <p class="text-sm text-muted-foreground text-center">Solution Space</p>
    </div>
  </div>
</template> 