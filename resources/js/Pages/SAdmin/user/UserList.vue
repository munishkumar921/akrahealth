<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed, onMounted } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import Modal from "@/Components/Common/Modal.vue";
import AddHospital from "@/Pages/Modals/AddHospital.vue";
import axios from 'axios';
 
const props = defineProps({
    request: String,
    states:String,
    countries:String,
    admins: Object,
    keyword: String,
 });
 

const columns = [
    { label: "Profile Picture", key: "profile_photo_url", type: "image" },
    { label: "Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Mobile", key: "mobile" },
    { label: "Role", key: "roles", type: "roles" },
    { label: "Verified", key: "is_verified", formatter: (value) => Number(value) === 1 ? "Yes" : "No" },
     { label: "Created At", key: "created_at" },
    { label: "Status", key: "is_active", type: "toggle" },
];

const removeRow = (row) => {
    Swal.fire({
        title: 'Are you sure to delete this data?',
        text: 'You will not be able to recover this!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            // Remove from local sample data for demo purposes
            const idx = sampleUsers.value.findIndex(u => u.id === row.id)
            if (idx !== -1) sampleUsers.value.splice(idx, 1)
            Swal.fire('Deleted!', 'The user record has been deleted (demo).', 'success')
        }
    })
};

const isAddModalOpen=ref(false);
const goToAddEncounter = () => {
    isAddModalOpen.value=true;

  };

const buttons = [
    {
        label: "Add Users",
        function: goToAddEncounter,
        icon: "bi bi-plus-circle",
    },
];

const filterActive = ref(usePage().props?.request?.filterActive ?? false);
const filterInactive = ref(usePage().props?.request?.filterInactive ?? false);

// top-level search term (keeps in sync with server request param)
const searchTerm = ref(usePage().props?.request?.search ?? '');

const byStatus = () => {
    router.get(route('superAdmin.userlist'), {
        search: searchTerm.value,
        filterActive: filterActive.value,
        filterInactive: filterInactive.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const tableDataProp = computed(() => {
    if (props.admins) return props.admins;

    // Start with full sample list
    let list = sampleUsers.value.slice();

    // Apply active/inactive filters
    if (filterActive.value && !filterInactive.value) {
        // Only Active checked
        list = list.filter(u => !!u.is_active);
    } else if (!filterActive.value && filterInactive.value) {
        // Only Inactive checked
        list = list.filter(u => !u.is_active);
    }
    return { data: list, links: [] };
});

// Modal / edit state for opening a popup and updating client-side data
const showModal = ref(false);
const selectedUser = ref(null);

const openRowModal = ({ row }) => {
    // copy to avoid mutating original directly
    selectedUser.value = { ...row };
    showModal.value = true;
};

 const closeAddModal=()=>{
    isAddModalOpen.value=false;
 }
</script>

<template>
    <AuthLayout title="Admins" description="Admins" heading="Admins">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="d-flex align-items-center text-xl mb-0">User List</h3>

            <div class="d-flex align-items-center gap-3">

                <div class="d-flex align-items-center gap-3">
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input id="flt-active" type="checkbox" @change="byStatus()"
                            class="status-check status-check--green" v-model="filterActive" />
                        <label class="form-check-label" for="flt-active">Active</label>
                    </div>
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input id="flt-inactive" type="checkbox" @change="byStatus()"
                            class="status-check status-check--grey" v-model="filterInactive" />
                        <label class="form-check-label" for="flt-inactive">Inactive</label>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    
                    <ActionButtons :actionButtons="buttons" />
                </div>
            </div>
        </div>

        <Table @cell-click="openRowModal" :columns="columns" :data="tableDataProp" table="users" :search="props.keyword" >
            <template #actions="{ row }">
                <div class="d-flex gap-2">
                    <button class="icon-btn btn btn-primary" @click.prevent="openRowModal({ row })">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </template>
        </Table>
       <Modal :isOpen="isAddModalOpen" title="Clinic Details" size="xl" @close="closeAddModal">
        <AddHospital
            :states="states"
            :countries="countries"
            @close="closeAddModal"
        />
</Modal>
    </AuthLayout>
</template>

 
