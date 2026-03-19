<script setup>
import { ref } from 'vue';


const props = defineProps({
    data: {
        type: Object,
        default: () => null
    }
});
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Prescription Details</h1>

        <div v-if="props.data">
            <pre class="bg-gray-100 p-4 rounded">
{{ props.data }}
            </pre>
        </div>

        <div v-else class="text-gray-500">
            No data available.
        </div>
    </div>
</template>
