<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     orders: Object,
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
        key: "payment_status",
        label: "Payment Status",
    },
    {
        key: "doctor_name",
        label: "Prescribed By",
    },
    {
        key: "status",
        label: "Status",
    },
    {
        key: "created_at",
        label: "Order Date",
        isDate: true,
    },
];

const orderData = computed(() => {
    const list = props.orders?.data ?? props.orders ?? [];

    return list.map(order => ({
        ...order,
        order_id: '#ORD-' + (order.id ? order.id.substring(0, 6).toUpperCase() : 'N/A'),
        patient_name: order?.patient?.name ?? order?.patient?.user?.name ?? "N/A",
        doctor_name: order?.doctor?.name ?? order?.doctor?.user?.name ?? "N/A",
        medications: order?.medications ? (Array.isArray(order.medications) ? order.medications.length + ' items' : order.medications) : 'N/A',
        payment_status: order?.payment_status ?? "Pending",
        status: order?.formatted_status ?? order?.status ?? "Pending",
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: orderData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('pharmacy.pharmacies.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>

