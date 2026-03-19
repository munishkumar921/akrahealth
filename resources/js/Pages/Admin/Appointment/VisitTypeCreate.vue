<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
const props = defineProps({
	visittype: Object,
});
const form = useForm({
	id: props.visittype?.id || null,
	name: props.visittype?.name || '',
	description: props.visittype?.description || '',
	colors: props.visittype?.colors || '',
	is_active: props.visittype?.is_active ?? 1,
});
const submitForm = () => {
	form.post(route('admin.visit-types.store'));
};
</script>

<template>
	<AuthLayout title="Create Visit Type" description="Create Visit Type" heading="Create Visit Type">
		<div class="container-fluid">
			<form @submit.prevent="submitForm">
				<div class="row">
					<!-- Visit Type -->
					<div class="col-12 mb-3">
						<label class="form-label">Visit Type</label>
						<input v-model="form.name" type="text" class="form-control" />
					</div>

					<!-- Description -->
					<div class="col-12 mb-3">
						<label class="form-label">Description</label>
						<textarea rows="3" v-model="form.description" class="form-control"></textarea>
					</div>

					<!-- Color -->
					<div class="col-md-6 mb-3">
						<label class="form-label">Color</label>
						<input v-model="form.colors" type="text" class="form-control" />
					</div>

					<!-- Status -->
					<div class="col-md-6 mb-3">
						<label class="form-label">Status</label>
						<select v-model="form.is_active" class="form-select">
							<option :value="1">Active</option>
							<option :value="0">Inactive</option>
						</select>
					</div>
				</div>
				

				<div class="mt-4 d-flex justify-content-end gap-2">
					<button type="button" class="btn btn-danger"
						@click="router.visit(route('admin.visit-types.index'))">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</AuthLayout>
</template>