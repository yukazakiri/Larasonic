<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Card, CardContent } from '@/Components/shadcn/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/Components/shadcn/ui/dialog'
import { Button } from '@/Components/shadcn/ui/button'
import { Form, FormControl, FormField, FormItem, FormLabel } from '@/Components/shadcn/ui/form'
import { Input } from '@/Components/shadcn/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/shadcn/ui/select'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/shadcn/ui/tabs'
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ref, computed, watch } from 'vue'
import { ScrollArea } from '@/Components/shadcn/ui/scroll-area'

const props = defineProps({
  schedules: {
    type: Object,
    required: true
  },
  days: {
    type: Array,
    required: true
  }
})

const editingSchedule = ref(null)
const isEditDialogOpen = ref(false)
const activeDay = ref('Monday')
const viewMode = ref('timeline') // 'timeline' or 'list'

const timeSlots = computed(() => {
  const slots = []
  for (let i = 0; i < 24; i++) {
    slots.push(`${i.toString().padStart(2, '0')}:00`)
  }
  return slots
})

const daySchedules = computed(() => {
  return props.schedules[activeDay.value] || []
})

function formatTime(time) {
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  })
}

function getSchedulePosition(schedule) {
  const [startHour, startMinute] = schedule.start_time.split(':').map(Number)
  const [endHour, endMinute] = schedule.end_time.split(':').map(Number)
  
  const top = (startHour + startMinute / 60) * 60
  const height = ((endHour + endMinute / 60) - (startHour + startMinute / 60)) * 60
  
  return {
    top: `${top}px`,
    height: `${height}px`
  }
}

const showDialog = ref(false)

const form = useInertiaForm({
  title: '',
  description: '',
  start_time: '',
  end_time: '',
  day_of_week: activeDay.value,
  color: '#4F46E5',
  is_recurring: true
})

watch(activeDay, (newDay) => {
  form.day_of_week = newDay
})

function onSubmit() {
  form.post(route('schedules.store'), {
    onSuccess: () => {
      showDialog.value = false
      form.reset()
    }
  })
}

function closeDialog() {
  showDialog.value = false
  form.reset()
}

function editSchedule(schedule) {
  editingSchedule.value = schedule
  form.title = schedule.title
  form.description = schedule.description
  form.start_time = schedule.start_time
  form.end_time = schedule.end_time
  form.day_of_week = schedule.day_of_week
  form.color = schedule.color
  form.is_recurring = schedule.is_recurring
  isEditDialogOpen.value = true
}

function deleteSchedule(schedule) {
  if (confirm('Are you sure you want to delete this schedule?')) {
    form.delete(route('schedules.destroy', schedule.id))
  }
}
</script>

