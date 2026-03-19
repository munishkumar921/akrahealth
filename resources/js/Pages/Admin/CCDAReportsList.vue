<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed, watch } from "vue";
import * as XLSX from "xlsx";

const props = defineProps({
	keyword: String,
	route: Array,
});

const columns = [
    { key: "select", type: "checkbox", headerSlot: "select-all-header" },
	{ label: "Profile Picture", key: "profile", type: "image" },
	{ label: "Name", key: "name" },
	{ label: "Email", key: "email" },
	{ label: "Mobile", key: "mobile" },
	{ label: "Created At", key: "created_at" },
	{ label: "Status", key: "active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const dataRows = ref([
	 
]);

const filterActive = ref(true);
const filterInactive = ref(true);

const filteredRows = computed(() => {
	return dataRows.value.filter(r => {
		const isActive = !!r.active;
		return (filterActive.value && isActive) || (filterInactive.value && !isActive);
	});
});

/* Selection state */
const selectedRows = ref([]);
const isAllSelected = computed(() => {
    return filteredRows.value.length > 0 && selectedRows.value.length === filteredRows.value.length;
});

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedRows.value = [];
    } else {
        selectedRows.value = filteredRows.value.map(r => r.id);
    }
};

watch(filteredRows, (newRows) => {
    // Clear selections of rows that are no longer visible
    const visibleIds = new Set(newRows.map(r => r.id));
    selectedRows.value = selectedRows.value.filter(id => visibleIds.has(id));
});

const toast = (msg) => {
    toastMsg.value = msg;
    toastType.value = "warning";
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 2000);
};

const downloadExcel = () => {
    if (selectedRows.value.length === 0) {
        toast("Please select at least one row to download.");
        return;
    }

    const dataToExport = dataRows.value.filter(row => selectedRows.value.includes(row.id)).map(row => ({
        Name: row.name,
        Email: row.email,
        Mobile: row.mobile,
        'Created At': row.created_at,
        Status: row.active ? 'Active' : 'Inactive',
    }));

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "CCDA Reports");
    XLSX.writeFile(workbook, "CCDAReports.xlsx");
};

/* Edit Modal State (UI-only) */
const showEdit = ref(false);
const editRow = ref({ id: null, profile: "", name: "", email: "", mobile: "", created_at: "", status: "" });

const openEdit = (row) => {
	editRow.value = { ...row };
	showEdit.value = true;
};

const saveEdit = () => {
	const index = dataRows.value.findIndex(r => r.id === editRow.value.id);
	if (index !== -1) dataRows.value[index] = { ...editRow.value };
	showEdit.value = false;
};

const cancelEdit = () => {
	showEdit.value = false;
};

const removeRow = (row) => {
	dataRows.value = dataRows.value.filter(r => r.id !== row.id);
};
// Toast state (UI-only)
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success"); // 'success' | 'warning'

const notifyStatus = (row) => {
	toastType.value = row.active ? "success" : "warning";
	toastMsg.value = row.active ? `${row.name} is now Active` : `${row.name} is now Inactive`;
	showToast.value = true;
	setTimeout(() => (showToast.value = false), 2000);
};

// Update your existing toggle handler to call notify
const toggleStatus = (row) => {
	row.active = !row.active;
	notifyStatus(row);
};

const buttons = [
    {
        label: "Download as Excel",
        function: downloadExcel,
        icon: "bi bi-download",
        disabled: computed(() => selectedRows.value.length === 0),
    },
];
</script>

