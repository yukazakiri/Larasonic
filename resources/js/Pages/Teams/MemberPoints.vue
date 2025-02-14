<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/shadcn/ui/avatar'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/shadcn/ui/table'
import { Input } from '@/Components/shadcn/ui/input'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import { ref, computed } from 'vue'

const props = defineProps({
  team: Object,
  activities: Array,
  students: Array,
})

const search = ref('')
const sortBy = ref('name') // 'name', 'total_points', or activity.id
const sortDirection = ref('asc')

// Filtered and sorted students
const filteredStudents = computed(() => {
  let result = [...props.students]
  
  // Apply search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(student => 
      student.name.toLowerCase().includes(searchLower) ||
      student.email.toLowerCase().includes(searchLower)
    )
  }
  
  // Apply sorting
  result.sort((a, b) => {
    let aValue, bValue
    
    if (sortBy.value === 'name') {
      aValue = a.name
      bValue = b.name
    } else if (sortBy.value === 'total_points') {
      aValue = a.total_points
      bValue = b.total_points
    } else {
      // Sorting by specific activity points
      aValue = a.points_by_activity[sortBy.value] || 0
      bValue = b.points_by_activity[sortBy.value] || 0
    }
    
    if (sortDirection.value === 'desc') {
      [aValue, bValue] = [bValue, aValue]
    }
    
    return aValue > bValue ? 1 : -1
  })
  
  return result
})

const toggleSort = (field) => {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDirection.value = 'asc'
  }
}

// Helper function to get sort icon
const getSortIcon = (field) => {
  if (sortBy.value !== field) return 'lucide:arrows-up-down'
  return sortDirection.value === 'asc' ? 'lucide:arrow-up' : 'lucide:arrow-down'
}
</script>

<template>
  <AppLayout :title="`${team.name} - Student Points`">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          {{ team.name }} - Student Points
        </h2>
        <div class="flex items-center space-x-4">
          <Input 
            v-model="search"
            placeholder="Search students..."
            class="w-64"
          >
            <template #prefix>
              <Icon icon="lucide:search" class="h-4 w-4 text-muted-foreground" />
            </template>
          </Input>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">Total Students</CardTitle>
              <Icon icon="lucide:users" class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ students.length }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">Total Activities</CardTitle>
              <Icon icon="lucide:target" class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">{{ activities.length }}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">Highest Points</CardTitle>
              <Icon icon="lucide:trophy" class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">
                {{ Math.max(...students.map(s => s.total_points)) }}
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle class="text-sm font-medium">Average Points</CardTitle>
              <Icon icon="lucide:bar-chart" class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">
                {{ Math.round(students.reduce((acc, s) => acc + s.total_points, 0) / students.length) }}
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Points Table -->
        <Card>
          <CardContent class="p-0">
            <Table>
              <TableHeader class="sticky top-0 bg-background">
                <TableRow>
                  <TableHead>
                    <Button 
                      variant="ghost" 
                      class="flex items-center space-x-2"
                      @click="toggleSort('name')"
                    >
                      <span>Student</span>
                      <Icon :icon="getSortIcon('name')" class="h-4 w-4" />
                    </Button>
                  </TableHead>
                  <TableHead v-for="activity in activities" :key="activity.id">
                    <Button 
                      variant="ghost" 
                      class="flex items-center space-x-2"
                      @click="toggleSort(activity.id)"
                    >
                      <span class="truncate max-w-[150px]" :title="activity.title">
                        {{ activity.title }}
                      </span>
                      <Icon :icon="getSortIcon(activity.id)" class="h-4 w-4" />
                    </Button>
                  </TableHead>
                  <TableHead>
                    <Button 
                      variant="ghost" 
                      class="flex items-center space-x-2"
                      @click="toggleSort('total_points')"
                    >
                      <span>Total Points</span>
                      <Icon :icon="getSortIcon('total_points')" class="h-4 w-4" />
                    </Button>
                  </TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow 
                  v-for="student in filteredStudents" 
                  :key="student.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell class="font-medium">
                    <div class="flex items-center space-x-4">
                      <Avatar>
                        <AvatarImage :src="student.profile_photo_url" :alt="student.name" />
                        <AvatarFallback>{{ student.name.charAt(0) }}</AvatarFallback>
                      </Avatar>
                      <div>
                        <div class="font-medium">{{ student.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ student.email }}</div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell 
                    v-for="activity in activities" 
                    :key="activity.id"
                    class="text-center"
                  >
                    <div 
                      class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5"
                      :class="{
                        'bg-green-100 text-green-700': student.points_by_activity[activity.id] >= (activity.total_points * 0.7),
                        'bg-yellow-100 text-yellow-700': student.points_by_activity[activity.id] >= (activity.total_points * 0.4) && student.points_by_activity[activity.id] < (activity.total_points * 0.7),
                        'bg-red-100 text-red-700': student.points_by_activity[activity.id] < (activity.total_points * 0.4)
                      }"
                    >
                      {{ student.points_by_activity[activity.id] || 0 }}
                    </div>
                  </TableCell>
                  <TableCell class="font-bold text-center">
                    <div 
                      class="inline-flex items-center justify-center rounded-full px-3 py-1"
                      :class="{
                        'bg-primary text-primary-foreground': student.total_points === Math.max(...students.map(s => s.total_points)),
                        'bg-secondary text-secondary-foreground': student.total_points !== Math.max(...students.map(s => s.total_points))
                      }"
                    >
                      {{ student.total_points }}
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 