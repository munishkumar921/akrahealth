<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import UploadDocument from "@/Pages/Modals/Documents/UploadDocument.vue";
import Education from "@/Pages/Modals/Documents/AddPatientEducation.vue";
import UploadCCD from "@/Pages/Modals/Documents/UploadCCD.vue";
import UploadCCR from "@/Pages/Modals/Documents/UploadCCR.vue";
import GenerateLetterModal from "@/Pages/Modals/Documents/GenerateLetter.vue";
import Modal from "@/Components/Common/Modal.vue";
import axios from "axios";
import Swal from "sweetalert2/dist/sweetalert2.js";

const selectedPatientId = computed(() => usePage().props.auth?.user?.doctor?.selected_patient_id ?? null);

const tabs = [
    { key: "Laboratory", label: "Laboratory", dotClass: "dot-success" },
    { key: "Imaging", label: "Imaging", dotClass: "dot-warning" },
    { key: "Cardiopulmonary", label: "Cardiopulmonary", dotClass: "dot-primary" },
    { key: "Endoscopy", label: "Endoscopy", dotClass: "dot-secondary" },
    { key: "Refferrals", label: "Referrals", dotClass: "dot-dark" },
    { key: "Past Records", label: "Past Records", dotClass: "dot-primary" },
    { key: "Other Forms", label: "Other Forms", dotClass: "dot-secondary" },
    { key: "Letters", label: "Letters", dotClass: "dot-warning" },
    { key: "Education", label: "Education", dotClass: "dot-success" },
    { key: "CCDAs", label: "CCDAs", dotClass: "dot-primary" },
    { key: "CCRs", label: "CCRs", dotClass: "dot-secondary" },
];

const columns = [
    { label: "Name", key: "name", type: "slot", slot: "name", align: "left" },
    { label: "Date", key: "date", type: "slot", slot: "date", align: "left" },
    { label: "From", key: "from", type: "slot", slot: "from", align: "left" },
];

const perPageOptions = [10, 15, 25, 50, 100];
const perPage = ref(10);
const currentPage = ref(1);
const currentTab = ref("Laboratory");
const isLoading = ref(false);
const isDownloading = ref(false);
const isPrinting = ref(false);
const uploadDocumentModal = ref(false);
const isOpenUploadDocumentCCDModal = ref(false);
const isOpenEducationModal = ref(false);
const isOpenUploadDocumentCCRModal = ref(false);
const isOpenGenerateLetterModal = ref(false);
const isOpenViewModal = ref(false);
const currentDocument = ref(null);
const error = ref(null);
const childComponentRef = ref(null);

const filterForm = ref({
    keyword: "",
    from: "",
});

const successNotification = ref({
    show: false,
    message: "",
});

const documentData = ref({
    laboratory: [],
    imaging: [],
    cardiopulmonary: [],
    endoscopy: [],
    refferrals: [],
    pastRecords: [],
    otherForms: [],
    letters: [],
    education: [],
    ccdas: [],
    ccrs: [],
});

const getStateKey = (tab) => {
    const mapping = {
        Laboratory: "laboratory",
        Imaging: "imaging",
        Cardiopulmonary: "cardiopulmonary",
        Endoscopy: "endoscopy",
        Refferrals: "refferrals",
        "Past Records": "pastRecords",
        "Other Forms": "otherForms",
        Letters: "letters",
        Education: "education",
        CCDAs: "ccdas",
        CCRs: "ccrs",
    };

    return mapping[tab] || tab.toLowerCase().replace(/\s+/g, "");
};

const currentData = computed(() => {
    const stateKey = getStateKey(currentTab.value);
    return documentData.value[stateKey] ?? [];
});

const sourceOptions = computed(() => {
    const values = [...new Set(currentData.value.map((item) => item.from).filter(Boolean))];
    return values.sort((a, b) => a.localeCompare(b));
});

