<script setup>
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { route } from "ziggy-js";

const props = defineProps({
    doctors: {
        type: Array,
        default: () => [],
    },
    appointments: {
        type: Object,
        default: () => ({ data: [] }),
    },
    calendarEvents: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    modeOptions: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    doctor_id: props.filters?.doctor_id || "",
    status: props.filters?.status ?? "",
    mode: props.filters?.mode || "",
});

const statusOptions = [
    { value: "", label: "All statuses" },
    { value: "upcoming", label: "Upcoming" },
    { value: "pending", label: "Pending" },
    { value: "confirmed", label: "Confirmed" },
    { value: "completed", label: "Completed" },
    { value: "cancelled", label: "Cancelled" },
    { value: "rescheduled", label: "Rescheduled" },
];

const columns = [
    { label: "Patient", key: "patient_name", type: "slot", slot: "patient", align: "left" },
    { label: "Doctor", key: "doctor_name", type: "slot", slot: "doctor", align: "left" },
    { label: "Type", key: "appointment_type_label", type: "slot", slot: "type", align: "left" },
    { label: "Mode", key: "appointment_mode_label", type: "slot", slot: "mode", align: "left" },
    { label: "Created By", key: "created_by_label", type: "slot", slot: "createdBy", align: "left" },
    { label: "Payment", key: "payment_status_label", type: "slot", slot: "payment", align: "center" },
    { label: "Status", key: "status_label", type: "slot", slot: "status", align: "center" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);
const rows = computed(() => props.appointments?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.appointments?.total ?? rows.value.length;
    const from = props.appointments?.from ?? (rows.value.length ? 1 : 0);
    const to = props.appointments?.to ?? rows.value.length;

    if (!total) {
        return "No appointments found";
    }

    return `Showing ${from}-${to} of ${total} appointments`;
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
    router.get(route("admin.allAppointments"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        doctor_id: "",
        status: "",
        mode: "",
    };

    router.get(route("admin.allAppointments"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.allAppointments"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const paymentBadgeClass = (value) => {
    const status = String(value || "").toLowerCase();

    if (status === "paid") return "status-pill--active";
    if (status === "pending") return "status-pill--pending";

    return "status-pill--inactive";
};

const appointmentStatusClass = (value) => {
    const status = String(value || "").toLowerCase();

    if (status === "completed" || status === "confirmed") return "status-pill--active";
    if (status === "pending" || status === "upcoming") return "status-pill--pending";

    return "status-pill--inactive";
};
</script>

<template>
    <AuthLayout title="Appointments" description="Manage appointments" heading="">
        <div class="all-appointments-page container-fluid">
            <div class="users-toolbar card border-0 shadow-sm mb-4 mt-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">All Appointments</h3>
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
                            <div class="input-group appointment-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by patient, doctor, type, mode, or creator"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Doctor</label>
                            <select v-model="filterForm.doctor_id" class="form-select" @change="applyFilters">
                                <option value="">All doctors</option>
                                <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                                    Dr. {{ doctor.name }}<span v-if="doctor.speciality"> - {{ doctor.speciality }}</span>
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-xl-3">
                            <label class="form-label text-muted small text-uppercase mb-2">Mode</label>
                            <select v-model="filterForm.mode" class="form-select" @change="applyFilters">
                                <option value="">All modes</option>
                                <option v-for="mode in modeOptions" :key="mode" :value="mode">
                                    {{ mode }}
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
                                id="appointments-per-page"
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

                    <Table :columns="columns" :data="appointments" :search-show="false" :PageOptions="false">
                        <template #patient="{ row }">
                            <div class="text-start d-flex align-items-center gap-3">
                                <img
                                    :src="row.patient_avatar_url || '/images/avatar.webp'"
                                    alt="Patient"
                                    class="rounded-circle listing-avatar"
                                />
                                <div>
                                    <div class="fw-semibold text-dark">{{ row.patient_name }}</div>
                                    <div class="text-muted small">{{ row.scheduled_at_label || "N/A" }}</div>
                                </div>
                            </div>
                        </template>

                        <template #doctor="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.doctor_name }}</div>
                                <div class="text-muted small">{{ row.doctor_speciality || "General" }}</div>
                            </div>
                        </template>

                        <template #type="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.appointment_type_label }}</div>
                            </div>
                        </template>

                        <template #mode="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.appointment_mode_label }}</div>
                            </div>
                        </template>

                        <template #createdBy="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.created_by_label }}</div>
                            </div>
                        </template>

                        <template #payment="{ row }">
                            <span class="status-pill" :class="paymentBadgeClass(row.payment_status_label)">
                                {{ row.payment_status_label }}
                            </span>
                        </template>

                        <template #status="{ row }">
                            <span class="status-pill" :class="appointmentStatusClass(row.status_label)">
                                {{ row.status_label }}
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

.status-pill--pending {
    background: #fef3c7;
    color: #92400e;
}

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.appointment-search-control {
    max-width: 560px;
}

.listing-avatar {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
}
</style>
