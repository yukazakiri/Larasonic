<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/shadcn/ui/card'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Label } from '@/Components/shadcn/ui/label'
import { Textarea } from '@/Components/shadcn/ui/textarea'
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/shadcn/ui/avatar'
import { Progress } from '@/Components/shadcn/ui/progress'
import { Icon } from '@iconify/vue'
import { toast } from 'vue-sonner'
import { ref, computed } from 'vue'
import { HexColorPicker } from '@/Components/shadcn/ui/color-picker'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/Components/shadcn/ui/dialog'
import { Badge } from '@/Components/shadcn/ui/badge'
import { Select } from '@/Components/shadcn/ui/select'

const props = defineProps({
  activity: Object,
  teamMembers: Array,
})

const showGroupModal = ref(false)
const selectedColor = ref('#4F46E5')

const groupForm = useForm({
  groups: [],
})

function initializeGroups() {
  groupForm.groups = props.activity.groups?.map(group => ({
    id: group.id,
    name: group.name,
    color: group.color,
    points: group.points,
    members: group.members.map(m => m.user_id),
  })) || []
}

function addGroup() {
  groupForm.groups.push({
    name: `Group ${groupForm.groups.length + 1}`,
    color: selectedColor.value,
    points: 0,
    members: [],
  })
}

function removeGroup(index) {
  groupForm.groups.splice(index, 1)
}

function saveGroups() {
  groupForm.post(route('activities.groups.manage', props.activity.id), {
    preserveScroll: true,
    onSuccess: () => {
      showGroupModal.value = false
    },
  })
}

const memberPointsForm = useForm({
  points_adjustment: 0,
  adjustment_reason: '',
})

function updateMemberPoints(member) {
  memberPointsForm.put(
    route('activities.members.points', [props.activity.id, member.id]), 
    {
      preserveScroll: true,
      onSuccess: () => {
        memberPointsForm.reset()
      },
    }
  )
}

const form = useForm(
  props.teamMembers.reduce((acc, member) => {
    const existingPoints = props.activity.user_points.find(up => up.user_id === member.id)
    acc.points[member.id] = existingPoints?.points || 0
    acc.notes[member.id] = existingPoints?.notes || ''
    return acc
  }, { points: {}, notes: {} })
)

const showNotes = ref({})

const progress = computed(() => {
  const studentsWithPoints = props.activity.user_points.filter(p => p.points > 0).length
  return (studentsWithPoints / props.teamMembers.length) * 100
})

function assignPoints() {
  form.post(route('activities.assign-points', props.activity.id), {
    onSuccess: () => {
      toast.success('Points assigned successfully')
    },
    onError: () => {
      toast.error('Failed to assign points')
    }
  })
}

const getPointsStatus = (points) => {
  const percentage = (points / props.activity.total_points) * 100
  if (percentage >= 80) return 'success'
  if (percentage >= 50) return 'warning'
  return 'danger'
}
</script>