const filteredRows = computed(() => {
    const keyword = filterForm.value.keyword.trim().toLowerCase();
    const source = filterForm.value.from;

    return currentData.value.filter((item) => {
        const matchesKeyword =
            keyword === "" ||
            [item.name, item.date, item.from, item.type, item.text]
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
        return `No documents found in ${currentTab.value}`;
    }

    return `Showing ${paginationData.value.from}-${paginationData.value.to} of ${total} ${currentTab.value.toLowerCase()} document${total === 1 ? "" : "s"}`;
});

const fetchDocuments = async (category) => {
    isLoading.value = true;
    error.value = null;

    try {
        const response = await axios.get(
            route("doctor.documents.byCategory", {
                type: category === "Past Records" ? "past_records" : category.toLowerCase(),
            })
        );

        let stateKey = category.toLowerCase().replace(/\s+/g, "");
        if (stateKey === "pastrecords") {
            stateKey = "pastRecords";
        } else if (stateKey === "otherforms") {
            stateKey = "otherForms";
        }

        if (documentData.value.hasOwnProperty(stateKey)) {
            documentData.value[stateKey] = response.data?.success ? response.data.data : [];
        }
    } catch (err) {
        error.value = err.response?.data?.message || "An error occurred while fetching documents";
        const stateKey = getStateKey(category);
        if (documentData.value.hasOwnProperty(stateKey)) {
            documentData.value[stateKey] = [];
        }
    } finally {
        isLoading.value = false;
    }
};

const resetPagination = () => {
    currentPage.value = 1;
};

const changeTab = async (tab) => {
    currentTab.value = tab;
    filterForm.value.from = "";
    resetPagination();

    const stateKey = getStateKey(tab);
    if (!documentData.value[stateKey]?.length) {
        await fetchDocuments(tab);
    }
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

const openUploadDocumentModal = () => {
    uploadDocumentModal.value = true;
    nextTick(() => {
        if (childComponentRef.value?.resetForm) {
            childComponentRef.value.resetForm(currentTab.value);
        }
    });
};

const closeUploadDocumentModal = () => {
    uploadDocumentModal.value = false;
};

const openEducationModal = () => {
    isOpenEducationModal.value = true;
};

const closeEducationModal = () => {
    isOpenEducationModal.value = false;
};

const openUploadCCDModal = () => {
    isOpenUploadDocumentCCDModal.value = true;
};

const closeUploadDocumentCCDModal = () => {
    isOpenUploadDocumentCCDModal.value = false;
};

const openUploadCCRModal = () => {
    isOpenUploadDocumentCCRModal.value = true;
};

const closeUploadDocumentCCRModal = () => {
    isOpenUploadDocumentCCRModal.value = false;
};

const openEditDocumentModal = (item) => {
    uploadDocumentModal.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(item);
        }
    }, 10);
};

const generateLetter = async () => {
    isOpenGenerateLetterModal.value = true;
};

const closeGenerateLetterModal = () => {
    isOpenGenerateLetterModal.value = false;
};

const viewDocument = (item) => {
    currentDocument.value = item;
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

const download = async (item) => {
    if (!item?.url) {
        return;
    }

    const url = getValidUrl(item.url);
    isDownloading.value = true;

    try {
        const response = await axios.get(url, {
            responseType: "blob",
        });
        const blob = new Blob([response.data], { type: "application/pdf" });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = downloadUrl;
        link.download = item.name || "document.pdf";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(downloadUrl);
    } catch (err) {
        window.open(url, "_blank");
    } finally {
        isDownloading.value = false;
    }
};

const print = async (item) => {
    if (!item?.url) {
        return;
    }

    const url = getValidUrl(item.url);
    isPrinting.value = true;

    try {
        const printWindow = window.open(url, "_blank");
        if (printWindow) {
            printWindow.onload = () => printWindow.print();
        }
    } catch (err) {
        window.open(url, "_blank");
    } finally {
        isPrinting.value = false;
    }
};

const onDocumentSaved = () => {
    fetchDocuments(currentTab.value);
};

const onEducationSaved = () => {
    successNotification.value = {
        show: true,
        message: "Education material saved successfully!",
    };

    fetchDocuments("Education");

    setTimeout(() => {
        successNotification.value.show = false;
    }, 5000);
};

const deleteDocument = (item) => {
    Swal.fire({
        toast: true,
        position: "top-end",
        title: "Delete document?",
        text: "This document will be permanently removed.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        showClass: {
            popup: "swal2-show",
        },
    }).then(async (result) => {
        if (!result.isConfirmed) {
            return;
        }

        try {
            isLoading.value = true;
            const response = await axios.delete(route("doctor.documents.destroy", { id: item.id }));

            if (response.data.success) {
                const stateKey = getStateKey(currentTab.value);
                documentData.value[stateKey] = documentData.value[stateKey].filter((doc) => doc.id !== item.id);
                resetPagination();
                toast("Document has been deleted successfully.", "success");
            } else {
                throw new Error(response.data.message || "Failed to delete document");
            }
        } catch (err) {
            toast(err.response?.data?.message || err.message || "Failed to delete document", "error");
        } finally {
            isLoading.value = false;
        }
    });
};

watch(
    () => [filterForm.value.keyword, filterForm.value.from, perPage.value],
    () => {
        resetPagination();
    }
);

onMounted(() => {
    fetchDocuments(currentTab.value);
});
</script>

