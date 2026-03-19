<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { ref , computed } from "vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import AddSpeciality from "@/Pages/Modals/AddSpeciality.vue";
import Modal from "@/Components/Common/Modal.vue";

/* ---------- PROPS ---------- */
const props = defineProps({
    keyword: String,
    specialities: Object,
});
const childComponentRef = ref(null);

/* ---------- TABLE COLUMNS ---------- */
const columns = [
    { label: "Banner", key: "banner", type: "image" },
    { label: "Name", key: "name" },
    { label: "Description", key: "description" },
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
            useForm({}).delete(route("admin.specialities.destroy", row.id));
        }
    });
};

/* ---------- ROUTE TO CREATE PAGE ---------- */
const isAddSpeciality = ref(false);
const closeAddSpeciality = () => {
    isAddSpeciality.value = false;
}

const openAddSpeciality = () => {
    isAddSpeciality.value = true;
};

const openEdit = (row) => {
    console.log(row);
    setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
    isAddSpeciality.value = true;
};

/* ---------- TOP ACTION BUTTONS ---------- */
const buttons = [
    {
        label: "Add Speciality",
        function: openAddSpeciality,
        icon: "bi bi-plus-circle",
    },
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
    const form = useForm({
        is_active: !row.is_active,
    });

    form.put(route("admin.specialities.update", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally refresh the page or update the local state
        },
    });
};

 

const currentTab = ref("is_active");

const tabs = [
	{ label: "Active", value: "is_active", active: 1},
	{ label: "Inactive", value: "is_inactive", active: 0 },
];

const computedData = computed(() => {
	const specialities = Array.isArray(props.specialities)
		? props.specialities
		: props.specialities?.specialities ?? props.specialities?.data ?? [];

	const tab = tabs.find(t => t.value === currentTab.value);

	return tab ? specialities.filter(s => s.is_active == tab.active) : specialities;
});
 

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};
</script>

<template>
    <AuthLayout title="Specialities" description="Specialities" heading="Specialities">
        <div class="d-flex align-items-center justify-content-between ">
            <h3 class="d-flex align-items-center">Specialities</h3>
            <div class="d-flex align-items-center gap-3">
                <TabSelector :tabs="tabs" :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab" />
            </div>

        </div>
         <Table :columns="columns" :data="{ ...(specialities || {}), data: computedData }" table="specialities" :search="keyword">
            <!-- Banner image column -->
            <template #banner="{ row }">
                <img :src="row.banner" alt="Banner" class="banner-img"
                    @error="(e) => e.target.src = '/images/avatar.webp'" />
            </template>
            <!-- Actions column -->
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

        <Modal :isOpen="isAddSpeciality" title="Add Speciality" @close="closeAddSpeciality" size="xl">
            <AddSpeciality ref="childComponentRef" @close="closeAddSpeciality" />
        </Modal>
    </AuthLayout>
</template>