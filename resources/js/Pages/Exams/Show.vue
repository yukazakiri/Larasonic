<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import { Badge } from '@/Components/shadcn/ui/badge'
import { Separator } from '@/Components/shadcn/ui/separator'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/Components/shadcn/ui/sheet'

// Import preview components directly
import MCQPreview from '@/Components/Questions/Previews/MCQPreview.vue'
import TrueFalsePreview from '@/Components/Questions/Previews/TrueFalsePreview.vue'
import ShortAnswerPreview from '@/Components/Questions/Previews/ShortAnswerPreview.vue'
import EssayPreview from '@/Components/Questions/Previews/EssayPreview.vue'
import FillBlankPreview from '@/Components/Questions/Previews/FillBlankPreview.vue'
import MatchingPreview from '@/Components/Questions/Previews/MatchingPreview.vue'
import ProblemSolvingPreview from '@/Components/Questions/Previews/ProblemSolvingPreview.vue'
import CaseStudyPreview from '@/Components/Questions/Previews/CaseStudyPreview.vue'
import DiagramPreview from '@/Components/Questions/Previews/DiagramPreview.vue'

const props = defineProps({
  exam: {
    type: Object,
    required: true,
  }
})

// Component mapping
const previewComponents = {
  mcq: MCQPreview,
  true_false: TrueFalsePreview,
  short_answer: ShortAnswerPreview,
  essay: EssayPreview,
  fill_blank: FillBlankPreview,
  matching: MatchingPreview,
  problem_solving: ProblemSolvingPreview,
  case_study: CaseStudyPreview,
  diagram: DiagramPreview,
}

const questionTypeLabels = {
  mcq: 'Multiple Choice',
  true_false: 'True/False',
  short_answer: 'Short Answer',
  essay: 'Essay',
  fill_blank: 'Fill-in-the-Blank',
  matching: 'Matching',
  problem_solving: 'Problem Solving',
  case_study: 'Case Study',
  diagram: 'Diagram Labeling'
}

const totalPoints = computed(() => {
  if (!props.exam?.sections) return 0
  return props.exam.sections.reduce((sum, section) => {
    if (!section?.questions) return sum
    return sum + section.questions.reduce((sectionSum, q) => sectionSum + (Number(q.points) || 0), 0)
  }, 0)
})

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const printExam = () => {
  window.print()
}
</script>

<template>
  <AppLayout :title="exam?.title || 'Exam Preview'">
    <!-- Top Bar -->
    <div class="print:hidden border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div class="container flex items-center justify-between h-16">
        <div class="flex items-center gap-4">
          <Button variant="outline" @click="$inertia.visit(route('exams.index'))">
            <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
            Back
          </Button>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" @click="$inertia.visit(route('exams.builder', exam.id))">
            <Icon icon="lucide:edit" class="mr-2 h-4 w-4" />
            Edit
          </Button>
          <Button @click="printExam">
            <Icon icon="lucide:printer" class="mr-2 h-4 w-4" />
            Print
          </Button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container py-8">
      <!-- Exam Paper -->
      <div class="max-w-4xl mx-auto bg-card text-card-foreground rounded-lg border shadow-sm">
        <!-- Exam Header -->
        <div class="p-8 border-b space-y-4">
          <div class="text-center space-y-2">
            <h1 class="text-2xl font-bold">{{ exam?.title }}</h1>
            <p v-if="exam?.description" class="text-muted-foreground">
              {{ exam.description }}
            </p>
          </div>
          
          <div class="flex justify-center gap-8 text-sm">
            <div class="flex items-center gap-2">
              <Icon icon="lucide:clock" class="h-4 w-4" />
              <span>{{ exam?.time_limit ? `${exam.time_limit} minutes` : 'No time limit' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon icon="lucide:star" class="h-4 w-4" />
              <span>{{ totalPoints }} points</span>
            </div>
          </div>
        </div>

        <!-- Questions -->
        <div class="p-8">
          <div class="space-y-12">
            <template v-if="exam?.sections?.length">
              <div 
                v-for="(section, sectionIndex) in exam.sections" 
                :key="section.id"
                class="space-y-6"
              >
                <!-- Section Header -->
                <div class="space-y-2">
                  <h2 class="text-lg font-semibold">
                    Section {{ sectionIndex + 1 }}: {{ section.title }}
                  </h2>
                  <p v-if="section.description" class="text-sm text-muted-foreground">
                    {{ section.description }}
                  </p>
                  <Separator />
                </div>

                <!-- Section Questions -->
                <div class="space-y-8">
                  <div 
                    v-for="(question, questionIndex) in section.questions" 
                    :key="question.id"
                    class="relative pl-8"
                  >
                    <!-- Question Number -->
                    <div class="absolute left-0 top-0 font-medium">
                      {{ questionIndex + 1 }}.
                    </div>

                    <!-- Question Content -->
                    <div class="space-y-3">
                      <div class="flex justify-between items-start gap-4">
                        <Badge variant="outline" class="print:hidden">
                          {{ questionTypeLabels[question.type] }}
                        </Badge>
                        <span class="text-sm text-muted-foreground whitespace-nowrap">
                          {{ question.points }} points
                        </span>
                      </div>

                      <component 
                        :is="previewComponents[question.type]"
                        :question="question"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <div class="max-w-sm mx-auto space-y-4">
                <Icon icon="lucide:file-question" class="h-12 w-12 mx-auto text-muted-foreground" />
                <p class="text-muted-foreground">No questions have been added to this exam yet.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t p-6">
          <div class="text-sm text-muted-foreground flex justify-between">
            <span>Exam ID: {{ exam?.id }}</span>
            <span>Created: {{ formatDate(exam?.created_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
@media print {
  @page {
    margin: 1.5cm;
    size: A4;
  }

  body {
    background: white !important;
  }

  .print\:hidden {
    display: none !important;
  }
}
</style> 