<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import InputError from "@/Components/InputError.vue";
import {useForm } from "@inertiajs/vue3";
import { watch, computed } from "vue";
 
const props = defineProps({
    doctors: Object,

});
const emit = defineEmits(["close", "submit"]);

const form = useForm({
    id: '',
    description: '',
    name: '',
    colors: '',
    duration: '',
    currency: '',
    price: '',
    hospital_id: '',
    doctor_id:'',
	is_active: '',

});
// Computed options for dropdowns
const durationOptions = [
    { value: '15', label: '15 Minutes' },
    { value: '30', label: '30 Minutes' },
    { value: '45', label: '45 Minutes' },
    { value: '60', label: '60 Minutes' },
    { value: '90', label: '90 Minutes' },
    { value: '120', label: '120 Minutes' },
];

const currencyOptions = [
    { value: 'USD', label: 'USD' },
    { value: 'INR', label: 'INR' },
];
 
 
const closeModal = () => {
    emit("close");
};
const submit = () => {
    form.post(route('admin.visit-types.store'), {
        onSuccess: () => closeModal(),
    });
};

// Auto-fill hospital_id when doctor_id changes
watch(() => form.doctor_id, (newDoctorId) => {
    if (newDoctorId) {
        const selectedDoctor = props.doctors.find(doctor => doctor.id === newDoctorId);
        if (selectedDoctor && selectedDoctor.hospital_id) {
            form.hospital_id = selectedDoctor.hospital_id;
        }
    } else {
        form.hospital_id = '';
    }
});

defineExpose({
     resetForm: () => form.reset(),
     update: (data) => {
         form.id = data.id;
         form.name = data.name;
         form.description = data.description;
         form.colors = data.colors;
         form.duration = data.duration;
         form.currency = data.currency;
         form.price = data.price;
         form.hospital_id = data.hospital_id;
         form.doctor_id = data.doctor_id;
         form.is_active = data.is_active;
     },
});
</script>
<template>
<form @submit.prevent="submit">
    <div class="row">
        <!-- Visit Type -->
        <div class="col-12 mb-3">
             <BaseInput v-model="form.name" type="text" label="Visit Type" placeholder="Visit Type" />
             <InputError :message="form.errors.name" />

        </div>
        <div class="col-12 mb-3">
             <BaseInput v-model="form.description" type="text" label="Description" placeholder="Description" />
             <InputError :message="form.errors.description" />

        </div>
        <div class="col-md-6 mb-3">
             <BaseSelect v-model="form.duration" label="Duration" required placeholder="Select Duration">
               <option v-for="duration in durationOptions" :key="duration.value" :value="duration.value">
                    {{ duration.label }}
                </option>
            </BaseSelect>
            <InputError :message="form.errors.duration" />
        </div>
        
             <div class="col-md-6 mb-3">
                 <BaseSelect v-model="form.currency" label="Currency" placeholder="Select Currency" >
                    <option v-for="currency in currencyOptions" :key="currency.value" :value="currency.value">
                        {{ currency.label }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.currency" />
            </div>
            <div class="col-md-6 mb-3">
             <BaseInput v-model="form.price" type="number" label="Price" required />
            <InputError :message="form.errors.price" />
        </div>
                <div class="col-md-6 mb-3">
                 <BaseSelect v-model="form.doctor_id" label="Provider" placeholder="Select Provider" >
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor?.id">{{ doctor.user?.name }}</option>
                </BaseSelect>
 
            </div>

        <!-- Status -->
        <div class="col-12 mb-3">
             <BaseSelect v-model="form.is_active" label="Status" placeholder="Select Status" >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </BaseSelect>
            <InputError :message="form.errors.is_active" />
        </div>	
    </div>

    <div class="mt-4 d-flex justify-content-end gap-2">
       
        <button type="submit" class="btn btn-primary" :disabled="form.processing">
            Save
        </button>
         <button type="button" class="btn btn-danger" @click="closeModal" data-dismiss="modal">
        Close
        </button>
    </div>
</form>
</template>