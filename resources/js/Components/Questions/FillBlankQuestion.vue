<script setup>
import { ref, watch, computed } from 'vue';
import BaseQuestion from './BaseQuestion.vue'
import { Input } from '@/Components/shadcn/ui/input'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
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
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || []
})

// Parse the question text for blanks
const blanks = computed(() => {
  if (!question.value.question) return []
  const regex = /\[(.*?)\]/g
  const matches = []
  let match
  while ((match = regex.exec(question.value.question)) !== null) {
    matches.push({
      text: match[1],
      index: match.index
    })
  }
  return matches
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || []
  }
}, { deep: true })

const updateQuestion = () => {
  // Auto-populate correct answers from brackets
  const regex = /\[(.*?)\]/g
  const answers = []
  let match
  while ((match = regex.exec(question.value.question)) !== null) {
    answers.push(match[1].trim())
  }
  question.value.correct_answers = answers
  emit('update:modelValue', {...question.value})
}

// Helper to display question with blanks
const displayText = computed(() => {
  return question.value.question?.replace(/\[(.*?)\]/g, '______') || ''
})
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
      <div>
        <Label>Question Text with Blanks</Label>
        <Textarea
          v-model="question.question"
          @update:modelValue="updateQuestion"
          placeholder="Enter text with blanks marked in brackets. Example: 'The [capital] of France is [Paris].'"
          rows="3"
        />
      </div>

      <div v-if="blanks.length > 0" class="space-y-4">
        <Label>Detected Blanks ({{ blanks.length }})</Label>
        <div class="p-4 bg-muted rounded-lg">
          <p class="mb-2 font-medium">Preview:</p>
          <p class="whitespace-pre-wrap">{{ displayText }}</p>
        </div>

        <div class="space-y-3">
          <div v-for="(blank, index) in blanks" :key="index" class="flex items-center gap-2">
            <Label>Blank {{ index + 1 }} Correct Answer:</Label>
            <Input
              :modelValue="question.correct_answers?.[index] || ''"
              @update:modelValue="v => {
                question.correct_answers[index] = v
                updateQuestion()
              }"
              placeholder="Correct answer"
              class="flex-1"
            />
          </div>
        </div>
      </div>

      <p class="text-sm text-muted-foreground">
        Tip: Wrap blank words in square brackets like [this]. The system will automatically detect them.
      </p>
    </div>
  </BaseQuestion>
</template> 