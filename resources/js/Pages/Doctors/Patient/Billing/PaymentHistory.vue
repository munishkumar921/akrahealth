<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { router } from "@inertiajs/vue3";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { ref } from "vue";
import { type } from "jquery";

const props = defineProps({
    paymentHistory: Object,
    encounter: Object,
    search: String,

});
const form = useForm({
    id: props.paymentHistory.id || '',
    encounter_id: props.paymentHistory.encounter_id || props.encounter.id || '',
    patient_id: props.paymentHistory.patient_id || props.encounter.patient_id || '',
    hospital_id: props.paymentHistory.hospital_id || props.encounter.hospital_id || '',
    other_billing_id: props.paymentHistory.other_billing_id || '',
    payment_type: props.paymentHistory.payment_type || '',
    payment: props.paymentHistory.payment || '',
    dos_f: props.paymentHistory.dos_f || '',
});
const columns = [
    { label: 'Date', key: 'dos_f' },
    { label: 'Amount', key: 'payment' },
];
const buttons = [
    {
        label: "Add payment",
        function: () => openAddPaymentModal(),
        icon: "bi bi-plus-circle",
    },
    {
        label: "Back to Bills",
        function: () => window.history.back(),
        icon: "bi bi- arrow-left-circle",
    }
];
const editPaymentDetails = (row) => {
    form.id = row.id;
    form.encounter_id = row.encounter_id;
    form.patient_id = row.patient_id;
    form.hospital_id = row.hospital_id;
    form.other_billing_id = row.other_billing_id;
    form.payment_type = row.payment_type;
    form.payment = row.payment;
    form.dos_f = row.dos_f;
    openAddPaymentModal();
};
const isOpenModal = ref(false);

const openAddPaymentModal = () => {
    isOpenModal.value = true;

};
const closeAddPaymentModal = () => {
    isOpenModal.value = false;
};
const deletePayment = (id) => {
    // Logic to delete payment
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.billing_payment_delete', id), {
                    preserveScroll: true,
                });
            }

        });

    console.log("Delete payment with ID:", id);
};
const addPayment = () => {
    form.post(route('doctor.billing_make_payment.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddPaymentModal();
            form.reset();
        },
    });
};

</script>
<template>
    <AuthLayout title="Payment History" description="Payment History for Patient" heading="">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="d-flex align-items-center">Payment History</h3>
            <ActionButtons :actionButtons="buttons" />
        </div>
        <Table :columns="columns" :data="{ data: paymentHistory }" :search="search">
            <template #actions="{ row }">
                <button class="btn btn-primary" @click="editPaymentDetails(row)"><i
                        class="bi bi-pencil-square"></i></button>
                <button class="btn btn-danger" @click="deletePayment(row.id)"><i class="bi bi-trash"></i></button>
            </template>
        </Table>
        <Modal :isOpen="isOpenModal" @close="closeAddPaymentModal" title="Add Payment" size="xl">
            <!-- Modal content for adding payment goes here -->
            <form @submit.prevent="addPayment" novalidate>
                <!-- Form fields for payment details -->
                <div class="mb-3">
                    <label for="paymentAmount" class="form-label">Payment Amount</label>
                    <BaseInput type="number" v-model="form.payment" id="paymentAmount"
                        placeholder="Enter payment amount" required />

                </div>
                <div class="mb-3">
                    <label for="paymentDate" class="form-label">Payment Date</label>
                    <BaseDatePicker v-model="form.dos_f" id="paymentDate" placeholder="Select payment date" required />
                </div>
                <div class="mb-3">
                    <label for="paymentMethod" class="form-label">Payment Type</label>
                    <BaseInput type="text" v-model="form.payment_type" id="paymentMethod"
                        placeholder="Enter payment method" required />
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-primary" @click="addPayment()">
                        Save
                    </button>
                    <button type="button" class="btn btn-danger" @click="closeAddPaymentModal">
                        Cancel
                    </button>
                </div>
            </form>
        </Modal>

    </AuthLayout>
</template>