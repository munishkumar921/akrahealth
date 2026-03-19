<template>
    <div>
        <Chart :type="'line'" :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Chart from '@/Components/Chart.vue';
import axios from 'axios';

const props = defineProps({
    vital: {
        type: String,
        required: true
    },
    patientId: {
        type: Number,
        required: true
    }
});

const chartData = ref({
    labels: [],
    datasets: []
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true
        }
    }
});

onMounted(async () => {
    try {
        const response = await axios.get(route('doctor.vitals.history', { patient: props.patientId, vital: props.vital }));
        const data = response.data;
        chartData.value = {
            labels: data.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: props.vital,
                data: data.map(item => item.value),
                borderColor: '#4A90E2',
                tension: 0.1
            }]
        };
    } catch (error) {
        console.error(`Error fetching ${props.vital} data:`, error);
    }
});
</script>
