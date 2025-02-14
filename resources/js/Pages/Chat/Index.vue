<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

// Components
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Input } from '@/Components/shadcn/ui/input'
import { Command, CommandInput } from '@/Components/shadcn/ui/command'
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/Components/shadcn/ui/sheet'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/Components/shadcn/ui/dropdown-menu'
import { ScrollArea } from '@/Components/shadcn/ui/scroll-area'
import { Badge } from '@/Components/shadcn/ui/badge'
import ChatShow from './Show.vue'

const props = defineProps({
    chats: Array,
    currentTeam: Object,
    models: Array,
    subscriptionEnabled: Boolean,
    chat: Object,
    messages: Array,
})

const route = inject('route')
const searchQuery = ref('')
const isCreatingChat = ref(false)
const isSheetOpen = ref(true)

const filteredChats = computed(() => {
    if (!searchQuery.value) return props.chats
    const query = searchQuery.value.toLowerCase()
    return props.chats.filter(chat =>
        chat.title?.toLowerCase().includes(query) ||
        chat.messages?.[chat.messages.length - 1]?.content.toLowerCase().includes(query)
    )
})

const sortedChats = computed(() => {
    return [...filteredChats.value].sort((a, b) =>
        new Date(b.updated_at) - new Date(a.updated_at)
    )
})

function createNewChat() {
    isCreatingChat.value = true
    router.post(route('chat.store'), {}, {
        onSuccess: () => {
            isCreatingChat.value = false
            toast.success('New chat created')
        },
        onError: () => {
            isCreatingChat.value = false
            toast.error('Failed to create chat')
        }
    })
}

function deleteChat(chatId) {
    toast.promise(
        new Promise((resolve, reject) => {
            router.delete(route('chat.destroy', chatId), {
                onSuccess: () => resolve(),
                onError: () => reject()
            })
        }),
        {
            loading: 'Deleting chat...',
            success: 'Chat deleted successfully',
            error: 'Failed to delete chat'
        }
    )
}

function formatDate(date) {
    const now = new Date()
    const chatDate = new Date(date)

    if (chatDate.toDateString() === now.toDateString()) {
        return chatDate.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit'
        })
    }

    return chatDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    })
}

onMounted(() => {
    if (!props.subscriptionEnabled) {
        toast.warning('Pro Subscription Required', {
            description: 'Upgrade to access all features',
            action: {
                label: 'Upgrade',
                onClick: () => router.visit(route('subscriptions.create'))
            },
            duration: 5000
        })
    }
})
</script>

<template>
    <AppLayout :title="`${usePage().props.name} - AI Chat`">
        <div class="flex h-[calc(100vh-4rem)]">
            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col">
                <div class="p-4 border-b">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold leading-tight flex items-center gap-2">
                            <Icon icon="lucide:message-square" class="size-6" />
                            {{ currentTeam.name }} - AI Chat
                        </h2>
                        <div class="flex items-center gap-2">
                            <Button @click="createNewChat" variant="default" class="gap-2" :disabled="isCreatingChat">
                                <Icon :icon="isCreatingChat ? 'lucide:loader-2' : 'lucide:plus'" class="size-4"
                                    :class="{ 'animate-spin': isCreatingChat }" />
                                {{ isCreatingChat ? 'Creating...' : 'New Chat' }}
                            </Button>
                            <Sheet v-model:open="isSheetOpen">
                                <SheetTrigger asChild>
                                    <Button variant="outline" size="icon">
                                        <Icon icon="lucide:sidebar" class="size-4" />
                                    </Button>
                                </SheetTrigger>
                                <SheetContent class="w-[400px] sm:w-[540px]" side="right">
                                    <SheetHeader>
                                        <SheetTitle>Chat History</SheetTitle>
                                    </SheetHeader>
                                    <div class="mt-4 space-y-4">
                                        <Command class="rounded-lg border shadow-md">
                                            <CommandInput v-model="searchQuery" placeholder="Search conversations..." />
                                        </Command>

                                        <ScrollArea class="h-[calc(100vh-12rem)]">
                                            <div class="grid gap-3">
                                                <div v-for="chat in sortedChats" :key="chat.id" class="group relative">
                                                    <Link :href="route('chat.show', chat.id)"
                                                        class="block p-4 rounded-lg border bg-card hover:shadow-lg hover:bg-accent/50 transition-all duration-200">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                                <Icon icon="lucide:message-square"
                                                                    class="size-5 text-primary" />
                                                            </div>
                                                            <div>
                                                                <h3 class="font-medium">{{ chat.title || 'New Chat' }}
                                                                </h3>
                                                                <p class="text-sm text-muted-foreground">
                                                                    {{ formatDate(chat.updated_at) }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="ghost" size="icon"
                                                                    class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                                    <Icon icon="lucide:more-vertical" class="size-4" />
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                <DropdownMenuItem @click="deleteChat(chat.id)"
                                                                    class="text-destructive cursor-pointer">
                                                                    <Icon icon="lucide:trash-2" class="size-4 mr-2" />
                                                                    Delete Chat
                                                                </DropdownMenuItem>
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                    </div>

                                                    <p
                                                        class="text-sm text-muted-foreground line-clamp-2 group-hover:text-foreground/90 transition-colors">
                                                        {{ chat.messages?.[chat.messages?.length - 1]?.content }}
                                                    </p>

                                                    <div class="flex items-center gap-2 mt-3">
                                                        <Badge variant="secondary" class="text-xs">
                                                            {{ chat.messages?.length || 0 }} messages
                                                        </Badge>
                                                    </div>
                                                    </Link>
                                                </div>
                                            </div>
                                        </ScrollArea>
                                    </div>
                                </SheetContent>
                            </Sheet>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Content Area -->
                <div class="flex-1 overflow-hidden">
                    <ChatShow v-if="chat" :chat="chat" :messages="messages" :models="models"
                        :subscription-enabled="subscriptionEnabled" />
                    <div v-else class="h-full flex items-center justify-center">
                        <div class="text-center space-y-4">
                            <div class="size-16 rounded-full bg-muted/20 flex items-center justify-center mx-auto">
                                <Icon icon="lucide:message-square" class="size-8 text-muted-foreground" />
                            </div>
                            <h3 class="text-lg font-semibold">Select a Chat</h3>
                            <p class="text-sm text-muted-foreground">
                                Choose a conversation from the sidebar or start a new one
                            </p>
                            <Button @click="createNewChat" variant="default" class="mt-2">
                                <Icon icon="lucide:plus" class="size-4 mr-2" />
                                Start New Chat
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.list-leave-active {
    position: absolute;
}
</style>
