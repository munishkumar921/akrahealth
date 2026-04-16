<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    pharmacyOrders: {
        type: [Array, Object],
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
            status: "",
        }),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    status: props.filters?.status || "",
});

const statusOptions = [
    { value: "", label: "All status" },
    { value: "active", label: "Active" },
    { value: "inactive", label: "Inactive" },
];

const columns = [
    { label: "Pharmacy", key: "pharmacy_name", type: "slot", slot: "pharmacy", align: "left" },
    { label: "Patient", key: "patient_name", type: "slot", slot: "patient", align: "left" },
    { label: "Doctor", key: "doctor_name", type: "slot", slot: "doctor", align: "left" },
    { label: "Medication", key: "medication_name", type: "slot", slot: "medication", align: "left" },
    { label: "Active Date", key: "active_date", type: "slot", slot: "activeDate", align: "left" },
    { label: "Due Date", key: "due_date", type: "slot", slot: "dueDate", align: "left" },
    { label: "Status", key: "status", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.pharmacyOrders?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.pharmacyOrders?.total ?? rows.value.length;
    const from = props.pharmacyOrders?.from ?? (rows.value.length ? 1 : 0);
    const to = props.pharmacyOrders?.to ?? rows.value.length;

    if (!total) {
        return "No pharmacy orders found";
    }

    return `Showing ${from}-${to} of ${total} pharmacy orders`;
});

const buildQuery = (overrides = {}) => {
    const params = new URLSearchParams(window.location.search);
    const query = {
        per_page: params.get("per_page") || undefined,
        sort: params.get("sort") || undefined,
        direction: params.get("direction") || undefined,
        ...filterForm.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("admin.pharmacy-orders.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        status: "",
    };

    router.get(route("admin.pharmacy-orders.list"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.pharmacy-orders.list"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const getStatusClass = (status) => {
    const lowerStatus = String(status || "").toLowerCase();

    return lowerStatus === "active" ? "status-pill--active" : "status-pill--inactive";
};
</script>

<template>
    <AuthLayout title="Pharmacy Orders" description="Manage pharmacy orders" heading="Pharmacy Orders">
        <div class="pharmacy-orders-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Pharmacy Orders</h3>
                            <p class="text-muted mb-0">{{ resultSummary }}</p>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                            <span v-if="hasActiveFilters" class="filter-count-badge">
                                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                            </span>
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="clearFilters"
                            >
                                <i class="bi bi-x-circle me-1"></i>Clear filters
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group pharmacy-orders-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by pharmacy, patient, doctor, medication, date, or status"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select
                                id="pharmacy-orders-per-page"
                                v-model="perPage"
                                class="form-select form-select-sm top-page-select"
                                @change="updatePerPage"
                            >
                                <option v-for="option in perPageOptions" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <Table :columns="columns" :data="pharmacyOrders" :search-show="false" :PageOptions="false">
                        <template #pharmacy="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.pharmacy_name || "-" }}</div>
                                <div class="text-muted small">{{ row.created_label || "-" }}</div>
                            </div>
                        </template>

                        <template #patient="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.patient_name || "-" }}</div>
                            </div>
                        </template>

                        <template #doctor="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.doctor_name || "-" }}</div>
                            </div>
                        </template>

                        <template #medication="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.medication_name || "-" }}</div>
                                <div v-if="row.prescription_notes" class="text-muted small">
                                    {{ row.prescription_notes }}
                                </div>
                            </div>
                        </template>

                        <template #activeDate="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.active_date || "-" }}</div>
                                <div v-if="row.inactive_date && row.inactive_date !== '-'" class="text-muted small">
                                    Inactive: {{ row.inactive_date }}
                                </div>
                            </div>
                        </template>

                        <template #dueDate="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.due_date || "-" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <span class="status-pill" :class="getStatusClass(row.status)">
                                {{ row.status_label || row.status || "N/A" }}
                            </span>
                        </template>
                    </Table>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar {
    border-radius: 20px;
}

.filter-count-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3730a3;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #f1f5f9;
    color: #475569;
}

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.pharmacy-orders-search-control {
    max-width: 720px;
}
</style>
