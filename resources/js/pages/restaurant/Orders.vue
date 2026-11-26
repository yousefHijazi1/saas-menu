<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Orders Management</h1>
            <p>View and manage all orders</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="top-bar">
                <div class="flex items-center gap-2">
                    <button v-for="s in statusFilters" :key="s.value" class="btn btn-sm"
                            :class="activeFilter === s.value ? 'btn-primary' : 'btn-outline'"
                            @click="activeFilter = s.value">
                        {{ s.label }}
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Table</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in filteredOrders" :key="order.id">
                                <td>#{{ order.id }}</td>
                                <td>{{ order.customer_name || 'Walk-in' }}</td>
                                <td>{{ order.table ? order.table.table_number : '-' }}</td>
                                <td>
                                    <div v-for="item in order.items" :key="item.id" style="font-size: 0.85rem;">
                                        {{ item.quantity }}x {{ item.item_name }}
                                    </div>
                                </td>
                                <td><strong>{{ restaurantCurrency }}{{ order.total }}</strong></td>
                                <td>
                                    <span class="badge" :class="'badge-' + order.status">{{ order.status }}</span>
                                </td>
                                <td>{{ new Date(order.created_at).toLocaleString() }}</td>
                                <td>
                                    <select class="form-control" style="width: auto; display: inline-block;"
                                            @change="updateOrderStatus(order, $event.target.value)"
                                            :value="order.status">
                                        <option value="pending">Pending</option>
                                        <option value="preparing">Preparing</option>
                                        <option value="ready">Ready</option>
                                        <option value="served">Served</option>
                                        <option value="paid">Paid</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="8" class="text-center">No orders found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from '../../components/DashboardLayout.vue';

export default {
    name: 'RestaurantOrders',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            restaurant: null,
            loading: true,
            orders: [],
            activeFilter: 'all',
            statusFilters: [
                { value: 'all', label: 'All' },
                { value: 'pending', label: 'Pending' },
                { value: 'preparing', label: 'Preparing' },
                { value: 'ready', label: 'Ready' },
                { value: 'served', label: 'Served' },
                { value: 'paid', label: 'Paid' },
                { value: 'cancelled', label: 'Cancelled' },
            ],
        };
    },
    computed: {
        restaurantCurrency() {
            return this.restaurant ? this.restaurant.currency : '$';
        },
        filteredOrders() {
            if (this.activeFilter === 'all') return this.orders;
            return this.orders.filter(o => o.status === this.activeFilter);
        },
    },
    async mounted() {
        await this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            this.loading = true;
            try {
                const restId = this.user.restaurant_id;
                const [restRes, ordersRes] = await Promise.all([
                    axios.get(`/restaurants/${restId}`),
                    axios.get(`/restaurants/${restId}/orders`),
                ]);
                this.restaurant = restRes.data;
                this.orders = ordersRes.data;
            } catch (err) {
                console.error('Failed to fetch orders:', err);
            } finally {
                this.loading = false;
            }
        },
        async updateOrderStatus(order, status) {
            try {
                await axios.put(`/orders/${order.id}/status`, { status });
                await this.fetchOrders();
            } catch (err) {
                alert('Failed to update order status');
            }
        },
    },
};
</script>
