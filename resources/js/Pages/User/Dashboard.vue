<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/shadcn/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/shadcn/ui/avatar'
import Button from '@/Components/shadcn/ui/button/Button.vue'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  stats: {
    type: Array,
    required: true,
  },
  teams: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <AppLayout title="User Dashboard">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        User Dashboard
      </h2>
    </template>

    <div class="space-y-8">
      <!-- Stats Grid -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="stat in stats" :key="stat.name">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
              {{ stat.name }}
            </CardTitle>
            <Icon :icon="stat.icon" class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stat.value }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Teams Section -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        <Card v-for="team in teams" :key="team.id" class="overflow-hidden">
          <CardHeader>
            <div class="flex items-center gap-4">
              <Avatar class="h-12 w-12">
                <AvatarFallback>{{ team.name.charAt(0) }}</AvatarFallback>
              </Avatar>
              <div>
                <CardTitle>{{ team.name }}</CardTitle>
                <CardDescription>
                  {{ team.personal_team ? 'Personal Team' : 'Team' }}
                </CardDescription>
              </div>
            </div>
          </CardHeader>
          <CardContent class="grid gap-4">
            <div class="flex items-center gap-4 text-sm">
              <div class="flex items-center gap-1">
                <Icon icon="lucide:users" class="h-4 w-4" />
                <span>{{ team.users_count }} Members</span>
              </div>
              <div class="flex items-center gap-1">
                <Icon icon="lucide:clipboard-list" class="h-4 w-4" />
                <span>{{ team.exams_count }} Exams</span>
              </div>
            </div>
            <div class="flex gap-2">
              <Button variant="outline" :as="Link" :href="route('teams.show', team.id)" class="w-full">
                <Icon icon="lucide:settings" class="mr-2 h-4 w-4" />
                Manage
              </Button>
              <Button 
                v-if="!team.personal_team" 
                variant="outline" 
                :as="Link" 
                :href="route('teams.show', team.id)" 
                class="w-full"
              >
                <Icon icon="lucide:log-in" class="mr-2 h-4 w-4" />
                Switch
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template> 