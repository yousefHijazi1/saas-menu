<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{ title }}</h3>
                <button class="close-btn" @click="$emit('close')">&times;</button>
            </div>
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Modal',
    props: {
        title: { type: String, required: true },
    },
};
</script>
