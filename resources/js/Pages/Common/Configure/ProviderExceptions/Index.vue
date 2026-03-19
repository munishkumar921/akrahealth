<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import ProviderExceptionList from "./ProviderExceptionList.vue";

import Modal from "../../../../Components/Common/Modal.vue";
import AddProviderExceptionModal from "./AddProviderExceptionModal.vue";
import { providerExceptionMockData } from "../../../../Data/MockData/configure/providerExceptions";
import { mockDoctorNames } from "../../../../Data/commonData";

const contactForm = useForm({
    day: "",
    start_time: "",
    end_time: "",
    title: "",
    reason: "",
    provider: "",
});

const currentTab = ref("Dr. Rohan Mehta");
const tableData = ref(providerExceptionMockData);
const isAddModalOpen = ref(false);

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openAddProviderExceptionModal = () => {
    isAddModalOpen.value = true;
};

const closeAddProviderExceptionModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddProviderExceptionModal,
        icon: "bi bi-plus-circle",
    },
];

const addProviderException = () => {
    closeAddProviderExceptionModal();
};
</script>
<template>
<AuthLayout title="Provider Exceptions" description="Manage provider schedule exceptions" heading="Provider Exceptions">
        <ProviderExceptionList
            name="Provider Exceptions"
            :currentTab="currentTab"
            :tableData="tableData"
            @update:currentTab="updateCurrentTab"
            :tabs="mockDoctorNames"
            :actionButtons="buttons"
            :showActions="false"
        />
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Provider Exception"
            @close="closeAddProviderExceptionModal"
            size="lg"
        >
            <AddProviderExceptionModal
                @close="closeAddProviderExceptionModal"
                :form="contactForm"
                @submit="addProviderException"
            />
        </Modal>
    </AuthLayout>
</template>