<template>
  <AppLayout title="Team Schedule">
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          Team Schedule
        </h2>
        <div class="flex items-center gap-2">
          <div class="flex items-center rounded-lg border p-1">
            <Button
              size="sm"
              variant="ghost"
              :class="{ 'bg-secondary': viewMode === 'timeline' }"
              @click="viewMode = 'timeline'"
            >
              <Icon icon="lucide:calendar" class="h-4 w-4" />
              <span class="ml-2 hidden sm:inline">Timeline</span>
            </Button>
            <Button
              size="sm"
              variant="ghost"
              :class="{ 'bg-secondary': viewMode === 'list' }"
              @click="viewMode = 'list'"
            >
              <Icon icon="lucide:list" class="h-4 w-4" />
              <span class="ml-2 hidden sm:inline">List</span>
            </Button>
          </div>
          <Button @click="showDialog = true">
            <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
            <span class="hidden sm:inline">Add Schedule</span>
          </Button>
        </div>
      </div>
    </template>

    <div class="space-y-4">
      <Card>
        <Tabs v-model="activeDay" class="w-full">
          <ScrollArea class="w-full">
            <TabsList class="w-full justify-start border-b rounded-none h-12 px-4">
              <TabsTrigger 
                v-for="day in days" 
                :key="day" 
                :value="day"
                class="flex-1 data-[state=active]:border-b-2 data-[state=active]:border-primary"
              >
                {{ day }}
                <span 
                  v-if="schedules[day]?.length" 
                  class="ml-2 rounded-full bg-primary/10 px-2 py-0.5 text-xs"
                >
                  {{ schedules[day].length }}
                </span>
              </TabsTrigger>
            </TabsList>
          </ScrollArea>

          <TabsContent 
            v-for="day in days" 
            :key="day" 
            :value="day" 
            class="mt-0 border-0 p-0"
          >
            <!-- Timeline View -->
            <div v-if="viewMode === 'timeline'" class="relative min-h-[600px] border-l">
              <!-- Time markers -->
              <div class="absolute left-0 top-0 w-16 h-full border-r bg-background/95 z-10">
                <div 
                  v-for="slot in timeSlots" 
                  :key="slot"
                  class="absolute w-full text-xs text-muted-foreground px-2"
                  :style="{ top: `${parseInt(slot) * 60}px` }"
                >
                  {{ slot }}
                </div>
              </div>

              <!-- Grid lines -->
              <div class="absolute left-16 right-0 top-0 h-full">
                <div 
                  v-for="slot in timeSlots" 
                  :key="slot"
                  class="absolute w-full border-t border-border/30"
                  :style="{ top: `${parseInt(slot) * 60}px` }"
                />
              </div>

              <!-- Schedule items -->
              <ScrollArea class="absolute left-16 right-0 top-0 bottom-0">
                <div class="relative" style="height: 1440px;">
                  <div
                    v-for="schedule in daySchedules"
                    :key="schedule.id"
                    class="absolute w-[calc(100%-1rem)] mx-2 rounded-lg p-3 shadow-sm transition-all hover:shadow-md cursor-pointer group"
                    :style="{
                      ...getSchedulePosition(schedule),
                      backgroundColor: `${schedule.color}15`,
                      borderLeft: `4px solid ${schedule.color}`
                    }"
                  >
                    <div class="flex items-start justify-between">
                      <div class="min-w-0 flex-1">
                        <h4 class="font-medium truncate">{{ schedule.title }}</h4>
                        <p class="text-xs text-muted-foreground">
                          {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                        </p>
                        <p v-if="schedule.description" class="text-sm mt-1 line-clamp-2">
                          {{ schedule.description }}
                        </p>
                      </div>
                      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button variant="ghost" size="icon" @click.stop="editSchedule(schedule)">
                          <Icon icon="lucide:pencil" class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" @click.stop="deleteSchedule(schedule)">
                          <Icon icon="lucide:trash" class="h-4 w-4" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </ScrollArea>
            </div>

            <!-- List View -->
            <div v-else class="p-4">
              <div v-if="daySchedules.length" class="space-y-3">
                <div
                  v-for="schedule in daySchedules"
                  :key="schedule.id"
                  class="flex items-center gap-4 p-4 rounded-lg border transition-all hover:shadow-md"
                  :style="{
                    backgroundColor: `${schedule.color}05`,
                    borderColor: schedule.color
                  }"
                >
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                      <h4 class="font-medium truncate">{{ schedule.title }}</h4>
                      <span class="text-xs text-muted-foreground px-2 py-0.5 rounded-full bg-secondary">
                        {{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}
                      </span>
                    </div>
                    <p v-if="schedule.description" class="text-sm text-muted-foreground mt-1">
                      {{ schedule.description }}
                    </p>
                  </div>
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="editSchedule(schedule)">
                      <Icon icon="lucide:pencil" class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" size="sm" @click="deleteSchedule(schedule)">
                      <Icon icon="lucide:trash" class="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-muted-foreground">
                No schedules for {{ day }}
              </div>
            </div>
          </TabsContent>
        </Tabs>
      </Card>
    </div>

    <!-- Add/Edit Dialog -->
    <Dialog :open="showDialog" @update:open="closeDialog">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Add New Schedule</DialogTitle>
        </DialogHeader>
        <Form @submit="onSubmit" class="space-y-4">
          <div class="grid gap-4">
            <div class="grid gap-2">
              <label for="title">Title</label>
              <Input 
                id="title"
                v-model="form.title" 
                placeholder="Schedule title"
                required
              />
            </div>

            <div class="grid gap-2">
              <label for="description">Description</label>
              <Input 
                id="description"
                v-model="form.description" 
                placeholder="Schedule description"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <label for="start_time">Start Time</label>
                <Input 
                  id="start_time"
                  type="time" 
                  v-model="form.start_time"
                  required
                />
              </div>

              <div class="grid gap-2">
                <label for="end_time">End Time</label>
                <Input 
                  id="end_time"
                  type="time" 
                  v-model="form.end_time"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="grid gap-2">
                <label for="day_of_week">Day of Week</label>
                <select 
                  id="day_of_week"
                  v-model="form.day_of_week"
                  class="w-full rounded-md border border-input bg-background px-3 py-2"
                  required
                >
                  <option v-for="day in days" :key="day" :value="day">
                    {{ day }}
                  </option>
                </select>
              </div>

              <div class="grid gap-2">
                <label for="color">Color</label>
                <Input 
                  id="color"
                  type="color" 
                  v-model="form.color"
                  required
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <Button 
              type="button" 
              variant="outline" 
              @click="closeDialog"
            >
              Cancel
            </Button>
            <Button 
              type="submit"
              :disabled="form.processing"
            >
              Save Schedule
            </Button>
          </div>
        </Form>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 