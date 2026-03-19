<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import CustomImageUpload from "@/Components/CustomImageUpload.vue";

const props = defineProps({
	speciality: Object,
});
// form state (UI-only)
const form = useForm({
	id: props.speciality?.id || null,
	banner: props.speciality?.banner || '',
	name: props.speciality?.name || '',
	description: props.speciality?.description || '',
	is_active: props.speciality?.is_active || '',
});

const submitForm = () => {
	form.post(route('admin.specialities.store'));
};
</script>

<template>
	<AuthLayout title="Create Speciality" description="Create Speciality" heading="Create Speciality">
		<div class="container-fluid">
			<form @submit.prevent="submitForm">
				<div class="row">
					<!-- Banner uploader -->
					<div class="col-12 d-flex justify-content-center mb-3">
						<CustomImageUpload :form="form" field="banner" btn_text="Select Banner Image" />
					</div>

					<!-- Name -->
					<div class="col-12 mb-3">
						<label class="form-label">Name</label>
						<input v-model="form.name" type="text" class="form-control" />
					</div>

					<!-- Description -->
					<div class="col-12 mb-3">
						<label class="form-label">Description</label>
						<textarea rows="3" v-model="form.description" class="form-control"></textarea>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Status</label>
						<select v-model="form.is_active" class="form-select">
							<option value="">-- Select --</option>
							<option :value="1">Active</option>
							<option :value="0">Inactive</option>
						</select>
					</div>
				</div>

				<div class="mt-4 d-flex justify-content-end gap-2">
					<button type="button" class="btn btn-danger" @click="router.visit(route('admin.specialities.index'))">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</AuthLayout>
</template>