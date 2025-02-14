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
  <div class="flex items-start gap-4">
    <!-- Answer Space -->
    <div class="w-16 h-6 border-b-2 border-dotted border-muted-foreground flex-shrink-0 mt-1">
      <Badge 
        v-if="question.correct_answers?.[0] !== undefined"
        variant="secondary"
        class="print:hidden"
      >
        {{ question.correct_answers[0] ? 'True' : 'False' }}
      </Badge>
    </div>

    <!-- Question Text -->
    <p class="text-foreground">{{ question.question }}</p>
  </div>
</template> 