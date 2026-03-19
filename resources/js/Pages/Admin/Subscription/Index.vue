<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";

const props = defineProps({
	subscriptionPlans: Object,
	filters: Object,
});

const columns = [
	{ label: "Plan for", key: "plan_for" },
	{ label: "Title", key: "title" },
	{ label: "Billing cycle", key: "billing_cycle" },
	{ label: "Price", key: "price", formatter: (value, row) => `${row.currency} ${value}` },
	{ label: "Currency", key: "currency" },
	{ label: "Status", key: "active", type: "toggle", onToggle: (row) => toggleStatus(row) },
];

const filterActive = ref(props.filters?.active !== false);
const filterInactive = ref(props.filters?.active !== true);

const filteredRows = computed(() => {
	if (!props.subscriptionPlans || !props.subscriptionPlans.data) return [];
	return props.subscriptionPlans.data.filter(r => {
		const isActive = !!r.active;
		return (filterActive.value && isActive) || (filterInactive.value && !isActive);
	});
});

/* Edit Modal State (UI-only) */
const showEdit = ref(false);
const editRow = ref({ id: null, plan_for: "", title: "", billing_cycle: "", price: "", currency: "", active: true });

const openEdit = (row) => {
	router.visit(route('admin.subscription-plans.edit', row.id));
};

const cancelEdit = () => {
	showEdit.value = false;
};

const removeRow = (row) => {
	if (confirm('Are you sure you want to delete this subscription plan?')) {
		router.delete(route('admin.subscription-plans.destroy', row.id));
	}
};

// Toast state (UI-only)
const showToast = ref(false);
const toastMsg = ref("");
const toastType = ref("success"); // 'success' | 'warning'

const notifyStatus = (row) => {
	toastType.value = row.active ? "success" : "warning";
	toastMsg.value = row.active ? `${row.title} is now Active` : `${row.title} is now Inactive`;
	showToast.value = true;
	setTimeout(() => (showToast.value = false), 2000);
};

// Update your existing toggle handler to call notify
const toggleStatus = (row) => {
	router.patch(route('admin.subscription-plans.toggle-active', row.id), {}, {
		onSuccess: () => {
			row.active = !row.active;
			notifyStatus(row);
		}
	});
};

const goToAddPlan = () => {
    router.visit(route("admin.subscription-plans.create"));
};

const buttons = [
    {
        label: "Add Plan",
        function: goToAddPlan,
        icon: "bi bi-plus-circle",
    },
];
</script>

<template>
	<AuthLayout
		title="Subscription Plans"
		description="Subscription Plans"
		heading="Subscription Plans"
	>
    <div class="d-flex align-items-center justify-content-between pl-4">
	<h3 class="d-flex align-items-center text-xl mb-0">Subscription Plans</h3>

	<div class="d-flex align-items-center gap-3">
		<!-- Status Filter FIRST -->
		<div class="d-flex align-items-center gap-3">
			<div class="form-check d-flex align-items-center gap-2 m-0">
				<input id="flt-active" type="checkbox" class="status-check status-check--green" v-model="filterActive" />
				<label class="form-check-label" for="flt-active">Active</label>
			</div>
			<div class="form-check d-flex align-items-center gap-2 m-0">
				<input id="flt-inactive" type="checkbox" class="status-check status-check--grey" v-model="filterInactive" />
				<label class="form-check-label" for="flt-inactive">Inactive</label>
			</div>
		</div>

		<!-- Add Plan BUTTON AFTER filter -->
		<ActionButtons :actionButtons="buttons" />
	</div>
</div>
		<Table :columns="columns" :data="filteredRows" :search="keyword">
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

		<!-- Edit Modal (UI-only) -->
		<!-- Edit Modal (UI-only) -->
<!-- Edit Modal (UI-only using ResultReply-style shell) -->
<Teleport to="body">
	<div v-if="showEdit" class="modal-overlay" @click="cancelEdit">
		<div class="modal-container" @click.stop>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Subscription Plan</h5>
					<button type="button" class="close" @click="cancelEdit" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Plan for</label>
						<select v-model="editRow.plan_for" class="form-select">
							<option value="Doctor">Doctor</option>
							<option value="Hospital">Hospital</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Title</label>
						<input v-model="editRow.title" type="text" class="form-control" placeholder="Enter title" />
					</div>
					<div class="mb-3">
						<label class="form-label">Billing cycle</label>
						<select v-model="editRow.billing_cycle" class="form-select">
							<option value="Monthly">Monthly</option>
							<option value="Yearly">Yearly</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Price</label>
						<input v-model="editRow.price" type="text" class="form-control" placeholder="Enter price" />
					</div>
					<div class="mb-3">
						<label class="form-label">Currency</label>
						<input v-model="editRow.currency" type="text" class="form-control" placeholder="Enter currency" />
					</div>
					
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Status</label>
							<select v-model="editRow.active" class="form-select">
								<option :value="true">Active</option>
								<option :value="false">Inactive</option>
							</select>
						</div>
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
