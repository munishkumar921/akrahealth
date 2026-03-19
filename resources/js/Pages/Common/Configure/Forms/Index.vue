<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import FormsList from "./FormsList.vue";

import Modal from "../../../../Components/Common/Modal.vue";
import AddFormModal from "./AddFormModal.vue";
import {
    addressTableMockData,
    addressTabs,
} from "../../../../Data/MockData/configure/addressBook";
import { formsTableMockData } from "../../../../Data/MockData/configure/myForms";

const props = defineProps({
    alerts: Object,
    doctors: Object,
    keyword: String,
});

const formsForm = useForm({
    form_title: "",
    gender_assosciation: "",
    age_assosciation: "",
});

const currentTab = ref("all");
const tableData = ref(formsTableMockData);
const isAddModalOpen = ref(false);

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openAddFormsModal = () => {
    isAddModalOpen.value = true;
};

const closeAddFormsModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddFormsModal,
        icon: "bi bi-plus-circle",
    },
];

const addForm = () => {
    closeAddFormsModal();
};
</script>
<template>
<AuthLayout title="My Forms" description="Manage custom forms" heading="My Forms">
        <FormsList
            name="My Forms"
            :currentTab="currentTab"
            :tableData="tableData"
            @update:currentTab="updateCurrentTab"
            :actionButtons="buttons"
            :showActions="false"
        />
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Form"
            @close="closeAddFormsModal"
            size="lg"
        >
            <AddFormModal
                @close="closeAddFormsModal"
                :form="formsForm"
                @submit="addForm"
            />
        </Modal>
    </AuthLayout>
</template>
