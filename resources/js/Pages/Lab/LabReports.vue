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
        key:"patient_name",
        label: "Patient Name",
    },
    {
        key: "lab",
        label: "Lab Name",
    },
    {
        key: "ordered_by",
        label: "Ordered By",
    },
    {
        key: "created_at",
        label: "Report Date",
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
        patient_name: report?.patient?.name ?? "N/A",
        ordered_by: report?.doctor?.name ?? "N/A",
        status: report?.is_completed ? "Completed" : "Pending",
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: reportData }" :search="keyword" class="mt-4">

    <template #actions="{ row }">
        <button class="btn btn-primary" @click="router.visit(route('lab.reports.show', row.id))"><i class="bi bi-eye"></i></button>
    </template>
     </Table>
</AuthLayout>
</template>

