<script setup>
import { ref } from "vue";
import DataTable from "./Partials/DataTable.vue";
import TabSelector from "./Partials/TabSelector.vue";
import Search from "../Common/Search.vue";
import ActionButtons from "./Partials/ActionButtons.vue";

const props = defineProps({
    name: String,
    headings: {
        type: Array,
        required: true,
    },
    showHeading: {
        type: Boolean,
        default: true,
    },
    showFilters: {
        type: Boolean,
        default: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
    showSearch: {
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
        <!-- <main class="p-3 rounded container-fluid">
            <TableHeader :name="name" />
        </main> -->

        <section class="rounded table container-fluid pt-2">
            <div class="d-flex justify-content-between divider pb-2">
                <div class="d-flex align-items-center gap-3 pl-3">
                    <h3 class="d-flex align-items-center pt-2 fs-20">
                        {{ name }}
                    </h3>
                    <Search v-model="searchTerm" v-if="showSearch" />
                </div>
                <div class="d-flex align-items-center gap-3">
                    <aside>
                        <template v-if="$slots.filters">
                            <slot name="filters"></slot>
                        </template>
                        <template v-else>
                            <TabSelector
                                v-if="showFilters"
                                :tabs="tabs"
                                :currentTab="currentTab"
                                @update:currentTab="
                                    $emit('update:currentTab', $event)
                                "
                                :actionButtons="actionButtons"
                            />
                            <ActionButtons
                                v-else-if="actionButtons"
                                :actionButtons="actionButtons"
                            />
                        </template>
                    </aside>
                </div>
            </div>
            <div class="mt-1">
                <DataTable
                    :title="name"
                    :showHeading="showHeading"
                    :currentTab="props.currentTab"
                    :headings="headings"
                    :tableData="tableData"
                    :showActions="showActions"
                >
                    <template #actions>
                        <div class="actions-col">
                            <slot name="actions"></slot>
                        </div>
                    </template>
                </DataTable>
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
.table {
	min-height: 50vh;
	height: auto;
}
.actions-col {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    vertical-align: middle;
}
:deep(thead th) {
  text-transform: capitalize !important; /* e.g., 'FIRST NAME' -> 'First Name' */
  letter-spacing: .2px;
}
</style>
