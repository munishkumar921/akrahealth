<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch, computed, nextTick } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseFileInput from "@/Components/Common/Input/BaseFileInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { Country, State } from 'country-state-city';
  
const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);
const isUpdating = ref(false);
const props = defineProps({
    labCategory: Array,
});

const form = useForm({
    /* personal detail */
    id: null,
    user_id: null,
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    password: '',
    password_confirmation: '',
    profile_photo: '',

    /* lab detail */
    lab_name: '',
    license_number: '',
    city: '',
    street_address1: '',
    street_address2: '',
    state: '',
    country: '',
    lab_mobile: '',
    lab_email: '',
    zip: '',
    opening_time: '',
    closing_time: '',
    sample_pickup_supported: 0,
    is_active: 1,  // Default to active for new labs
    is_verified: 0,
    about: '',
    banner: '',
    categories: [],
    website: '',
    pickup: 0,
});

const countries = Country.getAllCountries();
const personalStates = ref([]);


// Watch for country changes to update states
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
    // Reset state when country changes
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
    isValidated.value = true;
    const prefix = route().current().includes('doctor') ? 'doctor' : 'admin';

    if (!form.id) {

        // Add new user
        form.password = password;
     }
        form.post(route(`${prefix}.labs.store`), {
            onSuccess: () => {
                isValidated.value = false;
                emit("submit");
                closeModal();
            },
            onError: () => {
                isValidated.value = true;
            }
        });
    }

const closeModal = () => {
    emit("close");
    form.reset();
    isValidated.value = false;
};

const update = (lab) => {
    isUpdating.value = true;
    form.id = lab.id;
    form.user_id = lab.user_id;
    form.first_name = lab.user?.first_name || (lab.user_name ? lab.user_name.split(' ')[0] : '');
    form.last_name = lab.user?.last_name || (lab.user_name ? lab.user_name.split(' ').slice(1).join(' ') : '');
    form.email = lab.user?.email || lab.user_email || '';
    form.mobile = lab.user?.mobile || lab.user_mobile || '';
    form.profile_photo = lab.user?.profile_photo_path || lab.profile_photo || '';

    form.lab_name = lab.name;
    form.license_number = lab.license_number;
    form.street_address1 = lab.user?.address?.address_1 || lab.address || '';
    form.street_address2 = lab.user?.address?.address_2 || lab.street_address2 || '';
    form.city = lab.user?.address?.city || lab.city || '';
    form.country = lab.user?.address?.country|| lab.country || '';
    form.state = lab.user?.address?.state|| lab.state || '';
    form.lab_mobile = lab.mobile;
    form.lab_email = lab.email;
    form.zip = lab.user?.address?.zip || lab.pincode || '';
    form.opening_time = lab.opening_time||'';
    form.closing_time = lab.closing_time||'';
    form.is_active = lab.is_active;
    form.is_verified = lab.is_verified;
    form.about = lab.about;
    form.banner = lab.banner;
    
    // Handle categories - normalize to array of strings
    if (lab.categories && Array.isArray(lab.categories)) {
        form.categories = lab.categories.map(s => {
            // Handle both string and object formats
            if (typeof s === 'string') {
                return s;
            }
            return s.name || s;
        });
    } else if (lab.categories) {
        form.categories = Array.isArray(lab.categories) ? lab.categories : [lab.categories];
    } else {
        form.categories = [];
    }
    
    form.website = lab.website || '';
    form.pickup = lab.sample_pickup_supported || 0;

    if (form.country) {
        const selectedCountry = countries.find(c => c.name === form.country);
        if (selectedCountry) {
            personalStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
        }
    }
    if(form.banner){
        photoPreview.value = lab.banner_url;
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
        <!-- Contact Person Detail -->
        <div class="row">
            <div class="col-12">
                <h6 class="text-xl fw-semibold mb-3">Contact Person Detail</h6>
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name" required
                    :error="form.errors.first_name" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name"
                    :error="form.errors.last_name" />
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.email" label="Email" placeholder="Email" required :error="form.errors.email" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" required
                    :error="form.errors.mobile" />
            </div>
        </div>

        <!-- Lab Detail -->
        <div class="row">
            <div class="col-12">
                <h5 class="text-xl fw-semibold mb-3">Lab Detail</h5>
                <hr>
            </div>

            <div class="col-md-12 mb-3">
                  <BaseFileInput 
                     v-model="form.banner" 
                    label="Banner Upload" 
                    placeholder="upload Banner"
                    accept="image/*"
                    id="inputFileUpload"
                    @change="onChangeFileUpload($event)" 
                     :error="form.errors.banner" 
                />
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.lab_name" label="Lab Name" placeholder="Lab Name" required
                    :error="form.errors.lab_name" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.license_number" label="License Number" placeholder="License Number" required
                    :error="form.errors.license_number" />
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.lab_mobile" label="Lab Mobile" placeholder="Lab Mobile" required
                    :error="form.errors.lab_mobile" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.lab_email" label="Lab Email" placeholder="Lab Email" required
                    :error="form.errors.lab_email" />
            </div>
             <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.categories" label="Lab Categories" type="select" placeholder="Select Categories"
                    :error="form.errors.categories" required multiple>
                    <option v-for="row in labCategory" :key="row.id" :value="row.name">{{ row.name }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address1" label="Address 1" placeholder="Address 1" required
                    :error="form.errors.street_address1" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address2" label="Address 2" placeholder="Address 2"
                    :error="form.errors.street_address2" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required
                    :error="form.errors.city" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.country" label="Country" type="select" placeholder="Select Country"
                    :error="form.errors.country">
                    <option v-for="country in countries" :key="country.isoCode" :value="country.name">{{ country.name }}
                    </option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.state" label="State" type="select" placeholder="Select State"
                    :error="form.errors.state">
                    <option v-for="state in personalStates" :key="state.isoCode" :value="state.name">{{ state.name }}
                    </option>
                </BaseSelect>
            </div>

            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.zip" label="Zip Code" type="number" placeholder="Zip Code" required
                    :error="form.errors.zip" />
            </div>


            <div class="col-md-6 mb-3">
                <BaseDatePicker v-model="form.opening_time" type="time" label="Opening Time" placeholder="Opening Time"
                    required :error="form.errors.opening_time" />
            </div>

            <div class="col-md-6 mb-3">
                <BaseDatePicker v-model="form.closing_time" type="time" label="Closing Time" placeholder="Closing Time"
                    required :error="form.errors.closing_time" />
            </div>
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.website" label="Website" placeholder="Website" :error="form.errors.website" />
            </div>

            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" placeholder="Select Status"
                    :error="form.errors.is_active" required>
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_verified" label="Verification" placeholder="Select Verification"
                    :error="form.errors.is_verified" required>
                    <option :value="1">Verified</option>
                    <option :value="0">Unverified</option>
                </BaseSelect>
            </div>
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.pickup" label="Sample pickup available?" placeholder=""
                    :error="form.errors.pickup">
                    <option :value="1">Yes</option>
                    <option :value="0">No</option>
                </BaseSelect>
            </div>

            <div class="col-12 mb-2">
                <BaseInput v-model="form.about" type="textarea" label="Description" placeholder="Description"
                    :error="form.errors.about" />
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-danger" @click="closeModal">Close</button>

        </div>
    </form>
</template>