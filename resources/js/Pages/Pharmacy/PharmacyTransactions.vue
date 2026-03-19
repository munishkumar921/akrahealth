<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     transactions: Object,
     keyword: String,
     payment_type: String,
});

const tableColumns = [
    {
        key: "id",
        label: "Transaction ID",
    },
    {
        key: "lab_order_id",
        label: "Lab Order ID",
    },
    {
        key: "pharmacy_order_id",
        label: "Pharmacy Order ID",
    },
    {
        key: "payment_type",
        label: "Payment Type",
    },
    {
        key: "amount",
        label: "Amount",
    },
    {
        key: "status",
        label: "Status",
    },
    {
        key: "created_at",
        label: "Date",
        isDate: true,
    },
    {
        key: "patient_name",
        label: "Patient",
    },
];

const paymentTypeOptions = [
    { value: '', label: 'All' },
    { value: 'cash', label: 'Cash' },
    { value: 'card', label: 'Card' },
    { value: 'upi', label: 'UPI' },
    { value: 'insurance', label: 'Insurance' },
    { value: 'online', label: 'Online' },
];

const transactionData = computed(() => {
    const list = props.transactions?.data ?? props.transactions ?? [];

    return list.map(transaction => ({
        ...transaction,
        patient_name: transaction.patient?.name ?? 'N/A',
        lab_order_id: transaction.lab_order_id ? transaction.lab_order_id.substring(0, 8) + '...' : '-',
        pharmacy_order_id: transaction.pharmacy_order_id ? transaction.pharmacy_order_id.substring(0, 8) + '...' : '-',
        amount: transaction.amount ? `$${parseFloat(transaction.amount).toFixed(2)}` : '$0.00',
        status: transaction.status === 'completed' ? 'Completed' : 
                transaction.status === 'pending' ? 'Pending' : 
                transaction.status === 'failed' ? 'Failed' : 
                transaction.status === 'refunded' ? 'Refunded' : 
                transaction.status ?? 'N/A',
        payment_type: transaction.payment_type ? transaction.payment_type.charAt(0).toUpperCase() + transaction.payment_type.slice(1) : 'N/A',
    }));
});
</script>
<template>
 <AuthLayout >
     <div class="mb-4">
         <h2 class="text-2xl font-bold">Transactions</h2>
         <p class="text-gray-600">View and manage lab and pharmacy order transactions</p>
     </div>
     
     <Table :columns="tableColumns" :data="{ data: transactionData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('pharmacy.transactions.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>
