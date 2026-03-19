<script setup>
import AuthLayout from "@/Layouts/AuthLayout2.vue";
import { computed, ref, watch, onMounted } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import Modal from "@/Components/Common/Modal.vue";
import EditBillingNotesModal from "../Partials/EditBillingNotesModal.vue";
import Table from "@/Components/Table/Table.vue";
import AddPaymentModal from "@/Pages/Modals/AddBillingPayment.vue";
 
const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
    keyword: {
        type: Object,
        default: () => ({}),
    },
    billingData: {
        type: Object,
        default: () => ({
            encounters: [],
            misc_bills: [],
            bluebutton_data: [],
            type: 'encounters'
        }),
    },
    notes: {
        type: String,
        default: "",
    },
});

const currentTab = ref(props.billingData.type || "encounters");
const isEditModalOpen = ref(false);
const isAddPaymentModal = ref(false);
const selectedRow = ref(null);

const searchTerm = ref("");

const form = useForm({
    notes: props.notes?.billing_note,
    id: props.notes?.id,
});

const tabs = [
    { value: "encounters", label: "Encounters", dotClass: "dot-success" },
    { value: "misc", label: "Miscellaneous Bills", dotClass: "dot-warning" },
];

const columns = [
    { label: "Date", key: "date" ,formatter: (value) => new Date(value).toLocaleDateString()},
    { label: "Reason", key: "reason" },
    { label: "Balance", key: "balance" },
    { label: "Charges", key: "charges" },
];

const tableData = computed(() => {
    // Return the appropriate data based on current tab
    if (currentTab.value === 'misc') {
        return props.billingData.misc_bills || [];
    } else {
        return props.billingData.bluebutton_data || props.billingData.encounters || [];
    }
});

const filteredData = computed(() => {
    const data = tableData.value;
    if (!searchTerm.value) {
        return data;
    }
    const lowerCaseSearch = searchTerm.value.toLowerCase();
    return data.filter(row =>
        Object.values(row).some(val =>
            String(val).toLowerCase().includes(lowerCaseSearch)
        )
    );
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

watch(currentTab, (newTab) => {
    router.get(route('doctor.billing.index', { type: newTab }), {
        onSuccess: (page) => {
            // Data is already in props, no need to update tableData
        },
    });
});

const openEditBillingNotesModal = () => {
    isEditModalOpen.value = true;
};
const closeEditBillingNotesModal = () => {
    isEditModalOpen.value = false;
};

const editBillingNotes = () => {
    form.post(route('doctor.billing.notes.update'), {
        onSuccess: () => closeEditBillingNotesModal(),
    });
};

const openAddPaymentModal = (row) => {
    selectedRow.value = row;
    isAddPaymentModal.value = true;
};

const closeAddPaymentModal = () => {
    isAddPaymentModal.value = false;
    selectedRow.value = null;
};

// Format currency display
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};
</script>

<template>
    <AuthLayout title="Billing" description="Billing" heading="Billing">
        <div class="row">
            <div class="col-lg-3">
                <div class="iq-card">
                    <div class="iq-card-body">
                        <div class="finance-menu">
                            <button v-for="tab in tabs" :key="tab.value" type="button" class="menu-item"
                                :class="{ active: currentTab === tab.value }" @click="updateCurrentTab(tab.value)">
                                <span class="dot" :class="tab.dotClass"></span>
                                <span class="label">{{ tab.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card col-sm-9 p-3">
                <div class="align-items-center d-flex justify-content-between">
                    <div class="todo-date d-flex mr-3">
                        <h4 class="card-title">Billing</h4>
                    </div>
                    <div class="todo-notification d-flex align-items-center">
                        <div class="notification-icon position-relative d-flex align-items-center mr-3"></div>
                        <button class="btn btn-primary ms-2" @click="openEditBillingNotesModal">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Billing Notes
                        </button>
                    </div>
                </div>

                <!-- <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Search billing records..." 
                                v-model="searchTerm"
                            >
                            <span class="input-group-text">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div> -->

                <div class="iq-card-body mt-3">
                    <!-- Summary Cards -->
                    <div class="row mb-4" v-if="filteredData.length > 0">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Records</h6>
                                    <h4 class="text-primary">{{ filteredData.length }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Balance</h6>
                                    <h4 class="text-warning">
                                        {{formatCurrency(filteredData.reduce((sum, row) => sum +
                                        (parseFloat(row.balance) || 0), 0)) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total Charges</h6>
                                    <h4 class="text-success">
                                        {{formatCurrency(filteredData.reduce((sum, row) => sum +
                                        (parseFloat(row.charges) || 0), 0)) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-success rounded">
                            <p>Billing Notes: {{ notes?.billing_note }}</p>
                        </div>

                    </div>
                    <Table :columns="columns" :data="{ data: filteredData }" :search="keyword">
                        <template #balance="{ value }">
                            <span :class="{ 'text-danger': value < 0, 'text-success': value > 0 }">
                                {{ formatCurrency(value) }}
                            </span>
                        </template>

                        <template #charges="{ value }">
                            <span class="text-success">
                                {{ formatCurrency(value) }}
                            </span>
                        </template>

                        <template #actions="{ row }">
                            <div class="btn-group btn-group-sm">
                                <!-- Payment History -->
                                <Link :href="route('doctor.billing_payment_history', {
                                    id: row.encounter_id || row.id
                                })" class="btn btn-outline-primary" title="Payment History">
                                    <i class="fa fa-history"></i>
                                </Link>

                                <!-- Add Payment -->
                                <button class="btn btn-outline-success" @click="openAddPaymentModal(row)"
                                    title="Add Payment">
                                    <i class="fa fa-usd"></i>
                                </button>
                            </div>
                        </template>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="filteredData.length === 0" class="text-center py-5">
                        <i class="fa fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No billing records found</h5>
                        <p class="text-muted">
                            {{ searchTerm ? 'Try adjusting your search terms' : `No ${currentTab} billing records
                            available` }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Payment Modal -->
        <Modal :isOpen="isAddPaymentModal" title="Add Payment" @close="closeAddPaymentModal" size="lg">
            <AddPaymentModal v-if="isAddPaymentModal" :billingData="selectedRow" :record-type="currentTab"
                @close="closeAddPaymentModal" @success="closeAddPaymentModal" />
        </Modal>

        <!-- Edit Billing Notes Modal -->
        <Modal :isOpen="isEditModalOpen" title="Edit Billing Notes" @close="closeEditBillingNotesModal">
            <EditBillingNotesModal @close="closeEditBillingNotesModal" :form="form" @submit="editBillingNotes" />
        </Modal>
    </AuthLayout>
</template>

<style scoped>
.finance-menu {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    background: #fff;
    border: 1px solid #eef0f4;
    border-radius: 12px;
    padding: 12px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
    cursor: pointer;
    transition: .2s;
}

.menu-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, .06);
}

.menu-item.active {
    border-color: #6f42c1;
    box-shadow: 0 10px 20px rgba(111, 66, 193, .15);
}

.dot {
    height: 10px;
    width: 10px;
    border-radius: 50%;
}

.dot-success {
    background: #28a745;
}

.dot-warning {
    background: #ffc107;
}

.dot-primary {
    background: #0d6efd;
}

.label {
    font-size: 14px;
    color: #2b2b2b;
    font-weight: 600;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>