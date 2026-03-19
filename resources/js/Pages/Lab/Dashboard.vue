<script setup>
import AuthLayout2 from '@/Layouts/AuthLayout2.vue';
import Table from '@/Components/Table/Table.vue';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    totalTests: Number,
    totalPatients: Number,
    recentOrders: Object,
    keyword: String,
});

const stats = computed(() => [
    {
        label: 'Total Tests',
        value: props.totalTests || 0,
        icon: 'fa-flask',
        colorClass: 'bg-primary',
        percent: 70,
    },
    {
        label: 'Total Patients',
        value: props.totalPatients || 0,
        icon: 'fa-users',
        colorClass: 'bg-info',
        percent: 50,
    },
    {
        label: 'Pending Results',
        value: 0,
        icon: 'fa-clock',
        colorClass: 'iq-warning',
        percent: 30,
    },
    {
        label: 'Completed Today',
        value: 0,
        icon: 'fa-check-circle',
        colorClass: 'iq-spring-green',
        percent: 80,
    },
]);

const orderColumns = [
    {
        key: 'patient_name',
        label: 'Patient Name',
    },
    
    {
        key: 'ordered_by',
        label: 'Ordered By',
    },
    {
        key: 'created_at',
        label: 'Order Date',
        isDate: true,
    },
    {
        key: 'status',
        label: 'Status',
    },
];

const orderData = computed(() => {
    const list = props.recentOrders?.data ?? props.recentOrders ?? [];
    return list.map(order => ({
        ...order,
        patient_name: order?.patient?.name ?? 'N/A',
        ordered_by: order?.doctor?.name ?? 'N/A',
        status: order?.is_completed ? 'Completed' : 'Pending',
    }));
});

// Handle search
const handleSearch = (searchTerm) => {
    router.visit(route('lab.dashboard'), {
        method: 'get',
        data: { keyword: searchTerm },
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
<template>
    <AuthLayout2 title="Lab Dashboard" description="Laboratory Dashboard" heading="">
        <!-- Stats Cards -->
        <div class="row">
            <div v-for="(stat, index) in stats" :key="index" class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center">
                            <span>{{ stat.label }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0">
                                    <span class="counter">{{ stat.value }}</span>
                                </h2>
                            </div>
                            <div :class="['iq-iconbox', stat.colorClass]">
                                <i :class="['fa', stat.icon]"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between bg-white rounded">
                        <div class="iq-header-title">
                            <h4 class="card-title">Recent Orders</h4>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <Table :columns="orderColumns" :data="{ data: orderData }" :search="keyword" @search="handleSearch">
                            <template #actions="{ row }">
                                <button class="btn btn-primary btn-sm" 
                                    @click="router.visit(route('lab.labs.show', row.id))">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout2>
</template>

