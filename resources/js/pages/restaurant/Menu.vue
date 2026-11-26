<template>
    <DashboardLayout :user="user">
        <div class="page-header">
            <h1>Menu Management</h1>
            <p>Manage categories and menu items</p>
        </div>

        <div v-if="loading" class="loading-spinner">
            <div class="spinner"></div>
        </div>

        <template v-else>
            <div class="top-bar">
                <div>
                    <button class="btn btn-primary btn-sm" @click="openAddCategory">+ Add Category</button>
                    <button class="btn btn-success btn-sm" @click="openAddItem" style="margin-left: 0.5rem;">+ Add Item</button>
                </div>
            </div>

            <div v-for="category in categories" :key="category.id" class="card">
                <div class="card-header">
                    <h3>{{ category.name }}</h3>
                    <div>
                        <button class="btn btn-outline btn-sm" @click="editCategory(category)">Edit</button>
                        <button class="btn btn-danger btn-sm" @click="deleteCategory(category)" style="margin-left: 0.5rem;">Delete</button>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Discount</th>
                                <th>Available</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in category.menu_items" :key="item.id">
                                <td><strong>{{ item.name }}</strong></td>
                                <td>{{ restaurantCurrency }}{{ item.price }}</td>
                                <td>{{ item.discount_price ? restaurantCurrency + item.discount_price : '-' }}</td>
                                <td>
                                    <span class="badge" :class="item.is_available ? 'badge-ready' : 'badge-cancelled'">
                                        {{ item.is_available ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="item.is_featured ? 'badge-paid' : 'badge-pending'">
                                        {{ item.is_featured ? 'Featured' : 'Normal' }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-outline btn-sm" @click="editItem(item)">Edit</button>
                                    <button class="btn btn-danger btn-sm" @click="deleteItem(item)" style="margin-left: 0.5rem;">Del</button>
                                </td>
                            </tr>
                            <tr v-if="category.menu_items.length === 0">
                                <td colspan="6" class="text-center">No items in this category</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="categories.length === 0" class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No categories yet</h3>
                <p>Start by adding a category and then adding menu items.</p>
            </div>
        </template>

        <!-- Category Modal -->
        <div v-if="showCategoryModal" class="modal-overlay" @click.self="showCategoryModal = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ editingCategory ? 'Edit Category' : 'Add Category' }}</h3>
                    <button class="close-btn" @click="showCategoryModal = false">&times;</button>
                </div>
                <form @submit.prevent="saveCategory">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" class="form-control" v-model="categoryForm.name" required />
                    </div>
                    <div class="form-group">
                        <label>Icon (emoji)</label>
                        <input type="text" class="form-control" v-model="categoryForm.icon" placeholder="🍕" />
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" class="form-control" v-model.number="categoryForm.sort_order" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showCategoryModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Menu Item Modal -->
        <div v-if="showItemModal" class="modal-overlay" @click.self="showItemModal = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ editingItem ? 'Edit Menu Item' : 'Add Menu Item' }}</h3>
                    <button class="close-btn" @click="showItemModal = false">&times;</button>
                </div>
                <form @submit.prevent="saveItem">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" v-model="itemForm.name" required />
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" v-model="itemForm.category_id" required>
                            <option value="">Select category</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" class="form-control" v-model.number="itemForm.price" step="0.01" required />
                        </div>
                        <div class="form-group">
                            <label>Discount Price</label>
                            <input type="number" class="form-control" v-model.number="itemForm.discount_price" step="0.01" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" v-model="itemForm.description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ingredients (comma separated)</label>
                        <input type="text" class="form-control" v-model="ingredientsText" placeholder="Tomato, Cheese, Basil" />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><input type="checkbox" v-model="itemForm.is_available" /> Available</label>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" v-model="itemForm.is_featured" /> Featured</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showItemModal = false">Cancel</button>
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
    name: 'RestaurantMenu',
    components: { DashboardLayout },
    data() {
        return {
            user: JSON.parse(localStorage.getItem('user') || '{}'),
            restaurant: null,
            loading: true,
            saving: false,
            categories: [],
            showCategoryModal: false,
            editingCategory: null,
            categoryForm: { name: '', icon: '', sort_order: 0 },
            showItemModal: false,
            editingItem: null,
            itemForm: { category_id: '', name: '', description: '', price: 0, discount_price: null, is_available: true, is_featured: false },
            ingredientsText: '',
        };
    },
    computed: {
        restaurantCurrency() {
            return this.restaurant ? this.restaurant.currency : '$';
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
                const restRes = await axios.get(`/restaurants/${restId}`);
                this.restaurant = restRes.data;

                const catRes = await axios.get(`/restaurants/${restId}/categories`);
                this.categories = catRes.data;

                for (const cat of this.categories) {
                    const itemsRes = await axios.get(`/restaurants/${restId}/menu-items`);
                    cat.menu_items = itemsRes.data.filter(item => item.category_id === cat.id);
                }
            } catch (err) {
                console.error('Failed to fetch data:', err);
            } finally {
                this.loading = false;
            }
        },
        openAddCategory() {
            this.editingCategory = null;
            this.categoryForm = { name: '', icon: '', sort_order: 0 };
            this.showCategoryModal = true;
        },
        editCategory(cat) {
            this.editingCategory = cat;
            this.categoryForm = { name: cat.name, icon: cat.icon || '', sort_order: cat.sort_order };
            this.showCategoryModal = true;
        },
        async saveCategory() {
            this.saving = true;
            try {
                const restId = this.user.restaurant_id;
                if (this.editingCategory) {
                    await axios.put(`/categories/${this.editingCategory.id}`, this.categoryForm);
                } else {
                    await axios.post(`/restaurants/${restId}/categories`, this.categoryForm);
                }
                await this.fetchData();
                this.showCategoryModal = false;
            } catch (err) {
                alert('Failed to save category');
            } finally {
                this.saving = false;
            }
        },
        async deleteCategory(cat) {
            if (!confirm(`Delete category "${cat.name}"?`)) return;
            try {
                await axios.delete(`/categories/${cat.id}`);
                await this.fetchData();
            } catch (err) {
                alert('Failed to delete category');
            }
        },
        openAddItem() {
            this.editingItem = null;
            this.itemForm = { category_id: this.categories.length ? this.categories[0].id : '', name: '', description: '', price: 0, discount_price: null, is_available: true, is_featured: false };
            this.ingredientsText = '';
            this.showItemModal = true;
        },
        editItem(item) {
            this.editingItem = item;
            this.itemForm = {
                category_id: item.category_id,
                name: item.name,
                description: item.description || '',
                price: item.price,
                discount_price: item.discount_price,
                is_available: item.is_available,
                is_featured: item.is_featured,
            };
            this.ingredientsText = (item.ingredients || []).join(', ');
            this.showItemModal = true;
        },
        async saveItem() {
            this.saving = true;
            try {
                const restId = this.user.restaurant_id;
                const payload = {
                    ...this.itemForm,
                    ingredients: this.ingredientsText ? this.ingredientsText.split(',').map(s => s.trim()).filter(Boolean) : [],
                };

                if (this.editingItem) {
                    await axios.put(`/menu-items/${this.editingItem.id}`, payload);
                } else {
                    await axios.post(`/restaurants/${restId}/menu-items`, payload);
                }
                await this.fetchData();
                this.showItemModal = false;
            } catch (err) {
                alert('Failed to save menu item');
            } finally {
                this.saving = false;
            }
        },
        async deleteItem(item) {
            if (!confirm(`Delete "${item.name}"?`)) return;
            try {
                await axios.delete(`/menu-items/${item.id}`);
                await this.fetchData();
            } catch (err) {
                alert('Failed to delete menu item');
            }
        },
    },
};
</script>
