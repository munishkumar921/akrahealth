<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import AddUserModal from "./AddUserModal.vue";
import EditUserModal from "./EditUserModal.vue";
import { type } from "jquery";

const currentTab = ref("all");
const isAddUserModalOpen = ref(false);
const isEditUserModalOpen = ref(false);
const editUserModalRef = ref(null);

const props = defineProps({
    users: Object,
    Keyword: String,
    countries: Array,
    specialities: Array,
    states: Array,
    hospitalId: Number,
    branches: Array,
});


const tabs = [
    { value: "all", label: "All" },
    { value: "doctor", label: "Doctors" },
    { value: "biller", label: "Billers" },
    { value: "virtual assistant", label: "Virtual Assistants" },
    { value: "is_active", label: "Active" },
    { value: "is_inactive", label: "Inactive" },
];


const columns = [
    { label: "Name", key: "name", type: "slot", slot: "name" },
    { label: "Branch", key: "doctor.hospital.name", type: "slot", slot: "branch_name" },
    { label: "Type", key: "role_name" },
    { label: "Email", key: "email",type: "email" ,slot: "email"},
     { label: "Status", key: "is_active", type: "toggle" },
];

const computedData = computed(() => {
    const users = Array.isArray(props.users)
        ? props.users
        : props.users?.data ?? [];

    let filtered = [...users];

    // 🔹 Role filter
    if (currentTab.value !== "all" && currentTab.value !== "is_active" && currentTab.value !== "is_inactive") {
        filtered = filtered.filter(user =>
            user.roles?.some(
                role => role.name.toLowerCase() === currentTab.value
            )
        );
    }

    // 🔹 Status filter
    if (currentTab.value === "is_active") {
        filtered = filtered.filter(user => user.is_active === true);
    }

    if (currentTab.value === "is_inactive") {
        filtered = filtered.filter(user => user.is_active === false);
    }

    const allowedRoles = ['Doctor', 'Virtual Assistant', 'Biller'];

    return filtered.map(user => ({
        ...user,

        // ✅ show only allowed roles
        role_name: user.roles
            ?.filter(r => allowedRoles.includes(r.name))
            .map(r => r.name)
            .join(', ') || 'Doctor',

        is_active: user.is_active ? 'Active' : 'Inactive',
    }));
});



const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
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

// Computed property for modal title
const modalTitle = computed(() => "Edit User");
</script>

<template>
    <AuthLayout title="Users" description="Users" heading="Users">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Users</h3>
            <div class="d-flex">
                <TabSelector :tabs="tabs" :currentTab="currentTab" @update:currentTab="updateCurrentTab"
                    data-tooltip="User Type" data-tooltip-location="top" />

                <ActionButtons :actionButtons="buttons" />
            </div>
        </div>
        <Table :columns="columns" :data="{ data: computedData }" :search="Keyword">
            <template #name="{ row }">
                <span v-if="row.doctor?.name">{{ row.doctor?.name }}</span>
                <span v-else>{{ row.name }}</span>
            </template>
            <template #email="{ row }">
                <span v-if="row.doctor?.email">{{ row.doctor?.email }}</span>
                <span v-else>{{ row.email }}</span>
            </template>
            <template #branch_name="{ row }">
                <span v-if="row.doctor?.hospital || row.hospital?.name">
                    {{ row.doctor?.hospital?.name || row.hospital?.name }}
                    <span class="text-muted">({{ (row.doctor?.hospital || row.hospital).main_branch_id === null ? 'Main'
                        : 'Sub' }})</span>
                </span>
                <span v-else>N/A</span>
            </template>

            <template #actions="{ row }">
                <div class="d-flex gap-1">
                    <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Edit"
                        @click="editUser(row)">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete"
                        @click="deleteUser(row)">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </template>
        </Table>
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
