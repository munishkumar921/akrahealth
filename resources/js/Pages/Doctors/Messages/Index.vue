<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import TelephoneVisit from "@/Pages/Modals/TelephoneVisit.vue";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    keyword: String,
    messages: {
        type: Object,
        default: () => ({ data: [], links: {}, meta: {} })
    },
    patients: { type: Array, default: () => [] },
    doctors: { type: Array, default: () => [] },
 });

const columns = [
   { label:'date', key: 'date'},
    {label:'patient', key: 'patient.name'},
    {label:'doctor', key: 'doctor.name'},
    {label:'subject', key: 'subject'},
    {label:'message', key: 'message'},
    {label:'status', key: 'status'},
];

const openModal = ref(false);
const isEditMode = ref(false);
const selectedMessage = ref({});
 
const closeModal = () => {
    openModal.value = false;
    isEditMode.value = false;
    selectedMessage.value = {};
};

const openModalTelephoneVisit = () => {
     openModal.value = true;
};

const editMessage = (msg) => {
    selectedMessage.value = msg;
    openModalTelephoneVisit();
    isEditMode.value = true
};

const viewMessage = (msg) => {
    router.get(route('doctor.messages.show', msg.id));
};

const deleteMessage = (msg) => {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('doctor.messages.destroy', msg.id));
    }
};

const buttons = [
    {
        label: "Add Message",
        function: openModalTelephoneVisit,
        icon: "bi bi-plus-circle",
    },
];

const modalTitle = computed(() => isEditMode.value ? 'Edit Message' : 'New Message');
</script>

<template>
    <AuthLayout title="Messages" description="Manage patient messages" heading="Messages">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="mb-0">Messages</h3>
            <ActionButtons :actionButtons="buttons" />

            <Modal :isOpen="openModal" @close="closeModal" :title="modalTitle" :size="'lg'">
                <TelephoneVisit :patients="patients" :doctors="doctors" :row="selectedMessage" :isEdit="isEditMode"
                    @close="closeModal" />
            </Modal>
        </div>
         <!-- Table -->
        <Table :columns="columns" :data="messages" :search="keyword">
            <template #actions="{ row }">
                <button class="btn btn-primary" title="View" @click="viewMessage(row)">
                    <i class="ri-eye-line"></i>
                </button>
                <button class="btn btn-info" title="Edit" @click="editMessage(row)">
                    <i class="ri-pencil-line"></i>
                </button>
                <button class="btn btn-danger" title="Delete" @click="deleteMessage(row)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </template>
        </Table>
    </AuthLayout>
</template>

<style scoped>
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.pagination .page-link {
    color: #0d6efd;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
}

tbody tr.row-sent>td {
    background-color: #f0f9f0;
    /* light green */
}

.table-hover tbody tr.row-sent:hover>td {
    background-color: #e6f4e6;
    /* darker green on hover */
}
</style>