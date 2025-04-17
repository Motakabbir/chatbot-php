import { defineStore } from 'pinia';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as User | null,
        token: null as string | null,
    }),

    actions: {
        async login(email: string, password: string) {
            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                if (!response.ok) {
                    throw new Error('Login failed');
                }

                const data = await response.json();
                this.token = data.token;
                this.user = data.user;

                // Store token in localStorage for persistence
                localStorage.setItem('token', data.token);
            } catch (error) {
                console.error('Login error:', error);
                throw error;
            }
        },

        async logout() {
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`
                    }
                });
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        },

        async checkAuth() {
            const token = localStorage.getItem('token');
            if (!token) return;

            try {
                const response = await fetch('/api/user', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    const user = await response.json();
                    this.token = token;
                    this.user = user;
                } else {
                    this.logout();
                }
            } catch (error) {
                console.error('Auth check error:', error);
                this.logout();
            }
        }
    },

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAgent: (state) => state.user?.role === 'agent'
    }
});
