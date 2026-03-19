<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     tests: Object,
     keyword: String,
});

const tableColumns = [
    {
        key:"test_name",
        label: "Test Name",
    },
    {
        key: "loinc_code",
        label: "LOINC Code",
    },
    {
        key: "price",
        label: "Price",
    },
    {
        key: "created_at",
        label: "Created Date",
        isDate: true,
    },
    {
        key: "status",
        label: "Status",
    },
];

const testData = computed(() => {
    const list = props.tests?.data ?? props.tests ?? [];

    return list.map(test => ({
        ...test,
        status: test?.is_active ? "Active" : "Inactive",
    }));
});
</script>
<template>
 <AuthLayout >
     <Table :columns="tableColumns" :data="{ data: testData }" :search="keyword" class="mt-4">
     </Table>
</AuthLayout>
</template>

