<script setup>
import { ref } from "vue";
import Search from "../../../../Components/Common/Search.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";

const props = defineProps({
    name: String,
    showFilters: {
        type: Boolean,
        default: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
    tableData: {
        type: Array,
        required: true,
    },
    currentTab: String,
    actionButtons: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { label: "Form Title", key:"formTitle"},
    { label: "Gender Association", key: "genderAssociation"},
    { label: "Age Association", key: "ageAssociation"}
];


const searchTerm = ref("");
</script>

<template>
    <div>
        <section>
            <div class="d-flex justify-content-between divider">
                <div class="d-flex align-items-center gap-3 pl-3">
                    <h3 class="d-flex align-items-center pt-2">
                        {{ name }}
                    </h3>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <aside class="">
                        <ActionButtons :actionButtons="actionButtons" />
                    </aside>
                </div>
            </div>


            <Table :columns="columns" :data="tableData">
            <template #actions>
                <div class="d-flex gap-1 justify-content-end">
                    <button
                        class="btn btn-warning"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Edit"
                        @click="editOrder(order)"
                    >
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                        class="btn btn-danger"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="View"
                        @click="completeOrder(order)"
                    >
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </template>
        </Table>
        </section>
    </div>
</template>