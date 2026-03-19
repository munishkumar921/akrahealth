<script setup>
import { ref, defineProps, defineEmits } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Search from "@/Components/Common/Search.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Table from "@/Components/Table/Table.vue";
import { router, useForm } from "@inertiajs/vue3";
import Swal from 'sweetalert2';
import Modal from '@/Components/Common/Modal.vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    alerts: Object,
    keyword: String,
    currentTab: {
        type: String,
        default: "active",
    },
    showFilters: {
        type: Boolean,
        default: true,
    },
    showActions: {
        type: Boolean,
        default: true,
    },
    tabs: {
        type: Array,
        default: () => [
            { value: "active", label: "Active" },
            { value: "pending", label: "Pending" },
            { value: "results", label: "Pending Results" },
            { value: "completed", label: "Completed" },
            { value: "inactive", label: "Inactive" },
        ],
    },
    actionButtons: {
        type: Array,
        default: () => [],
    },
    columns: {
        type: Array,
        default: () => [
            { label: "Date", key: "date" },
            { label: "Description", key: "description" },
         ],
    },
});

const emit = defineEmits(['update:currentTab']);

const updateCurrentTab = (newTab) => {
    emit('update:currentTab', newTab);
    
    // Reload page with type parameter for backend filtering
    router.get(route('doctor.alerts.index'), { type: newTab }, { preserveState: true });
};

const isAddModalOpen = ref(false);
const createForm = useForm({
    alert: '',
    description: '',
    date_active: '',
});

const openAddModal = () => {
    createForm.reset();
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
    createForm.reset();
};

const storeAlert = () => {
    createForm.post(route('doctor.alerts.store'), {
        onSuccess: () => {
            closeAddModal();
            // Swal.fire('Saved!', 'Alert has been created.', 'success');
        },
    });
};

const isEditModalOpen = ref(false);
const editForm = useForm({
    id: '',
    alert: '',
    description: '',
    date_active: '',
});

const openEditModal = (row) => {
    editForm.id = row.id;
    editForm.alert = row.alert;
    editForm.description = row.description;
    editForm.date_active = row.date_active;
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
};

const updateAlert = () => {
    editForm.put(route('doctor.alerts.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
         },
    });
};

const deleteAlert = (id) => {
    Swal.fire(confirmSettings("Are you sure you want to delete this alert?")).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('doctor.alerts.destroy', id));
        }
    });
};

const markInactive = (id) => {
    Swal.fire({
        title: 'Reason for Cancellation',
        input: 'text',
        inputLabel: 'Please provide a reason',
        inputPlaceholder: 'Enter reason...',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        inputValidator: (value) => {
            if (!value) {
                return 'Reason is required!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
             router.post(
                route('doctor.alert.status'),
                {
                    id: id,
                    type: 'inactive',
                    why_not_complete: result.value
                },
            );
        }
    });
};
const markActive = (id) => {
    router.post(route('doctor.alert.status'), {
        type: 'active',
        id: id
    });
};

const markComplete = (id) => {
    router.post(route('doctor.alert.status'), {
        type: 'completed',
        id: id
    });
};
</script>

<template>
    <AuthLayout title="Alerts" description="Alerts" heading="">              
        <div class="d-flex justify-content-between divider pb-4">
            <div class="d-flex align-items-center gap-3">
                <h3 class="d-flex align-items-center pt-2">
                    Alerts
                </h3>
             </div>
            <div class="d-flex align-items-center gap-3">
               
                <TabSelector
                     :tabs="tabs"
                    :currentTab="currentTab"
                    @update:currentTab="updateCurrentTab"
                    :actionButtons="actionButtons"
                    dropdownHieght="157px"
                />
                 <button class="btn btn-primary" @click="openAddModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Alert
                </button>
            </div>
        </div>
        <Table 
            :data="alerts" 
            :columns="columns" 
            :search="keyword" 
         >
            <template #actions="{ row }">
                <div class="d-flex justify-content-end gap-2">
                     <button class="btn btn-primary" @click="openEditModal(row)"  title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button v-if="!row.date_complete && !row.why_not_complete" class="btn btn-success"  @click="markComplete(row.id)" title="Complete">
                        <i class="bi bi-check-circle"></i>
                    </button>

                    <button v-if="!row.date_complete && !row.why_not_complete" class="btn btn-info"  @click="markInactive(row.id)" title="Inactive">
                        <i class="fa fa-minus-circle"></i>
                    </button>
                     <button v-if="!row.date_complete && row.why_not_complete" class="btn btn-primary"  @click="markActive(row.id)" title="ReActive">
                        <i class="fa fa-plus-circle"></i>
                    </button>

                                         <button v-if="row.date_complete && !row.why_not_complete" class="btn btn-primary"  @click="markActive(row.id)" title="ReActive">
                        <i class="fa fa-plus-circle"></i>
                    </button>
                    <button class="btn btn-danger" @click="deleteAlert(row.id)" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </template>
        </Table>

        <Modal :isOpen="isAddModalOpen" title="Add Alert" @close="closeAddModal">
            <form @submit.prevent="storeAlert">
                <div class="mb-3">
                    <BaseInput v-model="createForm.alert" label="Alert Title" required />
                    <InputError :message="createForm.errors.alert" />
                </div>
                <div class="mb-3">
                    <BaseInput v-model="createForm.description" label="Description" type="textarea" />
                    <InputError :message="createForm.errors.description" />
                </div>
                <div class="mb-3">
                    <BaseDatePicker v-model="createForm.date_active" label="Active Date" />
                    <InputError :message="createForm.errors.date_active" />
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-danger" @click="closeAddModal">Cancel</button>
                    <button type="submit" class="btn btn-primary" :disabled="createForm.processing">Save</button>
                </div>
            </form>
        </Modal>

        <Modal :isOpen="isEditModalOpen" title="Edit Alert" @close="closeEditModal">
            <form @submit.prevent="updateAlert">
                <div class="mb-3">
                    <BaseInput v-model="editForm.alert" label="Alert Title" required />
                </div>
                <div class="mb-3">
                    <BaseInput v-model="editForm.description"  label="Description" type="textarea" required />
                </div>
                <div class="mb-3">
                    <BaseDatePicker v-model="editForm.date_active" label="Active Date" required />
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary" :disabled="editForm.processing">Update</button>
                    <button type="button" class="btn btn-danger" @click="closeEditModal">Close</button>

                </div>
            </form>
        </Modal>
    </AuthLayout>
</template>
