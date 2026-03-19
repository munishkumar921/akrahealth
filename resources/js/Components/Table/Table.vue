<script setup>
import { ref, computed, useSlots } from "vue";
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import axios from "axios";
import { route } from "ziggy-js";
import BaseInput from "../Common/Input/BaseInput.vue";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: [Array, Object],
        required: true,
    },
    search: {
        type: String,
        default: '',
    },
    itemsPerPageOptions: {
        type: Array,
        default: () => [10, 15, 25, 50, 100],
    },
    selectedRows: {
        type: Array,
        default: () => [],
    },
    isAllSelected: {
        type: Boolean,
        default: false,
    },
    table: {
        type: String,
        default: '',
    },
    searchShow: {
        type: Boolean,
        default: true,

    },
    PageOptions: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:selectedRows', 'toggle-select-all', 'cell-click']);

const slots = useSlots();
// Initialize from URL per_page parameter, fallback to first option
const urlParams = new URLSearchParams(window.location.search);
const initialPerPage = parseInt(urlParams.get('per_page'), 10) || props.itemsPerPageOptions[0];
const itemsPerPage = ref(initialPerPage);
const sortedColumn = ref(urlParams.get('sort') || null);
const sortOrder = ref(urlParams.get('direction') || "desc");

const updatePagination = () => {
    router.get(
        window.location.pathname,
        { ...Object.fromEntries(new URLSearchParams(window.location.search)), per_page: itemsPerPage.value },
        { preserveState: true, replace: true }
    );
};

const localSelectedRows = computed({
    get: () => props.selectedRows,
    set: (value) => {
        emit('update:selectedRows', value);
    }
});


