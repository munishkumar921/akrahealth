<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import {useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import TabSelector from "@/Components/Table/Partials/TabSelector.vue";
import Modal from "@/Components/Common/Modal.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

import VisitType from "@/Pages/Modals/VisitType.vue";

const props = defineProps({
    request: String,
    visittypes: Object,
	doctors: Object,
	keyword: String,
});

const columns = [
	{ label: "Visit Type", key: "name" },
	{ label: "Duration", key: "duration" , type: "duration" , durationType: "minutes" },
	{ label: "Description", key: "description" },
	{ label: "currency", key: "currency" },
	{ label: "provider", key: "doctor.name" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const isModalOpen = ref(false);
const openAddModal = () => {
     isModalOpen.value = true;
};

const toggleStatus = (VisitType) => {
     const toggleform = useForm({
        id: VisitType.id,
        is_active: !VisitType.is_active,
    });
     toggleform.post(route('admin.visit-types.store'), {
        preserveScroll: true,
    });
};

const removeRow = (row) => {
    // ✅ fixed: typo in sweetalert message
    Swal.fire(confirmSettings('Are you sure to delete this data?', 'You won’t be able to get it back!')).then((result) => {
        if (result.isConfirmed) {
            const deleteForm = useForm({});
            deleteForm.delete(route('admin.visit-types.destroy', row.id), { preserveState: true, preserveScroll: true });
        }
    });
};

const buttons = [
    {
        label: "Add Visit Type",	
        function: openAddModal,
        icon: "bi bi-plus-circle",
    },
];

const closeModal = () => {
    isModalOpen.value = false;
 };
 const childComponentRef = ref(null);

const openEditModal = (row) => {
 	isModalOpen.value = true;
     setTimeout(() => {
        if (childComponentRef.value?.update) {
            childComponentRef.value.update(row);
        }
    }, 100);
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
	const visittypes = Array.isArray(props.visittypes)
		? props.visittypes
		: props.visittypes?.data ?? [];

	let filtered = [...visittypes];
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
</script>

<template>
	<AuthLayout
		title="Visit Types"
		description="Visit Types"
		heading="Visit Types"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class=" d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">Visit Types</h3>

				<!-- Status Filters and Add Button -->
				<div class="d-flex align-items-center gap-3">
                   <TabSelector :tabs="tabs"  :actionButtons="buttons" :currentTab="currentTab" @update:currentTab="updateCurrentTab"/>
				</div>
			</div>
 
			 
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table :columns="columns" :data="{ data: computedData }" table="visit_types" :search="keyword">
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
         <Modal :isOpen="isModalOpen" :title="'Visit Type'" @close="closeModal" size="xl">
         <VisitType  ref="childComponentRef" :doctors="doctors"  @close="closeModal" />
        </Modal>
	</AuthLayout>
</template>
