<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import Modal from "@/Components/Common/Modal.vue";
import AddLabTestCategory from "@/Pages/Modals/AddLabTestCategory.vue";
import { ref, computed } from "vue";

const props = defineProps({
	request: Array,
	categories: Object,
	keyword: String,
});
const columns = [
	{ label: "Name", key: "name" },
	{ label: "Description", key: "description" },
 	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const removeRow = (row) => {
	Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
		if (result.isConfirmed) {
			const form = useForm({});
			form.delete(route('admin.lab-test-categories.destroy', row.id));
		}
	});
};

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
};

const buttons = [
	{
		label: "Add Lab Test Category",
		function: openAddModal,
		icon: "bi bi-plus-circle",
	},
];

const currentTab = ref("is_active");

const tabs = [
	{ label: "Active", value: "is_active", active: 1},
	{ label: "Inactive", value: "is_inactive", active: 0 },
];

const computedData = computed(() => {
	const categories = Array.isArray(props.categories)
		? props.categories
		: props.categories?.categories ?? props.categories?.data ?? [];

	const tab = tabs.find(t => t.value === currentTab.value);

	return tab ? categories.filter(s => s.is_active == tab.active) : categories;
});
 

const updateCurrentTab = (newTab) => {
    currentTab.value = newTab;
};
</script>

<template>
	<AuthLayout title="Lab Test Categories" description="Manage lab test categories" heading="Lab Test Categories">
 			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

				<!-- Title -->
				<h3 class="text-xl mb-0">Lab Test Categories</h3>

				<!-- Status Filters and Add Button -->
				<div class="d-flex align-items-center gap-3">
					<TabSelector :tabs="tabs" :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab" />
				</div>
			</div>
   		<div class="table-responsive">
			<Table :columns="columns" :data="{ ...(categories || {}), data: computedData }" table="lab_test_categories" :search="keyword">
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

		<Modal :isOpen="isAddModalOpen" title="Lab Test Category Details" @close="closeAddModal">
			<AddLabTestCategory ref="childComponentRef" @close="closeAddModal" @submit="closeAddModal" />
		</Modal>

	</AuthLayout>
</template>