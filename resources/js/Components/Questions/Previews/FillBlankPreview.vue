<script setup>
defineProps({
  question: {
    type: Object,
    required: true
  }
})

const displayText = computed(() => {
  return question.question?.replace(/\[(.*?)\]/g, '______') || ''
})
</script>

<template>
  <div class="space-y-3">
    <p class="text-sm whitespace-pre-wrap">{{ displayText }}</p>
  </div>
</template> 