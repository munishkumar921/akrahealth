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
