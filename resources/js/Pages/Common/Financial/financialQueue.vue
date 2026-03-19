<script setup>
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
 import {onMounted} from "vue";
import { router } from "@inertiajs/vue3";

import Tab from "./Tab.vue";

const props = defineProps({
    data: Object,

});
const columns = [
    { label: 'Id', key: 'id' },
    { label: 'Date of Service	', key: 'dos_f' },
    { label: 'Patient Name', key: 'patient_name', },
    { label: 'Amount', key: 'amount' },
    { label: 'Amount Type', key: 'amount_type' },



];
const buttons = [
    {
        label: "Back",
        function: () => window.history.back(),
        icon: "bi bi-arrow-left",
    }
];

const sortTable = (column) => {
    if (sortedColumn.value === column) {
        sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    } else {
        sortedColumn.value = column;
        sortOrder.value = "asc";
    }
};

</script>
<template>

    <AuthLayout2 title="Financial" description="Financial description" heading="">
        <div class="row">

            <Tab :currentTab="currentTab" />

            <div class="col-lg-9 card">
                <div class="m-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="d-flex align-items-center">Financial</h3>
                        <ActionButtons :actionButtons="buttons" />
                    </div>

                    <!-- <Table :columns="columns" :data="{data:data}" :searchShow="false" /> -->
                    <div class="table-responsive">

                        <table class="table mb-4 table-borderless">
                            <thead>
                                <tr>
                                    <th v-for="(column, index) in columns" :key="index" @click="sortTable(column.key)"
                                        :class="index != 0 ? 'text-center' : ''">

                                        <!-- Custom Header Slot -->
                                        <slot v-if="column.headerSlot" :name="`header-${column.headerSlot}`"></slot>

                                        <!-- Default Header -->
                                        <template v-else>
                                            {{ column.label }}
                                            <span v-if="sortedColumn === column.key">
                                                <i :class="`bi bi-chevron-${sortOrder === 'asc' ? 'up' : 'down'}`"></i>
                                            </span>
                                        </template>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- Table Rows -->
                                <tr v-for="(row, rowIndex) in data" :key="rowIndex">
                                    <td v-for="(column, colIndex) in columns" :key="colIndex"
                                        :class="colIndex != 0 ? 'text-center' : ''" style="padding-top:13px">
                                        {{ row[column.key] }} <!-- Correct display -->
                                    </td>
                                </tr>

                                <!-- No Data -->
                                <tr v-if="!data || data.length === 0">
                                    <td :colspan="columns.length" class="text-center">No records found.</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </AuthLayout2>
</template>