<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    supplements: Object,
    keyword: String,
});

const columns = [
    { label: "Supplement", key: "supplement" },
    { label: "Dosage", key: "dosage" },
    { label: "Dosage Unit", key: "dosage_unit" },
    { label: "Sig", key: "sig" },
    { label: "Route", key: "route" },
    { label: "Frequency", key: "frequency" },
    { label: "Instructions", key: "instructions" },
    { label: "Reason", key: "reason" },
    { label: "Date Active", key: "date_active" },
];

</script>

<template>
    <AuthLayout title="Supplements" description="Supplements" heading="Supplements">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Supplements</h3>
         </div>
        <Table :columns="columns" :data="supplements" :search="keyword" class="mt-4">
        </Table>

    </AuthLayout>
</template>
