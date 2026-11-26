<template>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🍽️ SaaS Menu</h2>
            <div class="subtitle">{{ user.name }}</div>
            <div class="subtitle" style="margin-top: 4px;">{{ roleLabel }}</div>
        </div>

        <div class="nav-section-title">Dashboard</div>

        <div v-for="item in navItems" :key="item.path" @click="navigate(item.path)"
             class="nav-item" :class="{ active: isActive(item.path) }">
            <span class="icon">{{ item.icon }}</span>
            <span>{{ item.label }}</span>
        </div>

        <!-- QR Code Button - only for restaurant owners -->
        <div v-if="user.role === 'restaurant'" style="margin-top: 1rem;">
            <div class="nav-item" @click="showQR = true">
                <span class="icon">📱</span>
                <span>QR Menu</span>
            </div>
        </div>

        <div style="margin-top: auto; padding-top: 2rem;">
            <div class="nav-item" @click="logout">
                <span class="icon">🚪</span>
                <span>Logout</span>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="showQR" class="modal-overlay" @click.self="showQR = false">
            <div class="modal-content" style="max-width: 400px; text-align: center;">
                <div class="modal-header">
                    <h3>📱 QR Menu</h3>
                    <button class="close-btn" @click="showQR = false">&times;</button>
                </div>
                <p style="color: #64748b; margin-bottom: 1rem;">
                    Customers scan this QR code to view your menu and place orders.
                </p>
                <div style="background: white; padding: 1rem; border-radius: 0.75rem; display: inline-block; border: 2px dashed #e2e8f0;">
                    <img :src="qrCodeUrl" alt="QR Code" style="width: 200px; height: 200px; display: block; margin: 0 auto;" />
                </div>
                <p style="margin-top: 1rem; font-size: 0.85rem; word-break: break-all;">
                    <strong>Menu Link:</strong><br/>
                    <a :href="menuUrl" target="_blank" style="color: #6366f1;">{{ menuUrl }}</a>
                </p>
                <div class="modal-footer" style="justify-content: center;">
                    <a :href="qrCodeUrl" download="qr-menu.png" class="btn btn-primary">
                        📥 Download QR Code
                    </a>
                    <button class="btn btn-outline" @click="copyLink" style="margin-left: 0.5rem;">
                        {{ copied ? '✅ Copied!' : '📋 Copy Link' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Sidebar',
    props: {
        user: { type: Object, required: true },
    },
    data() {
        return {
            showQR: false,
            copied: false,
            restaurantSlug: null,
        };
    },
    computed: {
        roleLabel() {
            const labels = { admin: 'Administrator', restaurant: 'Restaurant Owner', waiter: 'Waiter' };
            return labels[this.user.role] || this.user.role;
        },
        navItems() {
            const items = {
                admin: [
                    { path: '/admin', label: 'Overview', icon: '📊' },
                    { path: '/admin/restaurants', label: 'Restaurants', icon: '🏪' },
                ],
                restaurant: [
                    { path: '/restaurant', label: 'Overview', icon: '📊' },
                    { path: '/restaurant/menu', label: 'Menu Items', icon: '📋' },
                    { path: '/restaurant/tables', label: 'Tables', icon: '🪑' },
                    { path: '/restaurant/orders', label: 'Orders', icon: '📦' },
                ],
                waiter: [
                    { path: '/waiter', label: 'Overview', icon: '📊' },
                    { path: '/waiter/orders', label: 'Orders', icon: '📦' },
                ],
            };
            return items[this.user.role] || [];
        },
        menuUrl() {
            const base = window.location.origin;
            if (this.restaurantSlug) {
                return `${base}/menu/${this.restaurantSlug}`;
            }
            return base;
        },
        qrCodeUrl() {
            // Use Google Charts QR API (free, no API key needed)
            return `https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=${encodeURIComponent(this.menuUrl)}`;
        },
    },
    async mounted() {
        if (this.user.role === 'restaurant' && this.user.restaurant_id) {
            try {
                const res = await axios.get(`/restaurants/${this.user.restaurant_id}`);
                this.restaurantSlug = res.data.slug;
            } catch (err) {
                console.error('Failed to fetch restaurant slug:', err);
            }
        }
    },
    methods: {
        navigate(path) {
            if (this.$route.path !== path) {
                this.$router.push(path);
            }
        },
        isActive(path) {
            if (path === this.$route.path) return true;
            if (path !== '/' && this.$route.path.startsWith(path) && path !== '/admin') return true;
            return false;
        },
        logout() {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            delete window.axios.defaults.headers.common['Authorization'];
            this.$router.push('/login');
        },
        async copyLink() {
            try {
                await navigator.clipboard.writeText(this.menuUrl);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (err) {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = this.menuUrl;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            }
        },
    },
};
</script>
