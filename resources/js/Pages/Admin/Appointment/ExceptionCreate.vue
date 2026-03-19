<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

/* --------- form state --------- */
const form = ref({
	day: "",
	start_time: "",
	end_time: "",
	title: "",
	reason: "",
	provider: "",
	active: true,
});

/* --------- dropdown options --------- */
const days = [
	{ label: "Monday", value: "Monday" },
	{ label: "Tuesday", value: "Tuesday" },
	{ label: "Wednesday", value: "Wednesday" },
	{ label: "Thursday", value: "Thursday" },
	{ label: "Friday", value: "Friday" },
	{ label: "Saturday", value: "Saturday" },
	{ label: "Sunday", value: "Sunday" },
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

	if (!form.value.day.trim()) {
		errors.value.day = "Day is required";
	}
	if (!form.value.title.trim()) {
		errors.value.title = "Title is required";
	}
	if (!form.value.start_time.trim()) {
		errors.value.start_time = "Start time is required";
	}
	if (!form.value.end_time.trim()) {
		errors.value.end_time = "End time is required";
	}
	if (!form.value.reason.trim()) {
		errors.value.reason = "Reason is required";
	}
	if (!form.value.provider.trim()) {
		errors.value.provider = "Provider is required";
	}

	return Object.keys(errors.value).length === 0;
};

/* --------- submit --------- */
const submit = () => {
	if (!validateForm()) {
		return;
	}

	console.log("Exception form:", { ...form.value });
	toast("Exception created successfully!");

	// Navigate back to exception list
	router.visit(route('admin.exception.list'));
};
</script>

<template>
	<AuthLayout title="Create Exception" description="Create Exception" heading="Create Exception">
		<div class="container-fluid">
			<form @submit.prevent="submit">
				<!-- Exception Information -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Exception Information</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Day <span class="text-danger">*</span></label>
						<select v-model="form.day" class="form-select" :class="{ 'is-invalid': errors.day }">
							<option value="" disabled>Select Day</option>
							<option v-for="d in days" :key="d.value" :value="d.value">{{ d.label }}</option>
						</select>
						<div v-if="errors.day" class="invalid-feedback">{{ errors.day }}</div>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Title <span class="text-danger">*</span></label>
						<input v-model="form.title" type="text" class="form-control"
							:class="{ 'is-invalid': errors.title }" placeholder="Enter exception title" />
						<div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
					</div>
				</div>

				<!-- Time Information -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Time Information</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Start Time <span class="text-danger">*</span></label>
						<input v-model="form.start_time" type="time" class="form-control"
							:class="{ 'is-invalid': errors.start_time }" />
						<div v-if="errors.start_time" class="invalid-feedback">{{ errors.start_time }}</div>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">End Time <span class="text-danger">*</span></label>
						<input v-model="form.end_time" type="time" class="form-control"
							:class="{ 'is-invalid': errors.end_time }" />
						<div v-if="errors.end_time" class="invalid-feedback">{{ errors.end_time }}</div>
					</div>
				</div>

				<!-- Reason and Provider -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Details</h6>
					</div>

					<div class="col-12 mb-3">
						<label class="form-label">Reason <span class="text-danger">*</span></label>
						<textarea v-model="form.reason" rows="3" class="form-control"
							:class="{ 'is-invalid': errors.reason }"
							placeholder="Enter reason for exception"></textarea>
						<div v-if="errors.reason" class="invalid-feedback">{{ errors.reason }}</div>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Provider <span class="text-danger">*</span></label>
						<select v-model="form.provider" class="form-select" :class="{ 'is-invalid': errors.provider }">
							<option value="" disabled>Select Provider</option>
							<option v-for="p in providers" :key="p.value" :value="p.value">{{ p.label }}</option>
						</select>
						<div v-if="errors.provider" class="invalid-feedback">{{ errors.provider }}</div>
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
						@click="router.visit(route('admin.exception.list'))">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</AuthLayout>
</template>