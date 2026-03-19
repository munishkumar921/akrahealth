<script setup>
import { computed, ref } from "vue";
import Search from "../../../../Components/Common/Search.vue";
import TabSelector from "../../../../Components/Table/Partials/TabSelector.vue";
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
    tabs: {
        type: Array,
        default: () => [{ value: "", label: "" }],
    },
    actionButtons: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { label: "Day", key: "day"},
    { label: "Start Time", key: "start_time" },
    { label: "End Time", key: "end_time"},
    { label: "Title", key: "title"},
    { label: "Reason", key: "reason"},
    { label: "Provider", key: "provider"},
]

const searchTerm = ref("");

const filteredTableData = computed(() => {
    return props.tableData.filter((data) => data.provider === props.currentTab);
});
</script>

<template>
    <div>
        <section>
            <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center gap-3 pl-3">
                    <h3 class="d-flex align-items-center pt-2">
                        {{ name }}
                    </h3>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <aside class="">
                        <TabSelector
                            v-if="showFilters"
                            :tabs="tabs"
                            :currentTab="currentTab"
                            @update:currentTab="
                                $emit('update:currentTab', $event)
                            "
                            :actionButtons="actionButtons"
                            dropdownHieght="157px"
                        />
                    </aside>
                </div>
            </div>

            <!-- <pre>{{  filteredTableData }}</pre> -->

            <Table :columns="columns" :data="filteredTableData">
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
