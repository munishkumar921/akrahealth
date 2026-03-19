<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import {useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
 import Modal from "@/Components/Common/Modal.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import AddPatient from "./AddPatient.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { ref, computed } from "vue";
 
/* ---------- PROPS ---------- */
const props = defineProps({
	keyword: String,
	patients: Object,
	doctors: Object,
});

/* ---------- TABLE COLUMNS ---------- */
const columns = [
 	{ label: "Name", key: "name" },
	{ label: "Email", key: "email" },
	{ label: "Mobile", key: "mobile" },
	{ label: "Created At", key: "created_at" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];
 
/* ---------- DELETE ROW ---------- */
const removeRow = (row) => {
	Swal.fire(
		confirmSettings("Are you sure to delete this data?", "You won't be able to get it back")
	).then((result) => {
		if (result.isConfirmed) {
			useForm({}).delete(route("admin.patients.destroy", row.id));
		}
	});
};

/* ---------- ADD/EDIT MODAL ---------- */
const isAddPatientModal = ref(false);
const childComponentRef = ref(null);

const goToAddPatient = () => {
    isAddPatientModal.value = true;
};

const closeAddModal = () => {
    isAddPatientModal.value = false;
};

/* ---------- EDIT MODAL ---------- */
// Edit functionality is now handled by the AddPatient modal
const openEditModal = (row) => {
      // Call the update method on the AddPatient component to populate form data
   setTimeout(() => {
        if (childComponentRef.value) {
            childComponentRef.value.update(row);
        }
    }, 50);
    isAddPatientModal.value = true;
};

/* ---------- TOP ACTION BUTTONS ---------- */
const buttons = [
	{
		label: "Add Patients",
		function: goToAddPatient,
		icon: "bi bi-plus-circle",
	},
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
	const form = useForm({
		is_active: !row.is_active,
	});

	form.put(route("admin.patients.update", row.id), {
		preserveScroll: true,
		onSuccess: () => {
			// Optionally refresh the page or update the local state
		},
	});
};
 
const currentTab = ref("is_active");

const tabs = computed(() => [
	{
		label: "Active",
		value: "is_active",
	},
	{
		label: "Inactive",
		value: "is_inactive",
	},
]);

// Computed property to handle filtering based on current tab
const computedData = computed(() => {
	const patients = Array.isArray(props.patients)
		? props.patients
		: props.patients?.data ?? [];

	let filtered = [...patients];
	if (currentTab.value === "is_active") {
		filtered = patients.filter((patient) => patient.is_active);
	} else if (currentTab.value === "is_inactive") {
		filtered = patients.filter((patient) => !patient.is_active);
	}

	return filtered;
});

const updateCurrentTab = (newTab) => {
	currentTab.value = newTab;
};

 </script>

<template>
	<AuthLayout title="Patients" description="Patients" heading="Patients">
 
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

				<!-- Title -->
				<h3 class="text-xl mb-0">Patients</h3>

				<!-- Status Filters and Add Button -->
				<div class="d-flex align-items-center gap-3">
                   <TabSelector :tabs="tabs"  :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab"/>
					<!-- Add Button -->
 				</div>
			</div>
 		<!-- ================= TABLE + PAGINATION ================= -->
  		<div class="table-responsive">
			<Table :columns="columns" :data="{ data: computedData}" table="patients" :search="keyword">
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
		<Modal :isOpen="isAddPatientModal" :title="'Patient Details'" @close="closeAddModal" size="xl">
			<AddPatient ref="childComponentRef" :doctors="doctors" @close="closeAddModal" />
		</Modal>
	</AuthLayout>
</template>
 
