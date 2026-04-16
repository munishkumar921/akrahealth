<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import SAdminRolesModal from "@/Pages/Modals/SAdminRoles.vue";
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    roles: {
        type: Object,
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
});

const isModalOpen = ref(false);
const childComponentRef = ref(null);

const filterForm = useForm({
    keyword: props.filters.keyword || "",
    status: props.filters.status || "all",
    per_page: props.filters.per_page || 10,
    sort: props.filters.sort || "created_at",
    direction: props.filters.direction || "desc",
});

const hasActiveFilters = computed(
    () =>
        filterForm.keyword ||
        filterForm.status !== "all" ||
        Number(filterForm.per_page) !== 10 ||
        filterForm.sort !== "created_at" ||
        filterForm.direction !== "desc"
);

const metricCards = computed(() => [
    {
        label: "Total Roles",
        value: props.metrics.total_roles ?? 0,
        tone: "sky",
        icon: "bi bi-shield-lock",
    },
    {
        label: "Active Roles",
        value: props.metrics.active_roles ?? 0,
        tone: "emerald",
        icon: "bi bi-check2-circle",
    },
    {
        label: "Inactive Roles",
        value: props.metrics.inactive_roles ?? 0,
        tone: "amber",
        icon: "bi bi-slash-circle",
    },
    {
        label: "Latest Added",
        value: props.metrics.latest_created_at || "No roles yet",
        tone: "indigo",
        icon: "bi bi-clock-history",
    },
]);