const sortTable = (column, forcedDirection = null) => {
    if (!column) return;

    const direction =
        forcedDirection ??
        (sortedColumn.value === column && sortOrder.value === "asc"
            ? "desc"
            : "asc");

    sortedColumn.value = column;
    sortOrder.value = direction;

    router.get(
        window.location.pathname,
        {
            ...Object.fromEntries(new URLSearchParams(window.location.search)),
            sort: column,
            direction: direction,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};


const updateStatus = (row) => {
    const form = {
        table: props.table,
        id: row.id,
        is_active: !row.is_active
    };
    axios.post(route('update.status'), form).then(() => {
        toast("Status updated successfully.", "success", 2000);
        setTimeout(() => {
            router.visit(window.location.href);
        }, 500);
    });
};

const searchForm = useForm({
    search: props.search || '',
    filterActive: usePage().props?.request?.filterActive,
    filterInactive: usePage().props?.request?.filterInactive,
});

const search = ref(usePage()?.props?.request?.search ?? props?.search ?? '');
const onSearch = () => {
    searchForm.search = search.value;

    searchForm.get(route(route().current()));
};

const getValue = (row, key) => {

    if (!key) return null;
    // Handle simple keys (no dots)
    if (!key.includes('.')) {
        return row[key];
    }
    // Handle nested keys with dots
    return key.split('.').reduce((acc, part) => acc && acc[part], row);
};

const formatValue = (row, column) => {
    const value = getValue(row, column.key);
    if (column.formatter) {
        return column.formatter(value);
    }

    return value;
};

const fallbackImage = "/images/avatar.webp";

const resolveImageSrc = (value) => {
    if (!value) return fallbackImage;

    const src = String(value).trim();
    if (!src) return fallbackImage;

    if (src.startsWith("http://") || src.startsWith("https://") || src.startsWith("data:") || src.startsWith("blob:")) {
        return src;
    }

    if (src.startsWith("/")) return src;

    // Values like "storage/..." or "images/..." should map to public URLs.
    return `/${src}`;
};

const onImageError = (e) => {
    e.target.src = fallbackImage;
};

const normalizeSortValue = (value) => {
    if (value === null || value === undefined) return "";
    if (typeof value === "number") return value;
    if (typeof value === "boolean") return value ? 1 : 0;

    const asNumber = Number(value);
    if (!Number.isNaN(asNumber) && String(value).trim() !== "") {
        return asNumber;
    }

    const asDate = Date.parse(value);
    if (!Number.isNaN(asDate)) {
        return asDate;
    }

    return String(value).toLowerCase();
};

const rows = computed(() => (Array.isArray(props.data) ? props.data : props.data?.data ?? []));

const sortedRows = computed(() => {
    if (!sortedColumn.value) return rows.value;

    const sorted = [...rows.value];
    sorted.sort((a, b) => {
        const aVal = normalizeSortValue(getValue(a, sortedColumn.value));
        const bVal = normalizeSortValue(getValue(b, sortedColumn.value));

        if (aVal < bVal) return sortOrder.value === "asc" ? -1 : 1;
        if (aVal > bVal) return sortOrder.value === "asc" ? 1 : -1;
        return 0;
    });

    return sorted;
});

const getSortIconClass = (columnKey, direction) =>
    sortedColumn.value === columnKey && sortOrder.value === direction ? "text-white fw-bold" : "text-white";

const getSortUpIcon = (columnKey) =>
    sortedColumn.value === columnKey && sortOrder.value === "asc" ? "bi bi-caret-up-fill" : "bi bi-chevron-up";

const getSortDownIcon = (columnKey) =>
    sortedColumn.value === columnKey && sortOrder.value === "desc" ? "bi bi-caret-down-fill" : "bi bi-chevron-down";

</script>

<template>
    <div class="datatable-container">
        <div class="datatable-controls d-flex justify-content-between flex-wrap gap-2 ">
            <div class="d-flex gap-2 mb-0 align-items-baseline mb-4">
                <BaseInput v-model="search" type="search" @keydown.enter="onSearch" placeholder="Search..."
                    :class="searchShow == true ? '' : 'd-none'" />

                <button class="icon-btn btn btn-primary" type="button" @click="onSearch()" title="Search"
                    :class="searchShow == true ? '' : 'd-none'">Search</button>
                <!-- <span>
                    <Link v-if="search" class="icon-btn btn p-2 ml-1 btn-primary" :href="route(route().current())">
                    <i class="bi bi-trash"></i>
                    </Link>
                </span> -->
            </div>
            <div :class="PageOptions == true ? '' : 'd-none'" class="align-content-around mb-4">
                <select v-model="itemsPerPage" @change="updatePagination()" class="form-select form-select-sm">
                    <option v-for="option in itemsPerPageOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mb-4 table-borderless">
                <thead class="">
                    <tr>
                        <th v-for="(column, index) in columns" :key="index"
                            @click="column.key ? sortTable(column.key) : null"
                            :class="[index != 0 ? 'text-center' : '', column.key ? 'cursor-pointer' : '']">
                            <slot v-if="column.headerSlot" :name="`header-${column.headerSlot}`"></slot>
                            <template v-else>
                                {{ column.label }}
                                <div class="d-inline-flex flex-column ml-2 align-middle" style="line-height: .8;">
                                    <i class="bi bi-chevron-up"
                                        :class="sortedColumn === column.key && sortOrder === 'asc' ? 'text-primary' : 'text-white'"
                                        style="font-size: 0.8rem;"></i>
                                    <i class="bi bi-chevron-down"
                                        :class="sortedColumn === column.key && sortOrder === 'desc' ? 'text-primary' : 'text-white'"
                                        style="font-size: 0.8rem;"></i>
                                </div>
                            </template>
                        </th>
                        <th v-if="slots.actions" class="">Actions</th>

                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in (Array.isArray(data) ? data : data.data)" :key="index">
                        <td v-for="(column, index) in columns" :key="index" style="padding-top: 13px"
                            :class="index != 0 ? 'text-center' : ''" @click="$emit('cell-click', { row, column })">
                            <template v-if="column.type === 'checkbox'">
                                <input type="checkbox" :value="row.id" v-model="localSelectedRows" />
                            </template>
                            <template v-else-if="column.type === 'image'">
                                    <img
                                        :src="resolveImageSrc(getValue(row, column.key))"
                                        @error="onImageError"
                                        alt="avatar"
                                        style="width:36px;height:36px;border-radius:50%;object-fit:cover"
                                        />
                             </template>
                            <template v-else-if="column.type === 'boolean'">
                                <template v-if="row[column.key] === 1">
                                    <span class="badge">Yes</span>
                                </template>
                                <template v-else>
                                    <span class="badge">No</span>
                                </template>
                            </template>
                            <template v-else-if="column.type === 'toggle'">
                                <label class="ah-switch">
                                    <input type="checkbox" :checked="!!row[column.key]" @change="updateStatus(row)" />
                                    <span class="ah-slider">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>
                            </template>
                            <template v-else-if="column.type === 'status'">
                                <div class="badge badge-pill" :class="{
                                    'iq-bg-success': (getValue(row, column.key) || '').toLowerCase() === 'paid',
                                    'iq-bg-danger': (getValue(row, column.key) || '').toLowerCase() === 'past due',
                                    'iq-bg-warning': (getValue(row, column.key) || '').toLowerCase() === 'pending'
                                }">
                                    {{ getValue(row, column.key) ?? 'N/A' }}
                                </div>
                            </template>
                            <template v-else-if="column.type === 'roles'">
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="(role, idx) in getValue(row, column.key)" :key="idx"
                                        class="badge bg-primary">
                                        {{ role.name || role }}
                                    </span>
                                    <span v-if="!getValue(row, column.key)?.length">-</span>
                                </div>
                            </template>
                            <template v-else-if="column.type === 'badges'">
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="(item, idx) in getValue(row, column.key)" :key="idx"
                                        class="badge bg-info text-white">
                                        {{ item.name || item }}
                                    </span>
                                    <span v-if="!getValue(row, column.key)?.length">-</span>
                                </div>
                            </template>
                            <template v-else-if="column.type === 'slot'">
                                <slot :name="column.slot" :row="row"></slot>
                            </template>
                            <template v-else>

                                {{ formatValue(row, column) }}
                            </template>
                        </td>
                        <td v-if="slots.actions" >
                            <div class="d-flex gap-1 flex-nowrap">
                                <slot name="actions" :row="row"></slot>

                            </div>
                        </td>


                    </tr>
                    <tr v-if="sortedRows.length === 0">
                        <td :colspan="columns.length + (slots.actions ? 1 : 0)">
                            No records found.
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Mobile Card View -->
            <div class="mobile-table d-md-none">
                <div v-for="row in (Array.isArray(data) ? data : data.data)" :key="row.id" class="mobile-row-card">
                    <div v-for="column in columns" :key="column.key" class="mobile-row-item">
                        <span class="mobile-label">{{ column.label }}</span>
                        <span class="mobile-value">
                            <template v-if="column.type === 'checkbox'">
                                <input type="checkbox" :value="row.id" v-model="localSelectedRows" />
                            </template>
                            <template v-else-if="column.type === 'image'">
                                <img :src="resolveImageSrc(getValue(row, column.key))" @error="onImageError" alt=""
                                    style="width:36px;height:36px;border-radius:50%;object-fit:cover" />
                            </template>
                            <template v-else-if="column.type === 'boolean'">
                                <template v-if="row[column.key] === 1">
                                    <span class="badge">Yes</span>
                                </template>
                                <template v-else>
                                    <span class="badge">No</span>
                                </template>
                            </template>
                            <template v-else-if="column.type === 'toggle'">
                                <label class="ah-switch">
                                    <input type="checkbox" :checked="!!row[column.key]" @change="updateStatus(row)" />
                                    <span class="ah-slider">
                                        <i class="bi bi-check2"></i>
                                    </span>
                                </label>
                            </template>
                            <template v-else-if="column.type === 'status'">
                                <div class="badge badge-pill" :class="{
                                    'iq-bg-success': (getValue(row, column.key) || '').toLowerCase() === 'paid',
                                    'iq-bg-danger': (getValue(row, column.key) || '').toLowerCase() === 'past due',
                                    'iq-bg-warning': (getValue(row, column.key) || '').toLowerCase() === 'pending'
                                }">
                                    {{ getValue(row, column.key) ?? 'N/A' }}
                                </div>
                            </template>
                            <template v-else-if="column.type === 'roles'">
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="(role, idx) in getValue(row, column.key)" :key="idx"
                                        class="badge bg-primary">
                                        {{ role.name || role }}
                                    </span>
                                    <span v-if="!getValue(row, column.key)?.length">-</span>
                                </div>
                            </template>
                            <template v-else-if="column.type === 'badges'">
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="(item, idx) in getValue(row, column.key)" :key="idx"
                                        class="badge bg-info text-white">
                                        {{ item.name || item }}
                                    </span>
                                    <span v-if="!getValue(row, column.key)?.length">-</span>
                                </div>
                            </template>
                            <template v-else-if="column.type === 'slot'">
                                <slot :name="column.slot" :row="row"></slot>
                            </template>
                            <template v-else>
                                {{ formatValue(row, column) ?? '-' }}
                            </template>
                        </span>
                    </div>

                    <div v-if="slots.actions" class="mobile-actions">
                        <slot name="actions" :row="row"></slot>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end ">
                <nav v-if="props.data?.links?.length > 3">
                    <ul class="pagination">
                        <li v-for="link in props.data.links" :key="link.label" class="page-item"
                            :class="{ 'active': link.active, 'disabled': link.url === null }">
                            <Link :href="link.url" class="page-link">
                                <span v-html="link.label"></span>
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<style scoped>
.iq-bg-warning {
    color: #97973e !important;
}

