<script setup>
import { ref, watch } from 'vue'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'

const props = defineProps({
  modelValue: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'Color'
  }
})

const emit = defineEmits(['update:modelValue'])

const color = ref(props.modelValue)

watch(() => props.modelValue, (newValue) => {
  color.value = newValue
})

watch(color, (newValue) => {
  if (newValue.match(/^#[0-9A-F]{6}$/i)) {
    emit('update:modelValue', newValue)
  }
})
</script>

<template>
  <div class="grid gap-2">
    <Label>{{ label }}</Label>
    <div class="flex gap-2">
      <div 
        class="size-10 rounded-md border"
        :style="{ backgroundColor: modelValue }"
      />
      <Input
        v-model="color"
        type="text"
        placeholder="#000000"
        class="w-[100px]"
        pattern="^#[0-9A-Fa-f]{6}$"
      />
      <Input
        v-model="color"
        type="color"
        class="size-10 cursor-pointer p-0 [&::-webkit-color-swatch-wrapper]:p-0 [&::-webkit-color-swatch]:border-none"
      />
    </div>
  </div>
</template> 