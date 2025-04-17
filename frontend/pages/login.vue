<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6">Agent Login</h2>
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="email" type="email" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input v-model="password" type="password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Login
                </button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref } from 'vue';
    import { useAuthStore } from '~/stores/auth';
    import { useRouter } from 'vue-router';

    const auth = useAuthStore();
    const router = useRouter();

    const email = ref('');
    const password = ref('');
    const error = ref('');

    async function handleLogin() {
        try {
            error.value = '';
            await auth.login(email.value, password.value);
            if (auth.isAgent) {
                router.push('/agent');
            } else {
                error.value = 'Access denied. Only agents can login here.';
                await auth.logout();
            }
        } catch (e) {
            error.value = 'Invalid email or password';
        }
    }
</script>
