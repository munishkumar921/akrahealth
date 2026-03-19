<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import axios from "axios";
import { ref, watch, onMounted } from "vue";
import Modal from "@/Components/Common/Modal.vue";
import AddHospital from "../../Modals/AddHospital.vue";

const props = defineProps({
    keyword: String,
    hospitals: Object,
});

const columns = [
    { label: "Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Phone", key: "phone" },
    { label: "City", key: "city" },
    { label: "Address", key: "street_address1" },
];

const isOpenModal = ref(false);
const selected = ref(null);
const loading = ref(false);
 

const viewDetails = async (row) => {
    loading.value = true;
    try {
        const { data } = await axios.get(route("admin.hospitals.show", row.id));
        selected.value = data;
        viewModalOpen.value = true;
    } finally {
        loading.value = false;
    }
};

const openAddModal = () => {
     isOpenModal.value = true;
};
 
const closeOpenModal = () => {
    isOpenModal.value = false;
 };

const buttons = [
    {
        label: "Add Practice",
        icon: "bi bi-plus-circle",
        function: openAddModal,
    },
];
</script>

<template>
    <AuthLayout title="Practice" description="Practice" heading="Practice">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center text-xl mb-0">Practice</h3>
            <ActionButtons :actionButtons="buttons" />
        </div>

        <Table :columns="columns" :data="hospitals" table="hospitals" :search="keyword">
            <template #actions="{ row }">
                <button class="icon-btn btn btn-primary" @click="viewDetails(row)">
                    <i class="bi bi-eye"></i>
                </button>
            </template>
        </Table>
        <Modal :isOpen="isOpenModal" :title="'Practice Details'" @close="closeOpenModal" size="xl">
            <AddHospital  @close="closeOpenModal" />
            </Modal>
    </AuthLayout>
</template>

<style scoped>
.icon-btn {
    padding: 9px 8px 6px 8px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
    transform: scale(0.97);
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 20px;
}

.modal-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 720px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
}

.close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #000;
    opacity: .5;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

.close:hover {
    opacity: 1;
    background-color: rgba(0, 0, 0, .1);
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    max-height: calc(90vh - 140px);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.form-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.form-control, .form-select {
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    padding: 0.5rem 0.75rem;
}

.form-control:focus, .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.invalid-feedback {
    font-size: 0.875rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}
</style>

