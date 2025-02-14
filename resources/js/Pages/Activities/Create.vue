<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import Input from '@/Components/shadcn/ui/input/Input.vue'
import Label from '@/Components/shadcn/ui/label/Label.vue'
import Textarea from '@/Components/shadcn/ui/textarea/Textarea.vue'
import { Switch } from '@/Components/shadcn/ui/switch'
import InputError from '@/Components/InputError.vue'

const form = useForm({
  title: '',
  description: '',
  deadline: '',
  total_points: '',
  is_group_activity: false,
})

function createActivity() {
  form.post(route('activities.store'))
}
</script>

<template>
  <AppLayout title="Create Activity">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Create Activity
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>Activity Details</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="createActivity" class="space-y-6">
              <div>
                <Label for="title">Title</Label>
                <Input 
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.title" class="mt-2" />
              </div>

              <div>
                <Label for="description">Description</Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  class="mt-1 block w-full"
                  rows="3"
                />
                <InputError :message="form.errors.description" class="mt-2" />
              </div>

              <div>
                <Label for="deadline">Deadline (Optional)</Label>
                <Input
                  id="deadline"
                  v-model="form.deadline"
                  type="datetime-local"
                  class="mt-1 block w-full"
                />
                <InputError :message="form.errors.deadline" class="mt-2" />
              </div>

              <div>
                <Label for="total_points">Total Points</Label>
                <Input
                  id="total_points"
                  v-model="form.total_points"
                  type="number"
                  min="1"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.total_points" class="mt-2" />
              </div>

              <div class="flex items-center space-x-2">
                <Switch v-model:checked="form.is_group_activity" />
                <Label>Group Activity</Label>
              </div>

              <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">
                  Create Activity
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 