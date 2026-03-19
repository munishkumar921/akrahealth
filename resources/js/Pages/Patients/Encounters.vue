<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";

const props = defineProps({
    request: String,
    encounters: Object,
    keyword: String,
 });

const columns = [
    { label: "Date", key: "encounter_date_of_service" },
    { label: "Name", key: "patient.user.name" },
    { label: "Complaint", key: "chief_complaint" },
    { label: "Provider", key: "doctor.user.name" },
];
 
</script>

<template>
    <AuthLayout title="Encounters" description="Encounters" heading="Encounters">
        <div class="d-flex align-items-center justify-content-between pl-4">
            <h3 class="d-flex align-items-center">Encounters</h3>
         </div>
         <Table :columns="columns" :data="encounters" :search="keyword" class="mt-4">
            <template #actions="{ row }">
                <div class="d-flex gap-1 justify-content-center">
                     
                    <Link class="btn btn- iq-bg-success" :href="route('patient.encounters.show', row.id)" data-tooltip="View">
                        <i class="bi bi-eye"></i>
                    </Link>
                </div>
            </template>
        </Table>
    </AuthLayout>
</template>
