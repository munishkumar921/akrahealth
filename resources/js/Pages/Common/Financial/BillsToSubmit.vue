<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import Table from "@/Components/Table/Table.vue";
import Tab from "./Tab.vue";

const props = defineProps({
    bills: Object,
    keyword: String,
    head_arr: Object,
    count: Number
});
const currentTab = ref("bills_to_submit");
const columns = [
    { label: 'Date', key: 'date_of_service' },
    { label: 'Patient Name', key: 'patient_name',  },
    { label: 'Status', key: 'encounter_signed' },
    { label: 'Chief Complaint', key: 'chief_complaint' },
    { label: 'Batch Type', key: 'batch_type' },
];

</script>

<template>
<AuthLayout2 title="Bills to Submit" description="View bills pending submission" heading="Bills to Submit">
        <div class="row">
            			<Tab :currentTab="currentTab"/>

            <div class="col-lg-9 card">
                 <Table :columns="columns" :search="keyword" :data="bills.data">
                    <template #actions="{ row }">

                        <!-- Add to Print Image Queue -->
                        <!-- <a :href="route('doctor.finance.financial_queue', ['Pend'])" class="btn fa-btn"
                            title="Add to Print Image Queue">
                            <i class="fa fa-plus-square fa-lg"></i>
                        </a> -->

                        <!-- Add to HCFA-1500 Queue -->
                        <!-- <a :href="route('doctor.finance.financial_queue', ['HCFA'])" class="btn fa-btn" title="Add to HCFA-1500 Queue">
                            <i class="fa fa-plus-circle fa-lg"></i>
                        </a> -->

                        <!-- Print Image -->
                        <!-- <a :href="route('doctor.printimage_single', { id: row.id })" class="btn fa-btn" title="Print Image">
                            <i class="fa fa-file-image-o fa-lg"></i>
                        </a> -->

                        <!-- Print Editable HCFA-1500 -->
                        <!-- <a :href="route('doctor.generate_hcfa', { type: 'n', id: row.id })" class="btn fa-btn"
                            title="Print Editable HCFA-1500">
                            <i class="fa fa-pencil fa-lg"></i>
                        </a> -->

                        <!-- Print HCFA-1500 -->
                        <!-- <a :href="route('doctor.generate_hcfa', { type: 'y', id: row.id })" class="btn fa-btn"
                            title="Print HCFA-1500">
                            <i class="fa fa-file-text-o fa-lg"></i>
                        </a> -->
                    </template>

                </Table>

            </div>
        </div>
    </AuthLayout2>
</template>
<style scoped>
.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    cursor: pointer;
    transition: .2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, .15);
}

.dot {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}

.dot-success {
    background: #28a745;
}

.dot-warning {
    background: #ffc107;
}

.dot-secondary {
    background: #ff7b29;
}

.dot-primary {
    background: #0d6efd;
}

.dot-dark {
    background: #2e2138;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
}
</style>
