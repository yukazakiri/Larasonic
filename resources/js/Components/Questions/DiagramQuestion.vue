<script setup>
import { ref, watch } from 'vue'
import BaseQuestion from './BaseQuestion.vue'
import { Input } from '@/Components/shadcn/ui/input'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'

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
    image: props.modelValue?.options?.image || null,
    imagePreview: props.modelValue?.options?.imagePreview || null,
    labels: props.modelValue?.options?.labels || [],
    ...props.modelValue?.options
  },
  correct_answers: props.modelValue?.correct_answers || []
})

watch(() => props.modelValue, (newVal) => {
  question.value = {
    ...newVal,
    options: {
      image: newVal.options?.image || null,
      imagePreview: newVal.options?.imagePreview || null,
      labels: newVal.options?.labels || [],
      ...newVal.options
    },
    correct_answers: newVal.correct_answers || []
  }
}, { deep: true })

const updateQuestion = () => {
  emit('update:modelValue', {...question.value})
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      // Safely update the image and imagePreview properties
      const result = e && e.target ? e.target.result : null
      if (result !== null) {
        question.value.options.image = result
        question.value.options.imagePreview = URL.createObjectURL(file)
        updateQuestion()
      } else {
        console.error("FileReader result is null")
      }
    }
    reader.readAsDataURL(file)
  }
}

const addLabel = () => {
  question.value.options.labels.push({ text: '', x: 0, y: 0 })
  updateQuestion()
}

const updateLabel = (index, field, value) => {
  question.value.options.labels[index][field] = value
  updateQuestion()
}
</script>

<template>
  <BaseQuestion :modelValue="question" @update:modelValue="updateQuestion">
    <div class="space-y-4">
      <div v-if="question.options.imagePreview" class="relative">
        <img 
          :src="question.options.imagePreview" 
          class="max-h-64 w-full object-contain mb-4 rounded-lg border"
          alt="Diagram"
        />
      </div>
      
      <div class="relative">
        <Input
          type="file"
          accept="image/*"
          @change="handleImageUpload"
          class="cursor-pointer"
        />
      </div>
      
      <div class="space-y-3 mt-4">
        <div v-for="(label, index) in question.options.labels" :key="index" class="flex items-center gap-3">
          <Input
            :modelValue="label.text"
            @update:modelValue="v => updateLabel(index, 'text', v)"
            placeholder="Label text"
            class="flex-1"
          />
        </div>
        
        <Button variant="outline" @click="addLabel" class="w-full">
          <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
          Add Label
        </Button>
      </div>
    </div>
  </BaseQuestion>
</template> 