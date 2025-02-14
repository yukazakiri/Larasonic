<script setup>
import { Icon } from '@iconify/vue'
import { Button } from '@/Components/shadcn/ui/button'

const questionTypes = [
  { type: 'mcq', label: 'Multiple Choice', icon: 'lucide:list-checks' },
  { type: 'true_false', label: 'True/False', icon: 'lucide:check-circle' },
  { type: 'short_answer', label: 'Short Answer', icon: 'lucide:edit' },
  { type: 'essay', label: 'Essay', icon: 'lucide:align-left' },
  { type: 'fill_blank', label: 'Fill-in-the-Blank', icon: 'lucide:square-dash' },
  { type: 'matching', label: 'Matching', icon: 'lucide:link-2' },
  { type: 'problem_solving', label: 'Problem Solving', icon: 'lucide:calculator' },
  { type: 'case_study', label: 'Case Study', icon: 'lucide:case-sensitive' },
  { type: 'diagram', label: 'Diagram/Labeling', icon: 'lucide:image' },
]

const emit = defineEmits(['add-question'])

const handleAddQuestion = (type) => {
  emit('add-question', type)
}
</script>

<template>
  <div class="w-64 p-4 border-l bg-background">
    <h3 class="text-sm font-semibold mb-4">Add Question</h3>
    <div class="space-y-2">
      <Button
        v-for="type in questionTypes"
        :key="type.type"
        variant="ghost"
        class="w-full justify-start"
        @click="handleAddQuestion(type.type)"
      >
        <Icon :icon="type.icon" class="mr-2 h-4 w-4 text-primary" />
        <span class="text-foreground">{{ type.label }}</span>
      </Button>
    </div>
  </div>
</template> 