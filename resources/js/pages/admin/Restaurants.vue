<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Manage Restaurants</h1>
            <p>Create and manage all restaurant accounts</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="card">
                <div class="card-header">
                    <h3>Restaurants</h3>
                    <button class="btn btn-primary btn-sm" @click="openAddModal">+ New Restaurant</button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Currency</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in restaurants" :key="r.id">
                                <td>{{ i + 1 }}</td>
                                <td><strong>{{ r.name }}</strong></td>
                                <td>{{ r.slug }}</td>
                                <td>{{ r.phone || '-' }}</td>
                                <td>{{ r.email || '-' }}</td>
                                <td>{{ r.currency }}</td>
                                <td>
                                    <span class="badge" :class="r.is_active ? 'badge-ready' : 'badge-cancelled'">
                                        {{ r.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ new Date(r.created_at).toLocaleDateString() }}</td>
                                <td>
                                    <button class="btn btn-outline btn-sm" @click="editRestaurant(r)">Edit</button>
                                    <button class="btn btn-danger btn-sm" @click="deleteRestaurant(r)" style="margin-left: 0.5rem;">Del</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ editingRestaurant ? 'Edit Restaurant' : 'New Restaurant' }}</h3>
                    <button class="close-btn" @click="closeModal">&times;</button>
                </div>
                <form @submit.prevent="saveRestaurant">
                    <div class="form-group">
                        <label>Restaurant Name</label>
                        <input type="text" class="form-control" v-model="form.name" required />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" v-model="form.phone" />
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" v-model="form.email" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" v-model="form.address" />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" v-model="form.description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Currency Symbol</label>
                        <input type="text" class="form-control" v-model="form.currency" maxlength="5" />
                    </div>
                    <div v-if="editingRestaurant" class="form-group">
                        <label><input type="checkbox" v-model="form.is_active" /> Active</label>
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
    name: 'AdminRestaurants',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            loading: true,
            saving: false,
            restaurants: [],
            showModal: false,
            editingRestaurant: null,
            form: { name: '', phone: '', email: '', address: '', description: '', currency: '$', is_active: true },
        };
    },
    async mounted() {
        await this.fetchRestaurants();
    },
    methods: {
        async fetchRestaurants() {
            this.loading = true;
            try {
                const res = await axios.get('/restaurants');
                this.restaurants = res.data;
            } catch (err) {
                console.error('Failed to fetch restaurants:', err);
            } finally {
                this.loading = false;
            }
        },
        openAddModal() {
            this.editingRestaurant = null;
            this.form = { name: '', phone: '', email: '', address: '', description: '', currency: '$', is_active: true };
            this.showModal = true;
        },
        editRestaurant(restaurant) {
            this.editingRestaurant = restaurant;
            this.form = { ...restaurant };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.editingRestaurant = null;
        },
        async saveRestaurant() {
            this.saving = true;
            try {
                if (this.editingRestaurant) {
                    await axios.put(`/restaurants/${this.editingRestaurant.id}`, this.form);
                } else {
                    await axios.post('/restaurants', this.form);
                }
                await this.fetchRestaurants();
                this.closeModal();
            } catch (err) {
                alert('Failed to save restaurant');
            } finally {
                this.saving = false;
            }
        },
        async deleteRestaurant(restaurant) {
            if (!confirm(`Delete "${restaurant.name}"?`)) return;
            try {
                await axios.delete(`/restaurants/${restaurant.id}`);
                await this.fetchRestaurants();
            } catch (err) {
                alert('Failed to delete restaurant');
            }
        },
    },
};
</script>
