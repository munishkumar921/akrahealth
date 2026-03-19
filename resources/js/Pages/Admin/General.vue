<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";
import Swal from 'sweetalert2/dist/sweetalert2.js';

const props = defineProps({
	request: String,
	settings: Object,
});

const columns = [
	{ label: "Key", key: "key" },
	{ label: "Value", key: "value" },
	{ label: "Type", key: "type" },
	{ label: "Group", key: "group" },
	{ label: "Status", key: "is_active", type: "toggle", onToggle: (row) => toggleStatus(row) },
	{ label: "Encrypted", key: "is_encrypted", type: "boolean"},
];

const filterActive = ref(true);
const filterInactive = ref(true);

const filteredRows = computed(() => {
	if (!props.settings?.data) return [];
	return props.settings.data.filter(r => {
		const active = r.is_active;
		return (filterActive.value && active) ||
			   (filterInactive.value && !active);
	});
});

const toggleStatus = (row) => {
	const form = useForm({
		id: row.id,
		is_active: !row.is_active,
	});
	form.post(route('admin.settings.store'), {
		preserveScroll: true,
	});
};

const removeRow = (row) => {
	Swal.fire(confirmSettings('Are you sure to delete this data?', 'This action could affect the application flow.')).then((result) => {
		if (result.isConfirmed) {
			const form = useForm({});
			form.delete(route('admin.settings.destroy', row.id));
		}
	});
};

const goToAddService = () => {
	router.visit(route("admin.settings.create"));
};

const buttons = [
	{
		label: "Add Setting",
		function: goToAddService,
		icon: "bi bi-plus-circle",
	},
];
</script>

<template>
	<AuthLayout title="General Settings" description="Manage general application settings" heading="General Settings">
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">General Settings</h3>

				<!-- Status Filters and Add Button -->
				<div class="d-flex align-items-center gap-3">
					<div class="form-check d-flex align-items-center gap-2 m-0">
						<input
							id="flt-active"
							type="checkbox"
							class="status-check status-check--green"
							v-model="filterActive" />
						<label class="mt-2" for="flt-active">Active</label>
					</div>

					<div class="form-check d-flex align-items-center gap-2 m-0">
						<input
							id="flt-inactive"
							type="checkbox"
							class="status-check status-check--grey"
							v-model="filterInactive" />
						<label class="mt-2" for="flt-inactive">Inactive</label>
					</div>

					<!-- Add Button -->
					<ActionButtons :actionButtons="buttons" />
				</div>
			</div>

			<!-- ================= MOBILE VIEW - Title ================= -->
			<div class="d-md-none">
				<h3 class="text-xl mb-3">General Settings</h3>
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
						@click="goToAddService">
						<i class="bi bi-plus"> </i>Add Setting
					</button>
				</div>
			</div>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table :columns="columns" :data="filteredRows" table="settings">
				<template #actions="{ row }">
					<div class="d-flex gap-2">
						<Link class="icon-btn btn btn-success" :href="route('admin.settings.edit', row.id)" title="Edit">
							<i class="bi bi-pencil"></i>
						</Link>
						<button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
							<i class="bi bi-trash"></i>
						</button>
					</div>
				</template>
			</Table>
		</div>
	</AuthLayout>
</template>

<style scoped>
.icon-btn {
	/* width: 40px;
	height: 40px; */
	padding: 9px 8px 6px 8px;
	border: none;
	border-radius: 12px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	cursor: pointer;
	transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
	transform: scale(0.97);
}

.icon-btn--red {
	background: #ef4444;
}

/* red-500 */
.icon-btn i {
	font-size: 14px;
	line-height: 1;
}

.modal-content {
	border-radius: 12px;
}

.modal-title {
	font-size: 20px;
}

.form-label {
	font-weight: 600;
	color: #374151;
}

.modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0, 0, 0, 0.5);
	display: flex;
	justify-content: center;
	align-items: center;
	z-index: 9999;
	padding: 20px;
}

.modal-container {
	background: white;
	border-radius: 8px;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
	width: 100%;
	max-width: 600px;
	max-height: 90vh;
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.modal-content {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 1.5rem;
	border-bottom: 1px solid #dee2e6;
	background-color: #f8f9fa;
}

.modal-title {
	font-size: 1.25rem;
	font-weight: 600;
	color: #333;
}

.close {
	background: none;
	border: none;
	font-size: 1.5rem;
	line-height: 1;
	color: #000;
	opacity: .5;
	cursor: pointer;
	padding: 0;
	width: 30px;
	height: 30px;
	border-radius: 50%;
}

.close:hover {
	opacity: 1;
	background-color: rgba(0, 0, 0, .1);
}

.modal-body {
	flex: 1;
	overflow-y: auto;
	padding: 1.5rem;
	max-height: calc(90vh - 140px);
}

.modal-footer {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	padding: 1rem 1.5rem;
	border-top: 1px solid #dee2e6;
	background-color: #f8f9fa;
}

.ah-toast {
	position: fixed;
	top: 20px;
	right: 20px;
	z-index: 1050;
	display: inline-flex;
	align-items: center;
	gap: 10px;
	padding: 10px 14px;
	border-radius: 10px;
	color: #fff;
	box-shadow: 0 6px 20px rgba(0, 0, 0, .15);
	animation: ah-fade-in .15s ease-out;
}

.ah-toast--success {
	background: #16a34a;
}

/* green */
.ah-toast--warning {
	background: #f59e0b;
}

/* amber */
.ah-toast .bi {
	font-size: 18px;
}

@keyframes ah-fade-in {
	from {
		opacity: 0;
		transform: translateY(-6px);
	}

	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* colored checks */
.form-check-input--green:checked {
	background-color: #10b981;
	border-color: #10b981;
}

.form-check-input--blue:checked {
	background-color: #0ea5e9;
	border-color: #0ea5e9;
}

.status-check {
	appearance: none;
	width: 16px;
	height: 16px;
	border: 2px solid #d1d5db;
	/* gray-300 */
	border-radius: 4px;
	display: inline-block;
	position: relative;
	cursor: pointer;
	background: #fff;
}

.status-check:focus {
	outline: none;
	box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
}

.status-check--green:checked {
	border-color: #06c270;
	/* gray-400 */
	background-color: #06c270;
}

.status-check--grey:checked {
	border-color: #9ca3af;
	/* gray-400 */
	background-color: #9ca3af;
}

/* tick icon */
.status-check:checked::after {
	content: "";
	position: absolute;
	left: 4px;
	top: 1px;
	width: 4px;
	height: 8px;
	border: solid #fff;
	border-width: 0 2px 2px 0;
	transform: rotate(45deg);
}
</style>
