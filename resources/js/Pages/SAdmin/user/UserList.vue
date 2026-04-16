<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    admins: Object,
    metrics: Object,
    countries: Array,
    roles: Array,
    plans: Array,
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    role: props.filters?.role || "",
    status: props.filters?.status ?? "",
    verified: props.filters?.verified ?? "",
    country: props.filters?.country || "",
    plan_id: props.filters?.plan_id || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const selectedUser = ref(null);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isSaving = ref(false);

const editForm = ref({
    id: "",
    first_name: "",
    last_name: "",
    email: "",
    mobile: "",
    is_active: true,
    is_email_verified: false,
});

const columns = [
    { label: "User", key: "name", type: "slot", slot: "user", align: "left" },
    { label: "Role", type: "slot", slot: "role", align: "left" },
    { label: "Hospital", type: "slot", slot: "hospital", align: "left" },
    { label: "Country", key: "country", align: "left" },
    { label: "Plan", key: "plan_name", type: "slot", slot: "plan", align: "left" },
    { label: "Verified", key: "is_email_verified", type: "slot", slot: "verified" },
    { label: "Created", key: "created_at", type: "slot", slot: "created", align: "left" },
    { label: "Status", key: "is_active", type: "slot", slot: "status" },
];

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const verifiedOptions = [
    { value: "", label: "All verification" },
    { value: "true", label: "Verified" },
    { value: "false", label: "Pending" },
];

