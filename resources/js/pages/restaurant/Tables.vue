<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Table Management</h1>
            <p>Manage your restaurant tables and their status</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="top-bar">
                <div>
                    <button class="btn btn-primary btn-sm" @click="openAddModal">+ Add Table</button>
                </div>
                <div>
                    <span class="badge badge-available" style="margin-right: 0.5rem;">● Available</span>
                    <span class="badge badge-occupied" style="margin-right: 0.5rem;">● Occupied</span>
                    <span class="badge badge-pending">● Reserved</span>
                </div>
            </div>

            <div class="tables-grid">
                <div v-for="table in tables" :key="table.id"
                     class="table-card"
                     :class="table.status"
                     @click="editTable(table)">
                    <div class="table-number">{{ table.table_number }}</div>
                    <div class="table-status">{{ table.status }}</div>
                    <div style="font-size: 0.75rem; color: #94a3b8;">Capacity: {{ table.capacity }}</div>
                </div>
            </div>

            <div v-if="tables.length === 0" class="empty-state">
                <div class="empty-icon">🪑</div>
                <h3>No tables yet</h3>
                <p>Add tables to start managing your floor layout.</p>
            </div>
        </template>

        <!-- Add/Edit Table Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ editingTable ? 'Edit Table' : 'Add Table' }}</h3>
                    <button class="close-btn" @click="closeModal">&times;</button>
                </div>
                <form @submit.prevent="saveTable">
                    <div class="form-group">
                        <label>Table Number</label>
                        <input type="text" class="form-control" v-model="form.table_number" required />
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" class="form-control" v-model.number="form.capacity" min="1" />
                    </div>
                    <div v-if="editingTable" class="form-group">
                        <label>Status</label>
                        <select class="form-control" v-model="form.status">
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="reserved">Reserved</option>
                        </select>
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
    name: 'RestaurantTables',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            loading: true,
            saving: false,
            tables: [],
            showModal: false,
            editingTable: null,
            form: { table_number: '', capacity: 4, status: 'available' },
        };
    },
    async mounted() {
        await this.fetchTables();
    },
    methods: {
        async fetchTables() {
            this.loading = true;
            try {
                const restId = this.user.restaurant_id;
                const res = await axios.get(`/restaurants/${restId}/tables`);
                this.tables = res.data;
            } catch (err) {
                console.error('Failed to fetch tables:', err);
            } finally {
                this.loading = false;
            }
        },
        openAddModal() {
            this.editingTable = null;
            this.form = { table_number: '', capacity: 4, status: 'available' };
            this.showModal = true;
        },
        editTable(table) {
            this.editingTable = table;
            this.form = { table_number: table.table_number, capacity: table.capacity, status: table.status };
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.editingTable = null;
        },
        async saveTable() {
            this.saving = true;
            try {
                const restId = this.user.restaurant_id;
                if (this.editingTable) {
                    await axios.put(`/tables/${this.editingTable.id}`, this.form);
                } else {
                    await axios.post(`/restaurants/${restId}/tables`, this.form);
                }
                await this.fetchTables();
                this.closeModal();
            } catch (err) {
                alert('Failed to save table');
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
