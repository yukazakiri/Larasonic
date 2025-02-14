<script setup>
import { ref, watch } from 'vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Icon } from '@iconify/vue'
import { Label } from '@/Components/shadcn/ui/label'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

// Initialize question ref with a fallback to an empty object
const question = ref(props.modelValue || {})

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  question.value = { ...newVal }
}, { deep: true })

const updateField = (field, value) => {
  question.value = { ...question.value, [field]: value }
  emit('update:modelValue', question.value)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Question Header -->
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <Label class="text-base font-medium">Question Text</Label>
        <Input
          v-model="question.question"
          placeholder="Enter your question"
          class="mt-2"
          @update:modelValue="v => updateField('question', v)"
        />
      </div>
      
      <div class="flex items-center gap-2 ml-4">
        <Input
          v-model.number="question.points"
          type="number"
          min="0.5"
          max="10"
          step="0.5"
          class="w-20"
          @update:modelValue="v => updateField('points', v)"
        />
        <span class="text-sm text-muted-foreground">points</span>
      </div>
    </div>

    <!-- Question Content -->
    <div class="space-y-4 pt-4 border-t">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.question-card {
  animation-duration: 0.3s;
  animation-timing-function: ease-out;
}
</style> 