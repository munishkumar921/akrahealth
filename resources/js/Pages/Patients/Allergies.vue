<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { immunizationMockData } from "@/Data/MockData/immunizations";
import Table from "@/Components/Table/Table.vue";

const props = defineProps({
    allergies: Object,
    keyword: String,
   
});
 

const columns = [
    { label: "Substance or Medication", key: "allergies_medicine" },
    { label: "Reaction", key: "allergies_reaction" },
    { label: "Severity", key: "allergies_severity" },
    { label: "Notes", key: "notes" },
    { label: "Active Date", key: "date_active" },
 ];

</script>

<template>
    <AuthLayout
        title="Allergies"
        description="Allergies"
        heading="Allergies"
    >
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Allergies</h3>
        </div>
        <Table :columns="columns" :data="allergies" :search="keyword"/>
    </AuthLayout>
</template>
