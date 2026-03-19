<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import HospitalTimeModal from "@/Pages/Modals/HospitalTimeModal.vue";
 
import { router, useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2/dist/sweetalert2.js";

import { ref } from "vue";

const props = defineProps({
    hospitalTime: Object,
    keyword: String,
});

// Modal state
const isModalOpen = ref(false);
const childComponentRef = ref(null);

const openAddModal = () => {
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.resetForm();
        }
    }, 100);
};

const openEditModal = (row) => {
    isModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 100);
};
 
 const removeRow = (row) => {
 	Swal.fire(
		confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
	).then((result) => {
		if (result.isConfirmed) {
			router.delete(route("admin.hospital-timing.destroy", row.id));
		}
	});
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleSubmit = () => {
    closeModal();
};


// Table columns
const columns = [
    { key: 'day_of_week', type: "slot", slot: "day_of_week", label: 'Day Of Week'},
    { key: 'time_zone', label: 'Time Zone' },
    // { key: 'default_open_time', label: 'Default Open Time'},
    // { key: 'default_close_time', label: 'Default Close Time' },
    { key: 'open_time', label: 'Open Time' },
    { key: 'close_time', label: 'Close Time' },
];

</script>

<template>
    <AuthLayout title="Schedule Setup" description="Manage your schedule setup" heading="Schedule Setup">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Schedule Setup List</h4>
                <button class="btn btn-primary" @click="openAddModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    Add New
                </button>
            </div>

            <!-- Table -->
            <Table :columns="columns" :data="hospitalTime" table="schedule_setup" :search="keyword">
                <template #day_of_week="{ row }">
                    <span>{{ row.day_of_week }}</span>
                
                </template>
                <template  #actions="{ row }">
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" @click="openEditModal(row)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger" @click="removeRow(row)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </template>

            </Table>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :isOpen="isModalOpen" @close="closeModal" title="Clinic Time Setup" size="xl">
            <HospitalTimeModal ref="childComponentRef" @close="closeModal" @submit="handleSubmit" />
        </Modal>
    </AuthLayout>
</template>