function applyFilters() {
    router.get(route("superAdmin.rolesandpermission"), filterForm.data(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    filterForm.reset();
    filterForm.status = "all";
    filterForm.per_page = 10;
    filterForm.sort = "created_at";
    filterForm.direction = "desc";
    applyFilters();
}

function changeSort(column) {
    if (filterForm.sort === column) {
        filterForm.direction = filterForm.direction === "asc" ? "desc" : "asc";
    } else {
        filterForm.sort = column;
        filterForm.direction = column === "name" || column === "guard_name" ? "asc" : "desc";
    }

    applyFilters();
}

function openAdd() {
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

function openEdit(role) {
    isModalOpen.value = true;

    setTimeout(() => {
        childComponentRef.value?.update?.(role);
    }, 100);
}

function toggleStatus(role) {
    const form = useForm({
        is_active: !role.is_active,
    });

    form.post(route("superAdmin.api.roles.toggle", role.id), {
        preserveScroll: true,
        onSuccess: () => applyFilters(),
    });
}

function removeRole(role) {
    Swal.fire({
        title: "Delete role?",
        text: `This will permanently remove ${role.name}.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#7c8ba1",
        confirmButtonText: "Delete",
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        router.delete(route("superAdmin.api.roles.destroy", role.id), {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire("Deleted", "The role was removed successfully.", "success");
            },
        });
    });
}

function sortClass(column) {
    if (filterForm.sort !== column) {
        return "bi bi-arrow-down-up";
    }

    return filterForm.direction === "asc" ? "bi bi-arrow-up" : "bi bi-arrow-down";
}
</script>

<template>
    <AuthLayout title="Roles & Permissions" description="Super Admin - Roles & Permissions"
        heading="Roles & Permissions">
        <section class="roles-page">

            <div class="roles-panel">
                <div class="roles-panel-header">
                    <div>
                        <p class="panel-kicker">Role Registry</p>
                        <h2>Manage access groups</h2>
                    </div>

                    <div v-if="hasActiveFilters" class="filter-chip-row">
                        <span class="filter-chip">Filters active</span>
                        <button type="button" class="btn btn-link clear-link" @click="clearFilters">
                            Clear filters
                        </button>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg" @click="openAdd">
                        <i class="bi bi-plus-lg me-2"></i>
                        Add Role
                    </button>
                </div>

                <div class="filters-grid">
                    <div class="filter-field filter-search">
                        <label class="filter-label">Search</label>
                        <div class="d-flex">
                            <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input v-model="filterForm.keyword" type="text"
                                class="form-control border-start-0 rounded-circle-right"
                                placeholder="Search by role name or guard" @keyup.enter="applyFilters" />
                        </div>
                    </div>

                    <div class="filter-field status-field">
                        <label class="filter-label">Status</label>
                        <select v-model="filterForm.status" class="form-select form-control" @change="applyFilters">
                            <option value="all">All statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="filter-field rows-field">
                        <label class="filter-label">Rows</label>
                        <select v-model="filterForm.per_page" class="form-select form-control" @change="applyFilters">
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>

                    <div class="filter-field filter-actions">
                        <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                    </div>
                </div>

                <div class="table-shell">
                    <table class="table roles-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <button type="button" class="sort-button" @click="changeSort('name')">
                                        Name
                                        <i :class="sortClass('name')"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="sort-button" @click="changeSort('guard_name')">
                                        Guard
                                        <i :class="sortClass('guard_name')"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="sort-button" @click="changeSort('created_at')">
                                        Created At
                                        <i :class="sortClass('created_at')"></i>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="sort-button" @click="changeSort('is_active')">
                                        Status
                                        <i :class="sortClass('is_active')"></i>
                                    </button>
                                </th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="role in roles.data" :key="role.id">
                                <td>
                                    <div class="role-name-cell">
                                        <div class="role-avatar">
                                            {{ role.name.slice(0, 2).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="role-title">{{ role.name }}</div>
                                            <div class="role-subtitle">Role definition</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="guard-badge">{{ role.guard_name }}</span>
                                </td>
                                <td>{{ role.created_at }}</td>
                                <td>
                                    <div class="status-cell">
                                        <span class="status-badge"
                                            :class="role.is_active ? 'status-active' : 'status-inactive'">
                                            {{ role.is_active ? "Active" : "Inactive" }}
                                        </span>
                                        <label class="settings-switch">
                                            <input type="checkbox" :checked="role.is_active"
                                                @change="toggleStatus(role)" />
                                            <span class="settings-slider"></span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="icon-btn icon-edit" @click="openEdit(role)"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="icon-btn icon-delete" @click="removeRole(role)"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="roles.data.length === 0">
                                <td colspan="5">
                                    <div class="empty-state">
                                        No roles found for the current filters.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Modal :isOpen="isModalOpen" @close="closeModal" title="Manage Role" size="lg">
                <SAdminRolesModal ref="childComponentRef" @close="closeModal" />
            </Modal>
        </section>
    </AuthLayout>
</template>

<style scoped>
.roles-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1rem 0 2rem;
}

.roles-hero {
    display: flex;
    justify-content: space-between;
    gap: 1.5rem;
    align-items: flex-start;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(32, 156, 238, 0.16), transparent 40%),
        linear-gradient(135deg, #ffffff 0%, #f6fbff 100%);
    border: 1px solid #dce9f8;
    box-shadow: 0 18px 45px rgba(19, 71, 116, 0.08);
}

.roles-kicker,
.panel-kicker {
    margin: 0 0 0.35rem;
    font-size: 0.78rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #1792e6;
    font-weight: 700;
}

.roles-hero h1,
.roles-panel-header h2 {
    margin: 0;
    color: #1c2740;
    font-size: 2.05rem;
    font-weight: 700;
}

.roles-copy {
    max-width: 720px;
    margin: 0.8rem 0 0;
    color: #5f7293;
    font-size: 1rem;
    line-height: 1.75;
}

.roles-metrics {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.metric-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem 1.35rem;
    border-radius: 24px;
    background: #fff;
    border: 1px solid #dce9f8;
    box-shadow: 0 14px 35px rgba(31, 76, 121, 0.07);
}

.metric-icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    font-size: 1.35rem;
}

.metric-sky {
    background: #e9f5ff;
    color: #1290e4;
}

.metric-emerald {
    background: #eafbf2;
    color: #14a44d;
}

.metric-amber {
    background: #fff6e6;
    color: #d99000;
}

.metric-indigo {
    background: #eef0ff;
    color: #4f46e5;
}

.metric-label {
    display: block;
    color: #6c84a4;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    display: block;
    margin-top: 0.35rem;
    color: #1c2740;
    font-size: 1.45rem;
}

.roles-panel {
    border-radius: 28px;
    background: #fff;
    border: 1px solid #dce9f8;
    box-shadow: 0 18px 45px rgba(19, 71, 116, 0.08);
    padding: 1.5rem;
}

.roles-panel-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1.2rem;
}

.filter-chip-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.filter-chip {
    border-radius: 999px;
    background: #e8f4ff;
    color: #178fe4;
    padding: 0.45rem 0.8rem;
    font-size: 0.86rem;
    font-weight: 600;
}

.clear-link {
    padding: 0;
    color: #178fe4;
    text-decoration: none;
}

.filters-grid {
    display: grid;
    grid-template-columns: minmax(320px, 1.6fr) minmax(220px, 0.9fr) 120px 116px;
    gap: 1rem;
    align-items: end;
    margin-bottom: 1.35rem;
}

.filter-label {
    display: inline-block;
    margin-bottom: 0.5rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #546c8d;
}

.filter-search {
    min-width: 0;
}

.filter-field .form-control,
.filter-field .form-select,
.filter-search .form-control,
.filter-search .input-group-text {
    height: 48px;
}

.filter-field .form-select {
    min-width: 100%;
}

.status-field,
.rows-field {
    min-width: 0;
}

.rows-field .form-select {
    text-align: left;
}

.filter-actions {
    display: flex;
    justify-content: flex-end;
}

.filter-actions .btn {
    height: 48px;
    min-width: 116px;
    border-radius: 14px;
    font-weight: 600;
    padding-inline: 1rem;
}

.table-shell {
    overflow-x: auto;
    border: 1px solid #cfe0f5;
    border-radius: 24px;
}

.roles-table thead th {
    background: #1fa2eb;
    color: #fff;
    font-weight: 600;
    border: 0;
    padding: 1rem 1.1rem;
}

.roles-table tbody td {
    border-color: #dbe9f8;
    padding: 1.05rem 1.1rem;
    vertical-align: middle;
}

.sort-button {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border: 0;
    background: transparent;
    color: inherit;
    padding: 0;
    font-weight: inherit;
}

.role-name-cell {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.role-avatar {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #e8f4ff 0%, #f7fbff 100%);
    color: #128fe3;
    font-weight: 700;
}

.role-title {
    font-weight: 700;
    color: #1c2740;
}

.role-subtitle {
    color: #8096b3;
    font-size: 0.88rem;
}

.guard-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.45rem 0.8rem;
    background: #eff5fb;
    color: #516b8f;
    font-weight: 600;
    text-transform: lowercase;
}

.status-cell {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 88px;
    border-radius: 999px;
    padding: 0.48rem 0.8rem;
    font-weight: 700;
}

.status-active {
    background: #dcfce7;
    color: #15803d;
}

.status-inactive {
    background: #fef2f2;
    color: #dc2626;
}

.settings-switch {
    position: relative;
    display: inline-flex;
    width: 56px;
    height: 30px;
}

.settings-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.settings-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #d2dcea;
    transition: all 0.2s ease;
}

.settings-slider::before {
    content: "";
    position: absolute;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    left: 3px;
    top: 3px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
    transition: transform 0.2s ease;
}

.settings-switch input:checked+.settings-slider {
    background: #20bf7b;
}

.settings-switch input:checked+.settings-slider::before {
    transform: translateX(26px);
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.7rem;
}

.icon-btn {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid #dce9f8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}

.icon-edit {
    color: #1590e2;
}

.icon-delete {
    color: #ef4444;
}

.empty-state {
    padding: 2rem;
    text-align: center;
    color: #7f95b1;
}

@media (max-width: 1200px) {
    .roles-metrics {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {

    .roles-hero,
    .roles-panel-header {
        flex-direction: column;
        align-items: stretch;
    }

    .roles-metrics,
    .filters-grid {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        justify-content: stretch;
    }
}
</style>