.cursor-pointer {
    cursor: pointer;
}

.datatable-controls {
    align-items: center;
}

/* Mobile/Tablet */
@media (max-width: 1024px) {
    .datatable-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .datatable-controls .form-select,
    .datatable-controls input {
        width: 100%;
    }

    .datatable-controls button {
        width: 70%;
    }
}


@media (max-width: 1024px) {
    .mobile-table {
        display: block;
    }

    .mobile-row-card {
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .mobile-row-item {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding: 6px 0;
        border-bottom: 1px dashed #eee;
    }

    .mobile-row-item:last-child {
        border-bottom: none;
    }

    .mobile-label {
        font-weight: 600;
        color: #6c757d;
    }

    .mobile-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 10px;
    }

    /* Handle nested tables within mobile cards */
    .mobile-row-item .mobile-value table {
        width: 100%;
        margin: 8px 0;
        font-size: 12px;
    }

    .mobile-row-item .mobile-value table th,
    .mobile-row-item .mobile-value table td {
        padding: 4px 6px;
        border: 1px solid #dee2e6;
        text-align: left;
    }

    .mobile-row-item .mobile-value table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    /* Ensure nested tables are responsive within cards */
    .mobile-row-item .mobile-value .table-responsive {
        margin: 8px -6px;
    }

    .mobile-row-item .mobile-value .table-responsive table {
        margin: 0;
    }
}

/* iPad / Tablet */
@media (max-width: 1024px) {

    table th,
    table td {
        font-size: 13px;
        padding: 10px;
        white-space: nowrap;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table-borderless {
        display: none;
    }
}
</style>
