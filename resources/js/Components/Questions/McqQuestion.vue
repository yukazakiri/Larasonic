<script setup>
import { ref, watch } from 'vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Icon } from '@iconify/vue'
import { Label } from '@/Components/shadcn/ui/label'
import { Checkbox } from '@/Components/shadcn/ui/checkbox'
import { Textarea } from '@/Components/shadcn/ui/textarea'
import {
  Card,
  CardContent,
} from '@/Components/shadcn/ui/card'
import { Switch } from '@/Components/shadcn/ui/switch'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const question = ref({
  ...props.modelValue,
  options: {
    choices: props.modelValue.options?.choices || ['', ''],
    multiple: props.modelValue.options?.multiple || false
  },
  correct_answers: props.modelValue.correct_answers || []
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      choices: newVal.options?.choices || ['', ''],
      multiple: newVal.options?.multiple || false
    },
    correct_answers: newVal.correct_answers || []
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', question.value)
}

const addChoice = () => {
  question.value.options.choices.push('')
  updateQuestion()
}

const removeChoice = (index) => {
  if (question.value.options.choices.length <= 2) return
  question.value.options.choices.splice(index, 1)
  // Remove from correct answers if it was selected
  const position = question.value.correct_answers.indexOf(index)
  if (position !== -1) {
    question.value.correct_answers.splice(position, 1)
  }
  // Update indexes in correct_answers array
  question.value.correct_answers = question.value.correct_answers.map(i => {
    if (i > index) return i - 1
    return i
  })
  updateQuestion()
}

const toggleCorrectAnswer = (index) => {
  if (question.value.options.multiple) {
    const position = question.value.correct_answers.indexOf(index)
    if (position !== -1) {
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
  <div class="space-y-4">
    <!-- Question Text -->
    <div>
      <Textarea
        v-model="question.question"
        placeholder="Enter your question here..."
        rows="2"
        class="resize-none"
        @input="updateQuestion"
      />
    </div>

    <!-- Points and Multiple Choice Toggle -->
    <div class="flex items-center justify-between gap-4 pb-2">
      <div class="flex items-center gap-2">
        <Label>Points:</Label>
        <Input
          v-model="question.points"
          type="number"
          min="0.5"
          step="0.5"
          class="w-20"
          @input="updateQuestion"
        />
      </div>
      <div class="flex items-center gap-2">
        <Label for="multiple-correct" class="text-sm">Multiple Correct:</Label>
        <Switch
          v-model:checked="question.options.multiple"
          id="multiple-correct"
          @update:checked="() => {
            question.correct_answers = []
            updateQuestion()
          }"
        />
      </div>
    </div>

    <!-- Choices -->
    <Card class="border-dashed">
      <CardContent class="p-3 space-y-2">
        <div 
          v-for="(choice, index) in question.options.choices" 
          :key="index"
          class="flex items-center gap-2"
        >
          <Checkbox
            :checked="question.correct_answers.includes(index)"
            @update:checked="() => toggleCorrectAnswer(index)"
            :aria-label="'Mark as correct answer'"
          />
          <Input
            v-model="question.options.choices[index]"
            :placeholder="`Choice ${index + 1}`"
            class="flex-1"
            @input="updateQuestion"
          />
          <Button 
            variant="ghost" 
            size="sm"
            @click="removeChoice(index)"
            :disabled="question.options.choices.length <= 2"
            class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
          >
            <Icon icon="lucide:x" class="h-4 w-4" />
          </Button>
        </div>

        <!-- Add Choice Button -->
        <Button 
          variant="ghost" 
          size="sm" 
          @click="addChoice" 
          class="w-full mt-2 border border-dashed"
        >
          <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
          Add Choice
        </Button>
      </CardContent>
    </Card>

    <!-- Quick Help -->
    <div class="text-xs text-muted-foreground">
      <p v-if="question.options.multiple" class="flex items-center gap-1">
        <Icon icon="lucide:info" class="h-3 w-3" />
        Students can select multiple correct answers
      </p>
      <p v-else class="flex items-center gap-1">
        <Icon icon="lucide:info" class="h-3 w-3" />
        Students can select only one correct answer
      </p>
    </div>
  </div>
</template> 