<template>
  <AppLayout :title="activity.title">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight">
          {{ activity.title }}
        </h2>
        <Badge>{{ activity.total_points }} Total Points</Badge>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
        <!-- Activity Overview -->
        <div class="grid gap-6 md:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle>Progress</CardTitle>
              <CardDescription>
                {{ activity.user_points.filter(p => p.points > 0).length }} of {{ teamMembers.length }} students graded
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Progress :value="progress" class="w-full" />
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle>Average Points</CardTitle>
              <CardDescription>Across all graded students</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">
                {{ Math.round(activity.user_points.reduce((acc, p) => acc + p.points, 0) / 
                  (activity.user_points.filter(p => p.points > 0).length || 1)) }}
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle>Deadline</CardTitle>
              <CardDescription>Time remaining</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold">
                {{ activity.deadline ? new Date(activity.deadline).toLocaleDateString() : 'No deadline' }}
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Point Assignment -->
        <Card>
          <CardHeader>
            <CardTitle>Assign Points</CardTitle>
            <CardDescription>
              Assign points to students out of {{ activity.total_points }} total points
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="assignPoints" class="space-y-6">
              <div class="grid gap-4">
                <div v-for="member in teamMembers" :key="member.id" 
                  class="group rounded-lg border p-4 hover:bg-muted/50 transition-colors"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                      <Avatar>
                        <AvatarImage :src="member.profile_photo_url" :alt="member.name" />
                        <AvatarFallback>{{ member.name.charAt(0) }}</AvatarFallback>
                      </Avatar>
                      <div>
                        <div class="font-medium">{{ member.name }}</div>
                        <div class="text-sm text-muted-foreground">{{ member.email }}</div>
                      </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                      <div class="text-right">
                        <Input
                          :id="`points-${member.id}`"
                          v-model="form.points[member.id]"
                          type="number"
                          min="0"
                          :max="activity.total_points"
                          class="w-24 text-right"
                        />
                        <div class="mt-1 text-xs text-muted-foreground">
                          out of {{ activity.total_points }}
                        </div>
                      </div>
                      
                      <Button 
                        type="button" 
                        variant="ghost"
                        @click="showNotes[member.id] = !showNotes[member.id]"
                      >
                        <Icon 
                          :icon="showNotes[member.id] ? 'lucide:chevron-up' : 'lucide:chevron-down'" 
                          class="h-4 w-4"
                        />
                      </Button>
                    </div>
                  </div>
                  
                  <!-- Notes Section -->
                  <div v-show="showNotes[member.id]" class="mt-4">
                    <Textarea
                      v-model="form.notes[member.id]"
                      placeholder="Add notes about the student's performance..."
                      rows="3"
                    />
                  </div>

                  <!-- Progress Bar -->
                  <div class="mt-4">
                    <Progress 
                      :value="(form.points[member.id] / activity.total_points) * 100"
                      :class="{
                        'bg-green-200': getPointsStatus(form.points[member.id]) === 'success',
                        'bg-yellow-200': getPointsStatus(form.points[member.id]) === 'warning',
                        'bg-red-200': getPointsStatus(form.points[member.id]) === 'danger'
                      }"
                    />
                  </div>
                </div>
              </div>

              <div class="flex justify-end">
                <Button 
                  type="submit" 
                  :disabled="form.processing"
                  class="w-full md:w-auto"
                >
                  <Icon icon="lucide:save" class="mr-2 h-4 w-4" />
                  Save All Points
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Add this section for group activities -->
    <div v-if="activity.is_group_activity" class="mt-6">
      <Card>
        <CardHeader>
          <CardTitle>Group Management</CardTitle>
          <Button @click="showGroupModal = true">
            Manage Groups
          </Button>
        </CardHeader>
        <CardContent>
          <div class="grid gap-6 md:grid-cols-2">
            <div v-for="group in activity.groups" :key="group.id" class="space-y-4">
              <div 
                class="p-4 rounded-lg border"
                :style="{ borderColor: group.color }"
              >
                <div class="flex items-center justify-between">
                  <h3 class="font-semibold">{{ group.name }}</h3>
                  <Badge>{{ group.points }} points</Badge>
                </div>
                
                <div class="mt-4 space-y-3">
                  <div v-for="member in group.members" :key="member.id" class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <Avatar>
                        <AvatarImage :src="member.user.profile_photo_url" :alt="member.user.name" />
                        <AvatarFallback>{{ member.user.name.charAt(0) }}</AvatarFallback>
                      </Avatar>
                      <span>{{ member.user.name }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                      <Input
                        type="number"
                        v-model="member.points_adjustment"
                        class="w-20"
                        placeholder="Adjust"
                      />
                      <Button 
                        size="sm"
                        @click="updateMemberPoints(member)"
                      >
                        Save
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Group Management Modal -->
    <Dialog v-model:open="showGroupModal">
      <DialogContent class="sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>Manage Groups</DialogTitle>
        </DialogHeader>
        
        <div class="space-y-6">
          <div v-for="(group, index) in groupForm.groups" :key="index" class="space-y-4">
            <Card>
              <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <Input v-model="group.name" class="w-48" />
                <Button 
                  variant="destructive" 
                  size="sm"
                  @click="removeGroup(index)"
                >
                  <Icon icon="lucide:trash" class="h-4 w-4" />
                </Button>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex space-x-4">
                  <div>
                    <Label>Color</Label>
                    <HexColorPicker v-model="group.color" />
                  </div>
                  <div>
                    <Label>Points</Label>
                    <Input 
                      type="number" 
                      v-model="group.points"
                      min="0"
                    />
                  </div>
                </div>
                
                <div>
                  <Label>Members</Label>
                  <Select
                    v-model="group.members"
                    multiple
                    class="mt-1"
                  >
                    <option 
                      v-for="member in teamMembers"
                      :key="member.id"
                      :value="member.id"
                    >
                      {{ member.name }}
                    </option>
                  </Select>
                </div>
              </CardContent>
            </Card>
          </div>

          <div class="flex justify-between">
            <Button @click="addGroup">
              <Icon icon="lucide:plus" class="mr-2 h-4 w-4" />
              Add Group
            </Button>
            
            <div class="space-x-2">
              <Button variant="outline" @click="showGroupModal = false">
                Cancel
              </Button>
              <Button @click="saveGroups" :disabled="groupForm.processing">
                Save Groups
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template> 