<script setup>
import { ref, watch } from 'vue'
import BaseQuestion from './BaseQuestion.vue'
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
    problem: props.modelValue?.options?.problem || '',
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || ['']
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      problem: newVal.options?.problem || '',
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || ['']
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {...question.value})
}
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
      <Textarea
        placeholder="Problem statement"
        :modelValue="question.options.problem"
        @update:modelValue="v => {
          question.value.options.problem = v
          updateQuestion()
        }"
        class="font-mono text-sm"
        rows="4"
      />
      <Textarea
        placeholder="Step-by-step solution"
        :modelValue="question.correct_answers[0]"
        @update:modelValue="v => {
          question.value.correct_answers = [v]
          updateQuestion()
        }"
        rows="4"
      />
    </div>
  </BaseQuestion>
</template> 