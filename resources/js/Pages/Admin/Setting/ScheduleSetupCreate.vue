<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

/* --------- form state --------- */
const form = ref({
	visit_type: "",
	duration: "15 minutes",
	color: "Blue",
	provider: "All Providers",
	active: true,
});

/* --------- dropdown options --------- */
const durations = [
	{ label: "15 minutes", value: "15 minutes" },
	{ label: "20 minutes", value: "20 minutes" },
	{ label: "30 minutes", value: "30 minutes" },
	{ label: "45 minutes", value: "45 minutes" },
	{ label: "60 minutes", value: "60 minutes" },
	{ label: "90 minutes", value: "90 minutes" },
	{ label: "120 minutes", value: "120 minutes" },
];

const colors = [
	{ label: "Blue", value: "Blue" },
	{ label: "Green", value: "Green" },
	{ label: "Red", value: "Red" },
	{ label: "Purple", value: "Purple" },
	{ label: "Orange", value: "Orange" },
	{ label: "Yellow", value: "Yellow" },
	{ label: "Pink", value: "Pink" },
	{ label: "Gray", value: "Gray" },
];

const providers = [
	{ label: "All Providers", value: "All Providers" },
	{ label: "Dr. Emily Wilson", value: "Dr. Emily Wilson" },
	{ label: "Dr. Robert Chen", value: "Dr. Robert Chen" },
	{ label: "Dr. Sarah Johnson", value: "Dr. Sarah Johnson" },
	{ label: "Dr. Michael Brown", value: "Dr. Michael Brown" },
];

const statuses = [
	{ label: "Active", value: true },
	{ label: "Inactive", value: false },
];

/* --------- validation --------- */
const errors = ref({});

const validateForm = () => {
	errors.value = {};

	if (!form.value.visit_type.trim()) {
		errors.value.visit_type = "Visit type is required";
	}
	if (!form.value.duration) {
		errors.value.duration = "Duration is required";
	}
	if (!form.value.color) {
		errors.value.color = "Color is required";
	}
	if (!form.value.provider) {
		errors.value.provider = "Provider is required";
	}

	return Object.keys(errors.value).length === 0;
};

/* --------- submit --------- */
const submit = () => {
	if (!validateForm()) {
		return;
	}

	console.log("Visit Type form:", { ...form.value });
	toast("Visit Type created successfully!");

	// Navigate back to visit type list
	router.visit(route('admin.visittype.list'));
};
</script>

<template>
	<AuthLayout title="Create Visit Type" description="Create Visit Type" heading="Create Visit Type">
		<div class="container-fluid">
			<form @submit.prevent="submit">
				<!-- Visit Type Information -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Visit Type Information</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Visit Type <span class="text-danger">*</span></label>
						<input v-model="form.visit_type" type="text" class="form-control"
							:class="{ 'is-invalid': errors.visit_type }" placeholder="Enter visit type" />
						<div v-if="errors.visit_type" class="invalid-feedback">{{ errors.visit_type }}</div>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Duration <span class="text-danger">*</span></label>
						<select v-model="form.duration" class="form-select" :class="{ 'is-invalid': errors.duration }">
							<option value="" disabled>Select Duration</option>
							<option v-for="d in durations" :key="d.value" :value="d.value">{{ d.label }}</option>
						</select>
						<div v-if="errors.duration" class="invalid-feedback">{{ errors.duration }}</div>
					</div>
				</div>

				<!-- Color and Provider -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Configuration</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Color <span class="text-danger">*</span></label>
						<select v-model="form.color" class="form-select" :class="{ 'is-invalid': errors.color }">
							<option value="" disabled>Select Color</option>
							<option v-for="c in colors" :key="c.value" :value="c.value">{{ c.label }}</option>
						</select>
						<div v-if="errors.color" class="invalid-feedback">{{ errors.color }}</div>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Provider <span class="text-danger">*</span></label>
						<select v-model="form.provider" class="form-select" :class="{ 'is-invalid': errors.provider }">
							<option value="" disabled>Select Provider</option>
							<option v-for="p in providers" :key="p.value" :value="p.value">{{ p.label }}</option>
						</select>
						<div v-if="errors.provider" class="invalid-feedback">{{ errors.provider }}</div>
					</div>
				</div>

				<!-- Status -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Status</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Status</label>
						<select v-model="form.active" class="form-select">
							<option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
						</select>
					</div>
				</div>

				<div class="mt-4 d-flex justify-content-end gap-2">
					<button type="button" class="btn btn-danger"
						@click="router.visit(route('admin.visittype.list'))">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</AuthLayout>
</template>