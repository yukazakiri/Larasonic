<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch, computed } from 'vue'
import { toast } from 'vue-sonner'
import { Icon } from '@iconify/vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Link } from '@inertiajs/vue3'
import SectionManager from '@/Components/Sections/SectionManager.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/shadcn/ui/card'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/Components/shadcn/ui/tooltip'

const props = defineProps({
  exam: {
    type: Object,
    required: true
  },
  isPublished: Boolean
})

// Create a reactive reference for sections
const sections = ref(props.exam.sections || [])
const isDirty = ref(false) // Track if there are unsaved changes

// Initialize form with default values and computed sections
const form = useForm({
  title: props.exam.title || '',
  description: props.exam.description || '',
  sections: sections.value,
})

// Computed values for stats
const totalQuestions = computed(() => {
  return sections.value.reduce((sum, s) => sum + (s.questions?.length || 0), 0)
})

// Watch for exam changes
watch(() => props.exam, (newExam) => {
  sections.value = newExam.sections || []
  form.title = newExam.title || ''
  form.description = newExam.description || ''
  form.sections = sections.value
  isDirty.value = false
}, { deep: true })

// Watch for section changes
watch(sections, (newSections) => {
  form.sections = newSections
  isDirty.value = true // Mark as dirty when sections change
}, { deep: true })

// Save exam with debounce
let saveTimeout = null
const saveExam = async (isDraft = true) => {
  if (saveTimeout) clearTimeout(saveTimeout)
  
  try {
    await form.put(route('exams.update', props.exam.id), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Changes saved', {
          description: isDraft ? 'Your progress has been saved as a draft' : 'All changes have been saved',
        })
        isDirty.value = false
      },
      onError: (errors) => {
        console.error('Save errors:', errors)
        toast.error('Failed to save changes', {
          description: 'Please check your inputs and try again',
        })
      }
    })
  } catch (error) {
    console.error('Save error:', error)
    toast.error('An error occurred while saving')
  }
}

const handleNext = async () => {
  if (isDirty.value) {
    await saveExam(false)
  }
  router.visit(route('exams.options', { exam: props.exam.id }))
}

// Handle section updates
const handleSectionUpdate = (newSections) => {
  sections.value = newSections
  isDirty.value = true // Mark as dirty, but don't save automatically
}
</script>

<template>
  <AppLayout title="Exam Builder">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Build Exam: {{ form.title }}
        </h2>
        <div class="flex items-center gap-4">
          <Button 
            variant="outline" 
            @click="saveExam(true)"
            :disabled="!isDirty"
          >
            <Icon 
              :icon="isDirty ? 'lucide:save' : 'lucide:check'" 
              class="mr-2 h-4 w-4"
            />
            {{ isDirty ? 'Save Draft' : 'Saved' }}
          </Button>
          
          <Button variant="outline" asChild>
            <Link :href="route('exams.index')">Cancel</Link>
          </Button>
          
          <Button @click="handleNext">
            Next: Exam Settings
            <Icon icon="lucide:arrow-right" class="ml-2 h-4 w-4" />
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
      <!-- Progress Card -->
      <Card>
        <CardContent class="py-6">
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium">Building Exam</span>
              <span class="text-muted-foreground">
                {{ sections.length }} section(s), 
                {{ totalQuestions }} questions
              </span>
            </div>
            <div class="h-2 bg-muted rounded-full">
              <div 
                class="h-full bg-primary rounded-full transition-all"
                :style="{ width: sections.length ? '66%' : '33%' }"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Builder Area -->
      <div class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Question Sections</CardTitle>
            <CardDescription>
              Create sections for different types of questions. Each section can contain multiple questions of the same type.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <SectionManager
              v-model="sections"
              @update:modelValue="handleSectionUpdate"
            />

            <!-- Empty State -->
            <div v-if="sections.length === 0" 
                 class="text-center text-muted-foreground py-12 border-2 border-dashed rounded-lg">
              <div class="mb-4 mx-auto max-w-md">
                <Icon icon="lucide:file-question" class="h-12 w-12 mx-auto mb-4 text-primary/20" />
                <h4 class="font-medium mb-2">Start Building Your Exam</h4>
                <p class="text-sm">
                  Click "Add Question Type Section" above to begin adding questions
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Quick Help -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">Quick Tips</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2 text-sm text-muted-foreground">
              <li class="flex items-center gap-2">
                <Icon icon="lucide:info" class="h-4 w-4 text-primary" />
                Each section can only contain one type of question
              </li>
              <li class="flex items-center gap-2">
                <Icon icon="lucide:info" class="h-4 w-4 text-primary" />
                You can add multiple sections of the same type if needed
              </li>
              <li class="flex items-center gap-2">
                <Icon icon="lucide:info" class="h-4 w-4 text-primary" />
                Don't forget to save your progress regularly
              </li>
            </ul>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Add unsaved changes warning -->
    <div 
      v-if="isDirty" 
      class="fixed bottom-4 right-4 bg-warning text-warning-foreground px-4 py-2 rounded-lg shadow-lg flex items-center gap-2"
    >
      <Icon icon="lucide:alert-circle" class="h-4 w-4" />
      You have unsaved changes
    </div>
  </AppLayout>
</template> 