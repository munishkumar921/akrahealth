<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
	statuses: { type: Array, default: () => [{ label: "Active", value: 1 }, { label: "Inactive", value: 0 }] },
	user: Object,
	permissions: Array,
	formPermissions: {
		type: Array,
		default: () => [],
	},
});

const form = useForm({
	id: props.user?.id || '',
	name: props.user?.name || '',
	email: props.user?.email || '',
	mobile: props.user?.mobile || '',
	password: "",
	password_confirmation: "",
	is_active: props.user?.is_active || 0,
	profile_photo: null,
	permissions: [...props.formPermissions],
	_method: props.user?.id ? 'PUT' : 'POST',
});

const submit = () => {
	if (props.user?.id) {
		// Update existing admin
		form.post(route('admin.admins.update', props.user.id), {
			forceFormData: true,
		});
	} else {
		// Create new admin
		form.post(route('admin.admins.store'), {
			forceFormData: true,
		});
	}
};
const objectUrl = ref(null);
const fileInput = ref(null);
const fileError = ref("");
const clearInput = () => {
	if (fileInput.value) fileInput.value.value = null;
	if (typeof form?.value !== "undefined") form.value.profile_photo = null;
	else if (typeof form !== "undefined") form.profile_photo = null;
};

const removeImage = () => {
	clearInput();
	if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
	objectUrl.value = null;
	previewUrl.value = null;
	fileError.value = "";
};

const photoInput = ref(null);
const previewUrl = ref(props?.user?.profile_photo_url);
const onFileChange = (e) => {

	const photo = photoInput.value.files[0];
	if (!photo) return;
	const reader = new FileReader();
	reader.onload = (e) => {
		previewUrl.value = e.target.result;
	};
	reader.readAsDataURL(photo);
	form.profile_photo = photo;
};
</script>

<template>
	<AuthLayout title="Create Admin" description="Create Admin" heading="Create Admin">
		<div class="container-fluid">
			<div class="">
				<div class="">
					<form @submit.prevent="submit">
						<div class="row">
							<h2 class="text-xl font-semibold">Create Admin</h2>
							<div class="col-md-12">
								<div class="col-md-12">
									<div class="d-flex flex-column align-items-center">
										<div class="position-relative mb-2 rounded-circle border"
											style="width: 100px; height: 100px; overflow: hidden;">
											<img :src="previewUrl || '/images/avatar.webp'" alt="Profile"
												style="width: 100%; height: 100%; object-fit: cover;" />

											<label for="profile-upload"
												class="btn btn-light position-absolute bottom-0 end-0 m-1 rounded-circle shadow-sm"
												style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
												<i class="bi bi-pencil"></i>
											</label>
										</div>

										<input id="profile-upload" type="file" accept="image/png,image/jpeg,image/webp"
											class="d-none" ref="photoInput" @change="onFileChange" />

										<div class="text-muted small mb-2">PNG, JPG, or WEBP up to 2 MB.</div>
										<div class="text-danger small" v-if="fileError">{{ fileError }}</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 mb-3">
										<label class="form-label">Name</label>
										<input v-model="form.name" type="text" class="form-control" required />
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Email</label>
										<input v-model="form.email" type="email" class="form-control" required />
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Password</label>
										<input v-model="form.password" type="password" class="form-control"
											:required="route().current('admin.admins.cresate')" />
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Confirm Password</label>
										<input v-model="form.password_confirmation" type="password" class="form-control"
											:required="route().current('admin.admins.cresate')" />
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Mobile</label>
										<input v-model="form.mobile" type="text" class="form-control" />
									</div>

									<div class="col-md-6 mb-3">
										<label class="form-label">Status</label>
										<select v-model="form.is_active" class="form-select">
											<option v-for="s in props.statuses" :key="s.value" :value="s.value">
												{{ s.label }}
											</option>
										</select>
									</div>
								</div>

								<div class="mt-4">
									<h5 class="mb-3 text-xl font-semibold">Assign permissions</h5>
									<div class="row">
										<div class="col-md-4 mb-2" v-for="perm in props.permissions" :key="perm">
											<div class="form-check">
												<input class="form-check-input cursor-pointer" type="checkbox" :id="`perm-${perm}`"
													:value="perm" v-model="form.permissions" />
												<label class="form-check-label cursor-pointer" :for="`perm-${perm}`">
													{{ perm }}
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>


						</div>

						<div class="mt-4 d-flex justify-content-end gap-2">
							<button type="button" class="btn btn-danger" @click="router.visit(route('admin.admins.index'))">
								Cancel
							</button>
							<button type="submit" class="btn btn-primary">
								Save
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</AuthLayout>
</template>
