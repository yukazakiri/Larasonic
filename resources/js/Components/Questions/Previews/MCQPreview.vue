<script setup>
import { Badge } from '@/Components/shadcn/ui/badge'

defineProps({
  question: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="space-y-3">
    <!-- Question Text -->
    <p class="text-foreground">{{ question.question }}</p>
    
    <!-- Options in horizontal layout -->
    <div class="flex flex-wrap gap-x-6 gap-y-2 pl-6">
      <div 
        v-for="(choice, index) in question.options?.choices" 
        :key="index"
        class="flex items-center gap-2"
      >
        <span class="font-medium text-foreground">{{ String.fromCharCode(65 + index) }}:</span>
        <span class="text-foreground">{{ choice }}</span>
        <Badge 
          v-if="question.correct_answers?.includes(index)"
          variant="secondary"
          class="ml-1 print:hidden"
        >
          ✓
        </Badge>
      </div>
    </div>
  </div>
</template> 