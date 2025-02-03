<script setup>
import { ref } from 'vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Icon } from '@iconify/vue'
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/Components/shadcn/ui/collapsible'
import { Badge } from '@/Components/shadcn/ui/badge'

const props = defineProps({
  title: String,
  description: String,
  icon: String,
  totalQuestions: Number,
  totalPoints: Number,
})

const isOpen = ref(true)
</script>

<template>
  <Collapsible
    :open="isOpen"
    @update:open="isOpen = $event"
    class="border rounded-lg bg-card"
  >
    <div class="p-4 flex items-start justify-between">
      <div class="space-y-1.5">
        <div class="flex items-center gap-2">
          <Icon :icon="icon" class="h-5 w-5 text-primary" />
          <h3 class="font-semibold">{{ title }}</h3>
          <Badge variant="secondary" class="ml-2">
            {{ totalQuestions }} questions
          </Badge>
          <Badge variant="outline">
            {{ totalPoints }} points
          </Badge>
        </div>
        <p class="text-sm text-muted-foreground">
          {{ description }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <slot name="actions" />
        <CollapsibleTrigger asChild>
          <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
            <Icon
              :icon="isOpen ? 'lucide:chevron-up' : 'lucide:chevron-down'"
              class="h-4 w-4"
            />
          </Button>
        </CollapsibleTrigger>
      </div>
    </div>
    <CollapsibleContent class="px-4 pb-4">
      <slot />
    </CollapsibleContent>
  </Collapsible>
</template> 