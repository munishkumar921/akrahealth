<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { computed } from 'vue';

const props = defineProps({
     pharmacies: Object,
     keyword: String,
});

const tableColumns = [
    {
        key:"practice_name",
        label: "practice Name",

    },
    {
        key: "patient_name",
        label: "Patient Name",
    },
    
    {
        key: "medication",
        label: "Medications Ordered",
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
const PahrmacyData = computed(() => {
    const list = props.pharmacies?.data ?? props.pharmacies ?? [];

    return list.map(Pharmacy => ({
        ...Pharmacy,
        patient_name: Pharmacy?.patient?.user?.name ?? "N/A",
        practice_name: Pharmacy?.doctor?.hospital?.name ?? "N/A",
        status: Pharmacy?.is_completed ? "Completed" : "Pending",
    }));
});

</script>
<template>
 <AuthLayout >
      <Table :columns="tableColumns" :data="{ data: PahrmacyData }" :search="keyword" class="mt-4"/>
</AuthLayout>
</template>