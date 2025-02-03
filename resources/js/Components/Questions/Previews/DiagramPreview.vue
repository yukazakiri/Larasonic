<script setup>
defineProps({
  question: Object
})
</script>

<template>
  <div class="space-y-4">
    <img 
      v-if="question.options?.image" 
      :src="asset(question.options.image)" 
      class="max-h-64 mx-auto mb-4"
      alt="Diagram"
    />
    <div class="grid grid-cols-2 gap-4">
      <div v-for="(label, index) in question.options?.labels" :key="index">
        <span class="text-sm font-medium">{{ label.text }}</span>
      </div>
    </div>
  </div>
</template> 