<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { router, useForm, Link } from "@inertiajs/vue3";
import CustomImageUpload from "@/Components/CustomImageUpload.vue";

const props = defineProps({
	pharmacy: Object,
	states:Object,
	countries:Object
	
});

const form = useForm({

	/* personal detail */
	id: props.pharmacy?.id || null,
	user_id: props.pharmacy?.user?.id || null,
	name: props.pharmacy?.user?.name,
	email: props.pharmacy?.user?.email || '',
	mobile: props.pharmacy?.user?.mobile || '',
	password: '',
	password_confirmation: '',
	profile_photo: props.pharmacy?.user?.profile_pic || '',

	/* pharmacy detail */
	pharmacy_name: props.pharmacy?.name,
	license_number: props.pharmacy?.license_number || '',
	address: props.pharmacy?.address,
	city: props.pharmacy?.city,
	country_id: props.pharmacy?.country_id || '',
	state_id: props.pharmacy?.state_id || '',
	pharmacy_mobile: props.pharmacy?.mobile || '',
	pharmacy_email: props.pharmacy?.email || '',
	pincode: props.pharmacy?.pincode || '',
	opening_time: props.pharmacy?.opening_time || '',
	closing_time: props.pharmacy?.closing_time || '',
	sample_pickup_supported: props.pharmacy?.sample_pickup_supported || false,
	is_active: props.pharmacy?.is_active || 0,
	is_verified: props.pharmacy?.is_verified || 0,
	about: props.pharmacy?.about,
	banner: props.pharmacy?.banner || '',
});

const submitForm = () => {
	form.post(route('admin.pharmacies.store'), {
		onFinish: () => {
			//
		},
	});
};

</script>

<template>
	<AuthLayout title="Pharmacy Lab" description="Create pharmacy" heading="Create pharmacy">
		<div class="container-fluid">
			<form @submit.prevent="submitForm">
				<!-- Contact Person Detail -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Contact Person Detail</h6>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Name </label>
						<input v-model="form.name" type="text" class="form-control" placeholder="en" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Email</label>
						<input v-model="form.email" type="email" class="form-control"
							placeholder="admin@gmail.com" />
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Password</label>
						<input v-model="form.password" type="password" class="form-control" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Confirm Password</label>
						<input v-model="form.password_confirmation" type="password" class="form-control" />
					</div>

					<div class="col-md-6 mb-4">
						<label class="form-label">Mobile</label>
						<input v-model="form.mobile" type="text" class="form-control" />
					</div>
				</div>

				<!-- Pharmacy Detail -->
				<div class="row">
					<div class="col-12">
						<h6 class="text-xl fw-semibold mb-3">Pharmacy Detail</h6>
					</div>

					<!-- centered avatar -->
					<div class="col-12 d-flex justify-content-center mb-4">
						<CustomImageUpload :form="form" field="banner" btn_text="Select Banner Image" />
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Pharmacy name </label>
						<input v-model="form.pharmacy_name" type="text" class="form-control" placeholder="en" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">License number</label>
						<input v-model="form.license_number" type="text" class="form-control" />
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Pharmacy mobile</label>
						<input v-model="form.pharmacy_mobile" type="text" class="form-control" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Pharmacy email</label>
						<input v-model="form.pharmacy_email" type="email" class="form-control" />
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Address</label>
						<input v-model="form.address" type="text" class="form-control" placeholder="en" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">City</label>
						<input v-model="form.city" type="text" class="form-control" placeholder="en" />
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Country</label>
						<select v-model="form.country" class="form-select">
							<option value="" disabled>Select Country</option>
							<option v-for="c in countries" :key="c" :value="c.name">{{ c.name }}</option>
						</select>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">State</label>
						<select v-model="form.state" class="form-select">
							<option value="" disabled>Select State</option>
							<option v-for="s in states" :key="s" :value="s.name">{{ s.name }}</option>
						</select>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Pincode</label>
						<input v-model="form.pincode" type="text" class="form-control" />
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Opening time</label>
						<div class="input-group">
							<input v-model="form.opening_time" type="time" class="form-control" />
						</div>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Closing time</label>
						<div class="input-group">
							<input v-model="form.closing_time" type="time" class="form-control" />
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Status</label>
						<select v-model="form.is_active" class="form-select">
							<option value="1">Active</option>
							<option value="0">In active</option>
						</select>
					</div>

					<div class="col-md-6 mb-3">
						<label class="form-label">Verified?</label>
						<select v-model="form.is_verified" class="form-select">
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>
					<div class="col-md-6 mb-3">
						<label class="form-label">Sample pickup available?</label>
						<select v-model="form.sample_pickup_supported" class="form-select">
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>

					<div class="col-12 mb-2">
						<label class="form-label">More about</label>
						<textarea v-model="form.about" rows="3" class="form-control" placeholder=""></textarea>
					</div>
				</div>

				<div class="mt-4 d-flex justify-content-end gap-2">
					<button type="button" class="btn btn-danger"
						@click="router.visit(route('admin.pharmacies.index'))">Cancel</button>
					<button type="submit" class="btn btn-primary">Save</button>
				</div>
			</form>
		</div>
	</AuthLayout>
</template>