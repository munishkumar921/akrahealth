<script setup>
const props = defineProps({});
</script>

<template>
    <section class="rounded mx-5 cursor-pointer border hover-hover">
        <div
            class="d-flex justify-content-between align-items-center gap-3 p-4"
        >
            <slot name="field"></slot>
            <slot name="actions"></slot>
        </div>
    </section>
</template>

<style scoped>
.hover-hover {
    transition: border-color 0.3s ease, box-shadow 0.3s ease,
        background-color 0.3s ease;
    border-color: #ff9900;
}

.hover-hover:hover {
    box-shadow: 0 0 5px #09acff;
}
</style>
