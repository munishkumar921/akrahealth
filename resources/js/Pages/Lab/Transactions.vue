<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     transactions: Object,
     keyword: String,
});

const tableColumns = [
    {
        key:"transaction_id",
        label: "Transaction ID",
    },
    {
        key: "patient_name",
        label: "Patient Name",
    },
    {
        key: "lab",
        label: "Lab",
    },
    {
        key: "amount",
        label: "Amount",
    },
    {
        key: "payment_status",
        label: "Payment Status",
    },
    {
        key: "created_at",
        label: "Date",
        isDate: true,
    },
];

const transactionData = computed(() => {
    const list = props.transactions?.data ?? props.transactions ?? [];

    return list.map(transaction => ({
        ...transaction,
        patient_name: transaction?.patient?.name ?? "N/A",
        transaction_id: `TXN-${transaction.id}`,
        payment_status: transaction?.payment_status ?? "Pending",
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: transactionData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('lab.transactions.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>

