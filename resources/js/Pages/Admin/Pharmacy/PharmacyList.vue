<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import {useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddPharmacy from "@/Pages/Modals/AddPharmacy.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { ref, computed } from "vue";

/* ---------- PROPS ---------- */
const props = defineProps({
    keyword: String,
    pharmacies: Object,
});

/* ---------- TABLE COLUMNS ---------- */
const columns = [
    { label: "Logo", key: "banner", type: "image" },
    { label: "Name", key: "name" },
    { label: "Email", key: "email" },
    { label: "Mobile", key: "mobile" },
    { label: "Address", key: "address" },
    { label: "Created At", key: "created_at" },
    { label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

 
/* ---------- DELETE ROW ---------- */
const removeRow = (row) => {
    Swal.fire(
        confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
    ).then((result) => {
        if (result.isConfirmed) {
            useForm({}).delete(route("admin.pharmacies.destroy", row.id));
        }
    });
};

/* ---------- ADD/EDIT MODAL ---------- */
const isAddPharmacyModal = ref(false);
const childComponentRef = ref(null);

const openAddModal = () => {
    isAddPharmacyModal.value = true;
};

const closeAddModal = () => {
    isAddPharmacyModal.value = false;
};

const openEditModal = (row) => {
    isAddPharmacyModal.value = true;
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
};

/* ---------- TOP ACTION BUTTONS ---------- */
const buttons = [
    {
        label: "Add Pharmacy",
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
    const form = useForm({
        is_active: !row.is_active,
    });

    form.put(route("admin.pharmacies.update", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally refresh the page or update the local state
        },
    });
};
 
const currentTab = ref("is_active");

const tabs = [
	{ label: "Active", value: "is_active", active: 1 },
	{ label: "Inactive", value: "is_inactive", active: 0 },
];

const computedData = computed(() => {
	const pharmacies = Array.isArray(props.pharmacies)
		? props.pharmacies
		: props.pharmacies?.pharmacies ?? props.pharmacies?.data ?? [];

	const tab = tabs.find(t => t.value === currentTab.value);

	return tab ? pharmacies.filter(p => p.is_active == tab.active) : pharmacies;
});


const updateCurrentTab = (newTab) => {
	currentTab.value = newTab;
};
</script>

<template>
    <AuthLayout title="Pharmacy" description="Pharmacy" heading="Pharmacy">
            <div class="d-none d-md-flex align-items-center justify-content-between mb-3">

                <!-- Title -->
                <h3 class="text-xl mb-0">Pharmacy</h3>

                <!-- Status Filters and Add Button -->
                <div class="d-flex align-items-center gap-3">
                    <TabSelector :tabs="tabs":actionButtons="buttons" :currentTab="currentTab"
				@update:currentTab="updateCurrentTab" />
                </div>
            </div>
                <!-- ================= TABLE + PAGINATION ================= -->
                <div class="table-responsive">
                    <Table :columns="columns" :data="{ data: computedData }"   table="pharmacies" :search="keyword">
                        <template #actions="{ row }">
                            <div class="d-flex gap-2">
                                <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>
                    </Table>
                </div>

                <Modal :isOpen="isAddPharmacyModal" :title="'Pharmacy Details'" @close="closeAddModal" size="xl">
                    <AddPharmacy ref="childComponentRef" @close="closeAddModal" @submit="closeAddModal" />
                </Modal>
    </AuthLayout>
</template>


<style scoped>
.icon-btn {
    /* width: 40px;
	height: 40px; */
    padding: 9px 8px 6px 8px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
    transform: scale(0.97);
}

.icon-btn--red {
    background: #ef4444;
}

/* red-500 */
.icon-btn i {
    font-size: 14px;
    line-height: 1;
}

.modal-content {
    border-radius: 12px;
}

.modal-title {
    font-size: 20px;
}

.form-label {
    font-weight: 600;
    color: #374151;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 20px;
}

.modal-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #333;
}

.close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    color: #000;
    opacity: .5;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

.close:hover {
    opacity: 1;
    background-color: rgba(0, 0, 0, .1);
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    max-height: calc(90vh - 140px);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.ah-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    color: #fff;
    box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
    animation: ah-fade-in .15s ease-out;
}

.ah-toast--success {
    background: #16a34a;
}

/* green */
.ah-toast--warning {
    background: #f59e0b;
}

/* amber */
.ah-toast .bi {
    font-size: 18px;
}

@keyframes ah-fade-in {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* colored checks */
.form-check-input--green:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.form-check-input--blue:checked {
    background-color: #0ea5e9;
    border-color: #0ea5e9;
}

.status-check {
    appearance: none;
    width: 16px;
    height: 16px;
    border: 2px solid #d1d5db;
    /* gray-300 */
    border-radius: 4px;
    display: inline-block;
    position: relative;
    cursor: pointer;
    background: #fff;
}

.status-check:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
}

.status-check--green:checked {
    border-color: #06c270;
    /* gray-400 */
    background-color: #06c270;
}

.status-check--grey:checked {
    border-color: #9ca3af;
    /* gray-400 */
    background-color: #9ca3af;
}

/* tick icon */
.status-check:checked::after {
    content: "";
    position: absolute;
    left: 4px;
    top: 1px;
    width: 4px;
    height: 8px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}
</style>