<template>
    <AuthLayout title="Documents" description="Documents" heading="Documents">
        <div v-if="successNotification.show" class="alert alert-success alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            {{ successNotification.message }}
            <button type="button" class="close" @click="successNotification.show = false" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div v-if="!selectedPatientId" class="alert alert-warning text-center py-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Patient Selected</h5>
            <p class="mb-0">Please select a patient from the patient list to view documents.</p>
        </div>

        <template v-else>
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
                                    <button v-if="hasActiveFilters" type="button"
                                        class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                        <i class="bi bi-x-circle me-1"></i>Clear filters
                                    </button>

                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bi bi-plus-circle me-1"></i>Add
                                        </button>
                                        <div class="dropdown-menu">
                                            <button @click="openUploadDocumentModal" class="dropdown-item pointer">
                                                Upload Document
                                            </button>
                                            <button @click="generateLetter" class="dropdown-item pointer">
                                                Generate Letter
                                            </button>
                                            <button @click="openEducationModal" class="dropdown-item pointer">
                                                Add Patient Education
                                            </button>
                                            <button @click="openUploadCCDModal" class="dropdown-item pointer">
                                                Upload Consolidated Clinical Document (C-CDA)
                                            </button>
                                            <button @click="openUploadCCRModal" class="dropdown-item pointer">
                                                Upload Continuity of Care Record (CCR)
                                            </button>
                                        </div>
                                    </div>
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
                                            class="form-control border-start-0"
                                            placeholder="Search by name, date, source"
                                            @keydown.enter.prevent="applyFilters" />
                                        <button type="button" class="btn btn-primary"
                                            @click="applyFilters">Search</button>
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

                            <div v-if="error" class="alert alert-danger mx-3 mx-md-0">
                                {{ error }}
                            </div>

                            <Table :columns="columns" :data="paginationData" :search-show="false" :PageOptions="false">
                                <template #name="{ row }">
                                    <div class="text-start">
                                        <div class="fw-semibold text-dark">{{ row.name || "-" }}</div>
                                        <div class="text-muted small">{{ row.type || currentTab }}</div>
                                    </div>
                                </template>

                                <template #date="{ row }">
                                    <div class="text-start">
                                        <div class="fw-medium text-dark">{{ row.date || "-" }}</div>
                                    </div>
                                </template>

                                <template #from="{ row }">
                                    <div class="text-start">
                                        <div class="fw-medium text-dark">{{ row.from || "-" }}</div>
                                    </div>
                                </template>

                                <template #actions="{ row: item }">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button @click="openEditDocumentModal(item)" class="btn btn-primary action-btn"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button @click="viewDocument(item)" class="btn btn-success action-btn"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button @click="deleteDocument(item)" class="btn btn-danger action-btn"
                                            title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </template>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <Modal :isOpen="uploadDocumentModal" @close="closeUploadDocumentModal" :title="`${'Document'} ${currentTab}`" size="xl">
            <UploadDocument
                ref="childComponentRef"
                :default-type="currentTab"
                @close="closeUploadDocumentModal"
                @saved="onDocumentSaved"
            />
        </Modal>

        <Modal :isOpen="isOpenEducationModal" @close="closeEducationModal" title="Add Patient Education" size="xl">
            <Education @close="closeEducationModal" @saved="onEducationSaved" />
        </Modal>

        <Modal :isOpen="isOpenUploadDocumentCCDModal" @close="closeUploadDocumentCCDModal"
            title="Upload Consolidated Clinical Document (C-CDA)" size="xl">
            <UploadCCD @close="closeUploadDocumentCCDModal" />
        </Modal>

        <Modal :isOpen="isOpenUploadDocumentCCRModal" @close="closeUploadDocumentCCRModal"
            title="Upload Continuity of Care Record (CCR)" size="xl">
            <UploadCCR @close="closeUploadDocumentCCRModal" />
        </Modal>

        <Modal :isOpen="isOpenGenerateLetterModal" @close="closeGenerateLetterModal" title="Generate Letter" size="xl">
            <GenerateLetterModal :patient-id="selectedPatientId" @close="closeGenerateLetterModal" />
        </Modal>

        <Modal :isOpen="isOpenViewModal" @close="closeViewModal" title="View Document" size="xl">
            <div v-if="currentDocument">
                <div class="letter-details mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Type:</strong> {{ currentDocument.type || "Letter" }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong> {{ currentDocument.date }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>From:</strong> {{ currentDocument.from || "N/A" }}
                        </div>
                        <div class="col-md-6">
                            <strong>Description:</strong> {{ currentDocument.text || currentDocument.name }}
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

.users-toolbar {
    position: relative;
    z-index: 20;
    overflow: visible !important;
}

.users-toolbar .card-body {
    overflow: visible !important;
}

.users-toolbar .btn-group {
    position: relative;
    z-index: 30;
}

.users-toolbar .dropdown-menu {
    position: absolute;
    z-index: 2050;
}

.users-toolbar + .card {
    position: relative;
    z-index: 1;
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
