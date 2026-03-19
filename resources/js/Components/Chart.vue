<template>
    <canvas ref="chartCanvas"></canvas>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    type: {
        type: String,
        required: true,
        default: 'line'
    },
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
});

const chartCanvas = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d');
        chartInstance = new Chart(ctx, {
            type: props.type,
            data: props.data,
            options: props.options
        });
    }
});

watch(() => props.data, (newData) => {
    if (chartInstance) {
        chartInstance.data = newData;
        chartInstance.update();
    }
}, { deep: true });
</script>
