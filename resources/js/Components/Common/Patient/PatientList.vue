<script setup>
import { ref } from "vue";

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});


const activeItem = ref(null);

const toggleItem = (index) => {
    activeItem.value = activeItem.value === index ? null : index;
};
</script>

<template>

</template>