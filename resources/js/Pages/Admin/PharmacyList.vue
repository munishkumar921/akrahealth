<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
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

/* ---------- FILTER STATE (FIXED — no duplicates) ---------- */
const filterActive = ref(usePage().props?.request?.filterActive ?? true);
const filterInactive = ref(usePage().props?.request?.filterInactive ?? true);

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

/* ---------- FILTER BY STATUS ---------- */
const byStatus = () => {
    const searchForm = useForm({
        filterActive: filterActive.value,
        filterInactive: filterInactive.value,
        search: usePage().props?.request?.search,
    });

    searchForm.get(route(route().current()));
};
</script>

<template>
    <AuthLayout title="Pharmacy" description="Pharmacy" heading="Pharmacy">

        <!-- ================= HEADER ================= -->
        <div class="">
            <!-- ================= DESKTOP VIEW ================= -->
           
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

            <!-- Title -->
            <h3 class="text-xl mb-0">Pharmacy</h3>


                <!-- Status Filters and Add Button -->
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input
                            id="flt-active"
                            type="checkbox"
                            class="status-check status-check--green"
                            v-model="filterActive"
                            @change="byStatus()" />
                        <label class="mt-2" for="flt-active">Active</label>
                    </div>

                    <div class="form-check d-flex align-items-center gap-2 m-0">
                        <input
                            id="flt-inactive"
                            type="checkbox"
                            class="status-check status-check--grey"
                            v-model="filterInactive"
                            @change="byStatus()" />
                        <label class="mt-2" for="flt-inactive">Inactive</label>
                    </div>

                    <!-- Add Button -->
                    <ActionButtons :actionButtons="buttons" />
                </div>

               
            </div>

          <!-- ================= MOBILE VIEW - Title ================= -->
            <div class="d-md-none">
                <h3 class="text-xl mb-3">Pharmacy</h3>
            </div>
            <div class="d-md-none">

                <!-- Active / Inactive / Add -->
                <div class="d-flex align-items-center justify-content-between mb-3">

                    <div class="d-flex gap-3">
                        <label class="d-flex align-items-center gap-1">
                            <input
                                type="checkbox"
                                class="status-check status-check--green"
                                v-model="filterActive" />
                            Active
                        </label>

                        <label class="d-flex align-items-center gap-1">
                            <input
                                type="checkbox"
                                class="status-check status-check--grey"
                                v-model="filterInactive" />
                            Inactive
                        </label>
                    </div>

                    <button
                        class="btn btn-primary btn-sm"
                        @click="openAddModal">
                        <i class="bi bi-plus"></i>Add Pharmacy
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= TABLE + PAGINATION ================= -->
        <div class="table-responsive">
            <Table
                :columns="columns"
                :data="pharmacies"
                table="pharmacies"
                :search="keyword"
            >
                <template #actions="{ row }">
                    <div class="d-flex gap-2">
                        <button class="icon-btn btn btn-primary" @click="openEditModal(row)" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button
                            class="icon-btn btn btn-danger"
                            @click="removeRow(row)"
                            title="Delete">
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
