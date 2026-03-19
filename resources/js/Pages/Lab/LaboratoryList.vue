<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     labs: Object,
     keyword: String,
});

const tableColumns = [
    {
        key:"hospital_name",
        label: "practice Name",

    },
    {
        key: "patient_name",
        label: "Patient Name",
    },
    
    {
        key: "labs",
        label: "Labs Ordered",
    },
    {
        key: "created_at",
        label: "Order Date",
        isDate: true,
    },
    {
        key: "status",
        label: "Status",
    },
];
const labData = computed(() => {
    const list = props.labs?.data ?? props.labs ?? [];

    return list.map(lab => ({
        ...lab,
        patient_name: lab?.patient?.name ?? "N/A",
        hospital_name: lab?.doctor?.hospital?.name ?? "N/A",
        status: lab?.is_completed ? "Completed" : "Pending",
    }));
});

</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: labData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('lab.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>