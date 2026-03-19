<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AddressList from "./AddressList.vue";

import Modal from "../../../../Components/Common/Modal.vue";
import AddAddressModal from "./AddAddressModal.vue";
import {
    addressTableMockData,
    addressTabs,
} from "../../../../Data/MockData/configure/addressBook";

const props = defineProps({
    alerts: Object,
    doctors: Object,
    keyword: String,
});

const contactForm = useForm({
    first_name: "",
    last_name: "",
    prefix: "",
    suffix: "",
    facility: "",
    speciality: "",
    address: "",
    city: "",
    state: "",
    pin_code: "",
    phone: "",
    email: "",
    comments: "",
});

const currentTab = ref("all");
const tableData = ref(addressTableMockData);
const isAddModalOpen = ref(false);

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

const openAddAddressModal = () => {
    isAddModalOpen.value = true;
};

const closeAddAddressModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddAddressModal,
        icon: "bi bi-plus-circle",
    },
];

const addAlert = () => {
    closeAddAddressModal();
};
</script>
<template>
<AuthLayout title="Address Book" description="Manage address book contacts" heading="Address Book">
        <AddressList
            name="Address List"
            :currentTab="currentTab"
            :tableData="tableData"
            @update:currentTab="updateCurrentTab"
            :tabs="addressTabs"
            :actionButtons="buttons"
            :showActions="false"
        />
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Address"
            @close="closeAddAddressModal"
            size="lg"
        >
            <AddAddressModal
                @close="closeAddAddressModal"
                :form="contactForm"
                @submit="addAlert"
            />
        </Modal>
    </AuthLayout>
</template>
