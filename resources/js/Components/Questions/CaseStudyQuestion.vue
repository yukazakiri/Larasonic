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
    case_text: props.modelValue?.options?.case_text || '',
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || ['']
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      case_text: newVal.options?.case_text || '',
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
        placeholder="Case study text"
        :modelValue="question.options.case_text"
        @update:modelValue="v => {
          question.value.options.case_text = v
          updateQuestion()
        }"
        rows="4"
      />
      <Textarea
        placeholder="Expected analysis and conclusions"
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