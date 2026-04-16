<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddUserModal from "./AddUserModal.vue";
import EditUserModal from "./EditUserModal.vue";
import axios from "axios";
import { route } from "ziggy-js";

const isAddUserModalOpen = ref(false);
const isEditUserModalOpen = ref(false);
const editUserModalRef = ref(null);

const props = defineProps({
    users: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    countries: Array,
    specialities: Array,
    states: Array,
    hospitalId: [Number, String],
    branches: {
        type: Array,
        default: () => [],
    },
});

const filterForm = ref({
    keyword: props.filters?.keyword || "",
    role: props.filters?.role || "",
    status: props.filters?.status ?? "",
    branch_id: props.filters?.branch_id || "",
    speciality: props.filters?.speciality || "",
});

const roleOptions = [
    { value: "", label: "All roles" },
    { value: "Doctor", label: "Doctors" },
    { value: "Virtual Assistant", label: "Virtual Assistants" },
    { value: "Biller", label: "Billers" },
];

const statusOptions = [
    { value: "", label: "All status" },
    { value: "true", label: "Active" },
    { value: "false", label: "Inactive" },
];

const columns = [
    { label: "User", key: "display_name", type: "slot", slot: "name" },
    { label: "Branch", key: "branch_name", type: "slot", slot: "branch" },
    { label: "Role", key: "role_name", type: "slot", slot: "role" },
    { label: "Speciality", key: "speciality_label", type: "slot", slot: "speciality" },
    { label: "Contact", key: "email", type: "slot", slot: "contact" },
    { label: "Status", key: "is_active", type: "slot", slot: "status" },
];

const rows = computed(() => props.users?.data ?? []);
const activeFilterCount = computed(() =>
    Object.values(filterForm.value).filter((value) => value !== null && value !== "").length
);
const hasActiveFilters = computed(() => activeFilterCount.value > 0);
const resultSummary = computed(() => {
    const total = props.users?.total ?? rows.value.length;
    const from = props.users?.from ?? (rows.value.length ? 1 : 0);
    const to = props.users?.to ?? rows.value.length;

    if (!total) {
        return "No users found";
    }

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
    router.get(route("admin.users.index"), buildQuery(), {
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
        branch_id: "",
        speciality: "",
    };

    router.get(route("admin.users.index"), buildQuery(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const openAddUserModal = () => {
    isAddUserModalOpen.value = true;
};

const closeAddUserModal = () => {
    isAddUserModalOpen.value = false;
};

const buttons = [
    {
        label: "Add User",
        function: openAddUserModal,
        icon: "bi bi-plus-circle",
    },
];

const editUser = (user) => {
    isEditUserModalOpen.value = true;
    setTimeout(() => {
        if (editUserModalRef.value) {
            editUserModalRef.value.update(user);
        }
    }, 50);
};

const deleteUser = (user) => {
    console.info("Delete user:", user);
};

const closeEditUserModal = () => {
    isEditUserModalOpen.value = false;
};

const toggleStatus = async (row) => {
    await axios.post(route("update.status"), {
        table: row.table || (row.roles?.length ? "users" : "doctors"),
        id: row.id,
        is_active: !row.is_active,
    });

    row.is_active = !row.is_active;
    row.status_label = row.is_active ? "Active" : "Inactive";
};

const badgeClass = (roleName) => {
    const palette = {
        Doctor: "badge-soft-primary",
        "Virtual Assistant": "badge-soft-info",
        Biller: "badge-soft-warning",
    };

    return palette[roleName] || "badge-soft-secondary";
};

const modalTitle = computed(() => "Edit User");
</script>

<template>
    <AuthLayout title="Users" description="Users" heading="Users">
        <div class="user-directory-page">
            <div class="users-toolbar card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="mb-1">Users</h3>
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
                            <ActionButtons :actionButtons="buttons" />
                        </div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-4">
                            <label class="form-label text-muted small text-uppercase mb-2">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 border col-1 rounded-circle-left">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input v-model="filterForm.keyword" type="search" class="form-control border-start-0"
                                    placeholder="Search by name, email, or mobile"
                                    @keydown.enter.prevent="applyFilters" />
                                <button type="button" class="btn btn-primary" @click="applyFilters">Search</button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Role</label>
                            <select v-model="filterForm.role" class="form-select" @change="applyFilters">
                                <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
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
                            <label class="form-label text-muted small text-uppercase mb-2">Branch</label>
                            <select v-model="filterForm.branch_id" class="form-select" @change="applyFilters">
                                <option value="">All branches</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-2">
                            <label class="form-label text-muted small text-uppercase mb-2">Speciality</label>
                            <select v-model="filterForm.speciality" class="form-select" @change="applyFilters">
                                <option value="">All specialities</option>
                                <option v-for="speciality in specialities" :key="speciality" :value="speciality">
                                    {{ speciality }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0 p-md-3">
                    <Table :columns="columns" :data="users" :search-show="false">
                        <template #name="{ row }">
                            <div class="d-flex align-items-center gap-3 text-start">
                                <img :src="row.profile_photo_url || '/images/avatar.webp'" alt="User avatar"
                                    class="user-avatar" />
                                <div>
                                    <div class="fw-semibold text-dark">{{ row.display_name || row.name }}</div>
                                    <div class="text-muted small">{{ row.created_label }}</div>
                                </div>
                            </div>
                        </template>

                        <template #branch="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.branch_name || "N/A" }}</div>
                                <div v-if="row.branch_type" class="text-muted small">{{ row.branch_type }}</div>
                            </div>
                        </template>

                        <template #role="{ row }">
                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                <span v-for="roleItem in (row.roles?.length ? row.roles : [{ name: row.primary_role }])"
                                    :key="roleItem.name" class="role-badge" :class="badgeClass(roleItem.name)">
                                    {{ roleItem.name }}
                                </span>
                            </div>
                        </template>

                        <template #speciality="{ row }">
                            <div class="text-start">
                                <span v-if="row.speciality_label">{{ row.speciality_label }}</span>
                                <span v-else class="text-muted">N/A</span>
                            </div>
                        </template>

                        <template #contact="{ row }">
                            <div class="text-start">
                                <div class="fw-medium text-dark">{{ row.email || "N/A" }}</div>
                                <div class="text-muted small">{{ row.mobile || "No mobile" }}</div>
                            </div>
                        </template>

                        <template #status="{ row }">
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span class="status-pill"
                                    :class="row.is_active ? 'status-pill--active' : 'status-pill--inactive'">
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
                                <button class="btn btn-light border" title="Edit user" @click="editUser(row)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-light border text-danger" title="Delete user"
                                    @click="deleteUser(row)">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>
            </div>
        </div>

        <Modal :isOpen="isAddUserModalOpen" title="Add User" @close="closeAddUserModal" size="xl">
            <AddUserModal @close="closeAddUserModal" :countries="countries" :specialities="specialities"
                :states="states" :hospitalId="hospitalId" :branches="branches" />
        </Modal>

        <Modal :isOpen="isEditUserModalOpen" :title="modalTitle" @close="closeEditUserModal" size="xl">
            <EditUserModal ref="editUserModalRef" @close="closeEditUserModal" :specialities="specialities"
                :hospitalId="hospitalId" :branches="branches" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.users-toolbar {
    border-radius: 20px;
}

.user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
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

.role-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-soft-primary {
    background: #e0f2fe;
    color: #075985;
}

.badge-soft-info {
    background: #ecfeff;
    color: #155e75;
}

.badge-soft-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-soft-secondary {
    background: #f1f5f9;
    color: #334155;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 74px;
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
</style>
