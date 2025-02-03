<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Switch } from '@/Components/shadcn/ui/switch'
import { Link } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
const props = defineProps({
  exam: Object,
  isPublished: Boolean
})

const form = useForm({
  time_limit: props.exam.time_limit,
  passing_score: props.exam.passing_score,
  attempts_limit: props.exam.attempts_limit,
  show_correct_answers: props.exam.show_correct_answers,
  is_public: props.exam.is_public,
  available_from: props.exam.available_from,
  available_to: props.exam.available_to,
})

const saveSettings = () => {
  form.put(route('exams.update', props.exam.id), {
    preserveScroll: true,
    onSuccess: () => toast.success('Settings saved successfully'),
    onError: () => toast.error('Failed to save settings')
  })
}
</script>

<template>
  <AppLayout title="Exam Settings">
    <div class="space-y-8 max-w-3xl mx-auto">
      <div class="flex justify-between items-center">
        <Link 
          :href="route('exams.builder', { exam: props.exam.id })" 
          class="inline-flex items-center justify-center"
        >
          <Button variant="ghost">
            Back to Builder
          </Button>
        </Link>
      </div>
      <div class="space-y-2">
        <h1 class="text-2xl font-bold">Exam Settings</h1>
        <p class="text-muted-foreground">Configure your exam's rules and availability</p>
      </div>

      <div class="space-y-6">
        <!-- Timing Settings -->
        <div class="space-y-4 p-6 bg-card rounded-lg border">
          <h2 class="text-lg font-semibold">Timing & Availability</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Time Limit (minutes)</Label>
              <Input 
                v-model="form.time_limit" 
                type="number" 
                min="0"
                placeholder="0 for no limit"
              />
            </div>
            <div>
              <Label>Available From</Label>
              <Input v-model="form.available_from" type="datetime-local" />
            </div>
            <div>
              <Label>Available To</Label>
              <Input v-model="form.available_to" type="datetime-local" />
            </div>
          </div>
        </div>

        <!-- Attempts & Scoring -->
        <div class="space-y-4 p-6 bg-card rounded-lg border">
          <h2 class="text-lg font-semibold">Attempts & Scoring</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label>Maximum Attempts</Label>
              <Input 
                v-model="form.attempts_limit" 
                type="number" 
                min="0"
                placeholder="0 for unlimited"
              />
            </div>
            <div>
              <Label>Passing Score (%)</Label>
              <Input 
                v-model="form.passing_score" 
                type="number" 
                min="0" 
                max="100"
              />
            </div>
          </div>
        </div>

        <!-- Privacy & Display -->
        <div class="space-y-4 p-6 bg-card rounded-lg border">
          <h2 class="text-lg font-semibold">Privacy & Display</h2>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <Label>Public Exam</Label>
                <p class="text-sm text-muted-foreground">
                  Allow anyone with the link to take the exam
                </p>
              </div>
              <Switch v-model:checked="form.is_public" />
            </div>
            <div class="flex items-center justify-between">
              <div>
                <Label>Show Correct Answers</Label>
                <p class="text-sm text-muted-foreground">
                  Display correct answers after submission
                </p>
              </div>
              <Switch v-model:checked="form.show_correct_answers" />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-4">
          <Button 
            variant="outline" 
            :disabled="form.processing"
            @click="saveSettings"
          >
            Save Settings
          </Button>
          <Button 
            v-if="!isPublished"
            @click="$inertia.put(route('exams.publish', exam.id))"
          >
            Publish Exam
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 