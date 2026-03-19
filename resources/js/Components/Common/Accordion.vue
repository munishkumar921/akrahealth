<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    index: {
        type: Number,
        required: true,
    },
    activeItem: {
        type: Number,
        required: true,
    },
    toggleItem: {
        type: Function,
        required: true,
    },
});
</script>

<template>
    <div
        class="accordion rounded p-2 my-1"
        :class="index % 2 ? 'bg-light' : 'bg-light'"
    >
        <div
            class="accordion-header d-flex justify-content-between align-items-center"
            :id="'heading' + index"
        >
            <h5 class="mb-0">
                <button
                    class="btn btn-link"
                    type="button"
                    :class="{ collapsed: activeItem !== index }"
                    @click="toggleItem(index)"
                    :aria-expanded="activeItem === index"
                    :aria-controls="'collapse' + index"
                    :data-target="'#collapse' + index"
                >
                    {{ title }}
                </button>
            </h5>
            <slot name="header-right" />
        </div>
        <div
            :id="'collapse' + index"
            class="collapse"
            :class="{ show: activeItem === index }"
            :aria-labelledby="'heading' + index"
        >
            <div class="p-3">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.accordion-header button {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.accordion-header button.collapsed {
    color: #6c757d;
}

.accordion .btn-primary {
    font-size: 14px;
}
</style>
