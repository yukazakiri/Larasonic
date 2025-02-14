<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import { Icon } from '@iconify/vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/shadcn/ui/dropdown-menu'
import Input from '@/Components/shadcn/ui/input/Input.vue'
import { toast } from 'vue-sonner'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/shadcn/ui/dialog'

const props = defineProps({
  exams: Array,
})

const showDeleteModal = ref(false)
const examToDelete = ref(null)

const confirmDelete = (exam) => {
  examToDelete.value = exam
  showDeleteModal.value = true
}

const handleDelete = () => {
  router.delete(route('exams.destroy', examToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      examToDelete.value = null
      toast.success('Exam deleted successfully', {
        description: 'The exam and all its questions have been removed.',
        duration: 5000,
      })
    },
    onError: () => {
      toast.error('Failed to delete exam', {
        description: 'There was an error deleting the exam. Please try again.',
        duration: 5000,
      })
    }
  })
}
</script>

<template>
  <AppLayout title="Exams">
    <template #header>
      <div class="flex flex-col space-y-2">
        <div class="flex justify-between items-center">
          <h1 class="text-3xl font-bold tracking-tight">My Exams</h1>
          
        </div>
        <p class="text-sm text-muted-foreground">
          Manage all your examinations and track their status
        </p>
      </div>
    </template>

    <div class="py-6">
      <div class="container mx-auto space-y-4">
        <!-- Search and Filters -->
        <Link href="/exams/create">
            <Button >
              <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
              New Exam
            </Button>
          </Link>
        <div class="flex flex-col sm:flex-row gap-3">
          <Input
            placeholder="Search exams..."
            class="max-w-[300px]"
          />
          <div class="flex gap-2">
            <Button variant="outline">All ({{ exams.length }})</Button>
            <Button variant="outline">Draft (2)</Button>
            <Button variant="outline">Published (4)</Button>
          </div>
        </div>

        <!-- Exams Grid -->
        <div v-if="exams.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="exam in exams"
            :key="exam.id"
            class="group relative border rounded-lg p-6 hover:bg-accent/50 transition-colors"
          >
            <!-- Status Badge -->
            <div class="absolute top-4 right-4">
              <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                <Icon icon="lucide:check-circle" class="mr-1 h-3 w-3" />
                Published
              </span>
            </div>

            <!-- Exam Content -->
            <div class="space-y-4">
              <div class="space-y-1">
                <h3 class="text-lg font-semibold">{{ exam.title }}</h3>
                <p class="text-sm text-muted-foreground line-clamp-2">
                  {{ exam.description || 'No description provided' }}
                </p>
              </div>

              <!-- Metadata -->
              <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center">
                  <Icon icon="lucide:file-text" class="mr-1 h-4 w-4 text-muted-foreground" />
                  <span>12 Questions</span>
                </div>
                <div class="flex items-center">
                  <Icon icon="lucide:calendar" class="mr-1 h-4 w-4 text-muted-foreground" />
                  <span>Created {{ exam.created_at }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2">
                <Link :href="`/exams/${exam.id}`">
                  <Button variant="outline" size="sm">
                    <Icon icon="lucide:eye" class="mr-2 h-4 w-4" />
                    Preview
                  </Button>
                </Link>
                
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                      <Icon icon="lucide:more-horizontal" class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem as-child>
                      <Link :href="`/exams/${exam.id}/edit`" class="flex items-center w-full">
                        <Icon icon="lucide:pencil" class="mr-2 h-4 w-4" />
                        Edit
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="confirmDelete(exam)" class="text-red-600">
                      <Icon icon="lucide:trash-2" class="mr-2 h-4 w-4" />
                      Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center gap-4 text-center py-24">
          <div class="bg-accent p-4 rounded-full">
            <Icon icon="lucide:clipboard-list" class="h-8 w-8" />
          </div>
          <div class="space-y-2">
            <h3 class="text-xl font-semibold">No exams created yet</h3>
            <p class="text-sm text-muted-foreground">
              Get started by creating a new examination
            </p>
          </div>
          <Link href="/exams/create">
            <Button >
              <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
              Create Exam
            </Button>
          </Link>
        </div>
      </div>
    </div>

    <!-- Add the delete confirmation modal -->
    <Dialog :open="showDeleteModal" @update:open="showDeleteModal = false">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Exam</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete "{{ examToDelete?.title }}"? This action cannot be undone
            and will delete all associated questions.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button
            variant="ghost"
            @click="showDeleteModal = false"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            @click="handleDelete"
          >
            Delete Exam
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template> 