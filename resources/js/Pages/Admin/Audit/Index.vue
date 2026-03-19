<script setup>
import { ref, watch } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from '@/Components/Table/Table.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';

const props = defineProps({
    auditLogs: Object,
    filters: Object,
    modules: Array,
    actions: Array,
});

const showFilters = ref(false);
const searchForm = useForm({
    keyword: props.filters.keyword || '',
    module: props.filters.module || '',
    action: props.filters.action || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const resetFilters = () => {
    searchForm.keyword = '';
    searchForm.module = '';
    searchForm.action = '';
    searchForm.date_from = '';
    searchForm.date_to = '';
    searchForm.get(route('admin.audit-logs.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const search = () => {
    searchForm.get(route('admin.audit-logs.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportCsv = () => {
    const params = new URLSearchParams();
    if (searchForm.keyword) params.append('keyword', searchForm.keyword);
    if (searchForm.module) params.append('module', searchForm.module);
    if (searchForm.action) params.append('action', searchForm.action);
    if (searchForm.date_from) params.append('date_from', searchForm.date_from);
    if (searchForm.date_to) params.append('date_to', searchForm.date_to);
    
    window.location.href = route('admin.audit-logs.export.csv') + '?' + params.toString();
};

const exportPdf = () => {
    const params = new URLSearchParams();
    if (searchForm.keyword) params.append('keyword', searchForm.keyword);
    if (searchForm.module) params.append('module', searchForm.module);
    if (searchForm.action) params.append('action', searchForm.action);
    if (searchForm.date_from) params.append('date_from', searchForm.date_from);
    if (searchForm.date_to) params.append('date_to', searchForm.date_to);
    
    window.location.href = route('admin.audit-logs.export.pdf') + '?' + params.toString();
};
 

const columns = [
    { label: 'User', key: 'user' },
    { label: 'Module', key: 'module_label' },
    { label: 'Action', key: 'action' },
    { label: 'Date', key: 'created_at', type: 'slot', slot: 'date' },
    { label: 'Time', key: 'created_at', type: 'slot', slot: 'time' },
];

// Helper function to capitalize first letter
const capitalize = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

// Helper function to get module label (uses backend module_label)
const getModuleLabel = (module) => {
    return capitalize(module);
};
</script>

<template>
    <AuthLayout title="Audit Logs" description="Audit Logs" heading="Audit Logs">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="text-xl mb-0">Audit Logs</h3>
                <p class="mb-0 text-muted small">Track all administrative activities and changes</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-secondary" @click="showFilters = !showFilters">
                    <i class="bi bi-funnel me-1"></i> Filters
                </button>
                <button class="btn btn-success" @click="exportCsv">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
                </button>
                <button class="btn btn-danger" @click="exportPdf">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div v-if="showFilters" class="card mb-3">
            <div class="card-body">
                <form @submit.prevent="search">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" v-model="searchForm.keyword" class="form-control" placeholder="Search..." />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Module</label>
                            <select v-model="searchForm.module" class="form-select">
                                <option value="">All Modules</option>
                                <option v-for="module in modules" :key="module" :value="module">
                                    {{ getModuleLabel(module) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Action</label>
                            <select v-model="searchForm.action" class="form-select">
                                <option value="">All Actions</option>
                                <option v-for="action in actions" :key="action" :value="action">
                                    {{ capitalize(action) }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <BaseDatePicker v-model="searchForm.date_from" type="date" placeholder="Select date" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <BaseDatePicker v-model="searchForm.date_to" type="date" placeholder="Select date" />
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        <button type="button" @click="resetFilters" class="btn btn-secondary">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <Table :columns="columns" :data="auditLogs" :searchShow="false">
            <template #date="{ row }">
                {{ row?.created_at }}
            </template>
            <template #time="{ row }">
                {{ row?.created_at_time }}
            </template>
            <template #actions="{ row }">
                <Link :href="route('admin.audit-logs.show', row.id)" class="btn btn-primary" title="View Details">
                    <i class="bi bi-eye"></i>
                </Link>
            </template>
        </Table>
    </AuthLayout>
</template>
