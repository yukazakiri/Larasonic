<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/shadcn/ui/table'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import Badge from '@/Components/shadcn/ui/badge/Badge.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/shadcn/ui/tabs'
import { computed, ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/shadcn/ui/dialog'
import { toast } from 'vue-sonner'

const props = defineProps({
  activities: Object,
})

const searchQuery = ref('')
const selectedTab = ref('all')
const deleteDialog = ref(false)
const activityToDelete = ref(null)

const formatDate = (date) => {
  if (!date) return 'No deadline'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusBadge = (activity) => {
  const now = new Date()
  const deadline = activity.deadline ? new Date(activity.deadline) : null

  if (!deadline) return { label: 'No Deadline', variant: 'secondary' }
  if (now > deadline) return { label: 'Overdue', variant: 'destructive' }
  
  const daysUntilDeadline = Math.ceil((deadline - now) / (1000 * 60 * 60 * 24))
  if (daysUntilDeadline <= 2) return { label: 'Due Soon', variant: 'warning' }
  return { label: 'Active', variant: 'success' }
}

const filteredActivities = computed(() => {
  let filtered = props.activities.data

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(activity => 
      activity.title.toLowerCase().includes(query) ||
      activity.description?.toLowerCase().includes(query)
    )
  }

  // Apply tab filter
  if (selectedTab.value !== 'all') {
    const now = new Date()
    filtered = filtered.filter(activity => {
      const deadline = activity.deadline ? new Date(activity.deadline) : null
      switch (selectedTab.value) {
        case 'active':
          return !deadline || now <= deadline
        case 'completed':
          return activity.user_points.length > 0
        case 'overdue':
          return deadline && now > deadline
        default:
          return true
      }
    })
  }

  return filtered
})

const stats = computed(() => [
  {
    name: 'Total Activities',
    value: props.activities.data.length,
    icon: 'lucide:layout-list',
  },
  {
    name: 'Active Activities',
    value: props.activities.data.filter(a => !a.deadline || new Date() <= new Date(a.deadline)).length,
    icon: 'lucide:activity',
  },
  {
    name: 'Overdue',
    value: props.activities.data.filter(a => a.deadline && new Date() > new Date(a.deadline)).length,
    icon: 'lucide:alert-circle',
  },
])

function confirmDelete(activity) {
  activityToDelete.value = activity
  deleteDialog.value = true
}

function deleteActivity() {
  router.delete(route('activities.destroy', activityToDelete.value.id), {
    onSuccess: () => {
      deleteDialog.value = false
      activityToDelete.value = null
      toast.success('Activity deleted successfully')
    },
    onError: () => {
      toast.error('Failed to delete activity')
    }
  })
}
</script>

<template>
  <AppLayout title="Team Activities">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          Team Activities
        </h2>
        <Button :as="Link" :href="route('activities.create')">
          <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
          New Activity
        </Button>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
          <Card v-for="stat in stats" :key="stat.name">
            <CardContent class="flex items-center p-6">
              <Icon :icon="stat.icon" class="h-8 w-8 text-muted-foreground" />
              <div class="ml-4">
                <p class="text-sm font-medium text-muted-foreground">
                  {{ stat.name }}
                </p>
                <p class="text-2xl font-bold">{{ stat.value }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Main Content Card -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Activities</CardTitle>
              <div class="relative">
                <Icon 
                  icon="lucide:search" 
                  class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <input
                  v-model="searchQuery"
                  type="search"
                  placeholder="Search activities..."
                  class="pl-9 h-9 w-64 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                />
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <Tabs v-model="selectedTab" class="w-full">
              <TabsList class="mb-4">
                <TabsTrigger value="all">All</TabsTrigger>
                <TabsTrigger value="active">Active</TabsTrigger>
                <TabsTrigger value="completed">Completed</TabsTrigger>
                <TabsTrigger value="overdue">Overdue</TabsTrigger>
              </TabsList>

              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Points</TableHead>
                    <TableHead>Progress</TableHead>
                    <TableHead>Deadline</TableHead>
                    <TableHead class="w-[100px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="activity in filteredActivities" :key="activity.id">
                    <TableCell>
                      <div class="font-medium">{{ activity.title }}</div>
                      <div class="text-sm text-muted-foreground">
                        {{ activity.description?.substring(0, 50) }}{{ activity.description?.length > 50 ? '...' : '' }}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="activity.is_group_activity ? 'default' : 'secondary'">
                        {{ activity.is_group_activity ? 'Group' : 'Individual' }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="getStatusBadge(activity).variant">
                        {{ getStatusBadge(activity).label }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="font-medium">{{ activity.total_points }}</div>
                      <div class="text-sm text-muted-foreground">
                        {{ activity.user_points.length }} assigned
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="w-full bg-secondary rounded-full h-2.5">
                        <div 
                          class="bg-primary h-2.5 rounded-full" 
                          :style="{
                            width: `${(activity.user_points.filter(p => p.points > 0).length / activity.team_members_count) * 100}%`
                          }"
                        ></div>
                      </div>
                      <div class="mt-1 text-xs text-muted-foreground text-center">
                        {{ activity.user_points.filter(p => p.points > 0).length }} / {{ activity.team_members_count }} students
                      </div>
                    </TableCell>
                    <TableCell>
                      <div :class="{'text-destructive': activity.deadline && new Date() > new Date(activity.deadline)}">
                        {{ formatDate(activity.deadline) }}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex space-x-2">
                        <Button 
                          variant="outline" 
                          size="sm"
                          :as="Link"
                          :href="route('activities.show', activity.id)"
                        >
                          <Icon icon="lucide:eye" class="h-4 w-4" />
                        </Button>
                        <Button 
                          variant="destructive"
                          @click="confirmDelete(activity)"
                        >
                          <Icon icon="lucide:trash" class="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="filteredActivities.length === 0">
                    <TableCell colspan="7" class="text-center py-8">
                      <div class="text-muted-foreground">
                        <Icon icon="lucide:inbox" class="h-8 w-8 mx-auto mb-2" />
                        <p>No activities found</p>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Activity</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete "{{ activityToDelete?.title }}"? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button
            variant="outline"
            @click="deleteDialog = false"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            @click="deleteActivity"
          >
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template> 