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