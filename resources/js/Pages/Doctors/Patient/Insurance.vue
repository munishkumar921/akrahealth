<script setup>
import { computed, ref } from "vue";
 import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddInsuranceModal from "@/Pages/Modals/AddInsurance.vue";
import  Table from "@/Components/Table/Table.vue";
 import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";

const props = defineProps({
    states: Array,
    countries: Array,
    keyword: String,
    insurances: Object,
    
});
const currentTab = ref("active");
const isAddModalOpen = ref(false);

 

const columns = [
    { label: "Facility", key: "plan_name" },
    { label: "Insurance Company", key: "insurance_company" },
    { label: "Address", key: "address_1" },
    { label: "City", key: "city" },
    { label: "State", key: "state" },
    { label: "Zip", key: "zip" },
    { label: "Phone", key: "phone" },
    { label: "Email", key: "email" },
    { label: "Comments", key: "comment" },
];

const computedData = computed(() => {
    // Handle both paginated and array formats safely
    const insurances = props.insurances;
    
    let list = [];
    
    if (Array.isArray(insurances)) {
        list = insurances; // direct array
    } else if (insurances && Array.isArray(insurances.data)) {
        list = insurances.data; // paginator object
    }
    
    // Nothing? Return empty
    if (!list.length) return [];
    
    return list.map((item) => ({
        plan_name: item.plan_name || '',
        insurance_company: item.insurance_company || '',
        address_1: item.address?.address_1 || '',
        city: item.address?.city || '',
        state: item.address?.state || '',
        zip: item.address?.zip || '',
        phone: item.address?.phone || '',
        email: item.address?.email || '',
        comment: item.address?.comment || '',
    }));
});
 

const openAddInsuranceModal = () => {
    isAddModalOpen.value = true;
};

const closeAddInsuranceModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add insurance",
        function: openAddInsuranceModal,
        icon: "bi bi-plus-circle",
    },
];
 
</script>

<template>
    <AuthLayout title="Insurance" description="Insurance" heading="Insurance">
        <div class="d-flex align-items-center justify-content-between ">
            <h3 class="d-flex align-items-center">Insurance</h3>
            <div class="d-flex">
                
                <ActionButtons :actionButtons="buttons" />
            </div>
        </div>
    
        
        
        <Table :columns="columns" :data="{ data: computedData }" :search="keyword" />
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Insurance Details"
            @close="closeAddInsuranceModal"
            size="lg"
        >
            <AddInsuranceModal :states="states" :countries="countries"
                @close="closeAddInsuranceModal"
             />
        </Modal>
    </AuthLayout>
</template>
