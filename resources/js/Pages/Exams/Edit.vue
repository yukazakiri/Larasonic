<script setup>
import { ref, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import Input from '@/Components/shadcn/ui/input/Input.vue'
import Textarea from '@/Components/shadcn/ui/textarea/Textarea.vue'
import { Icon } from '@iconify/vue'
import { Link, router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

// Import shadcn ui Tabs components (adjust path if needed)
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/shadcn/ui/tabs'
// Import shadcn ui Sheet components (adjust path if needed)
import { Sheet, SheetTrigger, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetClose } from '@/Components/shadcn/ui/sheet'

// Import question components for the form editor
import McqQuestion from '@/Components/Questions/McqQuestion.vue'
import TrueFalseQuestion from '@/Components/Questions/TrueFalseQuestion.vue'
import ShortAnswerQuestion from '@/Components/Questions/ShortAnswerQuestion.vue'
import EssayQuestion from '@/Components/Questions/EssayQuestion.vue'
import FillBlankQuestion from '@/Components/Questions/FillBlankQuestion.vue'
import MatchingQuestion from '@/Components/Questions/MatchingQuestion.vue'
import ProblemSolvingQuestion from '@/Components/Questions/ProblemSolvingQuestion.vue'
import CaseStudyQuestion from '@/Components/Questions/CaseStudyQuestion.vue'
import DiagramQuestion from '@/Components/Questions/DiagramQuestion.vue'
import { Switch } from '@/Components/shadcn/ui/switch'

const props = defineProps({
  exam: Object,
})

// Updated form to include additional exam settings for online exams
const form = useForm({
  title: props.exam.title,
  description: props.exam.description,
  time_limit: props.exam.time_limit,
  passing_score: props.exam.passing_score,
  attempts_limit: props.exam.attempts_limit,
  show_correct_answers: props.exam.show_correct_answers,
  is_public: props.exam.is_public,
  available_from: props.exam.available_from,
  available_to: props.exam.available_to,
  shuffle_questions: props.exam.shuffle_questions,
  shuffle_options: props.exam.shuffle_options,
  allow_review: props.exam.allow_review,
  exam_instructions: props.exam.exam_instructions,
  questions: props.exam.questions || [],
})

// Mapping for question type labels
const questionTypeLabels = {
  mcq: 'Multiple Choice',
  true_false: 'True/False',
  short_answer: 'Short Answer',
  essay: 'Essay',
  fill_blank: 'Fill-in-the-Blank',
  matching: 'Matching',
  problem_solving: 'Problem Solving',
  case_study: 'Case Study',
  diagram: 'Diagram/Labeling'
}

// Mapping for question components
const questionComponents = {
  mcq: McqQuestion,
  true_false: TrueFalseQuestion,
  short_answer: ShortAnswerQuestion,
  essay: EssayQuestion,
  fill_blank: FillBlankQuestion,
  matching: MatchingQuestion,
  problem_solving: ProblemSolvingQuestion,
  case_study: CaseStudyQuestion,
  diagram: DiagramQuestion,
}

// Remove a question from the exam
const removeQuestion = (index) => {
  form.questions.splice(index, 1)
  updatePositions()
}

// Update positions for each question
const updatePositions = () => {
  form.questions = form.questions.map((q, index) => ({
    ...q,
    position: index + 1
  }))
}

// Function to add a new question of specified type
const addQuestion = async (type) => {
  // Base question default structure
  const baseQuestion = {
    id: Date.now(), // Unique id for Vue rendering
    type: type,
    question: '',
    points: 1,
    options: {},
    correct_answers: [],
    position: form.questions.length + 1
  }
  
  // Initialize type-specific options as needed
  switch(type) {
    case 'mcq':
      baseQuestion.options = { choices: ['', ''], multiple: false }
      break
    case 'true_false':
      baseQuestion.options = { choices: ['True', 'False'], explanation: '' }
      break
    case 'diagram':
      baseQuestion.options = { image: null, labels: [] }
      break
    case 'matching':
      baseQuestion.options = { left_list: [], right_list: [] }
      break
    case 'problem_solving':
      baseQuestion.options = { steps: [] }
      break
    case 'case_study':
      baseQuestion.options = { scenario: '', questions: [] }
      break
    case 'fill_blank':
      baseQuestion.options = { blanks: [] }
      break
    case 'essay':
      baseQuestion.options = { word_limit: 0 }
      break
    case 'short_answer':
      baseQuestion.options = { word_limit: 0 }
      break
      
    // Additional initialization for other types can be added here
    default:
      break
  }
  
  form.questions.push(baseQuestion)
  await nextTick()
}

// For the Sheet selection for question types:
const sheetOpen = ref(false)
const questionTypes = [
  { type: 'mcq', label: 'Multiple Choice', icon: 'lucide:list-checks', hint: 'Allows selection from several choices—single or multiple correct answers.' },
  { type: 'true_false', label: 'True/False', icon: 'lucide:check-circle', hint: 'A simple binary choice question.' },
  { type: 'short_answer', label: 'Short Answer', icon: 'lucide:edit', hint: 'Requires a brief text response.' },
  { type: 'essay', label: 'Essay', icon: 'lucide:align-left', hint: 'An extended, open-ended response.' },
  { type: 'fill_blank', label: 'Fill-in-the-Blank', icon: 'lucide:square-dash', hint: 'Students complete a statement with missing words.' },
  { type: 'matching', label: 'Matching', icon: 'lucide:link-2', hint: 'Match items in two corresponding lists.' },
  { type: 'problem_solving', label: 'Problem Solving', icon: 'lucide:calculator', hint: 'Requires step-by-step reasoning to solve.' },
  { type: 'case_study', label: 'Case Study', icon: 'lucide:case-sensitive', hint: 'An in-depth analysis of a scenario.' },
  { type: 'diagram', label: 'Diagram/Labeling', icon: 'lucide:image', hint: 'Label parts of a given diagram.' },
]

const handleAddQuestionFromSheet = (type) => {
  addQuestion(type)
  sheetOpen.value = false
}

// Submit the complete exam form (applies to both settings and questions)
function submitForm() {
  form.put(route('exams.update', props.exam.id), {
    preserveScroll: true,
    // Transform questions to remove temporary IDs
    transform: (data) => ({
      ...data,
      questions: data.questions.map(q => ({
        ...q,
        id: q.id?.toString().startsWith('temp-') ? null : q.id
      }))
    }),
    onSuccess: () => {
      toast.success('Exam updated successfully', {
        description: 'All changes have been saved.',
        duration: 3000
      })
    },
    onError: () => {
      toast.error('Failed to save exam', {
        description: 'Please check your inputs and try again.',
        duration: 5000
      })
    }
  })
}
</script>

<template>
  <AppLayout title="Edit Exam">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('exams.index')" class="mr-4">
          <Button variant="ghost">
            <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
            Back to Exams
          </Button>
        </Link>
        <h2 class="text-2xl font-bold">Edit Exam</h2>
      </div>
      <h1 class="text-gray-500 mt-1">Modify the exam details, options, and questions. <span class="font-medium text-primary">Remember to click "Save Exam"</span> to persist all changes.</h1>
    </template>
    <div class="py-12">
      <div class="container mx-auto max-w-4xl">
        <!-- Wrap the entire editing form in a single form element -->
        <form @submit.prevent="submitForm">
          <!-- Tabs for switching between Settings and Form Editor -->
          <Tabs defaultValue="settings">
            <TabsList class="mb-4 border-b">
              <TabsTrigger value="settings">Settings</TabsTrigger>
              <TabsTrigger value="editor">Form Editor</TabsTrigger>
            </TabsList>
 
            <!-- Settings Tab -->
            <TabsContent value="settings">
              <div class="space-y-4">
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Exam Title</label>
                  <Input id="title" v-model="form.title" class="mt-1 block w-full" placeholder="Enter a clear and descriptive title" />
            <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
                  <p class="text-xs text-muted-foreground">Keep the title short yet descriptive so students know what to expect.</p>
          </div>
          <div>
                  <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                  <Textarea id="description" v-model="form.description" rows="3" class="mt-1 block w-full" placeholder="Provide a brief overview and instructions" />
            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                  <p class="text-xs text-muted-foreground">This description will be shown to potential exam takers—include any important details.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="time_limit" class="block text-sm font-medium text-gray-700">Time Limit (minutes)</label>
                    <Input id="time_limit" v-model="form.time_limit" type="number" class="mt-1 block w-full" placeholder="0 for no limit" />
                    <p class="text-xs text-muted-foreground">Set a time limit for the exam; leave as 0 if unlimited.</p>
                  </div>
                  <div>
                    <label for="passing_score" class="block text-sm font-medium text-gray-700">Passing Score (%)</label>
                    <Input id="passing_score" v-model="form.passing_score" type="number" class="mt-1 block w-full" placeholder="e.g. 70" />
                    <p class="text-xs text-muted-foreground">Define the minimum percentage required to pass.</p>
                  </div>
                  <div>
                    <label for="attempts_limit" class="block text-sm font-medium text-gray-700">Max Attempts</label>
                    <Input id="attempts_limit" v-model="form.attempts_limit" type="number" class="mt-1 block w-full" placeholder="0 for unlimited" />
                    <p class="text-xs text-muted-foreground">Determine the number of allowed attempts; 0 means unlimited.</p>
                  </div>
                </div>
                <div class="space-y-4 mt-4">
                  <label for="exam_instructions" class="block text-sm font-medium text-gray-700">Exam Instructions</label>
                  <Textarea id="exam_instructions" v-model="form.exam_instructions" rows="3" class="mt-1 block w-full" placeholder="Provide detailed exam instructions here" />
                  <p class="text-xs text-muted-foreground">These instructions will be displayed to students before they begin the exam.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 mt-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Shuffle Questions</label>
                      <p class="text-xs text-muted-foreground">Randomize the order of questions each time the exam starts.</p>
                    </div>
                    <Switch v-model:checked="form.shuffle_questions" />
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Shuffle Options</label>
                      <p class="text-xs text-muted-foreground">Randomize the order of answer options for each question to reduce cheating.</p>
                    </div>
                    <Switch v-model:checked="form.shuffle_options" />
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Allow Review</label>
                      <p class="text-xs text-muted-foreground">Permit students to review and change answers before final submission.</p>
                    </div>
                    <Switch v-model:checked="form.allow_review" />
                  </div>
                </div>
              </div>
            </TabsContent>
 
            <!-- Form Editor Tab -->
            <TabsContent value="editor">
              <!-- Button to open Sheet for selecting question type -->
              <div class="mb-4">
                <Sheet v-model:open="sheetOpen">
                  <SheetTrigger asChild>
                    <Button variant="outline" type="button">Add Question</Button>
                  </SheetTrigger>
                  <SheetContent class="p-4">
                    <SheetHeader>
                      <SheetTitle>Select Question Type</SheetTitle>
                      <SheetDescription>Choose the type of question to add. Each type has its own format and settings.</SheetDescription>
                    </SheetHeader>
                    <div class="grid grid-cols-1 gap-4">
                      <div v-for="qt in questionTypes" :key="qt.type" class="flex items-center gap-3 p-2 border rounded">
                        <Icon :icon="qt.icon" class="h-5 w-5" />
                        <div>
                          <div class="font-medium">{{ qt.label }}</div>
                          <div class="text-xs text-muted-foreground">{{ qt.hint }}</div>
                        </div>
                        <Button variant="ghost" class="ml-auto" @click="handleAddQuestionFromSheet(qt.type)">Add</Button>
                      </div>
                    </div>
                    <SheetClose asChild>
                      <Button variant="outline" class="mt-4">Close</Button>
                    </SheetClose>
                  </SheetContent>
                </Sheet>
              </div>
              <!-- Display List of Questions -->
              <div class="space-y-6">
                <div
                  v-for="(question, index) in form.questions"
                  :key="question.id"
                  class="relative bg-background rounded-lg border shadow-sm p-6"
                >
                  <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                      <span class="px-2 py-1 bg-primary/10 rounded text-primary text-sm font-medium">
                        {{ questionTypeLabels[question.type] }} #{{ index + 1 }}
                      </span>
                    </div>
                    <Button variant="ghost" size="sm" type="button" @click="removeQuestion(index)" class="text-destructive hover:text-destructive/90 h-8 w-8 p-0">
                      <Icon icon="lucide:trash-2" class="h-4 w-4" />
                    </Button>
                  </div>
                  <!-- Dynamically render the appropriate question component -->
                  <component :is="questionComponents[question.type]" v-model="form.questions[index]" />
                </div>
          </div>
            </TabsContent>
          </Tabs>
          <!-- Save button; placed below the Tabs -->
          <div class="mt-6">
          <Button type="submit" variant="primary">
            <Icon icon="lucide:save" class="mr-2 h-4 w-4" />
              Save Exam
          </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template> 