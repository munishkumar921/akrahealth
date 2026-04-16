<script setup>
import { computed, ref, watch } from "vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";

const props = defineProps({
    documents: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const tabs = [
    { key: "laboratory", label: "Laboratory", dotClass: "dot-success" },
    { key: "imaging", label: "Imaging", dotClass: "dot-warning" },
    { key: "cardiopulmonary", label: "Cardiopulmonary", dotClass: "dot-primary" },
    { key: "endoscopy", label: "Endoscopy", dotClass: "dot-secondary" },
    { key: "referrals", label: "Referrals", dotClass: "dot-dark" },
    { key: "past-records", label: "Past Records", dotClass: "dot-primary" },
    { key: "other-forms", label: "Other Forms", dotClass: "dot-secondary" },
    { key: "letters", label: "Letters", dotClass: "dot-warning" },
    { key: "education", label: "Education", dotClass: "dot-success" },
    { key: "ccdas", label: "CCDAs", dotClass: "dot-primary" },
    { key: "ccrs", label: "CCRs", dotClass: "dot-secondary" },
];

const columns = [
    { label: "Name", key: "description", type: "slot", slot: "name", align: "left" },
    { label: "From", key: "from", type: "slot", slot: "from", align: "left" },
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
];

const currentTab = ref("laboratory");
const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(10);
const currentPage = ref(1);
const isOpenViewModal = ref(false);
const currentDocument = ref(null);
const isDownloading = ref(false);
const isPrinting = ref(false);

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    from: "",
});

const currentData = computed(() => props.documents?.[currentTab.value] || []);

const sourceOptions = computed(() => {
    const values = [...new Set(currentData.value.map((item) => item.from).filter(Boolean))];
    return values.sort((a, b) => String(a).localeCompare(String(b)));
});

const filteredRows = computed(() => {
    const keyword = filterForm.value.keyword.trim().toLowerCase();
    const source = filterForm.value.from;

    return currentData.value.filter((item) => {
        const matchesKeyword =
            keyword === "" ||
            [item.description, item.name, item.date, item.from, item.type, item.text]
                .filter(Boolean)
                .some((value) => String(value).toLowerCase().includes(keyword));

        const matchesSource = source === "" || item.from === source;

        return matchesKeyword && matchesSource;
    });
});

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredRows.value.slice(start, start + perPage.value);
});

const paginationData = computed(() => {
    const total = filteredRows.value.length;
    const from = total === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1;
    const to = total === 0 ? 0 : Math.min(currentPage.value * perPage.value, total);
    const lastPage = Math.max(1, Math.ceil(total / perPage.value));

    return {
        data: paginatedRows.value,
        current_page: currentPage.value,
        last_page: lastPage,
        per_page: perPage.value,
        total,
        from,
        to,
        prev_page_url: currentPage.value > 1 ? "#" : null,
        next_page_url: currentPage.value < lastPage ? "#" : null,
    };
});

const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = paginationData.value.total;
    if (!total) {
        return `No documents found in ${tabs.find((tab) => tab.key === currentTab.value)?.label || "Documents"}`;
    }

    const label = tabs.find((tab) => tab.key === currentTab.value)?.label?.toLowerCase() || "document";
    return `Showing ${paginationData.value.from}-${paginationData.value.to} of ${total} ${label} document${total === 1 ? "" : "s"}`;
});

const resetPagination = () => {
    currentPage.value = 1;
};

const changeTab = (tab) => {
    currentTab.value = tab;
    filterForm.value.from = "";
    resetPagination();
};

const applyFilters = () => {
    resetPagination();
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        from: "",
    };
    resetPagination();
};

const updatePerPage = () => {
    resetPagination();
};

const view = (document) => {
    currentDocument.value = document;
    isOpenViewModal.value = true;
};

const closeViewModal = () => {
    isOpenViewModal.value = false;
    currentDocument.value = null;
    isDownloading.value = false;
    isPrinting.value = false;
};

const getValidUrl = (url) => {
    if (!url) return "";
    return url.startsWith("http") || url.startsWith("/") ? url : `/${url}`;
};

const download = async (document) => {
    if (!document?.url) return;

    isDownloading.value = true;
    try {
        window.open(getValidUrl(document.url), "_blank");
    } finally {
        isDownloading.value = false;
    }
};

const print = async (document) => {
    if (!document?.url) return;

    isPrinting.value = true;
    try {
        const printWindow = window.open(getValidUrl(document.url), "_blank");
        if (printWindow) {
            printWindow.onload = () => {
                printWindow.print();
            };
        }
    } finally {
        isPrinting.value = false;
    }
};

watch(
    () => [filterForm.value.keyword, filterForm.value.from, perPage.value],
    () => {
        resetPagination();
    }
);
</script>

