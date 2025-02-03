<script setup>
defineProps({
  question: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="space-y-3">
    <p class="text-sm">{{ question.question }}</p>
    <div class="border rounded-lg border-dashed p-4 bg-muted/30">
      <p class="text-sm text-muted-foreground text-center">Essay Response Area</p>
    </div>
  </div>
</template> 