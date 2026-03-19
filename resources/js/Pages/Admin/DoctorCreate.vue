<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import CustomImageUpload from "@/Components/CustomImageUpload.vue";
import MultiSelect from 'primevue/multiselect';

const props = defineProps({
	doctor: Object,
	specialities: Object,
	selected_specialities: {
		type: Array,
		default: () => [],
	},
});

const form = useForm({

	/* personal detail */
	id: props.doctor?.user?.id || null,
	name: props.doctor?.name??props.doctor?.user?.name,
	email: props.doctor?.user?.email || '',
	mobile: props.doctor?.user?.mobile || '',
	password: '',
	password_confirmation: '',
	profile_photo_path: props.doctor?.user?.profile_photo_path || null,

	/* professional detail */
	registration_number: props.doctor?.registration_number || '',
	qualification: props.doctor?.qualification || '',
	experience: props.doctor?.experience || '',
	specialities: [...props.selected_specialities],
	consultation_fee: props.doctor?.consultation_fee || '',
	dea: props.doctor?.dea || '',
	about: props.doctor?.about,
	certification: props.doctor?.certificate_doc || '',
	government_id_proof: props.doctor?.id_doc || '',
	old_certification: props.doctor?.certification || '',
	old_government_id_proof: props.doctor?.government_id_proof || '',
	appointment_slot_duration: props.doctor?.appointment_slot_duration || '',

	/* hospital detail */
	hospital_name: props.doctor?.hospital?.name,
	hospital_address: props.doctor?.hospital?.address,
	hospital_phone: props.doctor?.hospital?.phone || '',
	sunday: props.doctor?.hospital?.timing?.sunday || '',
	monday: Boolean(Number(props.doctor?.hospital?.timings?.monday)) ? true : false,
	tuesday: Boolean(Number(props.doctor?.hospital?.timings?.tuesday)) ? true : false,
	wednesday: Boolean(Number(props.doctor?.hospital?.timings?.wednesday)) ? true : false,
	thursday: Boolean(Number(props.doctor?.hospital?.timings?.thursday)) ? true : false,
	friday: Boolean(Number(props.doctor?.hospital?.timings?.friday)) ? true : false,
	saturday: Boolean(Number(props.doctor?.hospital?.timings?.saturday)) ? true : false,
	morning_slot: Boolean(Number(props.doctor?.hospital?.timings?.morning_slot)) ? true : false,
	afternoon_slot: Boolean(Number(props.doctor?.hospital?.timings?.afternoon_slot)) ? true : false,
	evening_slot: Boolean(Number(props.doctor?.hospital?.timings?.evening_slot)) ? true : false,
	night_slot: Boolean(Number(props.doctor?.hospital?.timings?.night_slot)) ? true : false,
	is_active: props.doctor?.user?.is_active || 0,
	is_verified: props.doctor?.is_verified || 0,
});

const submit = () => {
	if (props.user?.id) {
		// Update existing admin
		form.post(route('admin.doctors.update', props.user.id), {
			forceFormData: true,
		});
	} else {
		// Create new admin
		form.post(route('admin.doctors.store'), {
			forceFormData: true,
		});
	}
};
const previewUrl = ref(props.doctor?.user?.profile_photo_path || null);

const onFileChange = (e) => {
    const photo = e.target.files[0];
    if (!photo) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(photo);
    form.profile_photo_path = photo;
};
</script>

