<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    items: {
        type: Array,
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
        class="accordion rounded p-2"
        :class="index % 2 ? 'bg-secondary' : 'bg-light'"
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
            <button class="btn btn-primary">Edit</button>
        </div>
        <div
            :id="'collapse' + index"
            class="collapse"
            :class="{ show: activeItem === index }"
            :aria-labelledby="'heading' + index"
        >
            <div class="p-3">
                <table class="table table-borderless">
                    <tbody>
                        <tr v-for="(item, idx) in items" :key="idx">
                            <td>
                                <strong>{{ item.label }}:</strong>
                            </td>
                            <td>{{ item.value }}</td>
                        </tr>
                    </tbody>
                </table>
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
