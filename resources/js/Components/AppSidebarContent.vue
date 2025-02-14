<script setup>
import {
  SidebarContent,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/Components/shadcn/ui/sidebar'
import { Icon } from '@iconify/vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useColorMode } from '@vueuse/core'
import { computed, inject } from 'vue'
import AppTeamManager from '@/Components/AppTeamManager.vue'
const route = inject('route')
const mode = useColorMode({
  attribute: 'class',
  modes: { light: '', dark: 'dark' },
})
const page = usePage()

const topNavigationConfig = [
  {
    label: "Your Dashboard",
    items: [
      // { name: 'Home', icon: 'lucide:home', route: 'home' },
      { name: 'User Dashboard', icon: 'lucide:layout-grid', route: 'user-dashboard' },
    ],
  },
]

const navigationConfig = [
  {
    label: page.props.auth.user.current_team.name,
    items: [
      { name: 'Dashboard', icon: 'lucide:layout-dashboard', route: 'dashboard' },
      { name: 'Exams', icon: 'lucide:clipboard-list', route: 'exams.index' },
      { name: 'Activities', icon: 'lucide:target', route: 'activities.index' },
      { 
        name: 'Member Points', 
        icon: 'lucide:medal', 
        route: 'teams.member-points',
        params: { team: page.props.auth.user.current_team.id }
      },
      // { name: 'Settings', icon: 'lucide:settings', route: 'profile.show' },
      { name: 'Chat', icon: 'lucide:message-circle', route: 'chat.index' },
      { name: 'Schedule', icon: 'lucide:calendar', route: 'schedules.index' },
    ],
  },
  // {
  //   label: 'API',
  //   items: [
  //     { name: 'API Tokens', icon: 'lucide:key', route: 'api-tokens.index' },
  //     { name: 'API Documentation', icon: 'lucide:book-heart', route: 'scribe', external: true },
  //   ],
  // },
  {
    label: null,
    class: 'mt-auto',
    items: [
      {
        name: 'Support',
        icon: 'lucide:life-buoy',
        href: 'https://github.com/pushpak1300/larasonic/issues',
        external: true,
      },
      {
        name: 'Documentation',
        icon: 'lucide:book-marked',
        href: 'https://docs.larasonic.com',
        external: true,
      },
    ],
  },
]

const isDarkMode = computed(() => mode.value === 'dark')

function renderLink(item) {
  if (item.external) {
    return {
      is: 'a',
      href: item.href || route(item.route),
      target: '_blank',
    }
  }
  return {
    is: Link,
    href: route(item.route, item.params || {}),
  }
}
</script>

<template>
  <SidebarContent>
    <SidebarGroup v-for="(group, index) in topNavigationConfig" :key="index" :class="group.class">
      <SidebarGroupLabel> {{ group.label }} </SidebarGroupLabel>
      <SidebarMenu>
        <SidebarMenuItem v-for="item in group.items" :key="item.name" :class="{ 'font-semibold text-primary bg-secondary rounded': !item.external && route().current(item.route) }">
          <SidebarMenuButton as-child>
            <component v-bind="renderLink(item)" :is="item.external ? 'a' : Link" prefetch>
              <Icon :icon="item.icon" />
              {{ item.name }}
            </component>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>
    <SidebarMenuItem>
              <AppTeamManager v-if="$page.props.jetstream.hasTeamFeatures" />
            </SidebarMenuItem>
    <SidebarGroup v-for="(group, index) in navigationConfig" :key="index" :class="group.class">
    

      <SidebarGroupLabel v-if="group.label">
        {{ group.label }}
      </SidebarGroupLabel>
      <SidebarMenu>
        <SidebarMenuItem
          v-for="item in group.items"
          :key="item.name"
          :class="{ 'font-semibold text-primary bg-secondary rounded': !item.external && route().current(item.route) }"
        >
          <SidebarMenuButton as-child>
            <component v-bind="renderLink(item)" :is="item.external ? 'a' : Link" prefetch>
              <Icon :icon="item.icon" />
              {{ item.name }}
            </component>
          </SidebarMenuButton>
        </SidebarMenuItem>
        <SidebarMenuItem v-if="index === navigationConfig.length - 1">
          <SidebarMenuButton @click="mode = isDarkMode ? 'light' : 'dark'">
            <Icon :icon="isDarkMode ? 'lucide:moon' : 'lucide:sun'" />
            {{ isDarkMode ? 'Dark' : 'Light' }} Mode
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarGroup>
  </SidebarContent>
</template>
