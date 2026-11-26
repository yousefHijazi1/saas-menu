<template>
    <div class="app-container">
        <Sidebar :user="user" />
        <div class="main-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
import Sidebar from './Sidebar.vue';

export default {
    name: 'DashboardLayout',
    components: { Sidebar },
    props: {
        user: { type: Object, required: true },
    },
};
</script>
