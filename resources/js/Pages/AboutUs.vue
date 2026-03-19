<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

defineProps({
    terms: String,
});
</script>

<template>
<AppLayout title="About Us" description="Learn more about AkraHealth">
        <div class="font-sans text-gray-900 antialiased">
            <div class="pt-4 bg-gray-100">
                <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
                    About Us
                </div>
            </div>
        </div>
    </AppLayout>
</template>
