<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm, router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { ref, computed } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import SAdminRolesModal from "@/Pages/Modals/SAdminRoles.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";

const props = defineProps({
    keyword: String,
    roles: {
        type: Object,
        default: () => []
    }
});

// Modal State
const isModalOpen = ref(false);
const childComponentRef = ref(null);

// Action buttons
const buttons = [
    {
        label: "Add Role",
        icon: "bi bi-plus-lg",
        function: () => openAdd(),
    },
];

// Columns definition
const columns = [
    { label: "Name", key: "name" },
    { label: "Guard", key: "guard_name" },
    { label: "Created At", key: "created_at" },
    { label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const toggleStatus = (row) => {
    const form = useForm({
        is_active: !row.is_active,
    });

    form.post(route("superAdmin.api.roles.toggle", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Role status toggled successfully
        },
        onError: (err) => {
            console.error("Error toggling status:", err);
        },
    });
};

// Computed property to handle filtering based on current tab
const computedData = computed(() => {
    const roles = Array.isArray(props.roles)
        ? props.roles
        : props.roles?.data ?? [];

    let filtered = [...roles];

    // Filter by status
    if (currentTab.value === "is_active") {
        filtered = filtered.filter(role => role.is_active === 1);
    }

    if (currentTab.value === "is_inactive") {
        filtered = filtered.filter(role => role.is_active === 0);
    }

    return filtered;
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const currentTab = ref("is_active");

const tabs = computed(() => [
    {
        label: "Active",
        value: "is_active",
    },
    {
        label: "Inactive",
        value: "is_inactive",
    },
]);

const openAdd = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const openEdit = (row) => {
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            // Create a custom update method that uses SuperAdmin routes
            if (typeof childComponentRef.value.updateSuperAdmin === 'function') {
                childComponentRef.value.updateSuperAdmin(row);
            } else if (typeof childComponentRef.value.update === 'function') {
                childComponentRef.value.update(row);
            }
        }
    }, 100);
};

const removeRow = (row) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('superAdmin.api.roles.destroy', row.id), {
                onSuccess: () => {
                    Swal.fire('Deleted!', 'Role has been deleted.', 'success');
                },
                onError: (err) => {
                    Swal.fire('Error!', 'Failed to delete role.', 'error');
                }
            });
        }
    });
};
</script>

<template>
    <AuthLayout title="Roles & Permissions" description="Roles & Permissions" heading="Roles & Permissions">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <!-- Desktop View - Title and Controls in Same Row -->
            <div class="d-none d-md-flex align-items-center gap-3">
                <h3 class="text-xl mb-0">Roles & Permissions</h3>
            </div>
            <div class="d-flex gap-2">
                <TabSelector :tabs="tabs" :currentTab="currentTab" @update:currentTab="updateCurrentTab"
                    :actionButtons="buttons" />
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="table-responsive">
            <Table :columns="columns" :data="{ data: computedData}" :search="keyword">
                <template #status="{ row }">
                    <span :class="row.is_active ? 'text-success' : 'text-danger'">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
                <template #actions="{ row }">
                    <div class="d-flex gap-2">
                        <button class="icon-btn btn btn-primary" @click="openEdit(row)" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </template>
            </Table>
        </div>
        
        <Modal :isOpen="isModalOpen" @close="closeModal" title="Manage Role" size="lg">
            <SAdminRolesModal ref="childComponentRef" @close="closeModal" />
        </Modal>
    </AuthLayout>
</template>

