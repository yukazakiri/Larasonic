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
  <div class="space-y-2">
    <div class="flex items-start gap-2">
      <!-- Question Text -->
      <p class="text-foreground flex-grow">{{ question.question }}</p>
      
      <!-- Answer Line -->
      <div class="flex-shrink-0 w-32 relative">
        <div class="border-b-2 border-dotted border-muted-foreground h-6"></div>
        <Badge 
          v-if="question.correct_answers?.length"
          variant="secondary"
          class="absolute top-0 left-0 print:hidden"
        >
          {{ question.correct_answers[0] }}
        </Badge>
      </div>
    </div>
  </div>
</template> 