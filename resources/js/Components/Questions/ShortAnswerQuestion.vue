<script setup>
import { ref, watch } from 'vue';
import BaseQuestion from './BaseQuestion.vue'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Textarea } from '@/Components/shadcn/ui/textarea'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

// Initialize with proper structure
const question = ref({
  ...props.modelValue,
  options: {
    explanation: props.modelValue?.options?.explanation || '',
    case_sensitive: props.modelValue?.options?.case_sensitive || false,
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || ['']
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      explanation: newVal.options?.explanation || '',
      case_sensitive: newVal.options?.case_sensitive || false,
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || ['']
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {...question.value})
}

const updateQuestionText = (value) => {
  question.value.question = value
  updateQuestion()
}

const handleAnswerUpdate = (v) => {
  question.value.correct_answers = [v]
  updateQuestion()
}

const updateExplanation = (value) => {
  question.value.options.explanation = value
  updateQuestion()
}
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
      <!-- Question Text -->
      <div class="space-y-2">
        <Label>Question Text</Label>
        <Textarea
          v-model="question.question"
          placeholder="Enter your question"
          rows="3"
          @update:modelValue="updateQuestionText"
          class="resize-none"
        />
      </div>

      <!-- Correct Answer -->
      <div class="space-y-2">
        <Label for="correct-answer">Correct Answer</Label>
        <Input
          id="correct-answer"
          :modelValue="question.correct_answers[0]"
          @update:modelValue="handleAnswerUpdate"
          placeholder="Enter the correct answer"
          class="mt-1"
        />
      </div>

      <!-- Explanation -->
      <div class="space-y-2">
        <Label for="explanation">Explanation (optional)</Label>
        <Input
          id="explanation"
          :modelValue="question.options.explanation"
          @update:modelValue="updateExplanation"
          placeholder="Add an explanation for this answer (optional)"
          class="mt-1"
        />
      </div>
    </div>
  </BaseQuestion>
</template> 