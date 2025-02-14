<script setup>
import { ref, nextTick, inject } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { useFetch } from '@vueuse/core'
import { toast } from 'vue-sonner'

// Components
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/shadcn/ui/button'
import { Textarea } from '@/Components/shadcn/ui/textarea'
import { ScrollArea } from '@/Components/shadcn/ui/scroll-area'
import { Separator } from '@/Components/shadcn/ui/separator'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/shadcn/ui/dropdown-menu'
import { Badge } from '@/Components/shadcn/ui/badge'
import ModelSelector from './Components/ModelSelector.vue'

const props = defineProps({
    chat: Object,
    messages: Array,
    models: Array,
    subscriptionEnabled: Boolean,
})

const route = inject('route')
const userInput = ref('')
const error = ref(null)
const isProcessing = ref(false)
const messagesContainer = ref(null)

const selectedModel = ref(props.models[0])
function onModelSelect(model) {
    selectedModel.value = model
}

async function submit() {
    if (!userInput.value.trim()) {
        toast.error('Please enter a message')
        return
    }

    isProcessing.value = true

    try {
        // First store the user message
        await router.post(route('chat.messages.store', props.chat.id), {
            content: userInput.value,
            role: 'user',
        })

        // Then get AI response
        const { data, error: fetchError } = await useFetch('/prism/openai/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                model: selectedModel.value,
                messages: [
                    ...props.messages.map(msg => ({
                        role: msg.role,
                        content: msg.content,
                    })),
                    { role: 'user', content: userInput.value },
                ],
            }),
        }).json()

        if (fetchError.value) {
            throw new Error('Failed to get AI response')
        }

        // Store AI response
        await router.post(route('chat.messages.store', props.chat.id), {
            content: data.value.choices[0].message.content,
            role: 'assistant',
        })

        userInput.value = ''
    } catch (err) {
        toast.error('An error occurred while processing your message')
    } finally {
        isProcessing.value = false

        // Scroll to bottom after messages update
        await nextTick()
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    }
}

function copyMessage(content) {
    navigator.clipboard.writeText(content)
    toast.success('Message copied to clipboard')
}
</script>

<template>
    <AppLayout :title="`${usePage().props.name} - ${chat.title || 'Chat'}`">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <Icon icon="lucide:message-square" class="size-5 text-primary" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">{{ chat.title || 'New Chat' }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ props.messages?.length || 0 }} messages
                        </p>
                    </div>
                </div>
                <ModelSelector :models="models" @update:model-select="onModelSelect" />
            </div>
        </template>

        <div class="flex flex-col h-[calc(100vh-8rem)]">
            <!-- Messages Area -->
            <ScrollArea ref="messagesContainer" class="flex-1 px-4">
                <div class="max-w-4xl mx-auto space-y-6 py-6">
                    <template v-if="messages.length">
                        <div v-for="message in messages" :key="message.id" class="group">
                            <div class="flex items-start gap-4 rounded-lg p-4" :class="{
                                'bg-accent/50': message.role === 'assistant'
                            }">
                                <!-- Avatar -->
                                <div
                                    class="size-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                    <Icon :icon="message.role === 'assistant' ? 'lucide:bot' : 'lucide:user'"
                                        class="size-4 text-primary" />
                                </div>

                                <!-- Message Content -->
                                <div class="flex-1 space-y-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">
                                            {{ message.role === 'assistant' ? 'AI Assistant' : 'You' }}
                                        </span>
                                        <div
                                            class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Button variant="ghost" size="icon" @click="copyMessage(message.content)">
                                                <Icon icon="lucide:clipboard" class="size-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div class="prose prose-sm dark:prose-invert max-w-none">
                                        {{ message.content }}
                                    </div>
                                </div>
                            </div>
                            <Separator class="my-4" />
                        </div>
                    </template>
                    <div v-else class="text-center text-muted-foreground py-8">
                        Start a conversation by sending a message below.
                    </div>
                </div>
            </ScrollArea>

            <!-- Input Area -->
            <div class="p-4 border-t">
                <div class="max-w-4xl mx-auto">
                    <div class="flex gap-2">
                        <Textarea v-model="userInput" placeholder="Type a message..." :disabled="isProcessing"
                            class="min-h-[60px]" :rows="1" @keydown.enter.prevent="submit" />
                        <Button @click="submit" size="icon" :disabled="isProcessing">
                            <Icon :icon="isProcessing ? 'lucide:loader-2' : 'lucide:send'" class="size-4"
                                :class="{ 'animate-spin': isProcessing }" />
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground mt-2">
                        Press <kbd class="px-1 py-0.5 bg-muted rounded text-[10px]">Enter</kbd> to send
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.prose {
    max-width: none;
}

.prose pre {
    background-color: hsl(var(--muted));
    padding: 1rem;
    border-radius: 0.5rem;
}

.prose code {
    background-color: hsl(var(--muted));
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
