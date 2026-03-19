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
    { label: "Visit Type", key: "visit_type" },
    { label: "Duration", key: "duration" },
    { label: "Provider", key: "provider" },
    { label: "Status", key: "status" }
]

const searchTerm = ref("");

const filteredTableData = computed(() => {
    return props.tableData.filter((data) => data.status === props.currentTab);
});
</script>

<template>
    <div>
        <section class="rounded table container-fluid pt-4">
            <div class="d-flex justify-content-between divider pb-4">
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
    <!-- <pre>{{ filteredTableData }}</pre> -->
            <Table :columns="columns" :data="filteredTableData">
            <template #actions>
                <div class="d-flex gap-1 justify-content-end">
                    <button
                        class="btn btn-success"
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
                        title="Inactive"
                        @click="completeOrder(order)"
                    >
                        <i class="fa fa-minus-circle"></i>
                    </button>
                </div>
            </template>
        </Table>
        
            <!-- <div class="list-options">
                <div
                    class="mt-3 container-fluid"
                    v-for="data in filteredTableData"
                    :key="data"
                >
                    <div class="row border rounded align-items-center">
                        <div class="col-8">
                            <div class="d-flex gap-3">
                                <div
                                    :style="`background-color:${data.color}`"
                                    style="
                                        width: 20px;
                                        height: 20px;
                                        border-radius: 100%;
                                    "
                                ></div>
                            </div>
                            <div class="d-flex gap-3 align-items-center">
                                <strong>
                                    {{ data.visit_type }}
                                </strong>
                                <small>{{ data.duration }} minutes</small>
                            </div>
                            <div>
                                <strong>Provider: </strong>
                                <small>{{ data.provider }}</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-end">
                                <div
                                    class="d-flex border-left border-primary pl-3 gap-1 flex-wrap align-items-center"
                                >
                                    <button class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-primary">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </section>
    </div>
</template>

<style scoped>
.btn {
    position: relative;
    z-index: 0 !important;
}

.list-options {
    overflow: scroll;
    height: 100%;
}

.table {
    height: 40rem;
}

.divider {
    border-bottom: 2px solid rgba(0, 0, 0, 0.493);
}

.list-options {
    overflow: scroll;
    height: 100%;
}

.search-input {
    width: 40%;
    height: 40px;
}

.search-icon {
    margin-left: -29rem;
    margin-top: -40px;
}

@media (max-width: 990px) {
    .search-input {
        width: 100%;
    }

    .search-icon {
        position: absolute;
        right: 30px;
        top: 60px;
    }
}
</style>
