<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
 import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Modal from "@/Components/Common/Modal.vue";
import AddLabTest from "@/Pages/Modals/AddLabTest.vue";
import { ref, computed } from "vue";
 
const props = defineProps({
	request: Array,
	tests: Object,
	keyword: String,
	categories: Array,
});
 
/* ---------- MODAL STATE ---------- */
const isAddModalOpen = ref(false);
const childComponentRef = ref(null);
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
}
const handleFormSubmit = () => {
	// Refresh the page data after successful form submission
	router.reload({ only: ['tests'] });
};

 
const columns = [
	{ label: "Test category", key: "category.name" },
	{ label: "Test name", key: "name" },
	{ label: "Sample type", key: "sample_type" },
	{ label: "Fasting required", key: "fasting_required", type: "boolean" },
	{ label: "Report time", key: "report_time" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];


const removeRow = (row) => {
	dataRows.value = dataRows.value.filter(r => r.id !== row.id);
};

const goToAddService = () => {
	openAddModal();
};

const buttons = [
	{
		label: "Add Lab Test",
		function: goToAddService,
		icon: "bi bi-plus-circle",
	},
];
 
 
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
	const tests = Array.isArray(props.tests)
		? props.tests
		: props.tests?.data ?? [];

	let filtered = [...tests];

	// Filter by status
	if (currentTab.value === "is_active") {
		filtered = filtered.filter(t => t.is_active === 1);
	}

	if (currentTab.value === "is_inactive") {
		filtered = filtered.filter(t => t.is_active === 0);
	}

	return filtered;
});

const updateCurrentTab = (newTab) => {
	currentTab.value = newTab;
};

</script>

<template>
	<AuthLayout title="Lab Tests" description="Manage lab tests" heading="Lab Tests">

 		<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

			<!-- Title -->
			<h3 class="text-xl mb-0">Lab Tests</h3>

			<!-- Status Filters and Add Button -->
			<TabSelector :tabs="tabs" :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab" />
		</div>

		<div class="table-responsive">
			<Table :columns="columns" :data="{ ...(tests || {}), data: computedData }" table="lab_tests" :search="keyword">
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

		<!-- ================= ADD LAB TEST MODAL ================= -->
		<Modal :isOpen="isAddModalOpen" title="Lab Test Details" size="xl" @close="closeAddModal">
			<AddLabTest ref="childComponentRef" :categories="categories" @close="closeAddModal"
				@submit="handleFormSubmit" />
		</Modal>

	</AuthLayout>
</template>