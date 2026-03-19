s
<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
 import { useForm,router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
 import { ref, computed } from "vue";
import Modal from "@/Components/Common/Modal.vue";
 import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import roles from "@/Pages/Modals/Roles.vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
 
const props = defineProps({
	keyword: String,
 	roles: {
		type: Object,
		default: () => []
	}
});
 
// Modal State
const isModalOpen = ref(false);
const childComponentRef = ref(null);
// Action buttons
const buttons = [
	{
		label: "Add Role",
		icon: "bi bi-plus-lg",
		function: () => openAdd(),
	},
];

// Columns definition
const columns = [
	{ label: "Name", key: "name" },
	{ label: "Guard", key: "guard_name" },
	{ label: "Created At", key: "created_at" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
 ];
const toggleStatus = (row) => {
	const form = useForm({
 		is_active: !row.is_active,
	});

	form.put(route("admin.api.roles.toggle", row.id), {

		preserveScroll: true,
		onSuccess: () => {
			// Optionally refresh the page or update the local state
		},
	});
};
 // Computed property to handle filtering based on current tab
const computedData = computed(() => {
	const roles = Array.isArray(props.roles)
		? props.roles
		: props.roles?.data ?? [];

	let filtered = [...roles];

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
const openAdd = () => {
	isModalOpen.value = true;
};

const closeModal = () => {
	isModalOpen.value = false;
};
 

const openEdit = (row) => {
	isModalOpen.value = true;
	setTimeout(() => {
		if (childComponentRef.value) {
			childComponentRef.value.update(row);
		}
		
	}, 100);
 };

const removeRow = (row) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.api.roles.destroy', row.id));
        }
    });
};
</script>

<template>
	<AuthLayout title="Roles & Permissions" description="Roles & Permissions" heading="Roles & Permissions">
		<div class="d-flex justify-content-between align-items-center mb-4">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center gap-3">
				<h3 class="text-xl mb-0">Roles & Permissions</h3>
			</div>
			<div class="d-flex gap-2">
				<TabSelector :tabs="tabs" :currentTab="currentTab" @update:currentTab="updateCurrentTab"
					:actionButtons="buttons" />
			</div>
		</div>
		<!-- Data Table -->
		<div class="table-responsive">
			<Table :columns="columns" :data="{ data: computedData}" :search="keyword">
				<template #status="{ row }">
					<span :class="getStatusClass(row.is_active)" @click="Status(row)" style="cursor: pointer;">
						{{ row.is_active ? 'Active' : 'Inactive' }}
					</span>
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
			</Table>
		</div>
		<Modal :isOpen="isModalOpen" @close="closeModal" title="Manage Role" size="lg">
			<roles ref="childComponentRef" @close="closeModal" />
		</Modal>
	</AuthLayout>
</template>
