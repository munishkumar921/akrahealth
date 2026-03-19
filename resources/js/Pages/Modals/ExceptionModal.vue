<script setup>
import { useForm } from '@inertiajs/vue3';
import BaseSelect from '../../Components/Common/Input/BaseSelect.vue';
import BaseInput from '../../Components/Common/Input/BaseInput.vue';
import BaseDatePicker from '../../Components/Common/Input/BaseDatePicker.vue';

  const props = defineProps({
 	newException: Object,
	doctors: Object
});

const emit = defineEmits(['close','submit']);
 
const form = useForm({
	id: '',
	exception_date: '',
	title: '',
	start_time: '',
	end_time: '',
	reason: '',
	doctor_id: '',
	is_active: 1,
});
const submitForm = () => {
     form.post(route('admin.provider-exception.store'), {
		 onSuccess: () => {
			 closeModal();
			 form.reset();
		 },
		 onError: (error) => {
			 console.log(error);
		 }
	 });	
  };
const closeModal = () => {
 	emit('close');
 };
const update = (data) => {
	form.id = data.id;
 	form.exception_date = data.exception_date;
	form.title = data.title;
	form.start_time = data.start_time;
	form.end_time = data.end_time;
	form.reason = data.reason;
	form.doctor_id = data.doctor_id;
	form.is_active = data.is_active ? 1 : 0;
};
// ✅ Expose methods to parent
defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
	 <form @submit.prevent="submitForm">
						<div class="row">
							<div class="col-md-6 mb-3">
								  <BaseDatePicker type="date" v-model="form.exception_date" label="Date"  placeholder="Select date" :error="form.errors.exception_date" required  />
							</div>
							<div class="col-md-6 mb-3">
								 
								<BaseInput v-model="form.title" label="Title"  placeholder="Enter exception title" :error="form.errors.title" />
							</div>
							 
						</div>
						
						<div class="row">
							<div class="col-md-6 mb-3">
								<BaseDatePicker type="time" v-model="form.start_time" label="Start Time" :error="form.errors.start_time" required />
   							</div>
							<div class="col-md-6 mb-3">
								<BaseDatePicker type="time" v-model="form.end_time" label="End Time" :error="form.errors.end_time" />
 							</div>
						</div>
						
						<div class="mb-3">
							 <BaseInput type="textarea" v-model="form.reason" label="Reason" placeholder="Enter reason for exception" :error="form.errors.reason" required />
						</div>
						
						<div class="row">
							
							<div class="col-md-6 mb-3">
								 <BaseSelect v-model="form.doctor_id" label="Provider" placeholder="Select Provider" :error="form.errors.doctor_id" required>
									<option :value="row.id" v-for="row in doctors" :key="row.id">
										{{ row.name }}  {{ (row.specialty) ? ' - ' : '' }} {{ row.specialty }}
										 </option>
								</BaseSelect>
							</div>
							<div class="col-md-6 mb-3">
								 <BaseSelect v-model="form.is_active" label="Status" placeholder="Select Status" :error="form.errors.is_active">
									<option :value="1">Active</option>
									<option :value="0">Inactive</option>
								</BaseSelect>
							</div>
						</div>
						<div class="d-flex justify-content-end">
							<button type="button" class="btn btn-danger me-2" @click="closeModal">Cancel</button>
							<button type="submit" class="btn btn-primary" :disabled="form.processing">
								<span v-if="form.processing">Saving...</span>
								<span v-else>Save Exception</span>
							</button>
						</div>
  		</form>
</template>
 