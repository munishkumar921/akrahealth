<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
 import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import ExceptionModal from "@/Pages/Modals/ExceptionModal.vue";
import { ref} from "vue";
import Modal from "@/Components/Common/Modal.vue";
 
 
const props = defineProps({
	keyword: String,
	data: Array,
	route: Array,
	doctors: {
		type: Array,
		default: () => []
	},
	calendarEvents: {
		type: Array,
		default: () => []
	},
});

const columns = [
	{ label: "Date", key: "exception_date" },
	{ label: "Start Time", key: "start_time" },
	{ label: "End Time", key: "end_time" },
	{ label: "Title", key: "title" },
	{ label: "Reason", key: "reason" },
	{ label: "Provider", key: "doctor_name"},
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];
 
/* Tab State */
 
/* Create Modal State */
const isOpenModal = ref(false);

const closeModal = () => {
	isOpenModal.value = false;
};

const childComponentRef = ref(null);
/* Edit Modal State (UI-only) */
const openEdit = (row) => {
	isOpenModal.value = true;
	setTimeout(() => {
		if (childComponentRef.value) {
			childComponentRef.value.update(row);
		}
		
	}, 100);
 };

const toggleStatus = (row) => {
	row.active = !row.active;
};

const goToAddException = () => {
	isOpenModal.value = true;
};

const buttons = [
	{
		label: "Add Provider Exception",
		function: goToAddException,
		icon: "bi bi-plus-circle",
	},
];
 
</script>

<template>
	<AuthLayout title="Provider Exceptions for Schedule" description="Provider Exceptions for Schedule" heading="Provider Exceptions for Schedule">
		<!-- ================= HEADER ================= -->
		<div class="">

			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">

				<!-- Title -->
				<h3 class="text-xl mb-0">Exceptions</h3>
				<!-- Add Button -->
				<ActionButtons :actionButtons="buttons" />
			</div>
		</div>

		<!-- Tabs -->
		<div class="iq-card p-3">
			 
 
			<!-- Table View -->
 				<Table :columns="columns" :data="data" :search="keyword" >
					<template #actions="{ row }">
						<div class="d-flex gap-2">
							<button class="btn btn-primary" @click="openEdit(row)" title="Edit">
								<i class="bi bi-pencil"></i>
							</button>
							<button class=" btn btn-danger" @click="removeRow(row)" title="Delete">
								<i class="bi bi-trash"></i>
							</button>
						</div>
					</template>
				</Table>

		</div>

		<!-- Exception Modals -->
		<Modal :isOpen="isOpenModal" @close="closeModal" title="Provider Exceptions for Schedule" size="xl">
			<ExceptionModal ref="childComponentRef" @close="closeModal" :newException="newException" :doctors="doctors" />
		</Modal>
	</AuthLayout>
</template>
 
 