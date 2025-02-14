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
  <div class="space-y-4">
    <p class="text-foreground mb-4">{{ question.question }}</p>
    
    <div class="grid grid-cols-[1fr_auto_1fr] gap-4">
      <!-- Left Column (Numbers) -->
      <div class="space-y-3">
        <div 
          v-for="(pair, index) in question.options?.pairs" 
          :key="`left-${index}`"
          class="flex items-center gap-2"
        >
          <span class="text-sm font-medium text-foreground">{{ index + 1 }}.</span>
          <div class="p-2 rounded border bg-card">
            <p class="text-foreground">{{ pair.left }}</p>
          </div>
        </div>
      </div>

      <!-- Middle Column (Answer Spaces) -->
      <div class="space-y-3 flex flex-col items-center">
        <div 
          v-for="(_, index) in question.options?.pairs" 
          :key="`answer-${index}`"
          class="w-12 h-8 border-b-2 border-dotted border-muted-foreground relative"
        >
          <Badge 
            v-if="question.correct_answers?.[index] !== undefined"
            variant="secondary"
            class="absolute inset-0 flex items-center justify-center print:hidden"
          >
            {{ String.fromCharCode(65 + question.correct_answers[index]) }}
          </Badge>
        </div>
      </div>

      <!-- Right Column (Letters) -->
      <div class="space-y-3">
        <div 
          v-for="(pair, index) in question.options?.pairs" 
          :key="`right-${index}`"
          class="flex items-center gap-2"
        >
          <span class="text-sm font-medium text-foreground">{{ String.fromCharCode(65 + index) }}.</span>
          <div class="p-2 rounded border bg-card">
            <p class="text-foreground">{{ pair.right }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template> 