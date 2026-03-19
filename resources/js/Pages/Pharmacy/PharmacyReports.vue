<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     reports: Object,
     keyword: String,
});

const tableColumns = [
    {
        key:"order_id",
        label: "Order ID",
    },
    {
        key: "patient_name",
        label: "Patient Name",
    },
    {
        key: "medications",
        label: "Medications",
    },
    {
        key: "total_amount",
        label: "Amount",
    },
    {
        key: "doctor_name",
        label: "Prescribed By",
    },
    {
        key: "dispensed_at",
        label: "Fulfilled Date",
        isDate: true,
    },
    {
        key: "status",
        label: "Status",
    },
];

const reportData = computed(() => {
    const list = props.reports?.data ?? props.reports ?? [];

    return list.map(report => ({
        ...report,
        order_id: '#ORD-' + (report.id ? report.id.substring(0, 6).toUpperCase() : 'N/A'),
        patient_name: report?.patient?.name ?? report?.patient?.user?.name ?? "N/A",
        doctor_name: report?.doctor?.name ?? report?.doctor?.user?.name ?? "N/A",
        medications: report?.medications ? (Array.isArray(report.medications) ? report.medications.length + ' items' : report.medications) : 'N/A',
        status: report?.formatted_status ?? report?.status ?? "Completed",
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: reportData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('pharmacy.pharmacies.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>

