<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Create Order</h1>
            <p>Take customer orders quickly</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="form-row">
                <div class="card">
                    <div class="card-header">
                        <h3>Select Items</h3>
                    </div>

                    <div class="form-group">
                        <label>Search items...</label>
                        <input type="text" class="form-control" v-model="search" placeholder="Search menu items..." />
                    </div>

                    <div v-for="category in categories" :key="category.id" style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.75rem; color: #64748b;">{{ category.icon }} {{ category.name }}</h4>
                        <div class="menu-grid">
                            <div v-for="item in filteredItems(category)" :key="item.id" class="menu-item-card" @click="addToOrder(item)">
                                <div class="item-image">{{ item.category_icon || '🍽️' }}</div>
                                <div class="item-details">
                                    <div class="item-name">{{ item.name }}</div>
                                    <div class="item-description">{{ item.description || '' }}</div>
                                    <div class="item-footer">
                                        <div class="item-price">
                                            {{ restaurantCurrency }}{{ item.current_price || item.price }}
                                            <span v-if="item.discount_price" class="old-price">{{ restaurantCurrency }}{{ item.price }}</span>
                                        </div>
                                        <button class="btn btn-primary btn-sm">+ Add</button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="filteredItems(category).length === 0" class="empty-state" style="grid-column: 1/-1; padding: 1rem;">
                                <p>No items match your search</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="position: sticky; top: 2rem; height: fit-content;">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>

                    <div class="form-group">
                        <label>Table</label>
                        <select class="form-control" v-model="orderForm.table_id">
                            <option value="">Takeaway / No table</option>
                            <option v-for="table in tables" :key="table.id" :value="table.id">
                                Table {{ table.table_number }} ({{ table.status }})
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" class="form-control" v-model="orderForm.customer_name" placeholder="Customer name..." />
                    </div>

                    <div class="form-group">
                        <label>Note</label>
                        <textarea class="form-control" v-model="orderForm.note" placeholder="Order notes..."></textarea>
                    </div>

                    <div v-if="cart.length === 0" class="empty-state" style="padding: 1rem;">
                        <p>No items added yet. Click on menu items to add.</p>
                    </div>

                    <div v-for="(item, index) in cart" :key="index" style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div><strong>{{ item.name }}</strong></div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                                <button class="btn btn-outline btn-sm" @click="updateQuantity(index, -1)" style="padding: 0.25rem 0.5rem;">-</button>
                                <span>{{ item.quantity }}</span>
                                <button class="btn btn-outline btn-sm" @click="updateQuantity(index, 1)" style="padding: 0.25rem 0.5rem;">+</button>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div>{{ restaurantCurrency }}{{ (item.price * item.quantity).toFixed(2) }}</div>
                            <button class="btn btn-danger btn-sm" @click="removeFromCart(index)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">Remove</button>
                        </div>
                    </div>

                    <div v-if="cart.length > 0" style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>{{ restaurantCurrency }}{{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between" style="margin-top: 0.25rem;">
                            <span>Tax (10%):</span>
                            <span>{{ restaurantCurrency }}{{ tax.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between" style="margin-top: 0.5rem; font-size: 1.25rem; font-weight: 700;">
                            <span>Total:</span>
                            <span>{{ restaurantCurrency }}{{ total.toFixed(2) }}</span>
                        </div>

                        <button class="btn btn-success w-full btn-lg mt-2" @click="placeOrder" :disabled="submitting">
                            {{ submitting ? 'Placing Order...' : 'Place Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from '../../components/DashboardLayout.vue';

export default {
    name: 'WaiterOrders',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            restaurant: null,
            loading: true,
            submitting: false,
            categories: [],
            tables: [],
            cart: [],
            search: '',
            orderForm: { table_id: '', customer_name: '', note: '' },
        };
    },
    computed: {
        restaurantCurrency() {
            return this.restaurant ? this.restaurant.currency : '$';
        },
        subtotal() {
            return this.cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        },
        tax() {
            return this.subtotal * 0.1;
        },
        total() {
            return this.subtotal + this.tax;
        },
    },
    async mounted() {
        await this.fetchData();
    },
    methods: {
        async fetchData() {
            this.loading = true;
            try {
                const restId = this.user.restaurant_id;
                const [restRes, menuRes, tablesRes] = await Promise.all([
                    axios.get(`/restaurants/${restId}`),
                    axios.get(`/restaurants/${restId}/menu`),
                    axios.get(`/restaurants/${restId}/tables`),
                ]);

                this.restaurant = restRes.data;
                this.categories = menuRes.data.categories;
                this.tables = tablesRes.data;
            } catch (err) {
                console.error('Failed to fetch data:', err);
            } finally {
                this.loading = false;
            }
        },
        filteredItems(category) {
            if (!this.search) return category.menu_items;
            const s = this.search.toLowerCase();
            return category.menu_items.filter(item =>
                item.name.toLowerCase().includes(s) ||
                (item.description || '').toLowerCase().includes(s)
            );
        },
        addToOrder(item) {
            const existing = this.cart.find(i => i.id === item.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: item.id,
                    name: item.name,
                    price: parseFloat(item.discount_price || item.price),
                    quantity: 1,
                });
            }
        },
        updateQuantity(index, delta) {
            const item = this.cart[index];
            item.quantity += delta;
            if (item.quantity <= 0) {
                this.cart.splice(index, 1);
            }
        },
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        async placeOrder() {
            if (this.cart.length === 0) return;
            this.submitting = true;

            try {
                const restId = this.user.restaurant_id;
                const payload = {
                    table_id: this.orderForm.table_id || null,
                    customer_name: this.orderForm.customer_name || null,
                    note: this.orderForm.note || null,
                    items: this.cart.map(item => ({
                        menu_item_id: item.id,
                        item_name: item.name,
                        unit_price: item.price,
                        quantity: item.quantity,
                    })),
                };

                await axios.post(`/restaurants/${restId}/orders`, payload);

                // Reset
                this.cart = [];
                this.orderForm = { table_id: '', customer_name: '', note: '' };
                alert('Order placed successfully!');
            } catch (err) {
                alert('Failed to place order');
                console.error(err);
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>
