<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import ShareDetailsModal from "@/Pages/Modals/ShareDetailsModal.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const props = defineProps({
    connectedDoctors: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    specialityOptions: {
        type: Array,
        default: () => [],
    },
});

const showShareModal = ref(false);
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(Number(new URLSearchParams(window.location.search).get("per_page")) || 10);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    speciality: props.filters?.speciality || "",
});

const columns = [
    { label: "Provider", key: "name", type: "slot", slot: "provider", align: "left" },
    { label: "Specialities", key: "specialities", type: "slot", slot: "specialities", align: "left" },
    { label: "Email", key: "email", type: "slot", slot: "email", align: "left" },
    { label: "Phone", key: "mobile", type: "slot", slot: "phone", align: "left" },
];

const buttons = [
    {
        label: "Invite Provider",
        icon: "fa fa-refresh",
        function: () => {
            showShareModal.value = true;
        },
    },
];

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.connectedDoctors?.total ?? props.connectedDoctors?.data?.length ?? 0;
    const from = props.connectedDoctors?.from ?? (total ? 1 : 0);
    const to = props.connectedDoctors?.to ?? total;

    if (!total) {
        return "No providers found";
    }

    return `Showing ${from}-${to} of ${total} providers`;
});

const buildQuery = (overrides = {}) => {
    const params = new URLSearchParams(window.location.search);
    const query = {
        per_page: params.get("per_page") || undefined,
        ...filterForm.value,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("patient.providers"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        speciality: "",
    };

    router.get(route("patient.providers"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("patient.providers"), buildQuery({ per_page: perPage.value, page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const closeShareModal = () => {
    showShareModal.value = false;
};

const removeDoctorAccess = (doctorId) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Remove provider access?",
        text: "This provider will no longer have access to your shared records.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, remove",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        showClass: {
            popup: "swal2-show",
        },
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        router.delete(route("patient.remove.doctor.access", doctorId), {
            preserveScroll: true,
        });
    });
};
</script>

<template>
    <AuthLayout title="Providers" description="Manage your connected healthcare providers" heading="Providers">
        <div class="users-toolbar card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                    <div>
                        <h3 class="mb-1">Connected Providers</h3>
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
                        <ActionButtons :actionButtons="buttons" />
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-xl-4">
                        <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                        <div class="input-group providers-search-control">
                            <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input
                                v-model="filterForm.keyword"
                                type="search"
                                class="form-control border-start-0"
                                placeholder="Search providers"
                                @keydown.enter.prevent="applyFilters"
                            />
                            <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label text-muted small text-uppercase mb-2">Speciality</label>
                        <select v-model="filterForm.speciality" class="form-select" @change="applyFilters">
                            <option value="">All specialities</option>
                            <option v-for="option in specialityOptions" :key="option" :value="option">
                                {{ option }}
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
                        <label for="providers-per-page" class="text-muted small text-uppercase mb-0">Rows</label>
                        <select
                            id="providers-per-page"
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

                <Table :columns="columns" :data="connectedDoctors" :search-show="false" :PageOptions="false">
                    <template #provider="{ row }">
                        <div class="d-flex align-items-center gap-3 text-start">
                            <img :src="row.avatar" alt="Provider" class="provider-avatar" />
                            <div>
                                <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
                            </div>
                        </div>
                    </template>

                    <template #specialities="{ row }">
                        <div class="text-start d-flex flex-wrap gap-2">
                            <span v-for="speciality in row.specialities || []" :key="speciality" class="speciality-pill">
                                {{ speciality }}
                            </span>
                            <span v-if="!(row.specialities || []).length" class="text-muted">-</span>
                        </div>
                    </template>

                    <template #email="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.email || "-" }}</div>
                        </div>
                    </template>

                    <template #phone="{ row }">
                        <div class="text-start">
                            <div class="fw-medium text-dark">{{ row.mobile || "-" }}</div>
                        </div>
                    </template>

                    <template #actions="{ row }">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-danger remove-btn" @click="removeDoctorAccess(row.id)">
                                <i class="fa fa-times me-1"></i>Remove Access
                            </button>
                        </div>
                    </template>
                </Table>
            </div>
        </div>

        <ShareDetailsModal
            v-if="showShareModal"
            :isOpen="showShareModal"
            @close="closeShareModal"
        />
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

.providers-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.provider-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    object-fit: cover;
    background: #e2e8f0;
}

.speciality-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.78rem;
    font-weight: 600;
}

.remove-btn {
    border-radius: 10px;
}
</style>
