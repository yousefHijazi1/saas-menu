<template>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">🍽️</div>
                <h1>SaaS Menu</h1>
                <p>Sign in to your account</p>
            </div>

            <div v-if="error" class="alert alert-danger">{{ error }}</div>

            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" v-model="form.email" required placeholder="Enter your email" />
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" v-model="form.password" required placeholder="Enter your password" />
                </div>

                <button type="submit" class="btn btn-primary w-full btn-lg" :disabled="loading">
                    {{ loading ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <div class="mt-3 text-center" style="font-size: 0.8rem; color: #94a3b8;">
                <p>Demo accounts:</p>
                <p>admin@example.com / admin@restaurant.com / waiter@example.com</p>
                <p>Password: password</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Login',
    data() {
        return {
            form: { email: '', password: '' },
            loading: false,
            error: null,
        };
    },
    methods: {
        async handleLogin() {
            this.loading = true;
            this.error = null;

            try {
                const res = await axios.post('/login', this.form);
                const { user, token } = res.data;

                localStorage.setItem('auth_token', token);
                localStorage.setItem('user', JSON.stringify(user));
                window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

                const dashboards = {
                    admin: '/admin',
                    restaurant: '/restaurant',
                    waiter: '/waiter',
                };
                this.$router.push(dashboards[user.role] || '/admin');
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed. Please check your credentials.';
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
