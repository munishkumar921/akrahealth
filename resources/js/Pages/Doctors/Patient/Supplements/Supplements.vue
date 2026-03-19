<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { supplementsTabs } from "@/Data/MockData/suppliments";
import Table from "@/Components/Table/Table.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddSupplementModal from "@/Pages/Modals/Supplement.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    supplements: Object,
    keyword: String,
    route: Array,
    encounters: Object,
});

const currentTab = ref("active");
const isAddModalOpen = ref(false);
const childComponentRef = ref(null);

const openAddSupplementModal = () => {
    if(!props.encounters?.id){
        toast("Please create an encounter before adding a supplement.");
        return;
    }
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};

const edit = (supplement) => {
    isAddModalOpen.value = true;
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(supplement);
        }
    }, 100);
};

const closeAddSupplementModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add supplement",
        function: openAddSupplementModal,
        icon: "bi bi-plus-circle",
    },
];

const filteredSupplements = computed(() => {
    const meds = props.supplements;
    let list = [];

    if (Array.isArray(meds)) {
        list = meds;
    } else if (meds && Array.isArray(meds.data)) {
        list = meds.data;
    }
    
    if (!list.length) return [];

    if (currentTab.value === "active") {
        return list.filter(m => !!m.date_active);
    } else if (currentTab.value === "inactive") {
        return list.filter(m => !m.date_active);
    }
    return list;
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const SupplementStatus = (row) => {
    router.get(route("doctor.supplement.status", {id: row.id, type: row.date_inactive ? 'active' : 'inactive' }), {
        preserveScroll: true,
    });
};

const deleteSupplement = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.supplements.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
};

const columns = computed(() => [
    { label: "Supplement", key: "supplement" },
    { label: "Dosage", key: "dosage" },
    { label: "Dosage Unit", key: "dosage_unit" },
    { label: "Sig", key: "sig" },
    { label: "Route", key: "route" },
    { label: "Frequency", key: "frequency" },
    { label: "Instructions", key: "instructions" },
    { label: "Reason", key: "reason" },
    { label: currentTab.value === "active" ? "Date Active" : "Date Inactive", key: currentTab.value === "active" ? "date_active" : "date_inactive" },
    { label: "Status", key: "status", type: "slot", slot: "status" },
]);
</script>

<template>
    <AuthLayout title="supplements" description="supplements" heading="">
        <div class="supplements-header">
            <h3 class="supplements-title">Supplements</h3>
            <div class="tab-selector-container">
                <TabSelector 
                    :tabs="supplementsTabs" 
                    :currentTab="currentTab" 
                    @update:currentTab="updateCurrentTab"
                    :actionButtons="buttons" 
                />
            </div>
        </div>
        
        <Table 
            :columns="columns" 
            :data="filteredSupplements.data ? filteredSupplements : { data: filteredSupplements }"
            :search="keyword" 
            class="mt-4"
        >
            <template #status="{ row }">
                <label class="ah-switch">
                    <input type="checkbox" :checked="!row.date_inactive"
                        @change="SupplementStatus(row)" />
                    <span class="ah-slider">
                        <i class="bi bi-check2 toggle-check-icon"></i>
                    </span>
                </label>
            </template>
            
            <template #actions="{ row }">
                <button class="btn btn-primary" @click="edit(row)" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-danger" @click="deleteSupplement(row.id)" title="Delete">
                    <i class="bi bi-trash3"></i>
                </button>
            </template>
        </Table>
        
        <Modal :isOpen="isAddModalOpen" title="Add Supplement" @close="closeAddSupplementModal" size="xl">
            <AddSupplementModal ref="childComponentRef" :encounters="props.encounters" :route="route"
                @close="closeAddSupplementModal" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
/* Header Alignment - Mobile Responsive */
.supplements-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.supplements-title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}

.tab-selector-container {
    display: flex;
    align-items: center;
    flex: 1;
    justify-content: flex-end;
    min-width: 0;
}

/* Tablet Responsiveness */
@media (max-width: 991px) {
    .supplements-header {
        flex-direction: column;
        align-items: stretch;
    }

    .supplements-title {
        font-size: 1.5rem;
        text-align: center;
    }

    .tab-selector-container {
        justify-content: center;
        width: 100%;
    }
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .supplements-header {
        gap: 0.75rem;
    }

    .supplements-title {
        font-size: 1.25rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 480px) {
    .supplements-header {
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    .supplements-title {
        font-size: 1.1rem;
    }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
    .supplements-header {
        flex-direction: row;
        align-items: center;
    }

    .supplements-title {
        font-size: 1rem;
        text-align: left;
    }

    .tab-selector-container {
        justify-content: flex-end;
    }
}
</style>