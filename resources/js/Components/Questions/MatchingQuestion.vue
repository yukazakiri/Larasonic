<script setup>
import { ref, watch, computed } from 'vue';
import BaseQuestion from './BaseQuestion.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Icon } from '@iconify/vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/shadcn/ui/select'

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
    pairs: props.modelValue?.options?.pairs || [],
    pairPoints: props.modelValue?.options?.pairPoints || [],
    shuffleOptions: props.modelValue?.options?.shuffleOptions ?? true,
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || []
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      pairs: newVal.options?.pairs || [],
      pairPoints: newVal.options?.pairPoints || [],
      shuffleOptions: newVal.options?.shuffleOptions ?? true,
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || []
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {...question.value})
}

const addPair = () => {
  question.value.options.pairs.push({ left: '', right: '' })
  question.value.options.pairPoints.push(1) // Default points for new pair
  question.value.correct_answers.push('0') // Default match to first item
  updateQuestion()
}

const updatePair = (index, field, value) => {
  question.value.options.pairs[index][field] = value
  updateQuestion()
}

const updatePairPoints = (index, value) => {
  question.value.options.pairPoints[index] = parseFloat(value) || 1
  updateQuestion()
}

const updateCorrectMatch = (index, value) => {
  question.value.correct_answers[index] = value
  updateQuestion()
}

const deletePair = (index) => {
  question.value.options.pairs.splice(index, 1)
  question.value.options.pairPoints.splice(index, 1)
  question.value.correct_answers.splice(index, 1)
  updateQuestion()
}

// Get unique right items for dropdowns
const rightItems = computed(() => {
  return question.value.options.pairs.map((pair, index) => ({
    value: String(index),
    label: pair.right || `Right item ${index + 1}`
  }))
})
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
      <div class="grid grid-cols-1 gap-4">
        <div v-for="(pair, index) in question.options.pairs" :key="index" class="grid grid-cols-5 gap-4 items-end">
          <div>
            <Label>Left Item {{ index + 1 }}</Label>
            <Input
              :modelValue="pair.left"
              @update:modelValue="v => updatePair(index, 'left', v)"
              placeholder="Left item"
            />
          </div>
          
          <div>
            <Label>Right Item {{ index + 1 }}</Label>
            <Input
              :modelValue="pair.right"
              @update:modelValue="v => updatePair(index, 'right', v)"
              placeholder="Right item"
            />
          </div>
          
          <div>
            <Label>Points</Label>
            <Input
              type="number"
              min="0.5"
              step="0.5"
              :modelValue="question.options.pairPoints[index]"
              @update:modelValue="v => updatePairPoints(index, v)"
              placeholder="Points"
            />
          </div>
          
          <div>
            <Label>Correct Match</Label>
            <Select 
              :modelValue="question.correct_answers[index]"
              @update:modelValue="v => updateCorrectMatch(index, v)"
            >
              <SelectTrigger>
                <SelectValue :placeholder="`Select match ${index + 1}`" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="item in rightItems" 
                  :key="item.value"
                  :value="item.value"
                >
                  {{ item.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <Button 
            variant="ghost" 
            size="sm"
            @click="deletePair(index)"
            class="text-destructive hover:text-destructive/90 h-8 w-8 p-0"
          >
            <Icon icon="lucide:trash-2" class="h-4 w-4" />
          </Button>
        </div>
      </div>

      <Button variant="outline" @click="addPair" class="w-full">
        <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
        Add Pair
      </Button>
    </div>
  </BaseQuestion>
</template> 