<template>
	<AuthLayout
		title="CCDA Reports"
		description="CCDA Reports"
		heading="CCDA Reports"
	>
		<!-- ================= HEADER ================= -->
		<div class="">
			<!-- ================= DESKTOP VIEW - Title and Controls in Same Row ================= -->
			<div class="d-none d-md-flex align-items-center justify-content-between mb-3">
				<!-- Title -->
				<h3 class="text-xl mb-0">CCDA Reports</h3>

				<!-- Status Filters and Action Buttons -->
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

					<!-- Action Buttons -->
					<ActionButtons :actionButtons="buttons" />
				</div>
			</div>

			<!-- ================= MOBILE VIEW - Title ================= -->
			<div class="d-md-none">
				<h3 class="text-xl mb-3">CCDA Reports</h3>
			</div>

			<div class="d-md-none">
				<!-- Active / Inactive / Download -->
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
						@click="downloadExcel"
						:disabled="selectedRows.length === 0">
						<i class="bi bi-download"> </i>Download Excel
					</button>
				</div>
			</div>
		</div>

		<!-- ================= TABLE + PAGINATION ================= -->
		<div class="table-responsive">
			<Table
				:columns="columns"
				:data="filteredRows"
				v-model:selectedRows="selectedRows"
				:isAllSelected="isAllSelected"
				@toggle-select-all="toggleSelectAll"
			>
				<template #header-select-all-header>
					<input
						type="checkbox"
						:checked="isAllSelected"
						@change="toggleSelectAll"
					/>
				</template>
				<template #actions="{ row }">
					<div class="d-flex gap-2">
						<button class="icon-btn btn btn-success" @click="openEdit(row)" title="Edit">
							<i class="bi bi-pencil"></i>
						</button>
						<button class="icon-btn btn btn-danger" @click="removeRow(row)" title="Delete">
							<i class="bi bi-trash"></i>
						</button>
					</div>
				</template>
			</Table>
		</div>

		<!-- Edit Modal (UI-only) -->
		<!-- Edit Modal (UI-only) -->
<!-- Edit Modal (UI-only using ResultReply-style shell) -->
<Teleport to="body">
	<div v-if="showEdit" class="modal-overlay" @click="cancelEdit">
		<div class="modal-container" @click.stop>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Patient</h5>
					<button type="button" class="close" @click="cancelEdit" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<!-- FIELDS UNCHANGED -->
					<div class="mb-3">
						<label class="form-label">Name</label>
						<input v-model="editRow.name" type="text" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Email</label>
						<input v-model="editRow.email" type="email" class="form-control" />
					</div>
					<div class="mb-3">
						<label class="form-label">Mobile</label>
						<input v-model="editRow.mobile" type="text" class="form-control" />
					</div>
					<div class="mb-0">
						<label class="form-label">Status</label>
						<select v-model="editRow.status" class="form-select">
							<option>Active</option>
							<option>Inactive</option>
						</select>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-danger" @click="cancelEdit">Close</button>
					<button type="button" class="btn btn-primary" @click="saveEdit">Save</button>
				</div>
			</div>
		</div>
	</div>
</Teleport>

<!-- Toast -->
<div
	v-if="showToast"
	class="ah-toast"
	:class="toastType === 'success' ? 'ah-toast--success' : 'ah-toast--warning'"
	role="status"
>
	<i class="bi" :class="toastType === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'"></i>
	<span>{{ toastMsg }}</span>
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
.icon-btn:active { transform: scale(0.97); }
.icon-btn--red   { background: #ef4444; } /* red-500 */
.icon-btn i { font-size: 14px; line-height: 1; }
.modal-content { border-radius: 12px; }
.modal-title { font-size: 20px; }
.form-label { font-weight: 600; color: #374151; }
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
.modal-title { font-size: 1.25rem; font-weight: 600; color: #333; }
.close {
	background: none; border: none; font-size: 1.5rem; line-height: 1;
	color: #000; opacity: .5; cursor: pointer; padding: 0; width: 30px; height: 30px; border-radius: 50%;
}
.close:hover { opacity: 1; background-color: rgba(0,0,0,.1); }
.modal-body {
	flex: 1; overflow-y: auto; padding: 1.5rem; max-height: calc(90vh - 140px);
}
.modal-footer {
	display: flex; justify-content: flex-end; gap: 10px;
	padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; background-color: #f8f9fa;
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
	box-shadow: 0 6px 20px rgba(0,0,0,.15);
	animation: ah-fade-in .15s ease-out;
}
.ah-toast--success { background: #16a34a; } /* green */
.ah-toast--warning { background: #f59e0b; } /* amber */
.ah-toast .bi { font-size: 18px; }
@keyframes ah-fade-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

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
	border: 2px solid #d1d5db; /* gray-300 */
	border-radius: 4px;
	display: inline-block;
	position: relative;
	cursor: pointer;
	background: #fff;
}
.status-check:focus { outline: none; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }

.status-check--green:checked {
	border-color: #06c270; /* gray-400 */
	background-color: #06c270;
}
.status-check--grey:checked {
    border-color: #9ca3af; /* gray-400 */
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
