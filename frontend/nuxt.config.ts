// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
    modules: [
        '@pinia/nuxt',
        '@nuxtjs/tailwindcss'
    ],
    runtimeConfig: {
        public: {
            PUSHER_KEY: process.env.PUSHER_KEY,
            PUSHER_CLUSTER: process.env.PUSHER_CLUSTER
        }
    },
    app: {
        head: {
            title: 'AI Chatbot',
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' }
            ]
        }
    }
})
