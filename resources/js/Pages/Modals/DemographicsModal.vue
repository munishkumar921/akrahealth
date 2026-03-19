<script setup>
import { ref, watch, onMounted } from 'vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import BaseFileInput from '@/Components/Common/Input/BaseFileInput.vue';
import { Country, State } from 'country-state-city';
import { Input } from 'postcss';

const props = defineProps({
    patient: Object,
});
const page = usePage();
const countries = Country.getAllCountries();
const states = ref([]);
 // Update the options arrays with proper value/label pairs
const genderOptions = ref([
    { value: 'Male', label: 'Male' },
    { value: 'Female', label: 'Female' },
    { value: 'Other', label: 'Other' }
]);

const maritalOptions = ref([
    { value: '1', label: 'Single' },
    { value: '2', label: 'Married' },
    { value: '3', label: 'Divorced' },
    { value: '4', label: 'Widowed' }
]);

// Update the form with proper initial values
const form = useForm({
    id: props.patient?.id || '',
    first_name: props.patient?.first_name?.split(' ')[0] || '',
    last_name: props.patient?.last_name?.split(' ')[0] || '',
    sex: props.patient?.sex || '',
    dob: props.patient?.dob || '',
    marital_status: props.patient?.marital_status || '',
    address_1: props.patient?.address_1 || '',
    address_2: props.patient?.address_2 || '',
    country: props.patient?.country || '',
    city: props.patient?.city || '',
    state: props.patient?.state || '',
    zip: props.patient?.zip || '',
    email: props.patient?.email || props.patient?.user?.email|| '',
    mobile: props.patient?.mobile || '',
    profile_photo: null,
});

const submit = () => {

    const roles = page?.props?.auth?.user?.roles?.map(role => role?.name) ?? [];

    const isPatient = roles.includes('Patient');
    const isDoctor = roles.includes('Doctor');

    if (isPatient) {
        form.post(route('patient.demographics.update', form.id), {
            onSuccess: () => {
                emit("submit", form); // ✅ no need for .value
                closeModal();
            }
        })
    }

    if (isDoctor) {
        form.post(route('doctor.demographics.update', form.id), {
            onSuccess: () => {
                emit("submit", form); // ✅ no need for .value
                closeModal();
            }
        })
    }

};
const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);

// Watch for personal country changes to populate personal states
watch(() => form.country, (newCountry) => {
    if (newCountry) {
        const selectedCountry = countries.find(country => country.name === newCountry);
        if (selectedCountry) {
            states.value = State.getStatesOfCountry(selectedCountry.isoCode);
        } else {
            states.value = [];
        }
    } else {
        states.value = [];
    }
    // Reset state when country changes
    form.state = '';
});

onMounted(() => {
    if (form.country) {
        const selectedCountry = countries.find(country => country.name === form.country);
        if (selectedCountry) {
            states.value = State.getStatesOfCountry(selectedCountry.isoCode);
        }
    }
});
</script>

<template>
    <form @submit.prevent="submit" class="p-4">
         <div class="row g-3">
            <!-- Profile Photo -->
            <div class="col-md-12">
                <BaseFileInput id="profile_photo" label="Profile Photo" accept="image/*" v-model="form.profile_photo"
                    :error="form.errors.profile_photo" />
            </div>

            <!-- Personal Information -->
            <div class="col-md-6">
                <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name"
                    :error="form.errors.first_name" required />
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name"
                    :error="form.errors.last_name" required />
            </div>

            <div class="col-md-6">
                <BaseDatePicker v-model="form.dob" label="Date of Brith" :error="form.errors.dob"
                    placeholder="Enter Dob"/>

             </div>
            <div class="col-md-6">
                <BaseSelect v-model="form.sex" label="Gender" :error="form.errors.gender" placeholder="Select Gender">
                    <option v-for="option in genderOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6">
                <BaseSelect v-model="form.marital_status" name="marital_status" label="Marital Status"
                    :options="maritalOptions" :error="form.errors.marital_status" placeholder="Select Marital Status">
                    <option v-for="option in maritalOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.email" type="email" label="Email" placeholder="Email"
                    :error="form.errors.email" :readonly="!!props.patient?.email || !!props.patient?.user?.email" required />

            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" :error="form.errors.mobile" />
            </div>
            <!-- Contact Information -->
            <div class="row">
                <div class="col-md-6">
                    <BaseInput v-model="form.address_1" label="Address 1" placeholder="1234 Main St"
                        :error="form.errors.address_1" required />
                </div>
                <div class="col-md-6">
                    <BaseInput v-model="form.address_2" label="Address 2" placeholder="Apartment, studio, or floor"
                        :error="form.errors.address_2" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <BaseSelect v-model="form.country" label="Country">
                        <option v-for="country in countries" :key="country" :value="country.name">
                            {{ country.name }}
                        </option>
                    </BaseSelect>
                </div>
                <div class="col-md-4">
                    <BaseInput v-model="form.city" label="City" :error="form.errors.city" />
                </div>
                  <div class="col-md-4">
                       <BaseSelect v-model="form.state" label="State">
                        <option v-for="state in states" :key="state.isoCode" :value="state.name">
                            {{ state.name }}
                        </option>
                    </BaseSelect>
                 </div>
                
                <div class="col-md-4">
                    <BaseInput v-model="form.zip" label="ZIP" placeholder="ZIP" :error="form.errors.zip" />
                </div>
            </div>

            <div class="col-12 text-end">
               
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
                 <button type="button" class="btn btn-danger me-2" @click="closeModal()">
                    Close
                </button>
            </div>
        </div>
    </form>
</template>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}
</style>