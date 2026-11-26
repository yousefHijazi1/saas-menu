import Vue from 'vue';
import VueRouter from 'vue-router';

Vue.use(VueRouter);

import Login from '../pages/Login.vue';
import AdminDashboard from '../pages/admin/Dashboard.vue';
import AdminRestaurants from '../pages/admin/Restaurants.vue';
import RestaurantDashboard from '../pages/restaurant/Dashboard.vue';
import RestaurantMenu from '../pages/restaurant/Menu.vue';
import RestaurantTables from '../pages/restaurant/Tables.vue';
import RestaurantOrders from '../pages/restaurant/Orders.vue';
import WaiterDashboard from '../pages/waiter/Dashboard.vue';
import WaiterOrders from '../pages/waiter/Orders.vue';
import CustomerMenu from '../pages/customer/Menu.vue';

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: Login },
    { path: '/menu/:slug', name: 'customer.menu', component: CustomerMenu },
    {
        path: '/admin',
        name: 'admin.dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, role: 'admin' },
    },
    {
        path: '/admin/restaurants',
        name: 'admin.restaurants',
        component: AdminRestaurants,
        meta: { requiresAuth: true, role: 'admin' },
    },
    {
        path: '/restaurant',
        name: 'restaurant.dashboard',
        component: RestaurantDashboard,
        meta: { requiresAuth: true, role: 'restaurant' },
    },
    {
        path: '/restaurant/menu',
        name: 'restaurant.menu',
        component: RestaurantMenu,
        meta: { requiresAuth: true, role: 'restaurant' },
    },
    {
        path: '/restaurant/tables',
        name: 'restaurant.tables',
        component: RestaurantTables,
        meta: { requiresAuth: true, role: 'restaurant' },
    },
    {
        path: '/restaurant/orders',
        name: 'restaurant.orders',
        component: RestaurantOrders,
        meta: { requiresAuth: true, role: 'restaurant' },
    },
    {
        path: '/waiter',
        name: 'waiter.dashboard',
        component: WaiterDashboard,
        meta: { requiresAuth: true, role: 'waiter' },
    },
    {
        path: '/waiter/orders',
        name: 'waiter.orders',
        component: WaiterOrders,
        meta: { requiresAuth: true, role: 'waiter' },
    },
];

const router = new VueRouter({
    routes,
    mode: 'history',
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    if (to.meta && to.meta.requiresAuth) {
        if (!token || !user) {
            next('/login');
            return;
        }

        if (to.meta.role && user.role !== to.meta.role && user.role !== 'admin') {
            const dashboards = {
                admin: '/admin',
                restaurant: '/restaurant',
                waiter: '/waiter',
            };
            next(dashboards[user.role] || '/login');
            return;
        }
    }

    if (to.path === '/login' && token && user) {
        const dashboards = {
            admin: '/admin',
            restaurant: '/restaurant',
            waiter: '/waiter',
        };
        next(dashboards[user.role] || '/login');
        return;
    }

    next();
});

export default router;
