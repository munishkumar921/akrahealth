<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddMedicine from "@/Pages/Modals/AddMedicine.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import { ref, computed } from "vue";

const props = defineProps({
    request: Array,
    medicines: Object,
    keyword: String,
});

/* ---------- FILTER STATE ---------- */
const filterActive = ref(usePage().props?.request?.filterActive ?? true);
const filterInactive = ref(usePage().props?.request?.filterInactive ?? true);
 const childComponentRef = ref(null);

/* ---------- MODAL STATE ---------- */
const isAddModalOpen = ref(false);

const openAddModal = () => {
    isAddModalOpen.value = true;
};

const closeAddModal = () => {
    isAddModalOpen.value = false;
};
const openEdit = (row) => {
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
    isAddModalOpen.value = true;
};

const handleFormSubmit = () => {
    // Refresh the full page data after successful form submission
    router.visit(route('admin.medicines.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

 
const columns = [
    { label: "Name", key: "name" },
    { label: "Brand", key: "brand_name" },
    { label: "Strength", key: "strength" },
    { label: "Price", key: "price" },
    { label: "Expiry", key: "expiry_date" },
    { label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];


const removeRow = (row) => {
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
        if (result.isConfirmed) {
            const form = useForm({});
            form.delete(route('admin.medicines.destroy', row.id));
        }
    });
};

const goToAddEncounter = () => {
    openAddModal();
};

const buttons = [
    {
        label: "Add Medicine",
        function: goToAddEncounter,
        icon: "bi bi-plus-circle",
    },
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
    const form = useForm({
        is_active: !row.is_active,
    });

    form.put(route("admin.medicines.update", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally refresh the page or update the local state
        },
    });
};
const tabs = [
    { label: "Active", value: true },
    { label: "Inactive", value: false },
];

const currentTab = ref(true);

const computedData = computed(() => {
    const medicines = Array.isArray(props.medicines)
        ? props.medicines
        : props.medicines?.data ?? [];

    return medicines.filter(medicine => {
        // Convert is_active to boolean for comparison (handles 1/0 from database)
        const medicineActive = Boolean(medicine.is_active);
        return medicineActive === currentTab.value;
    });
});

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};

</script>

<template>
    <AuthLayout title="Medicines" description="Medicines" heading="Medicines">

        <!-- ================= HEADER ================= -->
        <div class="">

            <!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
            <div class="d-none d-md-flex align-items-center justify-content-between mb-3">

                <!-- Title -->
                <h3 class="text-xl mb-0">Medicines</h3>

                <!-- Status Filters and Add Button -->
                <div class="d-flex align-items-center gap-3">
                    <TabSelector :tabs="tabs" :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab" />
                  </div>
            </div>
 
        </div>
 <!-- ================= TABLE + PAGINATION ================= -->
        <div class="table-responsive">
            <Table :columns="columns" :data="computedData" table="medicines" :search="keyword">
                <template #actions="{ row }">
                    <div class="d-flex gap-2">
                       <button class="icon-btn btn btn-primary" @click="openEdit(row)" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </template>
            </Table>
        </div>

        <!-- ================= ADD MEDICINE MODAL ================= -->
        <Modal :isOpen="isAddModalOpen" title="Add New Medicine" size="xl" @close="closeAddModal">
            <AddMedicine  ref="childComponentRef"  @close="closeAddModal" @submit="handleFormSubmit" />
        </Modal>

    </AuthLayout>
</template>
