<script setup>
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2/dist/sweetalert2.js";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";

const props = defineProps({
    services: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    category: props.filters?.category || "",
    status: props.filters?.status || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const rows = computed(() => props.services?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const resultSummary = computed(() => {
    const total = props.services?.total ?? rows.value.length;
    const from = props.services?.from ?? (rows.value.length ? 1 : 0);
    const to = props.services?.to ?? rows.value.length;

    if (!total) return "No services found";

    return `Showing ${from}-${to} of ${total} services`;
});

const metricCards = computed(() => [
    {
        label: "Total Services",
        value: Number(props.metrics?.total_services ?? 0).toLocaleString("en-IN"),
        helper: "All services in the filtered result set",
        icon: "fa-solid fa-layer-group",
        tone: "tone-blue",
    },
    {
        label: "Active",
        value: Number(props.metrics?.active_services ?? 0).toLocaleString("en-IN"),
        helper: "Services currently available",
        icon: "fa-solid fa-circle-check",
        tone: "tone-green",
    },
    {
        label: "Inactive",
        value: Number(props.metrics?.inactive_services ?? 0).toLocaleString("en-IN"),
        helper: "Hidden or paused services",
        icon: "fa-solid fa-circle-pause",
        tone: "tone-slate",
    },
    {
        label: "New This Month",
        value: Number(props.metrics?.new_this_month ?? 0).toLocaleString("en-IN"),
        helper: "Recently added services",
        icon: "fa-solid fa-sparkles",
        tone: "tone-amber",
    },
]);

const columns = [
    { label: "Service", key: "name", type: "slot", slot: "service", align: "left" },
    { label: "Category", key: "category_label", align: "left" },
    { label: "Description", key: "description", type: "slot", slot: "description", align: "left" },
    { label: "Created", key: "created_at", type: "slot", slot: "created", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status" },
];

const buildQuery = () => {
    const params = new URLSearchParams(window.location.search);

    return Object.fromEntries(
        Object.entries({
            per_page: params.get("per_page") || undefined,
            sort: params.get("sort") || undefined,
            direction: params.get("direction") || undefined,
            ...filterForm.value,
        }).filter(([, value]) => value !== "" && value !== null && value !== undefined)
    );
};

const applyFilters = () => {
    router.get(route("superAdmin.services.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        category: "",
        status: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.services.index"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const isFormModalOpen = ref(false);
const isViewModalOpen = ref(false);
const currentService = ref(null);
const bannerPreview = ref(null);
const bannerInputRef = ref(null);

const form = useForm({
    id: null,
    name: "",
    description: "",
    category: props.categories?.[0]?.id || "consultation",
    is_active: true,
    banner: null,
    old_banner: "",
    remove_banner: false,
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    form.name = "";
    form.description = "";
    form.category = props.categories?.[0]?.id || "consultation";
    form.is_active = true;
    form.banner = null;
    form.old_banner = "";
    form.remove_banner = false;
    bannerPreview.value = null;
};

const openAddModal = () => {
    currentService.value = null;
    resetForm();
    isFormModalOpen.value = true;
};

const openEditModal = (service) => {
    currentService.value = service;
    form.clearErrors();
    form.id = service.id;
    form.name = service.name || "";
    form.description = service.description || "";
    form.category = service.category || props.categories?.[0]?.id || "consultation";
    form.is_active = !!service.is_active;
    form.banner = null;
    form.old_banner = service.banner_url || "";
    form.remove_banner = false;
    bannerPreview.value = service.banner_url || null;
    isFormModalOpen.value = true;
};

const openViewModal = (service) => {
    currentService.value = service;
    isViewModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    resetForm();
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    currentService.value = null;
};

const onBannerChange = (event) => {
    const file = event.target.files?.[0] || null;
    form.banner = file;
    form.remove_banner = false;

    if (file) {
        bannerPreview.value = URL.createObjectURL(file);
    } else {
        bannerPreview.value = form.old_banner || null;
    }
};

const triggerBannerSelect = () => {
    bannerInputRef.value?.click();
};

const removeBanner = () => {
    form.banner = null;
    form.old_banner = "";
    form.remove_banner = true;
    bannerPreview.value = null;
};

const submitForm = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeFormModal();
        },
    };

    if (form.id) {
        form.post(route("superAdmin.services.update", form.id), {
            ...options,
            _method: "put",
        });
        return;
    }

    form.post(route("superAdmin.services.store"), options);
};

const removeRow = (row) => {
    Swal.fire({
        title: "Delete service?",
        text: `You are about to delete "${row.name}". This action cannot be undone.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#ef4444",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("superAdmin.services.destroy", row.id), {
                preserveScroll: true,
            });
        }
    });
};

const toggleStatus = async (row) => {
    try {
        const response = await axios.post(route("superAdmin.services.toggle-status", row.id));
        row.is_active = response.data.is_active;
        row.status_label = response.data.is_active ? "Active" : "Inactive";
        toast(response.data.message, "success", 2000);
    } catch (error) {
        toast(error?.response?.data?.message || "Unable to update service status.", "error", 2000);
    }
};
</script>

<template>
    <AuthLayout title="Services" description="Manage platform services and offerings">
        <section class="service-page">

            <div class="card border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div v-if="hasActiveFilters" class="filter-tools">
                            <span class="filter-badge">
                                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                <i class="bi bi-x-circle me-1"></i> Clear filters
                            </button>
                        </div>
                    <div v-else></div>
                        <button type="button" class="btn btn-primary" @click="openAddModal">
                            <i class="bi bi-plus-circle me-2"></i>Add Service
                        </button>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by name, description, or category"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Category</label>
                            <select v-model="filterForm.category" class="form-select" @change="applyFilters">
                                <option value="">All categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Status</label>
                            <select v-model="filterForm.status" class="form-select" @change="applyFilters">
                                <option value="">All statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">From date</label>
                            <input v-model="filterForm.date_from" type="date" class="form-control"
                                @change="applyFilters" />
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">To date</label>
                            <input v-model="filterForm.date_to" type="date" class="form-control"
                                @change="applyFilters" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm table-shell">
                <div class="card-body">

                    <Table :columns="columns" :data="services" table="services" :search-show="false">
                        <template #service="{ row }">
                            <div class="service-cell">
                                <img :src="row.banner_url" :alt="row.name" class="service-avatar" />
                                <div>
                                    <div class="service-name">{{ row.name }}</div>
                                    <div class="service-subtitle">{{ row.category_label }}</div>
                                </div>
                            </div>
                        </template>

                        <template #description="{ row }">
                            <div class="description-cell">
                                {{ row.description || "No description added." }}
                            </div>
                        </template>

                        <template #created="{ row }">
                            {{ row.created_label || "N/A" }}
                        </template>

                        <template #status="{ row }">
                            <div class="status-cell">
                                <span class="status-pill" :class="row.is_active ? 'status-active' : 'status-inactive'">
                                    {{ row.status_label }}
                                </span>
                                <label class="switch ms-2">
                                    <input :checked="row.is_active" type="checkbox" @change="toggleStatus(row)" />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="action-row">
                                <button type="button" class="table-action-btn action-view" title="View"
                                    @click="openViewModal(row)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="table-action-btn action-edit" title="Edit"
                                    @click="openEditModal(row)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="table-action-btn action-delete" title="Delete"
                                    @click="removeRow(row)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </section>

        <Modal :is-open="isFormModalOpen" :title="form.id ? 'Edit Service' : 'Add Service'" size="xl"
            @close="closeFormModal">
            <form class="service-form" @submit.prevent="submitForm">
                <div class="form-grid">
                    <div class="form-main">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label">Service name</label>
                                <input v-model="form.name" type="text" class="form-control"
                                    placeholder="Enter service name" />
                                <div v-if="form.errors.name" class="text-danger small mt-1">{{ form.errors.name }}</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Category</label>
                                <select v-model="form.category" class="form-select">
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.category" class="text-danger small mt-1">{{ form.errors.category
                                }}</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea v-model="form.description" rows="5" class="form-control"
                                    placeholder="Add a short description for this service"></textarea>
                                <div v-if="form.errors.description" class="text-danger small mt-1">{{
                                    form.errors.description }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="form-side">
                        <div class="upload-card">
                            <p class="upload-title">Service banner</p>
                            <div class="upload-preview">
                                <img :src="bannerPreview || '/images/avatar.webp'" alt="Banner preview" />
                            </div>
                            <input ref="bannerInputRef" type="file" class="d-none" accept="image/*"
                                @change="onBannerChange" />
                            <div class="upload-actions">
                                <button type="button" class="btn btn-outline-primary" @click="triggerBannerSelect">
                                    <i class="bi bi-image me-2"></i>
                                    {{ bannerPreview || form.old_banner ? "Change banner" : "Select image" }}
                                </button>
                                <span class="upload-hint">PNG, JPG, or WEBP up to 5MB</span>
                            </div>
                            <div v-if="form.errors.banner" class="text-danger small mt-1">{{ form.errors.banner }}</div>
                            <button v-if="bannerPreview || form.old_banner" type="button"
                                class="btn btn-outline-danger btn-sm mt-2" @click="removeBanner">
                                Remove banner
                            </button>
                        </div>

                        <div class="status-card mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="status-title">Status</p>
                                </div>
                                <label class="switch">
                                    <input v-model="form.is_active" type="checkbox" />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline-secondary" @click="closeFormModal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? "Saving..." : (form.id ? "Update Service" : "Save Service") }}
                    </button>
                </div>
            </form>
        </Modal>

        <Modal :is-open="isViewModalOpen" title="Service Details" size="lg" @close="closeViewModal">
            <div v-if="currentService" class="service-view">
                <div class="service-view-header">
                    <img :src="currentService.banner_url" :alt="currentService.name" class="service-view-avatar" />
                    <div>
                        <h3>{{ currentService.name }}</h3>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="status-pill status-soft">{{ currentService.category_label }}</span>
                            <span class="status-pill"
                                :class="currentService.is_active ? 'status-active' : 'status-inactive'">
                                {{ currentService.status_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="service-view-grid">
                    <div class="detail-card">
                        <p class="detail-label">Description</p>
                        <p class="detail-value">{{ currentService.description || "No description added." }}</p>
                    </div>

                    <div class="detail-card">
                        <p class="detail-label">Created</p>
                        <p class="detail-value">{{ currentService.created_label || "N/A" }}</p>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.service-page {
    display: grid;
    gap: 1.5rem;
}

.service-hero .card-body {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    padding: 1.75rem;
}

.hero-kicker,
.filter-kicker,
.table-kicker {
    margin: 0 0 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.72rem;
    font-weight: 700;
    color: #0ea5e9;
}

.hero-title,
.filter-title,
.table-title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #0f172a;
}

.hero-text,
.table-summary {
    margin: 0.5rem 0 0;
    color: #64748b;
    max-width: 720px;
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.metric-card .card-body {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.metric-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    font-size: 1.2rem;
    background: rgba(255, 255, 255, 0.75);
}

.metric-label {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

.metric-value {
    margin: 0.25rem 0;
    font-size: 1.6rem;
    font-weight: 700;
    color: #0f172a;
}

.metric-helper {
    margin: 0;
    color: #64748b;
    font-size: 0.82rem;
}

.tone-blue {
    background: linear-gradient(135deg, #eff6ff, #f8fbff);
}

.tone-green {
    background: linear-gradient(135deg, #ecfdf5, #f7fffb);
}

.tone-slate {
    background: linear-gradient(135deg, #f8fafc, #ffffff);
}

.tone-amber {
    background: linear-gradient(135deg, #fff7ed, #fffdf7);
}

.filter-card .card-body,
.table-shell .card-body {
    padding: 1.5rem;
}

.filter-header,
.table-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.filter-tools {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.filter-badge {
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 0.85rem;
    font-weight: 600;
}

.service-cell {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.service-avatar,
.service-view-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid #dbeafe;
}

.service-name {
    font-weight: 700;
    color: #0f172a;
}

.service-subtitle {
    color: #64748b;
    font-size: 0.88rem;
}

.description-cell {
    max-width: 420px;
    color: #475569;
    white-space: normal;
}

.status-cell,
.action-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.table-action-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid #dbe4f0;
    background: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: 0.2s ease;
}

.table-action-btn i {
    line-height: 1;
}

.action-view {
    color: #2563eb;
}

.action-view:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.action-edit {
    color: #0f766e;
}

.action-edit:hover {
    background: #ecfeff;
    border-color: #99f6e4;
}

.action-delete {
    color: #ef4444;
}

.action-delete:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #e2e8f0;
    color: #475569;
}

.status-soft {
    background: #eff6ff;
    color: #2563eb;
}

.switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    inset: 0;
    cursor: pointer;
    background-color: #cbd5e1;
    transition: 0.2s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.2s;
}

input:checked+.slider {
    background-color: #10b981;
}

input:checked+.slider:before {
    transform: translateX(20px);
}

.slider.round {
    border-radius: 999px;
}

.slider.round:before {
    border-radius: 50%;
}

.service-form {
    display: grid;
    gap: 1.25rem;
}

.form-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
    gap: 1.25rem;
}

.form-main,
.upload-card,
.status-card,
.detail-card {
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 1rem;
    background: #fff;
}

.upload-title,
.status-title,
.detail-label {
    margin: 0 0 0.35rem;
    font-weight: 700;
    color: #0f172a;
}

.status-helper {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

.upload-preview {
    width: 100%;
    aspect-ratio: 4 / 2.3;
    border-radius: 18px;
    overflow: hidden;
    background: #f8fafc;
    margin-bottom: 0.9rem;
}

.upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-actions {
    display: grid;
    gap: 0.6rem;
}

.upload-hint {
    font-size: 0.82rem;
    color: #64748b;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.service-view {
    display: grid;
    gap: 1rem;
}

.service-view-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.service-view-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.detail-value {
    margin: 0;
    color: #334155;
}

@media (max-width: 1199px) {
    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 767px) {

    .service-hero .card-body,
    .filter-header,
    .table-header {
        flex-direction: column;
    }

    .metric-grid,
    .service-view-grid {
        grid-template-columns: 1fr;
    }

    .hero-title,
    .filter-title,
    .table-title {
        font-size: 1.4rem;
    }
}
</style>
