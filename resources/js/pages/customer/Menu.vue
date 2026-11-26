<template>
    <div class="customer-menu">
        <div class="restaurant-header">
            <div class="restaurant-info">
                <h1>{{ restaurant.name }}</h1>
                <p v-if="restaurant.description">{{ restaurant.description }}</p>
                <div class="restaurant-contact" v-if="restaurant.address || restaurant.phone">
                    <span v-if="restaurant.address">📍 {{ restaurant.address }}</span>
                    <span v-if="restaurant.phone">📞 {{ restaurant.phone }}</span>
                </div>
            </div>
        </div>

        <div v-if="error" class="alert alert-danger" style="margin: 1rem;">{{ error }}</div>

        <div class="search-bar">
            <input type="text" v-model="search" placeholder="🔍 Search menu items..." class="form-control" />
        </div>

        <div v-if="!loading && !error && categories.length > 0" class="menu-section">
            <div v-for="category in categories" :key="category.id" class="category-section">
                <div class="category-title">
                    <span class="category-icon">{{ category.icon || '🍽️' }}</span>
                    <h2>{{ category.name }}</h2>
                </div>
                <div class="customer-menu-grid">
                    <div v-for="item in filteredItems(category)" :key="item.id" class="customer-menu-card" @click="addToCart(item)">
                        <div class="item-content">
                            <div class="item-header">
                                <h3>{{ item.name }}</h3>
                                <div class="item-price">
                                    {{ restaurant.currency }}{{ item.discount_price || item.price }}
                                    <span v-if="item.discount_price" class="old-price">{{ restaurant.currency }}{{ item.price }}</span>
                                </div>
                            </div>
                            <p v-if="item.description" class="item-desc">{{ item.description }}</p>
                            <div class="item-meta">
                                <span v-if="item.is_featured" class="featured-badge">⭐ Featured</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredItems(category).length === 0" class="empty-state" style="grid-column: 1/-1; padding: 1rem;"><p>No items match your search</p></div>
                </div>
            </div>
        </div>

        <div v-if="!loading && !error && categories.length === 0" class="empty-state" style="padding: 3rem;">
            <div class="empty-icon">📋</div>
            <h3>Menu is empty</h3>
            <p>No items available yet.</p>
        </div>

        <!-- Cart Floating Button -->
        <div v-if="cart.length > 0" class="cart-floating" @click="showCart = true">
            <div class="cart-badge">{{ cart.length }}</div>
            <span>View Order - {{ restaurant.currency }}{{ cartTotal.toFixed(2) }}</span>
        </div>

        <!-- Cart Modal -->
        <div v-if="showCart" class="modal-overlay" @click.self="showCart = false">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3>🛒 Your Order</h3>
                    <button class="close-btn" @click="showCart = false">&times;</button>
                </div>

                <!-- Select Order Type -->
                <div class="form-group">
                    <label>Order Type</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="type-btn" style="flex:1;" :class="{ active: orderType === 'dinein' }" @click="orderType = 'dinein'">🍽️ Dine In</button>
                        <button class="type-btn" style="flex:1;" :class="{ active: orderType === 'takeaway' }" @click="orderType = 'takeaway'">🥡 Take Away</button>
                    </div>
                </div>

                <!-- Dine In: Table -->
                <div v-if="orderType === 'dinein'" class="form-group">
                    <label>Table Number <span style="color: #ef4444;">*</span></label>
                    <select class="form-control" v-model="selectedTable" required>
                        <option value="">Select your table</option>
                        <option v-for="table in tables" :key="table.id" :value="table.table_number">Table {{ table.table_number }}</option>
                    </select>
                </div>

                <!-- Take Away: Name + Address -->
                <div v-if="orderType === 'takeaway'" class="form-group">
                    <label>Your Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="form-control" v-model="customerName" placeholder="Enter your name" required />
                </div>
                <div v-if="orderType === 'takeaway'" class="form-group">
                    <label>Delivery Address <span style="color: #ef4444;">*</span></label>
                    <textarea class="form-control" v-model="customerAddress" placeholder="Enter your delivery address" rows="2" required></textarea>
                </div>

                <!-- Cart Items -->
                <div v-for="(item, index) in cart" :key="index" class="cart-item">
                    <div class="cart-item-info">
                        <div><strong>{{ item.name }}</strong></div>
                        <div class="cart-item-price">{{ restaurant.currency }}{{ item.price }} each</div>
                    </div>
                    <div class="cart-item-qty">
                        <button class="qty-btn" @click="updateQty(index, -1)">−</button>
                        <span>{{ item.quantity }}</span>
                        <button class="qty-btn" @click="updateQty(index, 1)">+</button>
                    </div>
                    <div class="cart-item-total">{{ restaurant.currency }}{{ (item.price * item.quantity).toFixed(2) }}</div>
                </div>

                <div v-if="cart.length === 0" class="empty-state" style="padding: 1rem;"><p>Your cart is empty</p></div>

                <div class="cart-summary" v-if="cart.length > 0">
                    <div class="summary-row"><span>Subtotal</span><span>{{ restaurant.currency }}{{ subtotal.toFixed(2) }}</span></div>
                    <div class="summary-row"><span>Tax (10%)</span><span>{{ restaurant.currency }}{{ tax.toFixed(2) }}</span></div>
                    <div class="summary-row total"><span>Total</span><span>{{ restaurant.currency }}{{ cartTotal.toFixed(2) }}</span></div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline" @click="showCart = false">Continue Browsing</button>
                    <button class="btn" :class="orderType === 'dinein' ? 'btn-success' : 'btn-primary'" @click="placeOrder" :disabled="!canPlaceOrder">
                        {{ submitting ? 'Sending...' : (orderType === 'takeaway' ? '📱 Order via WhatsApp' : '📱 Send to WhatsApp') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="call-waiter-fab" @click="showCallWaiter = true" title="Call Waiter">🔔</div>
        <div v-if="showCallWaiter" class="modal-overlay" @click.self="showCallWaiter = false">
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header"><h3>🔔 Call Waiter</h3><button class="close-btn" @click="showCallWaiter = false">&times;</button></div>
                <div class="form-group"><label>Table Number <span style="color: #ef4444;">*</span></label><input type="text" class="form-control" v-model="callForm.table_number" placeholder="Enter your table number" /></div>
                <div class="form-group"><label>Note (optional)</label><textarea class="form-control" v-model="callForm.note" placeholder="e.g. Need water, more napkins..."></textarea></div>
                <div class="modal-footer">
                    <button class="btn btn-outline" @click="showCallWaiter = false">Cancel</button>
                    <button class="btn btn-warning" @click="callWaiter" :disabled="callingWaiter || !callForm.table_number">{{ callingWaiter ? 'Calling...' : '🔔 Call Waiter' }}</button>
                </div>
            </div>
        </div>

        <div v-if="successMessage" class="notification success">✅ {{ successMessage }}</div>
        <div v-if="loading" class="loading-screen"><div class="spinner"></div><p>Loading menu...</p></div>
    </div>
</template>

<script>
const WHATSAPP_NUMBER = '96181086955';

export default {
    name: 'CustomerMenu',
    data() {
        return {
            restaurant: {},
            categories: [],
            tables: [],
            loading: true,
            error: null,
            search: '',
            orderType: 'dinein',
            selectedTable: '',
            customerName: '',
            customerAddress: '',
            cart: [],
            showCart: false,
            submitting: false,
            showCallWaiter: false,
            callingWaiter: false,
            callForm: { table_number: '', note: '' },
            successMessage: '',
        };
    },
    computed: {
        subtotal() { return this.cart.reduce((s, i) => s + i.price * i.quantity, 0); },
        tax() { return this.subtotal * 0.1; },
        cartTotal() { return this.subtotal + this.tax; },
        canPlaceOrder() {
            if (!this.cart.length || !this.orderType) return false;
            if (this.orderType === 'dinein' && !this.selectedTable) return false;
            if (this.orderType === 'takeaway' && (!this.customerName || !this.customerAddress)) return false;
            return true;
        },
    },
    async mounted() {
        const slug = this.$route.params.slug;
        try {
            const res1 = await axios.get('/restaurants');
            const found = res1.data.find(r => r.slug === slug || r.id == slug);
            if (!found) {
                this.error = 'Restaurant not found. Please check the link.';
                this.loading = false;
                return;
            }
            this.restaurant = found;
            const [resMenu, resTables] = await Promise.all([
                axios.get(`/restaurants/${found.id}/menu`),
                axios.get(`/restaurants/${found.id}/tables`),
            ]);
            this.categories = resMenu.data.categories || [];
            this.tables = resTables.data || [];
        } catch (err) {
            this.error = 'Failed to load menu. Please try again later.';
            console.error(err);
        } finally {
            this.loading = false;
        }
    },
    methods: {
        filteredItems(category) {
            if (!this.search) return category.menu_items;
            const s = this.search.toLowerCase();
            return category.menu_items.filter(item =>
                item.name.toLowerCase().includes(s) || (item.description || '').toLowerCase().includes(s)
            );
        },
        addToCart(item) {
            const price = parseFloat(item.discount_price || item.price);
            const existing = this.cart.find(i => i.id === item.id);
            existing ? existing.quantity++ : this.cart.push({ id: item.id, name: item.name, price, quantity: 1 });
            this.showSuccess(`${item.name} added to cart!`);
        },
        updateQty(index, delta) {
            const item = this.cart[index];
            item.quantity += delta;
            if (item.quantity <= 0) this.cart.splice(index, 1);
        },
        placeOrder() {
            if (!this.canPlaceOrder) return;
            this.submitting = true;

            let message = `🛵 *New Order - ${this.restaurant.name}*\n`;
            message += `━━━━━━━━━━━━━━━\n`;
            message += `*Type:* ${this.orderType === 'dinein' ? '🍽️ Dine In' : '🥡 Take Away'}\n`;

            if (this.orderType === 'dinein') {
                message += `*Table:* ${this.selectedTable}\n`;
            } else {
                message += `*Name:* ${this.customerName}\n`;
                message += `*Address:* ${this.customerAddress}\n`;
            }

            message += `━━━━━━━━━━━━━━━\n`;
            message += `*Items:*\n`;
            this.cart.forEach(item => {
                message += `• ${item.name} x${item.quantity} = ${this.restaurant.currency}${(item.price * item.quantity).toFixed(2)}\n`;
            });

            message += `━━━━━━━━━━━━━━━\n`;
            message += `*Subtotal:* ${this.restaurant.currency}${this.subtotal.toFixed(2)}\n`;
            message += `*Tax (10%):* ${this.restaurant.currency}${this.tax.toFixed(2)}\n`;
            message += `*Total:* ${this.restaurant.currency}${this.cartTotal.toFixed(2)}\n`;

            if (this.orderType === 'dinein') {
                message += `\n_We will bring your order to your table._ 🍽️`;
            } else {
                message += `\n_We will prepare your order for pickup / delivery._ 🥡`;
            }

            const encoded = encodeURIComponent(message);
            const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encoded}`;

            // Open WhatsApp
            window.open(whatsappUrl, '_blank');

            // Reset cart
            this.cart = [];
            this.showCart = false;
            this.submitting = false;
            this.showSuccess('Order sent to WhatsApp! 📱');
        },
        async callWaiter() {
            if (!this.callForm.table_number) return;
            this.callingWaiter = true;
            try {
                await axios.post(`/restaurants/${this.restaurant.id}/waiter-call`, this.callForm);
                this.showCallWaiter = false;
                this.callForm = { table_number: '', note: '' };
                this.showSuccess('Waiter has been called! 🛎️');
            } catch (err) { alert('Failed to call waiter. Please try again.'); } finally { this.callingWaiter = false; }
        },
        showSuccess(msg) { this.successMessage = msg; setTimeout(() => this.successMessage = '', 3000); },
    },
};
</script>

<style scoped>
.customer-menu { max-width: 800px; margin: 0 auto; padding-bottom: 100px; min-height: 100vh; background: #f8fafc; }
.restaurant-header { background: linear-gradient(135deg, #1e293b 0%, #475569 100%); color: white; padding: 2rem 1.5rem; text-align: center; }
.restaurant-info h1 { font-size: 1.75rem; margin-bottom: 0.5rem; }
.restaurant-info p { font-size: 0.9rem; opacity: 0.85; margin-bottom: 0.75rem; }
.restaurant-contact { font-size: 0.8rem; opacity: 0.7; display: flex; flex-direction: column; gap: 0.25rem; }
.search-bar { padding: 0.75rem 1.5rem; background: white; border-bottom: 1px solid #e2e8f0; }
.search-bar input { border-radius: 2rem; padding: 0.75rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; }
.search-bar input:focus { background: white; }
.menu-section { padding: 1rem 1.5rem; }
.category-section { margin-bottom: 2rem; }
.category-title { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.category-icon { font-size: 1.5rem; }
.category-title h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; }
.customer-menu-grid { display: grid; gap: 0.75rem; }
.customer-menu-card { background: white; border-radius: 0.75rem; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); cursor: pointer; transition: all 0.2s; border: 1px solid #e2e8f0; }
.customer-menu-card:hover { border-color: #6366f1; box-shadow: 0 4px 12px rgba(99,102,241,0.15); transform: translateY(-1px); }
.item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem; }
.item-header h3 { font-size: 1rem; font-weight: 600; color: #1e293b; }
.item-price { font-size: 1.1rem; font-weight: 700; color: #6366f1; white-space: nowrap; margin-left: 1rem; }
.old-price { font-size: 0.8rem; color: #94a3b8; text-decoration: line-through; margin-left: 0.35rem; font-weight: 400; }
.item-desc { font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem; }
.featured-badge { font-size: 0.7rem; padding: 0.15rem 0.5rem; background: #fef3c7; color: #92400e; border-radius: 9999px; }
.type-btn { padding: 0.6rem 1rem; border: 2px solid #e2e8f0; border-radius: 0.75rem; background: white; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.type-btn.active { border-color: #6366f1; background: #eef2ff; color: #6366f1; }
.cart-floating { position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%); background: #6366f1; color: white; padding: 0.875rem 2rem; border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 20px rgba(99,102,241,0.4); display: flex; align-items: center; gap: 0.75rem; z-index: 50; transition: transform 0.2s; }
.cart-floating:hover { transform: translateX(-50%) scale(1.05); }
.cart-badge { width: 28px; height: 28px; background: white; color: #6366f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; }
.cart-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0; }
.cart-item-info { flex: 1; }
.cart-item-price { font-size: 0.8rem; color: #64748b; }
.cart-item-qty { display: flex; align-items: center; gap: 0.5rem; margin: 0 1rem; }
.qty-btn { width: 30px; height: 30px; border-radius: 50%; border: 1px solid #e2e8f0; background: white; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.cart-item-total { font-weight: 600; min-width: 60px; text-align: right; }
.cart-summary { padding: 1rem 0; border-top: 2px solid #e2e8f0; margin-top: 0.5rem; }
.summary-row { display: flex; justify-content: space-between; padding: 0.25rem 0; font-size: 0.9rem; }
.summary-row.total { font-size: 1.2rem; font-weight: 700; color: #1e293b; padding-top: 0.5rem; border-top: 1px solid #e2e8f0; margin-top: 0.5rem; }
.call-waiter-fab { position: fixed; bottom: 5rem; right: 1.5rem; width: 56px; height: 56px; background: #f59e0b; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 16px rgba(245,158,11,0.4); z-index: 49; transition: transform 0.2s; animation: pulse 2s infinite; }
.call-waiter-fab:hover { transform: scale(1.1); }
@keyframes pulse { 0% { box-shadow: 0 4px 16px rgba(245,158,11,0.4); } 50% { box-shadow: 0 4px 24px rgba(245,158,11,0.6); } 100% { box-shadow: 0 4px 16px rgba(245,158,11,0.4); } }
.notification { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%); padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 500; z-index: 999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideDown 0.3s ease; }
.notification.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
@keyframes slideDown { from { transform: translateX(-50%) translateY(-100%); opacity: 0; } to { transform: translateX(-50%) translateY(0); opacity: 1; } }
.loading-screen { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; gap: 1rem; }
.loading-screen .spinner { width: 48px; height: 48px; border: 4px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@media (max-width: 600px) { .customer-menu { padding-bottom: 120px; } .menu-section { padding: 0.75rem 1rem; } }
</style>
