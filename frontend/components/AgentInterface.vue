<template>
    <div class="flex h-screen">
        <!-- Add logout button -->
        <div class="absolute top-4 right-4">
            <button @click="handleLogout" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                Logout
            </button>
        </div>

        <!-- Waiting Sessions List -->
        <div class="w-1/4 border-r p-4">
            <h2 class="text-lg font-semibold mb-4">Waiting Sessions</h2>
            <div class="space-y-4">
                <div v-for="session in waitingSessions" :key="session.id"
                    class="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer"
                    @click="acceptSession(session.session_id)">
                    <div class="font-medium">Session #{{ session.id }}</div>
                    <div class="text-sm text-gray-600">
                        {{ session.messages[session.messages.length - 1]?.message || 'No messages' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ formatTime(session.created_at) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Chat -->
        <div class="flex-1 flex flex-col">
            <div v-if="activeSession" class="flex-1 flex flex-col">
                <!-- Chat Messages -->
                <div class="flex-1 overflow-y-auto p-4" ref="messageContainer">
                    <ChatBubble v-for="message in activeSession.messages" :key="message.id" :message="message" />
                </div>

                <!-- Input Area -->
                <div class="border-t p-4">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input v-model="newMessage" type="text" placeholder="Type your message..."
                            class="flex-1 p-2 border rounded-lg focus:outline-none focus:border-blue-500" />
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                            :disabled="!newMessage.trim()">
                            Send
                        </button>
                    </form>
                </div>
            </div>

            <div v-else class="flex-1 flex items-center justify-center text-gray-500">
                Select a session to start chatting
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, onMounted, watch } from 'vue';
    import { useRouter } from 'vue-router';
    import { useAuthStore } from '@/stores/auth';

    interface Message {
        id: number;
        message: string;
        sender_type: 'user' | 'bot' | 'agent';
        created_at: string;
    }

    interface ChatSession {
        id: number;
        session_id: string;
        status: string;
        messages: Message[];
        created_at: string;
    }

    const waitingSessions = ref<ChatSession[]>([]);
    const activeSession = ref<ChatSession | null>(null);
    const newMessage = ref('');
    const messageContainer = ref<HTMLElement | null>(null);
    const auth = useAuthStore();
    const router = useRouter();

    onMounted(async () => {
        if (!auth.isAuthenticated) return;
        await fetchWaitingSessions();
        // Poll for new sessions every 10 seconds
        setInterval(fetchWaitingSessions, 10000);
    });

    watch(() => activeSession.value?.messages, () => {
        scrollToBottom();
    }, { deep: true });

    async function fetchWaitingSessions() {
        try {
            const response = await fetch('/api/agent/sessions/waiting', {
                headers: {
                    'Authorization': `Bearer ${auth.token}`
                }
            });
            waitingSessions.value = await response.json();
        } catch (error) {
            console.error('Failed to fetch waiting sessions:', error);
        }
    }

    async function acceptSession(sessionId: string) {
        try {
            await fetch('/api/agent/sessions/accept', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${auth.token}`
                },
                body: JSON.stringify({ session_id: sessionId })
            });

            // Get full session details including messages
            const response = await fetch(`/api/chat/messages/${sessionId}`, {
                headers: {
                    'Authorization': `Bearer ${auth.token}`
                }
            });
            const messages = await response.json();

            const session = waitingSessions.value.find(s => s.session_id === sessionId);
            if (session) {
                session.messages = messages;
                activeSession.value = session;
                waitingSessions.value = waitingSessions.value.filter(s => s.session_id !== sessionId);
            }
        } catch (error) {
            console.error('Failed to accept session:', error);
        }
    }

    async function sendMessage() {
        if (!activeSession.value || !newMessage.value.trim()) return;

        try {
            await fetch('/api/agent/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${auth.token}`
                },
                body: JSON.stringify({
                    session_id: activeSession.value.session_id,
                    message: newMessage.value.trim()
                })
            });
            newMessage.value = '';
        } catch (error) {
            console.error('Failed to send message:', error);
        }
    }

    async function handleLogout() {
        await auth.logout();
        router.push('/login');
    }

    function scrollToBottom() {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    }

    function formatTime(timestamp: string): string {
        return new Date(timestamp).toLocaleString();
    }
</script>
