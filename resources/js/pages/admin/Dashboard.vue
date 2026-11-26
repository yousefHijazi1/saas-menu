<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Admin Dashboard</h1>
            <p>Overview of all restaurants and system statistics</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">🏪</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.total_restaurants }}</div>
                        <div class="stat-label">Total Restaurants</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #22c55e;">📋</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.total_menu_items }}</div>
                        <div class="stat-label">Menu Items</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">📦</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.total_orders }}</div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce4ec; color: #ef4444;">👥</div>
                    <div class="stat-content">
                        <div class="stat-value">{{ stats.total_users }}</div>
                        <div class="stat-label">Users</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>All Restaurants</h3>
                    <button class="btn btn-primary btn-sm" @click="showAddRestaurant = true">+ Add Restaurant</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="restaurant in restaurants" :key="restaurant.id">
                                <td><strong>{{ restaurant.name }}</strong></td>
                                <td>{{ restaurant.phone || '-' }}</td>
                                <td>{{ restaurant.email || '-' }}</td>
                                <td>
                                    <span class="badge" :class="restaurant.is_active ? 'badge-ready' : 'badge-cancelled'">
                                        {{ restaurant.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-outline btn-sm" @click="editRestaurant(restaurant)">Edit</button>
                                    <button class="btn btn-danger btn-sm" @click="deleteRestaurant(restaurant)" style="margin-left: 0.5rem;">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="restaurants.length === 0">
                                <td colspan="5" class="text-center">No restaurants found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Add/Edit Restaurant Modal -->
        <div v-if="showAddRestaurant || editingRestaurant" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ editingRestaurant ? 'Edit Restaurant' : 'Add Restaurant' }}</h3>
                    <button class="close-btn" @click="closeModal">&times;</button>
                </div>
                <form @submit.prevent="saveRestaurant">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" v-model="restaurantForm.name" required />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" v-model="restaurantForm.phone" />
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" v-model="restaurantForm.email" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" v-model="restaurantForm.address" />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" v-model="restaurantForm.description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Currency</label>
                        <input type="text" class="form-control" v-model="restaurantForm.currency" maxlength="10" />
                    </div>
                    <div v-if="editingRestaurant" class="form-group">
                        <label>
                            <input type="checkbox" v-model="restaurantForm.is_active" /> Active
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script>
import DashboardLayout from '../../components/DashboardLayout.vue';

export default {
    name: 'AdminDashboard',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            loading: true,
            saving: false,
            restaurants: [],
            stats: { total_restaurants: 0, total_menu_items: 0, total_orders: 0, total_users: 0 },
            showAddRestaurant: false,
            editingRestaurant: null,
            restaurantForm: { name: '', phone: '', email: '', address: '', description: '', currency: '$', is_active: true },
        };
    },
    async mounted() {
        await this.fetchData();
    },
    methods: {
        async fetchData() {
            try {
                const [res, statsRes] = await Promise.all([
                    axios.get('/restaurants'),
                    axios.get('/admin/restaurants'), // will be handled by stats endpoint
                ]);
                this.restaurants = res.data;
                this.stats = {
                    total_restaurants: res.data.length,
                    total_menu_items: res.data.reduce((sum, r) => sum + (r.menu_items_count || 0), 0),
                    total_orders: res.data.reduce((sum, r) => sum + (r.orders_count || 0), 0),
                    total_users: 1,
                };
            } catch (err) {
                console.error('Failed to fetch data:', err);
            } finally {
                this.loading = false;
            }
        },
        closeModal() {
            this.showAddRestaurant = false;
            this.editingRestaurant = null;
            this.restaurantForm = { name: '', phone: '', email: '', address: '', description: '', currency: '$', is_active: true };
        },
        editRestaurant(restaurant) {
            this.editingRestaurant = restaurant;
            this.restaurantForm = { ...restaurant };
        },
        async saveRestaurant() {
            this.saving = true;
            try {
                if (this.editingRestaurant) {
                    await axios.put(`/restaurants/${this.editingRestaurant.id}`, this.restaurantForm);
                } else {
                    await axios.post('/restaurants', this.restaurantForm);
                }
                await this.fetchData();
                this.closeModal();
            } catch (err) {
                console.error('Failed to save restaurant:', err);
                alert('Failed to save restaurant');
            } finally {
                this.saving = false;
            }
        },
        async deleteRestaurant(restaurant) {
            if (!confirm(`Delete "${restaurant.name}"? This action cannot be undone.`)) return;
            try {
                await axios.delete(`/restaurants/${restaurant.id}`);
                await this.fetchData();
            } catch (err) {
                console.error('Failed to delete restaurant:', err);
                alert('Failed to delete restaurant');
            }
        },
    },
};
</script>
