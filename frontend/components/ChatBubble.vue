<template>
    <div class="flex mb-4" :class="message.sender_type === 'user' ? 'justify-end' : 'justify-start'">
        <div class="max-w-[70%] rounded-lg p-3" :class="[
            message.sender_type === 'user'
                ? 'bg-blue-500 text-white rounded-br-none'
                : message.sender_type === 'agent'
                    ? 'bg-green-500 text-white rounded-bl-none'
                    : 'bg-gray-200 text-gray-800 rounded-bl-none'
        ]">
            <div class="text-sm mb-1" v-if="message.sender_type !== 'user'">
                {{ message.sender_type === 'agent' ? 'Agent' : 'AI Assistant' }}
            </div>
            <div class="break-words">{{ message.message }}</div>
            <div class="text-xs mt-1 opacity-75">
                {{ formatTime(message.created_at) }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    interface Message {
        id: number;
        message: string;
        sender_type: 'user' | 'bot' | 'agent';
        created_at: string;
    }

    const props = defineProps<{
        message: Message;
    }>();

    function formatTime(timestamp: string): string {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
</script>