<template>
	<AuthLayout title="Create Doctor" description="Create Doctor" heading="Create Doctor">
		<div class="container-fluid">
			<div class="">
				<div class="">
					<form @submit.prevent="submit">
						<div class="row">
							<!-- avatar -->
							<div class="d-flex flex-column align-items-center">
								<div class="flex justify-center" style="width: 100px; height: 100px;">
									<CustomImageUpload :form="form" field="profile_photo_path"
										:old_banner="props.doctor?.user?.profile_photo_url || '/images/avatar.webp'"
										btn_text="Select Banner Image" />
								</div>

								<div class="text-muted small mb-2">PNG, JPG, or WEBP up to 2 MB.</div>
								<div class="text-danger small" v-if="fileError">{{ fileError }}</div>
							</div>

							<!-- Personal Detail -->
							<div class="col-12">
								<h6 class="text-xl fw-semibold mb-4">Personal Detail</h6>
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Name</label>
								<input v-model="form.name" type="text" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Email</label>
								<input v-model="form.email" type="email" class="form-control" />
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Password</label>
								<input v-model="form.password" type="password" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Confirm Password</label>
								<input v-model="form.password_confirmation" type="password" class="form-control" />
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Mobile</label>
								<input v-model="form.mobile" type="number" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Status</label>
								<select v-model="form.is_active" class="form-select">
									<option :value="1">Active</option>
									<option :value="0">In active</option>
								</select>
							</div>

							<!-- Professional Detail -->
							<div class="col-12 mt-2">
								<h6 class="text-xl fw-semibold mb-4">Professional Detail</h6>
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Medical license number</label>
								<input v-model="form.registration_number" type="text" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Degrees</label>
								<input v-model="form.qualification" type="text" class="form-control" />
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Experience</label>
								<input v-model="form.experience" type="text" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Consultation fee</label>
								<input v-model="form.consultation_fee" type="number" class="form-control" />
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Select specialties</label><br>
								<MultiSelect v-model="form.specialities" :options="specialities" optionLabel="name"
									filter placeholder="Select specialities" :maxSelectedLabels="3"
									class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full h-full items-center" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Appointment slot duration (minutes)</label>
								<input v-model="form.appointment_slot_duration" type="number" min="0"
									class="form-control" />
							</div>

							<!-- Uploads row -->
							<div class="col-md-6 mb-3">
								<label class="form-label d-block">Upload registration certificate</label>
								<div style="width: 80px; height: 80px;">
									<CustomImageUpload :form="form" field="certification" :show_label="true"
										btn_text="" />
								</div>
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label d-block">Upload PAN or Aadhaar</label>
								<div style="width: 80px; height: 80px;">
									<CustomImageUpload :form="form" field="government_id_proof" :show_label="true"
										btn_text="" />
								</div>
							</div>

							<!-- Clinic Detail -->
							<div class="col-12 mt-2">
								<h6 class="text-xl fw-semibold mb-4">Clinic Detail</h6>
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Clinic name</label>
								<input v-model="form.hospital_name" type="text" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Clinic address</label>
								<input v-model="form.hospital_address" type="text" class="form-control" />
							</div>

							<div class="col-md-6 mb-3">
								<label class="form-label">Clinic mobile</label>
								<input v-model="form.hospital_phone" type="number" class="form-control" />
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Verified?</label>
								<select v-model="form.is_verified" class="form-select">
									<option :value="0">No</option>
									<option :value="1">Yes</option>
								</select>
							</div>

							<!-- Select days -->
							<div class="col-12 mt-2">
								<h6 class="text-xl fw-semibold mb-4">Select days</h6>
							</div>
							<div class="col-12 mb-2">
								<div class="row">
									<div class="flex justify-between">

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.sunday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Sunday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.monday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Monday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.tuesday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Tuesday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.wednesday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Wednesday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.thursday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Thursday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.friday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Friday</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.saturday"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Saturday</span>
										</label>
									</div>
								</div>
							</div>

							<!-- Select timings -->
							<div class="col-12 mt-2">
								<h6 class="text-xl fw-semibold mb-4">Select timings</h6>
							</div>
							<div class="col-12 mb-2">
								<div class="row">
									<div class="flex justify-between">
										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.morning_slot"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Morning 9:00 AM - 1:00PM</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.afternoon_slot"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Afternoon 2:00PM - 5:00PM</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.evening_slot"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Evening 5:00PM - 8:00PM</span>
										</label>

										<label
											class="flex items-center space-x-2 border p-2 rounded shadow-sm cursor-pointer">
											<input type="checkbox" :value="1" v-model="form.night_slot"
												class="form-checkbox rounded h-5 w-5" />
											<span class="capitalize">Night 8:00PM - 10:00PM</span>
										</label>
									</div>
								</div>
							</div>

							<!-- More about -->
							<div class="col-12 mt-2">
								<h6 class="text-xl fw-semibold mb-4">More about</h6>
							</div>
							<div class="col-12">
								<textarea v-model="form.about" rows="3" class="form-control" placeholder=""></textarea>
							</div>
						</div>

						<div class="mt-4 d-flex justify-content-end gap-2">
							<button type="button" class="btn btn-danger"
								@click="router.visit(route('admin.doctors.index'))">Cancel</button>
							<button type="submit" class="btn btn-primary">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</AuthLayout>
</template>