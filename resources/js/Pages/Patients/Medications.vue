<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
 
const props = defineProps({
    medications: Object,
    keyword: String,
});

const columns = [
    { label: "Medication", key: "medication" },
    { label: "Date Active", key: "date_active" },
    { label: "Date Inactive", key: "date_inactive" },
    { label:"Due Date", key:"due_date"},
    { label: "Prescription Status", key: "prescription" },
];
 
 
</script>

<template>
    <AuthLayout
        title="Medications"
        description="Medications"
        heading="Medications"
    >
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Medications</h3>
             
         </div>
           <Table :columns="columns" :search="keyword"
            :data="medications.data ? medications : { data: medications }">
        </Table>
    </AuthLayout>
</template>
