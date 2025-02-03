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
    <!-- Case Study Text -->
    <div class="p-4 bg-muted/30 rounded-lg">
      <p class="text-sm whitespace-pre-wrap">{{ question.options?.case_text }}</p>
    </div>
    
    <!-- Question Text -->
    <div class="space-y-2">
      <p class="text-sm font-medium">Question:</p>
      <p class="text-sm">{{ question.question }}</p>
    </div>

    <!-- Answer Area -->
    <div class="border-2 border-dashed rounded-lg p-4">
      <p class="text-sm text-muted-foreground text-center">Response Area</p>
    </div>
  </div>
</template> 