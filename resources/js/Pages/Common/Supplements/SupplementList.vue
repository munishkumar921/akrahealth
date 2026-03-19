<script setup>
import { ref } from "vue";
import Search from "../../../Components/Common/Search.vue";
import TabSelector from "../../../Components/Table/Partials/TabSelector.vue";
import Listable from "../../../Components/Common/Listable.vue";

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
        type: Object,
        required: true,
    },
    currentTab: String,
    tabs: {
        type: Object,
        default: { value: "", label: "" },
    },
    actionButtons: {
        type: Array,
        default: () => [],
    },
});

const searchTerm = ref("");
</script>

<template>
    <div>
        <section class="rounded table container-fluid pt-4">
            <div class="d-flex justify-content-between divider pb-4">
                <div class="d-flex align-items-center gap-3 pl-3">
                    <h3 class="d-flex align-items-center pt-2">
                        {{ name }}
                    </h3>
                    <Search v-model="searchTerm" />
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
                        />
                    </aside>
                </div>
            </div>
            <div class="list-options">
                <div class="mt-3" v-for="data in tableData" :key="data">
                    <Listable>
                        <template #field>
                            <div class="d-flex flex-column">
                                <div class="font-weight-bold mb-2">
                                    {{ data.medication }}
                                </div>
                                <div class="row mb-2">
                                    <div class="col-auto">
                                        <p class="mb-0">
                                            <strong>Supplement:</strong>
                                            {{ data.description }}
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-auto">
                                        <p class="mb-0">
                                            <strong>Quantity:</strong>
                                            {{ data.quantity }}
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <p class="mb-0">
                                            <strong>Date Purchased:</strong>
                                            {{ data.purchase_date }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template #actions>
                            <div
                                class="d-flex gap-1 flex-wrap border-left border-primary pl-3"
                            >
                                <button
                                    class="btn btn-primary"
                                    data-tooltip="Edit"
                                    data-tooltip-location="bottom"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button
                                    class="btn btn-primary"
                                    data-tooltip="Inactivate"
                                    data-tooltip-location="bottom"
                                >
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                                <button
                                    class="btn btn-primary"
                                    data-tooltip="Delete"
                                    data-tooltip-location="bottom"
                                >
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </template>
                    </Listable>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
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
