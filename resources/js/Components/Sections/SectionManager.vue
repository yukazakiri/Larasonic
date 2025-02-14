<script setup>
import { ref, watch } from 'vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/Components/shadcn/ui/sheet'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/shadcn/ui/card'
import QuestionRepeater from './QuestionRepeater.vue'
import CollapsibleSection from './CollapsibleSection.vue'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const sections = ref(props.modelValue)
const showQuestionTypeSheet = ref(false)

const questionTypes = [
  {
    type: 'mcq',
    label: 'Multiple Choice',
    description: 'Questions with single or multiple correct answers',
    icon: 'lucide:list'
  },
  {
    type: 'true_false',
    label: 'True/False',
    description: 'Simple true or false questions',
    icon: 'lucide:toggle-left'
  },
  {
    type: 'short_answer',
    label: 'Short Answer',
    description: 'Questions requiring brief text responses',
    icon: 'lucide:text'
  },
  {
    type: 'essay',
    label: 'Essay',
    description: 'Long-form written responses',
    icon: 'lucide:file-text'
  },
  {
    type: 'fill_blank',
    label: 'Fill in the Blank',
    description: 'Text with missing words to fill in',
    icon: 'lucide:square-dot'
  },
  {
    type: 'matching',
    label: 'Matching',
    description: 'Match items from two columns',
    icon: 'lucide:git-merge'
  },
  {
    type: 'case_study',
    label: 'Case Study',
    description: 'Analyze and respond to a scenario',
    icon: 'lucide:book-open'
  },
  {
    type: 'problem_solving',
    label: 'Problem Solving',
    description: 'Step-by-step solution to problems',
    icon: 'lucide:lightbulb'
  },
  {
    type: 'diagram',
    label: 'Diagram',
    description: 'Label or analyze diagrams and images',
    icon: 'lucide:image'
  }
]

const addSection = (type) => {
  const questionType = questionTypes.find(qt => qt.type === type)
  sections.value.push({
    title: questionType.label,
    description: questionType.description,
    type: type,
    position: sections.value.length + 1,
    questions: [],
  })
  showQuestionTypeSheet.value = false
  emit('update:modelValue', sections.value)
}

const removeSection = (index) => {
  sections.value.splice(index, 1)
  // Update positions
  sections.value.forEach((section, idx) => {
    section.position = idx + 1
  })
  emit('update:modelValue', sections.value)
}

const updateSection = (index, questions) => {
  sections.value[index].questions = questions
  emit('update:modelValue', sections.value)
}

watch(() => props.modelValue, (newVal) => {
  sections.value = newVal
}, { deep: true })
</script>

<template>
  <div class="space-y-6">
    <!-- Add Section Button -->
    <Sheet v-model:open="showQuestionTypeSheet">
      <SheetTrigger asChild>
        <Button variant="outline" class="w-full">
          <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
          Add Question Type Section
        </Button>
      </SheetTrigger>
      <SheetContent side="right" class="sm:max-w-xl">
        <SheetHeader>
          <SheetTitle>Add Question Section</SheetTitle>
          <SheetDescription>
            Choose a question type to create a new section
          </SheetDescription>
        </SheetHeader>
        <div class="grid gap-4 py-4">
          <Button
            v-for="type in questionTypes"
            :key="type.type"
            variant="ghost"
            class="justify-start"
            @click="addSection(type.type)"
          >
            <Icon :icon="type.icon" class="mr-2 h-4 w-4" />
            <div class="flex flex-col items-start">
              <span class="font-medium">{{ type.label }}</span>
              <span class="text-xs text-muted-foreground">{{ type.description }}</span>
            </div>
          </Button>
        </div>
      </SheetContent>
    </Sheet>

    <!-- Sections List -->
    <div class="space-y-6">
      <div v-for="(section, index) in sections" :key="section.id" class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium">{{ section.title }}</h3>
            <p class="text-sm text-muted-foreground">{{ section.description }}</p>
          </div>
          <Button
            variant="ghost"
            size="sm"
            @click="removeSection(index)"
            class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
          >
            <Icon icon="lucide:trash-2" class="h-4 w-4" />
          </Button>
        </div>
        
        <QuestionRepeater
          v-model="section.questions"
          :question-type="section.type"
          @update:modelValue="questions => updateSection(index, questions)"
        />
      </div>
    </div>
  </div>
</template> 