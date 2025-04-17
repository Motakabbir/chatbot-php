<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-semibold mb-4">How was your chat experience?</h3>

            <!-- Star Rating -->
            <div class="flex justify-center space-x-2 mb-4">
                <button v-for="star in 5" :key="star" @click="rating = star" class="text-2xl focus:outline-none"
                    :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'">
                    ★
                </button>
            </div>

            <!-- Comment Box -->
            <textarea v-model="comment" placeholder="Share your feedback (optional)"
                class="w-full p-2 border rounded-lg mb-4 focus:outline-none focus:border-blue-500" rows="3"></textarea>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-2">
                <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Skip
                </button>
                <button @click="submitFeedback" :disabled="!rating"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50">
                    Submit
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref } from 'vue';

    const rating = ref(0);
    const comment = ref('');

    const emit = defineEmits<{
        (e: 'submit', rating: number, comment: string): void;
        (e: 'close'): void;
    }>();

    function submitFeedback() {
        if (rating.value > 0) {
            emit('submit', rating.value, comment.value);
        }
    }
</script>
