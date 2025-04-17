<template>
    <div>
        <AgentInterface />
    </div>
</template>

<script setup lang="ts">
    import { onMounted } from 'vue';
    import { useAuthStore } from '~/stores/auth';
    import { useRouter } from 'vue-router';
    import AgentInterface from '~/components/AgentInterface.vue';

    const auth = useAuthStore();
    const router = useRouter();

    onMounted(async () => {
        await auth.checkAuth();
        if (!auth.isAuthenticated || !auth.isAgent) {
            router.push('/login');
        }
    });
</script>
