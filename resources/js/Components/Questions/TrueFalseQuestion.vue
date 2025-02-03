<script setup>
import { ref, watch } from 'vue'
import BaseQuestion from './BaseQuestion.vue'
import { RadioGroup, RadioGroupItem } from '@/Components/shadcn/ui/radio-group'
import { Label } from '@/Components/shadcn/ui/label'
import { Input } from '@/Components/shadcn/ui/input'
import { Textarea } from '@/Components/shadcn/ui/textarea'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

// Initialize with proper structure including correct_answers
const question = ref({
  ...props.modelValue,
  options: {
    explanation: props.modelValue?.options?.explanation || '',
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || [false] // Default to false if not set
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      explanation: newVal.options?.explanation || '',
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || [false]
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {...question.value})
}

const updateCorrect = (value) => {
  question.value.correct_answers = [value === 'true']
  updateQuestion()
}

const updateQuestionText = (value) => {
  question.value.question = value
  updateQuestion()
}
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
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

      <RadioGroup 
        :modelValue="question.correct_answers[0] ? 'true' : 'false'"
        @update:modelValue="updateCorrect"
      >
        <div class="flex items-center gap-3">
          <RadioGroupItem value="true" />
          <Label>True</Label>
        </div>
        <div class="flex items-center gap-3">
          <RadioGroupItem value="false" />
          <Label>False</Label>
        </div>
      </RadioGroup>
      
      <div class="mt-4">
        <Label>Explanation (optional)</Label>
        <Input
          v-model="question.options.explanation"
          placeholder="Explanation (optional)"
          @update:modelValue="v => {
            question.options.explanation = v
            updateQuestion()
          }"
          class="mt-1"
        />
      </div>
    </div>
  </BaseQuestion>
</template> 