<template>
    <AuthLayout title="Documents" description="Documents" heading="Documents">
        <div class="row g-4">
            <div class="col-12 col-xl-3">
                <div class="card border-0 shadow-sm documents-side-card">
                    <div class="card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.key" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.key }" @click="changeTab(tab.key)">
                                <span class="dot" :class="tab.dotClass"></span>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-9">
                <div class="users-toolbar card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div
                            class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                            <div>
                                <h3 class="mb-1">Documents</h3>
                                <p class="text-muted mb-0">{{ resultSummary }}</p>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-lg-end">
                                <span v-if="hasActiveFilters" class="filter-count-badge">
                                    {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                                </span>
                                <button v-if="hasActiveFilters" type="button" class="btn btn-outline-secondary btn-sm"
                                    @click="clearFilters">
                                    <i class="bi bi-x-circle me-1"></i>Clear filters
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-sm-6 col-xl-4">
                                <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                                <div class="input-group documents-search-control">
                                    <span
                                        class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input v-model="filterForm.keyword" type="search"
                                        class="form-control border-start-0" placeholder="Search by name, date, source"
                                        @keydown.enter.prevent="applyFilters" />
                                    <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <label class="form-label text-muted small text-uppercase mb-2">From</label>
                                <select v-model="filterForm.from" class="form-select" @change="applyFilters">
                                    <option value="">All sources</option>
                                    <option v-for="option in sourceOptions" :key="option" :value="option">
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
                                <label for="documents-per-page"
                                    class="text-muted small text-uppercase mb-0">Rows</label>
                                <select id="documents-per-page" v-model="perPage"
                                    class="form-select form-select-sm top-page-select" @change="updatePerPage">
                                    <option v-for="option in perPageOptions" :key="option" :value="option">
                                        {{ option }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <Table :columns="columns" :data="paginationData" :search-show="false" :PageOptions="false">
                            <template #name="{ row }">
                                <div class="text-start">
                                    <div class="fw-semibold text-dark">{{ row.description || row.name || "-" }}</div>
                                    <div class="text-muted small">{{ row.type || currentTab }}</div>
                                </div>
                            </template>

                            <template #from="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.from || "-" }}</div>
                                </div>
                            </template>

                            <template #date="{ row }">
                                <div class="text-start">
                                    <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                                </div>
                            </template>

                            <template #actions="{ row }">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button class="btn btn-success action-btn" @click="view(row)" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :isOpen="isOpenViewModal" @close="closeViewModal" title="View Document" size="lg">
            <div v-if="currentDocument">
                <div class="letter-details mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ currentDocument.type || "Document" }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ currentDocument.date || "-" }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>From:</strong> {{ currentDocument.from || "N/A" }}
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> {{ currentDocument.description || currentDocument.name || "-"
                            }}
                        </div>
                    </div>
                </div>

                <div class="letter-actions mb-3">
                    <button @click="download(currentDocument)" class="btn btn-primary mr-2" :disabled="isDownloading">
                        <span v-if="isDownloading" class="spinner-border spinner-border-sm mr-1" role="status"
                            aria-hidden="true"></span>
                        <i v-else class="bi bi-download"></i> {{ isDownloading ? "Downloading..." : "Download PDF" }}
                    </button>
                    <button @click="print(currentDocument)" class="btn btn-success mr-2" :disabled="isPrinting">
                        <span v-if="isPrinting" class="spinner-border spinner-border-sm mr-1" role="status"
                            aria-hidden="true"></span>
                        <i v-else class="bi bi-printer"></i> {{ isPrinting ? "Preparing..." : "Print" }}
                    </button>
                    <a v-if="currentDocument.url" :href="getValidUrl(currentDocument.url)" target="_blank"
                        class="btn btn-info">
                        <i class="bi bi-eye"></i> Open PDF
                    </a>
                </div>
            </div>
            <div v-else class="text-center text-muted">No document data available</div>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar,
.documents-side-card {
    border-radius: 20px;
}

.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.85rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    color: #334155;
    text-align: left;
    transition: all 0.2s ease;
}

.menu-item.active {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}

.label {
    font-weight: 600;
    font-size: 0.95rem;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    flex-shrink: 0;
}

.dot-success {
    background: #22c55e;
}

.dot-warning {
    background: #f59e0b;
}

.dot-primary {
    background: #3b82f6;
}

.dot-secondary {
    background: #64748b;
}

.dot-dark {
    background: #0f172a;
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

.documents-search-control {
    max-width: 100%;
}

.rows-select-wrap {
    min-width: 118px;
}

.top-page-select {
    width: 84px;
}

.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
</style>
