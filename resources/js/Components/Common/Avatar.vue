<script setup>
import { ref } from "vue";

const props = defineProps({
    image: {
        type: String,
        required: true,
    },
    needHover: {
        type: Boolean,
        default: false,
    },
    alt: String
});

const hover = ref(false);
</script>

<template>
    <div
        class="avatar-container"
        @mouseover="needHover && (hover = true)"
        @mouseleave="needHover && (hover = false)"
    >
        <img
            :src="image"
            :alt="alt"
            class="avatar-image rounded-circle"
            style="border:1px solid rgba(0, 0, 0, .1)"
            :class="{ 'hover-active': hover }"
        />
    </div>
</template>

<style scoped>
.avatar-image {
    width: 3rem;
    height: 3rem;
    transition: transform 0.3s ease-in-out;
}

.hover-active {
    transform: scale(1.25);
}
</style>
