<template>
  <div class="flex flex-col h-screen">
    <!-- Chat Header -->
    <div class="bg-white border-b p-4">
      <h2 class="text-lg font-semibold">Chat Support</h2>
      <p v-if="chatStore.status === 'waiting_for_agent'" class="text-sm text-yellow-600">
        Connecting to an agent...
      </p>
      <p v-else-if="chatStore.status === 'with_agent'" class="text-sm text-green-600">
        Connected with an agent
      </p>
    </div>

    <!-- Chat Messages -->
    <div class="flex-1 overflow-y-auto p-4" ref="messageContainer">
      <ChatBubble
        v-for="message in chatStore.messages"
        :key="message.id"
        :message="message"
      />
    </div>

    <!-- Input Area -->
    <div class="border-t p-4 bg-white">
      <form @submit.prevent="sendMessage" class="flex gap-2">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Type your message..."
          class="flex-1 p-2 border rounded-lg focus:outline-none focus:border-blue-500"
          :disabled="chatStore.status === 'closed'"
        />
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50"
          :disabled="!newMessage.trim() || chatStore.status === 'closed'"
        >
          Send
        </button>
      </form>
    </div>

    <!-- Feedback Dialog -->
    <FeedbackDialog
      v-if="showFeedback"
      @submit="submitFeedback"
      @close="showFeedback = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useChatStore } from '~/stores/chat';

const chatStore = useChatStore();
const newMessage = ref('');
const messageContainer = ref<HTMLElement | null>(null);
const showFeedback = ref(false);

onMounted(async () => {
  await chatStore.startSession();
});

watch(() => chatStore.messages, () => {
  // Scroll to bottom when new messages arrive
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
  });
}, { deep: true });

async function sendMessage() {
  if (!newMessage.value.trim()) return;
  
  await chatStore.sendMessage(newMessage.value);
  newMessage.value = '';
}

async function submitFeedback(rating: number, comment: string) {
  try {
    await fetch('/api/feedback/submit', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        session_id: chatStore.sessionId,
        rating,
        comment
      })
    });
    showFeedback.value = false;
  } catch (error) {
    console.error('Failed to submit feedback:', error);
  }
}

// Show feedback dialog when chat ends
watch(() => chatStore.status, (newStatus) => {
  if (newStatus === 'closed') {
    showFeedback.value = true;
  }
});
</script>
