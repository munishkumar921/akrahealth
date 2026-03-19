<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch, nextTick } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseFileInput from "@/Components/Common/Input/BaseFileInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { Country, State } from 'country-state-city';

const emit = defineEmits(["close", "submit"]);

 const isUpdating = ref(false);

const form = useForm({
    id: null,
    user_id: null,
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    password: '',
    pharmacy_name: '',
    license_number: '',
    city: '',
    street_address1: '',
    street_address2: '',
    state: '',
    country: '',
    pharmacy_mobile: '',
    pharmacy_email: '',
    zip: '',
    opening_time: '',
    closing_time: '',
    is_active: 1,
    is_verified: 0,
    about: '',
    banner: '',
    website: '',
});

const countries = Country.getAllCountries();
const personalStates = ref([]);

watch(() => form.country, (newCountry) => {
    if (newCountry) {
        const selectedCountry = countries.find(country => country.name === newCountry);
        if (selectedCountry) {
            personalStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
        } else {
            personalStates.value = [];
        }
    } else {
        personalStates.value = [];
    }
    if (!isUpdating.value) {
        form.state = '';
    }
});
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
const password = randomPassword();
const submitForm = () => {
    if (!form.id) {
         form.password = password;
    }
        form.post(route('admin.pharmacies.store'), {
            onSuccess: () => {
                 emit("submit");
            },
            onError: () => {
             }
        });
};

const closeModal = () => {
    emit("close");
    form.reset();
 };

const update = (pharmacy) => {
    isUpdating.value = true;
    form.id = pharmacy.id;
    form.user_id = pharmacy.user_id;
    
    form.first_name = pharmacy.user_name ? pharmacy.user_name.split(' ')[0] : '';
    form.last_name = pharmacy.user_name ? pharmacy.user_name.split(' ').slice(1).join(' ') : '';
    form.email = pharmacy.user_email || pharmacy.email;
    form.mobile = pharmacy.user_mobile || pharmacy.mobile;

    form.pharmacy_name = pharmacy.name;
    form.license_number = pharmacy.license_number;
    
    form.street_address1 = pharmacy.address?.street_address1 || pharmacy.address || '';
    form.street_address2 = pharmacy.address?.street_address2 || pharmacy.street_address2 || '';
    form.city = pharmacy.address?.city || pharmacy.city || '';
    form.country = pharmacy.country?.name || pharmacy.address?.country || pharmacy.country || '';
    form.state = pharmacy.state?.name || pharmacy.address?.state || pharmacy.state || '';
    form.zip = pharmacy.address?.zip || pharmacy.pincode || '';

    form.pharmacy_mobile = pharmacy.mobile;
    form.pharmacy_email = pharmacy.email;
    
    form.opening_time = pharmacy.opening_time ||'';
    form.closing_time = pharmacy.closing_time ||'';
    
    form.is_active = pharmacy.is_active;
    form.is_verified = pharmacy.is_verified;
    form.about = pharmacy.about;
    form.banner = pharmacy.banner;
    form.website = pharmacy.website || '';

    if (form.country) {
        const selectedCountry = countries.find(c => c.name === form.country);
        if (selectedCountry) {
            personalStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
        }
    }

    if (form.state) {
        const selectedState = personalStates.value.find(s => s.name === form.state);
        if (selectedState) {
            form.state = selectedState.name;
        }
    }
    if (pharmacy.banner) {
        photoPreview.value = pharmacy.banner_url;
    } else {
        photoPreview.value = null;
    }

    nextTick(() => {
        isUpdating.value = false;
    });
};
const photoPreview = ref(null);
 
// Handle file upload
const onChangeFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.banner = file;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
 
defineExpose({ update });
</script>

<template>
    <form @submit.prevent="submitForm">
        <div class="row">
            <div class="col-12">
                <h6 class="text-xl fw-semibold mb-3">Contact Person Detail</h6>
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name" required :error="form.errors.first_name" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name" :error="form.errors.last_name" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.email" label="Email" placeholder="Email" required :error="form.errors.email" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" required :error="form.errors.mobile" />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h5 class="text-xl fw-semibold mb-3">Pharmacy Detail</h5>
                <hr>
            </div>
            <div class="col-md-12 mb-3">
                <BaseFileInput v-model="form.banner"    id="inputFileUpload"
                @change="onChangeFileUpload($event)" 
                accept="image/*"  label="Banner Upload" placeholder="Upload Banner" :error="form.errors.banner" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.pharmacy_name" label="Pharmacy Name" placeholder="Pharmacy Name" required :error="form.errors.pharmacy_name" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.license_number" label="License Number" placeholder="License Number" required :error="form.errors.license_number" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.pharmacy_mobile" label="Pharmacy Mobile" placeholder="Pharmacy Mobile" required :error="form.errors.pharmacy_mobile" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.pharmacy_email" label="Pharmacy Email" placeholder="Pharmacy Email" required :error="form.errors.pharmacy_email" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address1" label="Address 1" placeholder="Address 1" required :error="form.errors.street_address1" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address2" label="Address 2" placeholder="Address 2" :error="form.errors.street_address2" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.city" label="City" placeholder="City" required :error="form.errors.city" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.country" label="Country" placeholder="Select Country" :error="form.errors.country">
                    <option v-for="country in countries" :key="country.isoCode" :value="country.name">{{ country.name }}</option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.state" label="State" placeholder="Select State" :error="form.errors.state">
                    <option v-for="state in personalStates" :key="state.isoCode" :value="state.name">{{ state.name }}</option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.zip" label="Zip Code" type="number" placeholder="Zip Code" required :error="form.errors.zip" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseDatePicker v-model="form.opening_time" type="time" label="Opening Time" placeholder="Opening Time" required :error="form.errors.opening_time" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseDatePicker v-model="form.closing_time" type="time" label="Closing Time" placeholder="Closing Time" required :error="form.errors.closing_time" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.website" label="Website" placeholder="Website" :error="form.errors.website" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" placeholder="Select Status" :error="form.errors.is_active" required>
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_verified" label="Verification" placeholder="Select Verification" :error="form.errors.is_verified" required>
                    <option :value="1">Verified</option>
                    <option :value="0">Unverified</option>
                </BaseSelect>
            </div>
            <div class="col-12 mb-2">
                <BaseInput v-model="form.about" type="textarea" label="Description" placeholder="Description" :error="form.errors.about" />
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">Close</button>

        </div>
    </form>
</template>