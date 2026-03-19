<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, usePage } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    platforms: Object,
    filters: Object,
});

const columns = [
    { label: "Name", key: "name" },
    { label: "Code", key: "code" },
    { label: "Environment", key: "environment" },
    { label: "Currencies", key: "supported_currencies", type: "array" },
    { label: "Default", key: "is_default", type: "badge" },
    { label: "Status", key: "is_active", type: "toggle" },
];

const searchTerm = ref(props.filters?.keyword || '');
const environmentFilter = ref(props.filters?.environment || '');
const statusFilter = ref(props.filters?.status || '');

const applyFilters = () => {
    router.get(route('admin.payment-platforms.index'), {
        keyword: searchTerm.value,
        environment: environmentFilter.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const tableDataProp = computed(() => props.platforms);

const buttons = [
    {
        label: "Add Platform",
        function: () => router.get(route('admin.payment-platforms.create')),
        icon: "bi bi-plus-circle",
    },
];

const toggleStatus = (row) => {
    Swal.fire({
        title: `Are you sure to ${row.is_active ? 'deactivate' : 'activate'} this platform?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.payment-platforms.toggle-status', { id: row.id }), {
                preserveState: true,
            });
        }
    });
};

const setDefault = (row) => {
    Swal.fire({
        title: 'Set as default payment platform?',
        text: `Are you sure you want to set "${row.name}" as the default payment platform?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, set as default!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.payment-platforms.set-default', { id: row.id }), {
                preserveState: true,
            });
        }
    });
};

const deleteRow = (row) => {
    Swal.fire({
        title: 'Are you sure to delete this payment platform?',
        text: 'You will not be able to recover this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.payment-platforms.destroy', { id: row.id }), {
                preserveState: true,
            });
        }
    });
};

const getStatusBadgeClass = (environment) => {
    return environment === 'sandbox' ? 'bg-warning' : 'bg-success';
};
</script>

<template>
    <AuthLayout title="Payment Platforms" description="Manage payment platforms" heading="Payment Platforms">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="d-flex align-items-center text-xl mb-0">Payment Platforms</h3>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <select class="form-select" v-model="environmentFilter" @change="applyFilters">
                        <option value="">All Environments</option>
                        <option value="sandbox">Sandbox</option>
                        <option value="live">Live</option>
                    </select>

                    <select class="form-select" v-model="statusFilter" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <div class="form-group mb-0">
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Search platforms..." 
                            v-model="searchTerm" 
                            @input="applyFilters"
                        />
                    </div>

                    <ActionButtons :actionButtons="buttons" />
                </div>
            </div>
        </div>

        <Table 
            :columns="columns" 
            :data="tableDataProp" 
            table="payment_platforms"
        >
            <template #actions="{ row }">
                <div class="d-flex gap-2">
                    <Link :href="route('admin.payment-platforms.edit', { id: row.id })" class="icon-btn btn btn-primary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </Link>
                    <button 
                        v-if="!row.is_default" 
                        class="icon-btn btn btn-success" 
                        @click="setDefault(row)" 
                        title="Set as Default"
                    >
                        <i class="bi bi-star"></i>
                    </button>
                    <button 
                        class="icon-btn btn" 
                        :class="row.is_active ? 'btn-warning' : 'btn-success'" 
                        @click="toggleStatus(row)" 
                        :title="row.is_active ? 'Deactivate' : 'Activate'"
                    >
                        <i :class="row.is_active ? 'bi bi-pause' : 'bi bi-play'"></i>
                    </button>
                    <button 
                        v-if="!row.has_credentials" 
                        class="icon-btn btn btn-danger" 
                        @click="deleteRow(row)" 
                        title="Delete"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </template>
        </Table>
    </AuthLayout>
</template>

