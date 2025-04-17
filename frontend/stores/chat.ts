import { defineStore } from 'pinia';
import Pusher from 'pusher-js';

interface ChatMessage {
    id: number;
    message: string;
    sender_type: 'user' | 'bot' | 'agent';
    created_at: string;
}

interface ChatState {
    sessionId: string | null;
    messages: ChatMessage[];
    isConnected: boolean;
    status: 'active' | 'waiting_for_agent' | 'with_agent' | 'closed';
}

export const useChatStore = defineStore('chat', {
    state: (): ChatState => ({
        sessionId: null,
        messages: [],
        isConnected: false,
        status: 'active'
    }),

    actions: {
        async startSession() {
            try {
                const response = await fetch('/api/chat/start', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await response.json();
                this.sessionId = data.session_id;
                this.initializePusher();
            } catch (error) {
                console.error('Failed to start chat session:', error);
            }
        },

        initializePusher() {
            if (!this.sessionId) return;

            const config = useRuntimeConfig();
            const pusher = new Pusher(config.public.PUSHER_KEY as string, {
                cluster: config.public.PUSHER_CLUSTER as string,
            });

            const channel = pusher.subscribe(`chat-${this.sessionId}`);

            channel.bind('NewChatMessage', (data: { message: ChatMessage }) => {
                this.messages.push(data.message);
            });

            channel.bind('agent-status', (data: { status: ChatState['status'] }) => {
                this.status = data.status;
                if (data.status === 'with_agent') {
                    this.messages.push({
                        id: Date.now(),
                        message: 'An agent has joined the chat.',
                        sender_type: 'bot',
                        created_at: new Date().toISOString()
                    });
                }
            });
        },

        async sendMessage(message: string) {
            if (!this.sessionId || !message.trim()) return;

            try {
                const userMessage: ChatMessage = {
                    id: Date.now(),
                    message: message.trim(),
                    sender_type: 'user',
                    created_at: new Date().toISOString()
                };
                this.messages.push(userMessage);

                const response = await fetch('/api/chat/message', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: this.sessionId,
                        message: message.trim()
                    })
                });
                const data = await response.json();

                if (data.type === 'agent_handover') {
                    this.status = 'waiting_for_agent';
                }
            } catch (error) {
                console.error('Failed to send message:', error);
            }
        },

        async endSession() {
            if (!this.sessionId) return;

            try {
                await fetch('/api/chat/end', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: this.sessionId
                    })
                });
                this.status = 'closed';
            } catch (error) {
                console.error('Failed to end session:', error);
            }
        }
    }
});
