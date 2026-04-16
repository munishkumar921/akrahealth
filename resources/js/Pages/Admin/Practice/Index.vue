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