const rows = computed(() => props.admins?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.admins?.total ?? rows.value.length;
    const from = props.admins?.from ?? (rows.value.length ? 1 : 0);
    const to = props.admins?.to ?? rows.value.length;

    if (!total) return "No users found";

    return `Showing ${from}-${to} of ${total} users`;
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
    router.get(route("superAdmin.userlist"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        keyword: "",
        role: "",
        status: "",
        verified: "",
        country: "",
        plan_id: "",
        date_from: "",
        date_to: "",
    };

    router.get(route("superAdmin.userlist"), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openViewModal = async (row) => {
    try {
        const { data } = await axios.get(route("superAdmin.userlist.show", row.id));
        selectedUser.value = data;
        isViewModalOpen.value = true;
    } catch (error) {
        const message = error?.response?.data?.message || "Failed to load user details.";
        window.toast?.(message, "error", 2500);
    }
};

const closeViewModal = () => {
    isViewModalOpen.value = false;
    selectedUser.value = null;
};

const openEditModal = (row) => {
    editForm.value = {
        id: row.id,
        first_name: row.first_name || "",
        last_name: row.last_name || "",
        email: row.email || "",
        mobile: row.mobile || "",
        is_active: !!row.is_active,
        is_email_verified: !!row.is_email_verified,
    };

    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
};

const saveEdit = async () => {
    if (isSaving.value) return;
    isSaving.value = true;

    try {
        await axios.put(route("superAdmin.userlist.update", editForm.value.id), editForm.value);
        window.toast?.("User updated successfully.", "success", 2000);
        closeEditModal();
        router.reload({ preserveScroll: true });
    } catch (error) {
        const firstError = Object.values(error?.response?.data?.errors || {})?.[0]?.[0];
        const message = firstError || error?.response?.data?.message || "Failed to update user.";
        window.toast?.(message, "error", 2500);
    } finally {
        isSaving.value = false;
    }
};

const deleteUser = (row) => {
    Swal.fire({
        title: "Delete this user?",
        text: "This user will be removed from the system.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        confirmButtonColor: "#dc2626",
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
            await axios.delete(route("superAdmin.userlist.destroy", row.id));
            window.toast?.("User deleted successfully.", "success", 2000);
            router.reload({ preserveScroll: true });
        } catch (error) {
            const message = error?.response?.data?.message || "Failed to delete user.";
            window.toast?.(message, "error", 2500);
        }
    });
};

const toggleStatus = async (row) => {
    const nextStatus = !row.is_active;

    try {
        await axios.post(route("superAdmin.userlist.toggle-status", row.id), {
            is_active: nextStatus,
        });

        row.is_active = nextStatus;
        row.status_label = nextStatus ? "Active" : "Inactive";
        window.toast?.(`User ${nextStatus ? "activated" : "deactivated"} successfully.`, "success", 1800);
    } catch (error) {
        const message = error?.response?.data?.message || "Failed to update user status.";
        window.toast?.(message, "error", 2500);
    }
};

const toggleVerification = async (row) => {
    const nextValue = !row.is_email_verified;

    try {
        await axios.post(route("superAdmin.userlist.toggle-verification", row.id), {
            is_email_verified: nextValue,
        });

        row.is_email_verified = nextValue;
        row.is_verified = nextValue;
        row.verified_label = nextValue ? "Verified" : "Pending";
        window.toast?.(`User ${nextValue ? "verified" : "marked as pending"} successfully.`, "success", 1800);
    } catch (error) {
        const message = error?.response?.data?.message || "Failed to update verification.";
        window.toast?.(message, "error", 2500);
    }
};

const metricCards = computed(() => [
    {
        label: "Total Users",
        value: props.metrics?.total_users ?? 0,
        tone: "tone-blue",
        icon: "bi bi-people-fill",
    },
    {
        label: "Active Users",
        value: props.metrics?.active_users ?? 0,
        tone: "tone-green",
        icon: "bi bi-check2-circle",
    },
    {
        label: "Inactive Users",
        value: props.metrics?.inactive_users ?? 0,
        tone: "tone-amber",
        icon: "bi bi-pause-circle",
    },
    {
        label: "Pending Verification",
        value: props.metrics?.pending_verification ?? 0,
        tone: "tone-slate",
        icon: "bi bi-envelope-exclamation",
    },
]);
</script>

<template>
    <AuthLayout title="User Management" description="Manage platform users">
        <div class="sadmin-user-list">

            <div class="border-0 shadow-sm filter-card">
                <div class="card-body">
                    <div class="filter-header">
                        <div>
                            <p class="filter-kicker">Filters</p>
                            <h3 class="filter-title">Refine the user list</h3>
                        </div>
                        <div v-if="hasActiveFilters" class="filter-tools">
                            <span class="filter-badge">
                                {{ activeFilterCount }} filter{{ activeFilterCount > 1 ? "s" : "" }} active
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearFilters">
                                <i class="bi bi-x-circle me-1"></i> Clear filters
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input
                                    v-model="filterForm.keyword"
                                    type="search"
                                    class="form-control border-start-0"
                                    placeholder="Search by name, email, mobile, or hospital"
                                    @keydown.enter.prevent="applyFilters"
                                />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Role</label>
                            <select v-model="filterForm.role" class="form-select" @change="applyFilters">
                                <option value="">All roles</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
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

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Verification</label>
                            <select v-model="filterForm.verified" class="form-select" @change="applyFilters">
                                <option v-for="option in verifiedOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Country</label>
                            <select v-model="filterForm.country" class="form-select" @change="applyFilters">
                                <option value="">All countries</option>
                                <option v-for="country in countries" :key="country.id || country.name" :value="country.name">
                                    {{ country.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Plan</label>
                            <select v-model="filterForm.plan_id" class="form-select" @change="applyFilters">
                                <option value="">All plans</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.title }}</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">From date</label>
                            <input v-model="filterForm.date_from" type="date" class="form-control" @change="applyFilters" />
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">To date</label>
                            <input v-model="filterForm.date_to" type="date" class="form-control" @change="applyFilters" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm table-shell">
                <div class="card-body p-0 p-md-3">
                    <Table :columns="columns" :data="admins" :search-show="false" table="users">
                        <template #user="{ row }">
                            <div class="user-cell">
                                <img :src="row.profile_photo_url || '/images/avatar.webp'" alt="avatar" class="user-avatar" />
                                <div>
                                    <div class="user-name">{{ row.name || 'Unknown user' }}</div>
                                    <div class="user-meta">{{ row.email || 'No email' }}</div>
                                    <div class="user-submeta">{{ row.mobile || 'No mobile' }}</div>
                                </div>
                            </div>
                        </template>

                        <template #role="{ row }">
                            <div class="d-flex flex-wrap gap-1 justify-content-start">
                                <span v-for="role in row.roles" :key="role.name" class="role-pill">
                                    {{ role.name }}
                                </span>
                                <span v-if="!row.roles?.length" class="text-muted small">No role</span>
                            </div>
                        </template>

                        <template #hospital="{ row }">
                            <div class="text-start">
                                <div class="fw-semibold text-dark">{{ row.hospital_name || 'No hospital' }}</div>
                                <div class="text-muted small">{{ row.city || 'No city' }}</div>
                            </div>
                        </template>

                        <template #plan="{ row }">
                            <span class="plan-pill">{{ row.plan_name || 'No plan' }}</span>
                        </template>

                        <template #verified="{ row }">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="status-pill" :class="row.is_verified ? 'status-pill--verified' : 'status-pill--pending'">
                                    {{ row.verified_label }}
                                </span>
                                <label class="ah-switch">
                                    <input type="checkbox" :checked="!!row.is_email_verified" @change="toggleVerification(row)" />
                                    <span class="ah-slider">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>
                            </div>
                        </template>

                        <template #created="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.created_label }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <span class="status-pill" :class="row.is_active ? 'status-pill--active' : 'status-pill--inactive'">
                                    {{ row.status_label }}
                                </span>
                                <label class="ah-switch">
                                    <input type="checkbox" :checked="!!row.is_active" @change="toggleStatus(row)" />
                                    <span class="ah-slider">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>
                            </div>
                        </template>

                        <template #actions="{ row }">
                            <div class="d-flex gap-2">
                                <button class="btn btn-light border" title="View user" @click="openViewModal(row)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-light border" title="Edit user" @click="openEditModal(row)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-light border text-danger" title="Delete user" @click="deleteUser(row)">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isViewModalOpen" title="User Details" @close="closeViewModal" size="lg">
            <div v-if="selectedUser" class="detail-grid">
                <div class="detail-item">
                    <label>Name</label>
                    <span>{{ selectedUser.name || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span>{{ selectedUser.email || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Mobile</label>
                    <span>{{ selectedUser.mobile || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Role</label>
                    <span>{{ selectedUser.roles?.join(', ') || 'User' }}</span>
                </div>
                <div class="detail-item">
                    <label>Status</label>
                    <span>{{ selectedUser.is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="detail-item">
                    <label>Verification</label>
                    <span>{{ selectedUser.is_email_verified ? 'Verified' : 'Pending' }}</span>
                </div>
                <div class="detail-item">
                    <label>Hospital</label>
                    <span>{{ selectedUser.hospital?.name || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Location</label>
                    <span>
                        {{
                            [
                                selectedUser.hospital?.city || selectedUser.address?.city,
                                selectedUser.hospital?.state || selectedUser.address?.state,
                                selectedUser.hospital?.country || selectedUser.address?.country,
                            ].filter(Boolean).join(', ') || 'N/A'
                        }}
                    </span>
                </div>
                <div class="detail-item">
                    <label>Plan</label>
                    <span>{{ selectedUser.subscription_plan || 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <label>Created</label>
                    <span>{{ selectedUser.created_at || 'N/A' }}</span>
                </div>
            </div>
        </Modal>

        <Modal :isOpen="isEditModalOpen" title="Edit User" @close="closeEditModal" size="lg">
            <form @submit.prevent="saveEdit" class="edit-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input v-model="editForm.first_name" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input v-model="editForm.last_name" type="text" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input v-model="editForm.email" type="email" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input v-model="editForm.mobile" type="text" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select v-model="editForm.is_active" class="form-select">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Verification</label>
                        <select v-model="editForm.is_email_verified" class="form-select">
                            <option :value="true">Verified</option>
                            <option :value="false">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" @click="closeEditModal" :disabled="isSaving">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="isSaving">
                        {{ isSaving ? 'Saving...' : 'Update User' }}
                    </button>
                </div>
            </form>
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.sadmin-user-list {
    display: grid;
    gap: 20px;
}

.list-hero {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    padding: 28px;
    border-radius: 24px;
    background: linear-gradient(135deg, #f8fcff 0%, #eef7ff 48%, #ffffff 100%);
    border: 1px solid rgba(18, 148, 234, 0.10);
}

.hero-kicker {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.hero-title {
    margin: 0 0 10px;
    font-size: 34px;
    font-weight: 700;
    color: #0f172a;
}

.hero-copy {
    margin: 0;
    color: #64748b;
}

.hero-copy--muted {
    margin-top: 8px;
}

.hero-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.metric-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 22px 24px;
    border-radius: 22px;
    background: #fff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.metric-label {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    margin: 0;
    color: #0f172a;
    font-size: 2rem;
    font-weight: 700;
}

.metric-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    border-radius: 18px;
    font-size: 1.35rem;
}

.tone-blue .metric-icon {
    background: #e0f2fe;
    color: #0369a1;
}

.tone-green .metric-icon {
    background: #dcfce7;
    color: #15803d;
}

.tone-amber .metric-icon {
    background: #ffedd5;
    color: #c2410c;
}

.tone-slate .metric-icon {
    background: #e2e8f0;
    color: #334155;
}

.filter-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}

.filter-kicker {
    margin: 0 0 6px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #1294ea;
}

.filter-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.filter-tools {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #eef2ff;
    color: #3730a3;
    font-size: 0.8rem;
    font-weight: 600;
}

.filter-card,
.table-shell {
    border-radius: 22px;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
}

.user-avatar {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
}

.user-name {
    font-weight: 600;
    color: #0f172a;
}

.user-meta {
    color: #475569;
    font-size: 0.85rem;
}

.user-submeta {
    color: #94a3b8;
    font-size: 0.78rem;
}

.plan-pill,
.role-pill,
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.38rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
}

.plan-pill {
    background: #eff6ff;
    color: #1d4ed8;
}

.role-pill {
    background: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
}

.status-pill--active {
    background: #dcfce7;
    color: #166534;
}

.status-pill--inactive {
    background: #f1f5f9;
    color: #475569;
}

.status-pill--verified {
    background: #ecfeff;
    color: #0f766e;
}

.status-pill--pending {
    background: #fff7ed;
    color: #c2410c;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.detail-item {
    padding: 14px 16px;
    border-radius: 16px;
    background: #f8fbff;
    border: 1px solid #e2e8f0;
}

.detail-item label {
    display: block;
    margin-bottom: 6px;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.detail-item span {
    color: #0f172a;
    font-weight: 500;
}

.edit-form .form-label {
    font-weight: 600;
    color: #475569;
}

@media (max-width: 991px) {
    .list-hero {
        flex-direction: column;
    }

    .filter-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero-actions {
        justify-content: flex-start;
    }

    .metrics-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>
