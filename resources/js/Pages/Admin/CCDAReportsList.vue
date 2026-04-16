<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import * as XLSX from "xlsx";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";

const props = defineProps({
    ccdaReports: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({
            keyword: "",
        }),
    },
});

const columns = [
    { key: "select", type: "checkbox", headerSlot: "select-all-header" },
    { label: "Patient", key: "patient_name", type: "slot", slot: "patient", align: "left" },
    { label: "Provider", key: "doctor_name", type: "slot", slot: "provider", align: "left" },
    { label: "Source", key: "from", type: "slot", slot: "source", align: "left" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Document Date", key: "date", type: "slot", slot: "documentDate", align: "left" },
    { label: "Uploaded", key: "created_at", type: "slot", slot: "uploadedAt", align: "left" },
];

const filterForm = ref({
    keyword: props.filters?.keyword || "",
});

const selectedRows = ref([]);
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(
    Number(new URLSearchParams(window.location.search).get("per_page")) || Number(props.ccdaReports?.per_page) || 10
);

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.ccdaReports?.total ?? props.ccdaReports?.data?.length ?? 0;
    const from = props.ccdaReports?.from ?? (total ? 1 : 0);
    const to = props.ccdaReports?.to ?? total;

    if (!total) {
        return "No CCDA reports found";
    }

    return `Showing ${from}-${to} of ${total} CCDA reports`;
});

const visibleRows = computed(() => props.ccdaReports?.data ?? []);

const isAllSelected = computed(() => {
    return visibleRows.value.length > 0 && selectedRows.value.length === visibleRows.value.length;
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedRows.value = [];
    } else {
        selectedRows.value = visibleRows.value.map((row) => row.id);
    }
};

watch(visibleRows, (newRows) => {
    const visibleIds = new Set(newRows.map((row) => row.id));
    selectedRows.value = selectedRows.value.filter((id) => visibleIds.has(id));
});

const toast = (msg, type = "warning") => {
    toastMsg.value = msg;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 2000);
};

const buildQuery = (overrides = {}) => ({
    ...Object.fromEntries(
        Object.entries({
            ...filterForm.value,
            per_page: perPage.value,
            ...overrides,
        }).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    ),
});

const applyFilters = () => {
    router.get(route("admin.CCDAReports"), buildQuery({ page: 1 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
    };

    router.get(route("admin.CCDAReports"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const updatePerPage = () => {
    router.get(route("admin.CCDAReports"), buildQuery({ page: 1, per_page: perPage.value }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const exportRows = computed(() =>
    visibleRows.value.filter((row) => selectedRows.value.includes(row.id))
);

const buttons = [
    {
        label: "Download as Excel",
        function: () => downloadExcel(),
        icon: "bi bi-download",
        disabled: computed(() => selectedRows.value.length === 0),
    },
];

const downloadExcel = () => {
    if (!selectedRows.value.length) {
        toast("Please select at least one CCDA report to export.");
        return;
    }

    const dataToExport = exportRows.value.map((row) => ({
        Patient: row.patient_name,
        Provider: row.doctor_name,
        Source: row.from,
        Description: row.description,
        Type: row.type,
        "Document Date": row.date,
        Uploaded: row.created_at,
        File: row.file_name,
    }));

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "CCDA Reports");
    XLSX.writeFile(workbook, "CCDAReports.xlsx");
};

const resolveFileUrl = (value) => {
    if (!value) return null;
    if (value.startsWith("http://") || value.startsWith("https://") || value.startsWith("/")) {
        return value;
    }

    return `/${value}`;
};

const openDocument = (row) => {
    const target = resolveFileUrl(row.url);
    if (!target) {
        toast("No document file available for this CCDA report.");
        return;
    }

    window.open(target, "_blank", "noopener,noreferrer");
};

const downloadDocument = (row) => {
    const target = resolveFileUrl(row.url);
    if (!target) {
        toast("No document file available for this CCDA report.");
        return;
    }

    const link = document.createElement("a");
    link.href = target;
    link.download = row.file_name || "ccda-document";
    document.body.appendChild(link);
    link.click();
    link.remove();
};
</script>

<template>
    <AuthLayout title="CCDA Reports" description="CCDA Reports" heading="CCDA Reports">
        <div class="ccda-reports-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">CCDA Reports</h3>
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
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group ccda-search-control">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search patient, provider, source, description"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <div class="d-flex justify-content-end align-items-center px-3 px-md-0 pt-3 pt-md-0 pb-2">
                        <div class="d-flex align-items-center gap-2 rows-select-wrap">
                            <select
                                id="ccda-reports-per-page"
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

                    <Table
                        :columns="columns"
                        :data="ccdaReports"
                        v-model:selectedRows="selectedRows"
                        :isAllSelected="isAllSelected"
                        :search-show="false"
                        :PageOptions="false"
                        @toggle-select-all="toggleSelectAll"
                    >
                        <template #header-select-all-header>
                            <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
                        </template>

                        <template #patient="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.patient_name || "-" }}</div>
                            </div>
                        </template>

                        <template #provider="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.doctor_name || "-" }}</div>
                            </div>
                        </template>

                        <template #source="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.from || "-" }}</div>
                            </div>
                        </template>

                        <template #description="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.description || "-" }}</div>
                                <div class="text-muted small">{{ row.file_name || "-" }}</div>
                            </div>
                        </template>

                        <template #documentDate="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                            </div>
                        </template>

                        <template #uploadedAt="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.created_at || "-" }}</div>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex gap-2">
                                <button class="icon-btn btn btn-primary" @click="openDocument(row)" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="icon-btn btn btn-success" @click="downloadDocument(row)" title="Download">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>

            <div
                v-if="showToast"
                class="ah-toast"
                :class="toastType === 'success' ? 'ah-toast--success' : 'ah-toast--warning'"
                role="status"
            >
                <i class="bi" :class="toastType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
                <span>{{ toastMsg }}</span>
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

.top-page-select {
    min-width: 74px;
    width: 74px;
}

.rows-select-wrap {
    flex: 0 0 auto;
}

.ccda-search-control {
    max-width: 720px;
}

.ah-toast {
    position: fixed;
    right: 1.25rem;
    bottom: 1.25rem;
    z-index: 2000;
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1rem;
    border-radius: 14px;
    color: #fff;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

.ah-toast--success {
    background: #059669;
}

.ah-toast--warning {
    background: #d97706;
}
</style>
