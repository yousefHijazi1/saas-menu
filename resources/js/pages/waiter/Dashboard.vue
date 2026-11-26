<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Waiter Dashboard</h1>
            <p>View orders and manage table service</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">⏳</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.pending }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">👨‍🍳</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.preparing }}</div>
                        <div class="stat-label">Preparing</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #22c55e;">✅</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.ready }}</div>
                        <div class="stat-label">Ready to Serve</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f3e8ff; color: #8b5cf6;">🍽️</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.served }}</div>
                        <div class="stat-label">Served</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Active Orders</h3>
                    <button class="btn btn-primary btn-sm" @click="$router.push('/waiter/orders')">New Order</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Table</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in activeOrders" :key="order.id">
                                <td>#{{ order.id }}</td>
                                <td><strong>{{ order.table ? order.table.table_number : 'Takeaway' }}</strong></td>
                                <td>{{ order.customer_name || '-' }}</td>
                                <td>{{ order.items.length }} items</td>
                                <td>{{ restaurantCurrency }}{{ order.total }}</td>
                                <td><span class="badge" :class="'badge-' + order.status">{{ order.status }}</span></td>
                                <td>{{ new Date(order.created_at).toLocaleTimeString() }}</td>
                            </tr>
                            <tr v-if="activeOrders.length === 0">
                                <td colspan="7" class="text-center">No active orders</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Waiter Calls Section -->
            <div class="card" v-if="waiterCalls.length > 0">
                <div class="card-header">
                    <h3>🔔 Waiter Calls</h3>
                    <button class="btn btn-outline btn-sm" @click="refreshCalls">Refresh</button>
                </div>
                <div class="alert alert-warning" v-for="call in waiterCalls" :key="call.id" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>Table {{ call.table_number }}</strong>
                        <span v-if="call.note"> - {{ call.note }}</span>
                        <br/><small>{{ new Date(call.created_at).toLocaleTimeString() }}</small>
                    </div>
                    <button class="btn btn-success btn-sm" @click="handleCall(call)">✅ Done</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Tables Overview</h3>
                    <button class="btn btn-outline btn-sm" @click="refreshTables">Refresh</button>
                </div>
                <div class="tables-grid">
                    <div v-for="table in tables" :key="table.id"
                         class="table-card" :class="table.status">
                        <div class="table-number">{{ table.table_number }}</div>
                        <div class="table-status">{{ table.status }}</div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">Cap: {{ table.capacity }}</div>
                    </div>
                </div>
                <div v-if="tables.length === 0" class="empty-state" style="padding: 1rem;">
                    <p>No tables set up yet</p>
                </div>
            </div>
        </template>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from '../../components/DashboardLayout.vue';

export default {
    name: 'WaiterDashboard',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            restaurant: null,
            loading: true,
            orders: [],
            tables: [],
            waiterCalls: [],
        };
    },
    computed: {
        restaurantCurrency() {
            return this.restaurant ? this.restaurant.currency : '$';
        },
        stats() {
            return {
                pending: this.orders.filter(o => o.status === 'pending').length,
                preparing: this.orders.filter(o => o.status === 'preparing').length,
                ready: this.orders.filter(o => o.status === 'ready').length,
                served: this.orders.filter(o => o.status === 'served').length,
            };
        },
        activeOrders() {
            return this.orders.filter(o => !['paid', 'cancelled'].includes(o.status));
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
                if (!restId) return;

                const [restRes, ordersRes, tablesRes, callsRes] = await Promise.all([
                    axios.get(`/restaurants/${restId}`),
                    axios.get(`/restaurants/${restId}/orders`),
                    axios.get(`/restaurants/${restId}/tables`),
                    axios.get(`/restaurants/${restId}/waiter-calls`),
                ]);

                this.restaurant = restRes.data;
                this.orders = ordersRes.data;
                this.tables = tablesRes.data;
                this.waiterCalls = callsRes.data;
            } catch (err) {
                console.error('Failed to fetch data:', err);
            } finally {
                this.loading = false;
            }
        },
        refreshTables() {
            this.fetchData();
        },
        refreshCalls() {
            this.fetchData();
        },
        async handleCall(call) {
            try {
                await axios.put(`/waiter-calls/${call.id}/handled`);
                this.waiterCalls = this.waiterCalls.filter(c => c.id !== call.id);
            } catch (err) {
                console.error('Failed to handle call:', err);
            }
        },
    },
};
</script>
