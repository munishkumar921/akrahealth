<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { Country, State } from 'country-state-city';
import InputError from "@/Components/InputError.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";

const props = defineProps({
     doctors: {
        type: Array,
        default: () => [],
     },
});

const emit = defineEmits(["close", "submit"]);
 
const countries = Country.getAllCountries();
const personalStates = ref([]);
const isProcessing=ref(false);


const form = useForm({
    id: "",
    doctor_id: "",
    user_id:"",
    first_name: "",
    last_name: "",
    sex: "",
    email: "",
    mobile: "",
    is_active: 1,
    street_address1: "",
    street_address2: "",
    city: "",
    state: "",
    country: "",
    zip: "",
    banner: "",
    password: "",
    password_confirmation: "",

 
});

// Method to update form with patient data (for editing)
const update = (patient) => {
        form.id = patient?.id || patient.id || "";
        form.doctor_id = patient.doctor_patients?.[0]?.doctor_id || patient.doctorPatients?.[0]?.doctor_id || "";
        form.user_id = patient?.user_id || patient.user_id || "";
        form.first_name = patient?.first_name || patient.first_name || "";
        form.last_name = patient?.last_name || patient.last_name || "";
        form.sex = patient?.sex || patient.sex || "";
        form.email = patient?.email || patient.email || "";
        form.mobile = patient?.mobile || patient.mobile || "";
        form.is_active = patient.is_active??1;
        
        // Address info
        form.street_address1 = patient?.address_1 || "";
        form.street_address2 = patient?.address_2 || "";
        form.city = patient?.city || "";
        form.state = patient?.state || "";
        form.country = patient?.country || "";
        form.zip = patient?.zip || "";
         
        // Populate states if country is set
        if (form.country) {
            const selectedCountry = countries.find(c => c.name === form.country);
            if (selectedCountry) {
                personalStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
            }
        }
    };


// Handle country change to populate states
const handleCountryChange = (val) => {
    form.country = val;
    form.state = '';
    if (val) {
        const selectedCountry = countries.find(country => country.name === val);
        if (selectedCountry) {
            personalStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
        } else {
            personalStates.value = [];
        }
    } else {
        personalStates.value = [];
    }
};


const randomPassword = (length = 10) => {
    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const all = upper + lower + numbers;

    const result = [
        upper[Math.floor(Math.random() * upper.length)],
        lower[Math.floor(Math.random() * lower.length)],
        numbers[Math.floor(Math.random() * numbers.length)],
    ];

    while (result.length < length) {
        result.push(all[Math.floor(Math.random() * all.length)]);
    }

    return result.sort(() => 0.5 - Math.random()).join('');
};

const submitForm = () => {
    isProcessing.value = true;

    const isEdit = !!form.id;
    const password = randomPassword();
    if (!isEdit) {
    form.password = password;
    form.password_confirmation = password;
    }
      form.post(route("admin.patients.store"), {
        onSuccess: () => {
            isProcessing.value = false;
            emit("submit");
            closeModal();
        },
        onError: () => {
            isProcessing.value = false;
        }
    });
};


const closeModal = () => {
    emit("close");
    form.reset();
 };

// Expose the update method to parent component
 defineExpose({
  update,
  resetForm: () => form.reset(),
});

</script>

<template>
     
    <div class="modal-body">
        <form @submit.prevent="submitForm">
            <div class="row g-3">
                <!-- First Name -->
                <div class="col-md-6 ">
                      <BaseInput v-model="form.first_name"  label="First Name" required placeholder="First Name" :error="form.errors.first_name"/>
                  </div>

                <!-- Last Name -->
                <div class="col-md-6 ">
                     <BaseInput v-model="form.last_name"  label="Last Name" required placeholder="Last Name"  :error="form.errors.last_name"/>
                  </div>

                <!-- Sex -->
                <div class="col-md-6 ">
                    <BaseSelect v-model="form.sex" label="Gender" type="select" placeholder="Select Gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </BaseSelect>
                     
                    <InputError :message="form.errors.sex"/>
                  </div>

                <!-- Email -->
                <div class="col-md-6 ">
                     <BaseInput v-model="form.email" label="Email" type="email" placeholder="Email" required :error="form.errors.email" /> 
                 </div>

                <!-- Mobile -->
                <div class="col-md-6 ">
                   <BaseInput v-model="form.mobile" label="Mobile" type="text" placeholder="Mobile" required :error="form.errors.mobile" />
                 </div>

                <!-- Doctor -->
                 <div class="col-md-6 ">
                    <BaseSelect v-model="form.doctor_id" label="Doctor" type="select" placeholder="Select Provider" :error="form.errors.doctor_id">
                        <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{ doctor.name }}</option>
                    </BaseSelect>
                </div>

                <!-- Street Address 1 -->
                <div class="col-md-6 ">
                    <BaseInput v-model="form.street_address1" label="Street Address 1" type="text" placeholder="Street Address 1" required :error="form.errors.street_address1" />
                </div>

                <!-- Street Address 2 -->
                <div class="col-md-6 ">
                    <BaseInput v-model="form.street_address2" label="Street Address 2" type="text" placeholder="Street Address 2" :error="form.errors.street_address2" />
                </div>

                <!-- City -->
                <div class="col-md-6 ">
                    <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required :error="form.errors.city" />
                </div>

                <!-- Country -->
                <div class="col-md-6 ">
                    <BaseSelect :modelValue="form.country" @update:modelValue="handleCountryChange" label="Country" type="select" required placeholder="Select Country" :error="form.errors.country">
                         <option value="">Select Country</option>
                        <template v-for="country in countries" :key="country.isoCode">
                        <option :value="country.name">{{ country.name }}</option>
                        </template>
                    </BaseSelect>
                 </div>
                <!-- State -->
                <div class="col-md-6 ">
                    <BaseSelect v-model="form.state" label="State" type="select"   placeholder="Select State" :error="form.errors.state">
                         <option value="">Select State</option>
                         <template v-for="state in personalStates" :key="state.isoCode">
                         <option :value="state.name">{{ state.name }}</option>
                        </template>
                     </BaseSelect>
                 </div>
                <!-- Zip Code -->
                <div class="col-md-6 ">
                    <BaseInput v-model="form.zip" label="Zip Code" type="text" placeholder="Zip Code" required :error="form.errors.zip" />
                 </div>
                 <!-- Status -->
                <div class="col-md-6 ">
                    <BaseSelect v-model="form.is_active" label="Status" type="select" required placeholder="Select Status" :error="form.errors.status">
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                    </BaseSelect>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary" :disabled="isProcessing">{{  isProcessing? 'Saving...' : 'Submit' }}</button>
                   <button type="button" class="btn btn-danger" @click="closeModal" :disabled="isProcessing">Cancel</button>
            </div>
        </form>
    </div>
 </template>
<style scoped>
.profile-photo-preview {
    transition: all 0.3s ease;
}

.profile-photo-preview:hover {
    border-color: var(--bs-primary) !important;
    transform: scale(1.05);
}
</style>