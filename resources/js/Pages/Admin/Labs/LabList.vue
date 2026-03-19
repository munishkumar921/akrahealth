<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, Link, useForm, usePage } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';
import Modal from "@/Components/Common/Modal.vue";
import AddLab from "@/Pages/Modals/AddLab.vue";

const props = defineProps({
	request: String,
	labs: Object,
	keyword: String,
	labCategory: Array,
});

const columns = [
	{ label: "Name", key: "name" },
	{ label: "Email", key: "email" },
	{ label: "Mobile", key: "mobile" },
	{ label: "Specialities", key: "categories", type: "slot", slot: "categories" },
	{ label: "Address", key: "address", type: "slot", slot: "address"},
	{ label: "Created At", key: "created_at" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];


const removeRow = (row) => {
	Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
		if (result.isConfirmed) {
			const form = useForm({});
			form.delete(route('admin.labs.destroy', row.id));
		}
	});
};

const isAddLabModal = ref(false);
const childComponentRef = ref(null);

const openAddModal = () => {
	isAddLabModal.value = true;
};

const buttons = [
	{
		label: "Add Lab",
		function: openAddModal,
		icon: "bi bi-plus-circle",
	},
];

/* ---------- TOGGLE STATUS ---------- */
const toggleStatus = (row) => {
	const form = useForm({
		is_active: !row.is_active,
	});

	form.put(route("admin.labs.update", row.id), {
		preserveScroll: true,
		onSuccess: () => {
			// Optionally refresh the page or update the local state
		},
	});
};

const closeAddModal = () => {
	isAddLabModal.value = false;
};

const openEdit = (row) => {
	setTimeout(() => {
		if (childComponentRef.value) {
			childComponentRef.value.update(row);
		}
	}, 50);
	isAddLabModal.value = true;
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
	const labs = Array.isArray(props.labs)
		? props.labs
		: props.labs?.data ?? [];

	let filtered = [...labs];

	// Filter by status
	if (currentTab.value === "is_active") {
		filtered = filtered.filter(lab => lab.is_active === 1);
	}

	if (currentTab.value === "is_inactive") {
		filtered = filtered.filter(lab => lab.is_active === 0);
	}

	return filtered;
});

const updateCurrentTab = (newTab) => {
	currentTab.value = newTab;
	const isActive = newTab === "is_active" ? 1 : 0;
	router.get(
		window.location.pathname,
		{ ...Object.fromEntries(new URLSearchParams(window.location.search)), is_active: isActive },
		{ preserveState: true, replace: true }
	);
};
</script>
<template>
	<AuthLayout title="Labs" description="Labs" heading="Labs">
		<div class="d-flex align-items-center justify-content-between mb-3">
			<h3 class="d-flex align-items-center">Labs</h3>
			<TabSelector :tabs="tabs" :actionButtons="buttons" :currentTab="currentTab"
				@update:currentTab="updateCurrentTab" />
		</div>
		<Table :columns="columns" :data="labs" table="labs" :search="keyword" :pagination="labs">
			<template #categories="{ row }">
				<div v-if="Array.isArray(row.categories) && row.categories.length">

					<span class="badge bg-primary" :title="row.categories.join(', ')">
						{{ row.categories[0] }}

						<template v-if="row.categories.length > 1">
							+{{ row.categories.length - 1 }}
						</template>
					</span>

				</div>

				<span v-else class="text-muted">N/A</span>
			</template>

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
		<template #address="{ row }">
 				<div v-if="row.user && row.user.address">
					<span>{{ row.user.address.address_1 }}</span>
					<span v-if="row.user.address.address_2">, {{ row.user.address.address_2 }}</span>
					<br>
					<span v-if="row.user.address.city"> {{ row.user.address.city }}</span>
					<span v-if="row.user.address.state">, {{ row.user.address.state }}</span>
					<span v-if="row.user.address.country">, {{ row.user.address.country }}</span>
					<br>
					<span v-if="row.user.address.zip"> {{ row.user.address.zip }}</span>
				</div>
 				<span v-else class="text-muted">N/A</span>
			</template>
		</Table>

		<Modal :isOpen="isAddLabModal" :title="'Lab Details'" @close="closeAddModal" size="lg">
			<AddLab ref="childComponentRef" :labCategory="labCategory" @close="closeAddModal" />
		</Modal>
	</AuthLayout>
</template>
