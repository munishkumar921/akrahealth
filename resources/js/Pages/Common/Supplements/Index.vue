<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "../../../Components/Common/Modal.vue";
import AddSupplementModal from "./AddSupplementModal.vue";
import {
    supplementsData,
    supplementsTabs,
} from "../../../Data/MockData/office/supplements";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";

const currentTab = ref("supplement_inventory");
const tableData = ref(supplementsData);
const isAddModalOpen = ref(false);

const addSupplementForm = useForm({
    slug: "",
    description: "",
    strength: "",
    manufacturer: "",
    quantity: "",
    charges: "",
    procedure_code: "",
    expiration_date: "",
    purchase_date: "",
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openAddSupplementModal = () => {
    isAddModalOpen.value = true;
};

const closeAddSupplementModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddSupplementModal,
        icon: "bi bi-plus-circle",
    },
];

const addSupplement = () => {
    closeAddSupplementModal();
};

const columns = [
    { label: "Slug", key: "slug" },
    { label: "Description", key: "description" },
    { label: "Strength", key: "strength" },
    { label: "Manufacturer", key: "manufacturer" },
    { label: "Quantity", key: "quantity" },
    { label: "Charges", key: "charges" },
    { label: "Procedure Code", key: "procedure_code" },
    { label: "Expiration Date", key: "expiration_date" },
    { label: "Purchase Date", key: "purchase_date" },
];
</script>

<template>
<AuthLayout
        title="Supplements"
        description="Manage supplements inventory"
        heading="Supplements"
    >
        <div class="d-flex align-items-center justify-content-between pl-4">
            <h3 class="d-flex align-items-center">Supplements</h3>
            <TabSelector
                :tabs="supplementsTabs"
                :currentTab="currentTab"
                @update:currentTab="updateCurrentTab"
                :actionButtons="buttons"
            />
        </div>
        <Table :columns="columns" :data="tableData">
            <template #actions>
                <div class="d-flex gap-1">
                    <button
                        class="btn btn-primary"
                        data-placement="top"
                        title="Edit"
                    >
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button
                        class="btn btn-primary"
                        data-placement="top"
                        title="Inactivate"
                    >
                        <i class="bi bi-slash-circle"></i>
                    </button>
                    <button
                        class="btn btn-primary"
                        data-placement="top"
                        title="Delete"
                    >
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </template>
        </Table>
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Supplement"
            @close="closeAddSupplementModal"
            size="lg"
        >
            <AddSupplementModal
                @close="closeAddSupplementModal"
                :form="addSupplementForm"
                @submit="addSupplement"
            />
        </Modal>
    </AuthLayout>
</template>
