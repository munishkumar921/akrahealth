<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Search from "../../../Components/Common/Search.vue";
import PrimaryReport from "./PrimaryReport.vue";
import ActionButtons from "../../../Components/Table/Partials/ActionButtons.vue";
import Modal from "../../../Components/Common/Modal.vue";
import AddReportModal from "./AddReportModal.vue";

const isAddModalOpen = ref(false);

const addReportForm = useForm({
    slug: "",
    field: "",
    operator: "",
    value: "",
    is_active: false,
    without_insurance: false,
    gender: "",
    title: "",
});

const openAddReportModal = () => {
    isAddModalOpen.value = true;
};

const closeAddReportModal = () => {
    isAddModalOpen.value = false;
};

const buttons = [
    {
        label: "Add",
        function: openAddReportModal,
        icon: "bi bi-plus-circle",
    },
];

const addReport = () => {
    closeAddReportModal();
};
</script>

<template>
<AuthLayout title="Reports" description="View and manage reports" heading="Reports">
        <div>
            <section class="rounded table container-fluid pt-4">
                <div class="d-flex justify-content-between divider pb-4">
                    <div class="d-flex align-items-center gap-3 pl-3">
                        <h3 class="d-flex align-items-center pt-2">Reports</h3>
                        <!-- <Search v-model="searchTerm" /> -->
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <ActionButtons :actionButtons="buttons" />
                    </div>
                </div>
                <div class="list-options">
                    <div class="mt-3">
                        <PrimaryReport />
                    </div>
                </div>
            </section>
        </div>
        <Modal
            :isOpen="isAddModalOpen"
            title="Add Report"
            @close="closeAddReportModal"
            size="lg"
        >
            <AddReportModal
                @close="closeAddReportModal"
                :form="addReportForm"
                @submit="addReport"
            />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.table {
    height: 40rem;
}

.divider {
    border-bottom: 2px solid rgba(0, 0, 0, 0.493);
}

.list-options {
    overflow: scroll;
    height: 100%;
}

.search-input {
    width: 40%;
    height: 40px;
}

.search-icon {
    margin-left: -29rem;
    margin-top: -40px;
}

@media (max-width: 990px) {
    .search-input {
        width: 100%;
    }

    .search-icon {
        position: absolute;
        right: 30px;
        top: 60px;
    }
}
</style>
