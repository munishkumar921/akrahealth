<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    keyword: String,
    route: Array,
    messages: {
        type: Object,
        default: () => ({
            data: [],
            links: {},
            meta: {}
        })
    }
});

const columns = [
   { label:'date', key: 'date'},
    {label:'patient', key: 'patient.name'},
    {label:'doctor', key: 'doctor.name'},
    {label:'subject', key: 'subject'},
    {label:'message', key: 'message'},
    {label:'status', key: 'status'},
];

 
</script>

<template>
    <AuthLayout title="Messages" description="Manage all messages" heading="Messages">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="d-flex align-items-center">
                 Messages
            </h3>
        </div>
       
        <Table :columns="columns" :data="messages" :search="keyword"/>
 
    </AuthLayout>
</template>

<style scoped>
.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}
</style>
