<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import VisitTypeList from "./VisitTypeList.vue";
import Modal from "../../../../Components/Common/Modal.vue";
import AddVisitTypeModal from "./AddVisitTypeModal.vue";

import {
    visitTypesMockData,
    visitTypesTab,
} from "../../../../Data/MockData/configure/visitTypes";

const visitTypeForm = useForm({
    visit_type: "",
    duration: "",
    color: "",
    provider: "",
});

const currentTab = ref("active");
const tableData = ref(visitTypesMockData);
const isAddModalOpen = ref(false);

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openVisitTypeModal = () => {
    isAddModalOpen.value = true;
};

const closeVisitTypeModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openVisitTypeModal,
        icon: "bi bi-plus-circle",
    },
];

const addVisitType = () => {
    closeVisitTypeModal();
};
</script>
<template>
<AuthLayout title="Visit Types" description="Configure visit types" heading="Visit Types">
        <VisitTypeList
            name="Visit Types"
            :currentTab="currentTab"
            :tableData="tableData"
            @update:currentTab="updateCurrentTab"
            :tabs="visitTypesTab"
            :actionButtons="buttons"
            :showActions="false"
        />
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Visit Type"
            @close="closeVisitTypeModal"
            size="lg"
        >
            <AddVisitTypeModal
                @close="closeVisitTypeModal"
                :form="visitTypeForm"
                @submit="addVisitType"
            />
        </Modal>
    </AuthLayout>
</template>
