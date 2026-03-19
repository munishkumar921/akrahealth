<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, computed, reactive } from 'vue';
import { Link, useForm, router  } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
 
const props = defineProps({
    issues: Object,
    keyword: String,

});
const currentTab = ref("all"); // default to Problem
 
 const form = reactive({
  record_type: 'problem',
});

// ✅ Table columns
const tableColumns = [
     { label: "Issue", key: "issue" },
    { label: "Note", key: "notes" },
    { label: "Active Date", key: "date_active" },
    { label: "Inactive Date", key: "date_inactive" },
];
const currentData = computed(() => {
    let list = [];

    // Handle both paginated and array formats safely
    if (Array.isArray(props.issues)) {
        list = props.issues; // Direct array
    } else if (props.issues && Array.isArray(props.issues.data)) {
        list = props.issues.data; // Paginator object
    }

    // Select base data based on record_type
    switch (currentTab.value) {
        case "problem":
            return list.filter(item => item.type === 'Problem');
        case "past":
            return list.filter(item => item.type === 'MedicalHistory');
        case "surgery":
            return list.filter(item => item.type === 'SurgicalHistory');
        default:
            return list; // Return all if no type matches or list is empty
    }
});

const ConditionTabs = [
    { label: "All", value: "all" },
    { label: "Problems", value: "problem" },
    { label: "Past Medical History", value: "past" },
    { label: "Surgical History", value: "surgery" },
];
const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
    form.record_type = newTab;
};

</script>

<template>
    <AuthLayout title="Conditions" description="Conditions" heading="Conditions">
        <div class="d-flex gap-3 align-items-center justify-content-between mb-3">
             <h3 class="mb-0">Conditions</h3>

             <TabSelector class="mb--3"
                :tabs="ConditionTabs"
                :currentTab="currentTab"
                @update:currentTab="updateCurrentTab"
            />
        </div>

        <!-- TABLE -->
        <Table
            class="conditions-table"
            :columns="tableColumns"
            :data="currentData.data ? currentData : { data: currentData }"
            :search="keyword"
        >
            <template #actions="{ row }">

                <!-- Problem -->
                <template v-if="form.record_type === 'problem'">
                    <button
                        class="btn btn-info btn-sm me-2" bs-tooltip="Move to Problem"
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'MedicalHistory' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>

                    <button
                        class="btn btn-dark btn-sm" bs-tooltip="Move to Surgical History"
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'SurgicalHistory' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>
                </template>

                <!-- Past -->
                <template v-else-if="form.record_type === 'past'">
                    <button
                        class="btn btn-info btn-sm me-2" data-tooltip="Move to Problem" 
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'Problem' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>

                    <button
                        class="btn btn-dark btn-sm" data-tooltip="Move to Surgical History"
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'SurgicalHistory' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>
                </template>

                <!-- Surgery -->
                <template v-else>
                    <button
                        class="btn btn-info btn-sm me-2" data-tooltip="Move to Problem"
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'Problem' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>

                    <button
                        class="btn btn-dark btn-sm" data-tooltip="Move to Surgical History"
                        @click="router.get(route('patient.move.condition', { id: row.id, type: 'MedicalHistory' }))">
                        <i class="fa-solid fa-share"></i>
                    </button>
                </template>

            </template>
        </Table>

    </AuthLayout>
</template>
<style scoped>  
    /* =========================
   CONDITIONS – MOBILE UI
   ========================= */

@media (max-width: 768px) {

    /* Title */
    .conditions-title {
        text-align: left;
    }

    /* Tabs: 2 x 2 layout */
    .conditions-tabs .tab-selector {
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .conditions-tabs button {
        width: 100%;
        font-size: 13px;
        padding: 8px 6px;
        white-space: normal;
        line-height: 1.2;
    }

    /* Table header stack */
    .conditions-table .table-header {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Search row */
    .conditions-table .table-search {
        display: flex;
        gap: 6px;
    }

    .conditions-table .table-search input {
        flex: 1;
    }

    /* Page size + pagination */
    .conditions-table .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    /* Table text */
    .conditions-table table {
        font-size: 13px;
    }
    
}

</style>
