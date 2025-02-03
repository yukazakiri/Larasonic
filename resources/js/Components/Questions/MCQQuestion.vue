<script setup>
import BaseQuestion from './BaseQuestion.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Icon } from '@iconify/vue'
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Object
})

const emit = defineEmits(['update:modelValue'])

// Create a reactive question object
const question = ref({ ...props.modelValue })

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  question.value = { ...newVal }
}, { deep: true })

// Initialize options if they don't exist
if (!question.value.options?.choices) {
  question.value.options = {
    choices: ['', ''],
    multiple: false
  }
}

const updateQuestion = () => {
  emit('update:modelValue', { ...question.value })
}

const addOption = () => {
  question.value.options.choices.push('')
  updateQuestion()
}

const removeOption = (index) => {
  question.value.options.choices.splice(index, 1)
  updateQuestion()
}

const updateOption = (index, value) => {
  question.value.options.choices[index] = value
  updateQuestion()
}

const toggleCorrect = (index) => {
  if (!question.value.correct_answers) {
    question.value.correct_answers = []
  }
  
  if (question.value.options.multiple) {
    const position = question.value.correct_answers.indexOf(index)
    if (position > -1) {
      question.value.correct_answers.splice(position, 1)
    } else {
      question.value.correct_answers.push(index)
    }
  } else {
    question.value.correct_answers = [index]
  }
  updateQuestion()
}
</script>

<template>
  <BaseQuestion v-bind="$attrs">
    <div class="space-y-4">
      <!-- Question Text -->
      <div class="space-y-2">
        <label class="text-sm font-medium">Question Text</label>
        <Input
          v-model="question.question"
          placeholder="Enter your question"
          class="w-full"
          @input="updateQuestion"
        />
      </div>

      <!-- Options -->
      <div class="space-y-3">
        <label class="text-sm font-medium">Answer Options</label>
        <div v-for="(choice, index) in question.options.choices" 
             :key="index" 
             class="flex items-center gap-2">
          <div class="flex-none w-8 h-8 flex items-center justify-center">
            <input 
              :type="question.options.multiple ? 'checkbox' : 'radio'"
              :name="'correct-'+question.id"
              :checked="question.correct_answers?.includes(index)"
              @change="toggleCorrect(index)"
              class="h-4 w-4"
            >
          </div>
          <Input
            :value="choice"
            @input="e => updateOption(index, e.target.value)"
            :placeholder="`Option ${index + 1}`"
            class="flex-1"
          />
          <Button 
            v-if="question.options.choices.length > 2"
            variant="ghost" 
            size="sm"
            @click="removeOption(index)"
          >
            <Icon icon="lucide:x" class="h-4 w-4 text-red-500" />
          </Button>
        </div>

        <Button 
          variant="outline" 
          @click="addOption"
          class="w-full"
        >
          <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
          Add Option
        </Button>
      </div>

      <!-- Multiple Choice Toggle -->
      <div class="flex items-center gap-2 pt-2">
        <input 
          type="checkbox"
          :checked="question.options.multiple"
          @change="e => { 
            question.options.multiple = e.target.checked
            question.correct_answers = []
            updateQuestion()
          }"
          class="h-4 w-4"
        />
        <label class="text-sm">Allow multiple correct answers</label>
      </div>
    </div>
  </BaseQuestion>
</template> 