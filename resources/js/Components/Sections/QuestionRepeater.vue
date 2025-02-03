<script setup>
import { ref, watch } from 'vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import McqQuestion from '@/Components/Questions/McqQuestion.vue'
import TrueFalseQuestion from '@/Components/Questions/TrueFalseQuestion.vue'
import ShortAnswerQuestion from '@/Components/Questions/ShortAnswerQuestion.vue'
import EssayQuestion from '@/Components/Questions/EssayQuestion.vue'
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/Components/shadcn/ui/collapsible'
import { Badge } from '@/Components/shadcn/ui/badge'
import FillBlankQuestion from '@/Components/Questions/FillBlankQuestion.vue'
import MatchingQuestion from '@/Components/Questions/MatchingQuestion.vue'
import CaseStudyQuestion from '@/Components/Questions/CaseStudyQuestion.vue'
import ProblemSolvingQuestion from '@/Components/Questions/ProblemSolvingQuestion.vue'
import DiagramQuestion from '@/Components/Questions/DiagramQuestion.vue'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  },
  questionType: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const questions = ref(props.modelValue)

const questionComponents = {
  mcq: McqQuestion,
  true_false: TrueFalseQuestion,
  short_answer: ShortAnswerQuestion,
  essay: EssayQuestion,
  fill_blank: FillBlankQuestion,
  matching: MatchingQuestion,
  case_study: CaseStudyQuestion,
  problem_solving: ProblemSolvingQuestion,
  diagram: DiagramQuestion,
}

watch(() => props.modelValue, (newVal) => {
  questions.value = newVal
}, { deep: true })

const addQuestion = () => {
  const newQuestion = {
    type: props.questionType,
    question: '',
    points: 1,
    options: {},
    correct_answers: [],
    position: questions.value.length + 1,
  }

  // Initialize type-specific options and correct_answers
  switch(props.questionType) {
    case 'mcq':
      newQuestion.options = { choices: ['', ''], multiple: false }
      newQuestion.correct_answers = []
      break
    case 'true_false':
      newQuestion.options = { choices: ['True', 'False'] }
      newQuestion.correct_answers = [false]
      break
    case 'short_answer':
      newQuestion.correct_answers = ['']
      break
    case 'essay':
      newQuestion.correct_answers = ['manual_grading']
      break
    case 'fill_blank':
      newQuestion.correct_answers = []
      break
    case 'matching':
      newQuestion.options = { pairs: [] }
      newQuestion.correct_answers = []
      break
    case 'case_study':
      newQuestion.options = { case_text: '' }
      newQuestion.correct_answers = ['']
      break
    case 'problem_solving':
      newQuestion.options = { problem: '' }
      newQuestion.correct_answers = ['']
      break
    case 'diagram':
      newQuestion.options = { image: null, labels: [] }
      newQuestion.correct_answers = []
      break
  }

  questions.value.push(newQuestion)
  emit('update:modelValue', questions.value)
}

const removeQuestion = (index) => {
  questions.value.splice(index, 1)
  questions.value.forEach((q, idx) => {
    q.position = idx + 1
  })
  emit('update:modelValue', questions.value)
}

const updateQuestion = (index, question) => {
  questions.value[index] = question
  emit('update:modelValue', questions.value)
}
</script>

<template>
  <div class="space-y-4">
    <!-- Add Question Button -->
   

    <!-- Questions List -->
    <div class="space-y-3">
      <Collapsible
        v-for="(question, index) in questions"
        :key="question.id"
        class="border rounded-lg bg-background"
      >
        <div class="p-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Badge variant="outline">Q{{ index + 1 }}</Badge>
            <span class="text-sm font-medium truncate max-w-[300px]">
              {{ question.question || 'New Question' }}
            </span>
            <Badge variant="secondary">{{ question.points }} pts</Badge>
          </div>
          <div class="flex items-center gap-2">
            <Button
              variant="ghost"
              size="sm"
              @click="removeQuestion(index)"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
            >
              <Icon icon="lucide:trash-2" class="h-4 w-4" />
            </Button>
            <CollapsibleTrigger asChild>
              <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                <Icon icon="lucide:chevron-down" class="h-4 w-4" />
              </Button>
            </CollapsibleTrigger>
          </div>
        </div>
        <CollapsibleContent class="px-3 pb-3">
          <component
            :is="questionComponents[questionType]"
            v-model="questions[index]"
            @update:modelValue="q => updateQuestion(index, q)"
          />
        </CollapsibleContent>
      </Collapsible>
    </div>
    <Button variant="outline" class="w-full" @click="addQuestion">
      <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
      Add Question
    </Button>
  </div>
</template> 