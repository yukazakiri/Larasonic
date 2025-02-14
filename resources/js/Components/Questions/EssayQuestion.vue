<script setup>
import { ref, watch } from 'vue'
import BaseQuestion from './BaseQuestion.vue'
import { Textarea } from '@/Components/shadcn/ui/textarea'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Switch } from '@/Components/shadcn/ui/switch'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

// Initialize with proper structure including options
const question = ref({
  ...props.modelValue,
  options: {
    instructions: props.modelValue.options?.instructions || '',
    min_words: props.modelValue.options?.min_words || 0,
    max_words: props.modelValue.options?.max_words || 0,
    show_word_count: props.modelValue.options?.show_word_count ?? true,
    rubric: props.modelValue.options?.rubric || '',
    placeholder: props.modelValue.options?.placeholder || ''
  },
  // Essay questions don't have correct answers in the traditional sense
  correct_answers: ['manual_grading']
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      instructions: newVal.options?.instructions || '',
      min_words: newVal.options?.min_words || 0,
      max_words: newVal.options?.max_words || 0,
      show_word_count: newVal.options?.show_word_count ?? true,
      rubric: newVal.options?.rubric || '',
      placeholder: newVal.options?.placeholder || ''
    },
    correct_answers: ['manual_grading']
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {
    ...question.value,
    options: {
      ...question.value.options
    },
    correct_answers: ['manual_grading'] // Always include this for essay questions
  })
}

const updateField = (field, value) => {
  question.value.options[field] = value
  updateQuestion()
}
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="emit('update:modelValue', $event)">
    <div class="space-y-6">
      <!-- Instructions -->
      <div class="space-y-2">
        <Label for="instructions">Instructions</Label>
        <Textarea
          id="instructions"
          placeholder="Enter detailed instructions for the essay question"
          :modelValue="question.options.instructions"
          @update:modelValue="v => updateField('instructions', v)"
          rows="3"
        />
      </div>

      <!-- Word Limits -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="min-words">Minimum Words</Label>
          <Input
            id="min-words"
            type="number"
            min="0"
            :modelValue="question.options.min_words"
            @update:modelValue="v => updateField('min_words', parseInt(v) || 0)"
            placeholder="0"
          />
        </div>
        <div class="space-y-2">
          <Label for="max-words">Maximum Words</Label>
          <Input
            id="max-words"
            type="number"
            min="0"
            :modelValue="question.options.max_words"
            @update:modelValue="v => updateField('max_words', parseInt(v) || 0)"
            placeholder="0"
          />
        </div>
      </div>

      <!-- Show Word Count Toggle -->
      <div class="flex items-center justify-between">
        <Label for="show-word-count" class="cursor-pointer">Show Word Count to Students</Label>
        <Switch
          id="show-word-count"
          :checked="question.options.show_word_count"
          @update:checked="v => updateField('show_word_count', v)"
        />
      </div>

      <!-- Rubric -->
      <div class="space-y-2">
        <Label for="rubric">Grading Rubric</Label>
        <Textarea
          id="rubric"
          placeholder="Enter grading criteria or rubric details"
          :modelValue="question.options.rubric"
          @update:modelValue="v => updateField('rubric', v)"
          rows="3"
        />
      </div>

      <!-- Answer Placeholder -->
      <div class="space-y-2">
        <Label for="placeholder">Answer Placeholder Text</Label>
        <Input
          id="placeholder"
          :modelValue="question.options.placeholder"
          @update:modelValue="v => updateField('placeholder', v)"
          placeholder="Enter placeholder text that students will see in the answer box"
        />
      </div>
    </div>
  </BaseQuestion>
</template> 