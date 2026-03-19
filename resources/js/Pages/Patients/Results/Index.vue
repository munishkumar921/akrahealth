<script setup>
import { ref, computed} from 'vue';
import {useForm,router } from '@inertiajs/vue3';
import Table from "@/Components/Table/Table.vue";
import AuthLayout from "@/Layouts/AuthLayout2.vue"; 
import { defineProps } from "vue";

const props = defineProps({
    results: Object,
    keyword: Object,
    doctors:Array,
});

const currentTab = ref('Laboratory')

const tabs = [
  { key: 'Laboratory', label: 'Laboratory', iconClass: 'icon-success', icon: 'fa-solid fa-vial' },
  { key: 'Imaging', label: 'Imaging', iconClass: 'icon-warning', icon: 'fa-solid fa-x-ray' },
 ];
 
const showResult=(id)=>{
    router.get(route('patient.results.show', id));
}

const columns = [
    { label: "name", key: "name" },
     { label: 'Date', key: 'created_at' },
];
// new: normalized results array (handles array or { data: [...] } shapes)
const resultsArray = computed(() => {
    if (!props.results) return [];
    if (Array.isArray(props.results)) return props.results;
    if (props.results.data && Array.isArray(props.results.data)) return props.results.data;
    if (props.results.message && Array.isArray(props.results.message)) return props.results.message;
    // fallback: try to extract enumerable values
    return Object.values(props.results).flat().filter(Boolean);
});
// ...existing code...
const currentData = computed(() => {
    const list = resultsArray.value || [];

    switch (currentTab.value) {
        case 'Laboratory':
            return list.filter(item => item && item.type === 'Laboratory');

        case 'Imaging':
            return list.filter(item => item && item.type === 'Imaging');
        default:
            return [];
    }
});

</script>
<template>
<AuthLayout title="Results" description="View your test results" heading="Results">
        <div class="row">
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                class="menu-item"
                                :class="{ active: currentTab === tab.key }"
                                @click="currentTab = tab.key">
                                  <i :class="tab.icon + ' ' + tab.iconClass"></i>

                                 <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>             
            </div>
            <div class="card col-sm-9 p-3">
                <div class="align-items-center d-flex justify-content-between">
                    <div class="todo-date d-flex mr-3">
                        <h4 class="card-title">Results</h4>
                         
                    </div>
                   
                </div>
                <div class="iq-card-body mt-3">
                     <Table v-if="currentTab ==='Laboratory' || currentTab === 'Imaging'" :columns="columns" :data="{ data: currentData }" :search="keyword"> <template #actions="{ row }">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-success" data-placement="top" title="view"
                                    @click="showResult(row.id)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                        
                    </div>
            </div>
        </div>
 
    </AuthLayout>
</template>

<style scoped>
.finance-menu { display: flex; flex-direction: column; gap: 12px; }
.menu-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: #fff; border: 1px solid #eef0f4; border-radius: 12px;
    padding: 12px 14px; box-shadow: 0 4px 12px rgba(0,0,0,.04);
    cursor: pointer; transition: .2s;
}
.menu-item:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(0,0,0,.06); }
.menu-item.active { border-color: #6f42c1; box-shadow: 0 10px 20px rgba(111,66,193,.15); }
.icon { height: 10px; width: 10px; border-radius: 50%; }
.icon-success { color: #28a745; }
.icon-warning { color: #ffc107; }
.icon-secondary { color: #ff7b29; }
.icon-primary { color: #0d6efd; }
.icon-dark { color: #2e2138; }
.label { font-size: 14px; color: #2b2b2b; font-weight: 600; }
</style>
