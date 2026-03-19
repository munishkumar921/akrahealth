<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
 
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    immunizations: Object,
    keyword: String,

});


const columns = [
    { label: "Immunization", key: "immunization" },
    { label: "Dosage", key: "dosage" },
    { label: "Dosage Unit", key: "dosage_unit" },
    { label: "Sequence", key: "sequence" },
    { label: "Route", key: "route" },
    { label: "Body Site", key: "body_site" },
    { label: "Manufacturer", key: "manufacturer" },
];

</script>

<template>
    <AuthLayout title="Immunizations" description="Immunizations" heading="Immunizations">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Immunizations</h3>
         </div>
        <Table :columns="columns" :data="immunizations" :search="keyword" class="mt-4"/>
     </AuthLayout>
</template>
