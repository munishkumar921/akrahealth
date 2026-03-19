<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Allergy from "../../Modals/Allergy.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import Modal from "@/Components/Common/Modal.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
    allergies: Object,
    keyword: String,
    encounters: Object,
    route: Array,
});

const childComponentRef = ref();

// 🟢 Edit Medication
const edit = (allergy) => {
    isAddModalOpen.value = true;
    // wait for modal to render before populating data
    setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(allergy);
        }
    }, 10);
};
  
const del = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this medication?"))
        .then((result) => {
            if (result.isConfirmed) {
                const deleteForm = useForm({});
                deleteForm.delete(route('doctor.allergies.destroy', id), {
                    preserveScroll: true,
                });
            }
        });
    
  };

const isAddModalOpen = ref(false);

const columns = [
    { label: "Substance or Medication", key: "allergies_medicine" },
    { label: "Reaction", key: "allergies_reaction" },
    { label: "Severity", key: "allergies_severity" },
    { label: "Notes", key: "notes" },
    { label: "Active Date", key: "date_active" },
 ];

const openAllergiesModal = () => {
      if (childComponentRef.value?.resetForm) {
        childComponentRef.value.resetForm();
    }
    isAddModalOpen.value = true;
};

const closeAllergiesModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add Allergy",
        function: openAllergiesModal,
        icon: "bi bi-plus-circle",
    },
];
 
</script>
<template>
    
    <AuthLayout title="Allergies" description="Allergies" heading="Allergies">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center">Allergies</h3>
            <ActionButtons :actionButtons="buttons" />
        </div>
        <Table :columns="columns" :data="allergies" :search="keyword">
            <template #actions="{ row }">
                <div class="d-flex gap-1">
                     <button class="btn btn-primary" @click="edit(row)" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                        class="btn btn-danger"
                        data-placement="top"
                        title="Delete"
                        @click="del(row.id)"
                    >
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </template>
        </Table>
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Allergy"
            @close="closeAllergiesModal"
            size="lg"
        >
        <Allergy ref='childComponentRef' @close="closeAllergiesModal" :encounters="props.encounters"
         />
        </Modal>
    </AuthLayout>
</template>
