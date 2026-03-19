<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     prescriptions: Object,
     keyword: String,
     status: String,
});

const tableColumns = [
    {
        key: "id",
        label: "ID",
    },
    {
        key: "patient_name",
        label: "Patient Name",
    },
    {
        key: "medication",
        label: "Medication",
    },
    {
        key: "dosage",
        label: "Dosage",
    },
    {
        key: "quantity",
        label: "Quantity",
    },
    {
        key: "frequency",
        label: "Frequency",
    },
    {
        key: "instructions",
        label: "Instructions",
    },
    {
        key: "prescription",
        label: "Status",
    },
    {
        key: "created_at",
        label: "Date",
        isDate: true,
    },
    {
        key: "doctor_name",
        label: "Prescribed By",
    },
];

const prescriptionData = computed(() => {
    const list = props.prescriptions?.data ?? props.prescriptions ?? [];

    return list.map(prescription => ({
        ...prescription,
        patient_name: prescription.patient?.name ?? 'N/A',
        doctor_name: prescription.doctor?.user?.name ?? prescription.doctor?.name ?? 'N/A',
        prescription: prescription.prescription === 'pending' ? 'Pending' : 
                      prescription.prescription === 'completed' ? 'Completed' : 
                      prescription.prescription ?? 'N/A',
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: prescriptionData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('pharmacy.prescriptions.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>
