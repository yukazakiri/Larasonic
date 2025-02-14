<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import Input from '@/Components/shadcn/ui/input/Input.vue'
import Textarea from '@/Components/shadcn/ui/textarea/Textarea.vue'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'
import FormError from '@/Components/FormError.vue'
import Label from '@/Components/shadcn/ui/label/Label.vue'
import { router } from '@inertiajs/vue3'
import { toast } from '@/Components/shadcn/ui/toast'

const form = useForm({
  title: '',
  description: '',
  status: 'draft'
})

const submit = () => {
  form.post(route('exams.store'), {
    onSuccess: (response) => {
      // Handle Inertia redirect automatically
    }
  })
}
</script>

<template>
  <AppLayout title="Create Exam">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('exams.index')" class="mr-4">
          <Button >
            <Icon icon="lucide:arrow-left" class="mr-2 h-4 w-4" />
            Back to Exams
          </Button>
        </Link>
        <h2 class="text-2xl font-bold">Create New Exam</h2>
      </div>
      <p class="text-gray-500 mt-1">Let's start with the basic information for your new exam.</p>
    </template>

    <div class="py-12">
      <div class="container mx-auto max-w-2xl">
        <div class="bg-muted rounded-xl shadow-sm p-6">
          <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
              <div class="h-2 w-full bg-accent rounded-full">
                <div class="h-full bg-primary rounded-full w-1/3 transition-all"></div>
              </div>
              <span class="text-sm text-muted-foreground">Step 1 of 3 - Basic Info</span>
            </div>
            <h3 class="text-lg font-semibold">Exam Basics</h3>
            <p class="text-sm text-muted-foreground">Provide a title and description for your exam</p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <Label for="title" class="block mb-2">Exam Title</Label>
              <Input
                id="title"
                v-model="form.title"
                placeholder="e.g., Midterm Mathematics Exam"
                class="w-full"
                :error="form.errors.title"
                required
              />
              <FormError :message="form.errors.title" class="mt-2" />
              <p class="text-xs text-muted-foreground mt-2">
                Tip: Use a clear, descriptive title that students will recognize
              </p>
            </div>

            <div>
              <Label for="description" class="block mb-2">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                placeholder="Describe the exam scope, instructions, or special requirements..."
                rows="3"
                class="w-full"
                :error="form.errors.description"
                required
              />
              <FormError :message="form.errors.description" class="mt-2" />
              <p class="text-xs text-muted-foreground mt-2">
                Optional but recommended for better student understanding
              </p>
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t">
              <Button
                type="submit"
                variant="primary"
                :loading="form.processing"
                :disabled="form.processing"
              >
                <Icon icon="lucide:arrow-right" class="mr-2 h-4 w-4" />
                Create Exam
              </Button>
            </div>
          </form>
        </div>

        <div class="mt-8 bg-accent/50 p-6 rounded-xl">
          <div class="flex gap-4">
            <Icon icon="lucide:lightbulb" class="h-5 w-5 text-yellow-500 mt-1" />
            <div>
              <h4 class="font-medium mb-2">Quick Tips</h4>
              <ul class="text-sm text-muted-foreground space-y-2">
                <li>• Keep titles under 60 characters</li>
                <li>• Mention key topics covered</li>
                <li>• Specify time limits if applicable</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 