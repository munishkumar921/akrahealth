<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import AddImmunizationModal from "@/Pages/Modals/Immunization.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
const props = defineProps({
    immunizations: Object,
    keyword: String,
    encounters: Object,
    route: Array,

});

const isAddModalOpen = ref(false);

const columns = [
    { label: "Immunization", key: "immunization" },
    { label: "Dosage", key: "dosage" },
    { label: "Dosage Unit", key: "dosage_unit" },
    { label: "Sequence", key: "sequence" },
    { label: "Route", key: "route" },
    { label: "Body Site", key: "body_site" },
    { label: "Manufacturer", key: "manufacturer" },
];

// 🟢 Add Immunization
const openAddImmunizationModal = () => {
    isAddModalOpen.value = true;
    if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
};


const closeAddImmunizationModal = () => {
    isAddModalOpen.value = false;
};

// 🟢 Action Buttons
const buttons = [
    {
        label: "Add immunization",
        function: openAddImmunizationModal,
        icon: "bi bi-plus-circle",
    },
];
 const childComponentRef = ref(null);
 

// ✅ Edit immunization
const edit = (immunization) => {
     isAddModalOpen.value = true;
    // wait for modal to render before populating data
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(immunization);
        }
    }, 10);
};
 
// ✅ Delete (confirm first, then call DELETE)
const deleteImunization = (id) => {
    
Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                 router.delete(route('doctor.immunizations.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
};
</script>

<template>
    <AuthLayout title="Immunizations" description="Immunizations" heading="Immunizations">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="d-flex align-items-center">Immunizations</h3>
            <ActionButtons :actionButtons="buttons" />
        </div>
         <Table :columns="columns" :data="immunizations" :search="keyword">
            <template #actions="{ row }">
                     <button class="btn btn-primary" @click="edit(row)" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-danger" @click="deleteImunization(row.id)" title="Delete">
                        <i class="bi bi-trash3"></i>
                    </button>
             </template>
        </Table>
        <Modal :isOpen="isAddModalOpen" title="Add Immunization" @close="closeAddImmunizationModal" size="lg">
            <AddImmunizationModal ref="childComponentRef"  @close="closeAddImmunizationModal"
                :encounters="props.encounters" />
        </Modal>
    </AuthLayout>
</template>
