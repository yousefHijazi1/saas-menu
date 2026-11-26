<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>{{ restaurant ? restaurant.name : 'Restaurant Dashboard' }}</h1>
            <p>Manage your restaurant, menu, tables, and orders</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">📋</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.menu_items }}</div>
                        <div class="stat-label">Menu Items</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #22c55e;">🪑</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.tables }}</div>
                        <div class="stat-label">Tables</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">📦</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.active_orders }}</div>
                        <div class="stat-label">Active Orders</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce4ec; color: #ef4444;">💰</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.today_revenue }}</div>
                        <div class="stat-label">Today's Revenue</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <button class="btn btn-outline btn-sm" @click="$router.push('/restaurant/orders')">View All</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Table</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in recentOrders" :key="order.id">
                                <td>#{{ order.id }}</td>
                                <td>{{ order.customer_name || 'Walk-in' }}</td>
                                <td>{{ order.table ? order.table.table_number : '-' }}</td>
                                <td>{{ order.items.length }}</td>
                                <td>{{ restaurant.currency }}{{ order.total }}</td>
                                <td><span class="badge" :class="'badge-' + order.status">{{ order.status }}</span></td>
                                <td>{{ new Date(order.created_at).toLocaleTimeString() }}</td>
                            </tr>
                            <tr v-if="recentOrders.length === 0">
                                <td colspan="7" class="text-center">No orders yet</td>
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
    name: 'RestaurantDashboard',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            restaurant: null,
            loading: true,
            stats: { menu_items: 0, tables: 0, active_orders: 0, today_revenue: '0.00' },
            recentOrders: [],
        };
    },
    async mounted() {
        await this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const restId = this.user.restaurant_id;
                if (!restId) {
                    this.loading = false;
                    return;
                }

                const [restRes, itemsRes, tablesRes, ordersRes] = await Promise.all([
                    axios.get(`/restaurants/${restId}`),
                    axios.get(`/restaurants/${restId}/menu-items`),
                    axios.get(`/restaurants/${restId}/tables`),
                    axios.get(`/restaurants/${restId}/orders`),
                ]);

                this.restaurant = restRes.data;
                const items = itemsRes.data;
                const tables = tablesRes.data;
                const orders = ordersRes.data;

                this.stats = {
                    menu_items: items.length,
                    tables: tables.length,
                    active_orders: orders.filter(o => !['paid', 'cancelled'].includes(o.status)).length,
                    today_revenue: orders
                        .filter(o => o.status === 'paid' && new Date(o.created_at).toDateString() === new Date().toDateString())
                        .reduce((sum, o) => sum + parseFloat(o.total), 0)
                        .toFixed(2),
                };

                this.recentOrders = orders.slice(0, 10);
            } catch (err) {
                console.error('Failed to fetch data:', err);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
