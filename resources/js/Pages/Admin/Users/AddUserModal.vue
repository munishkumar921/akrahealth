<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import InputError from "@/Components/InputError.vue";
import { Country , State } from "country-state-city";

const props = defineProps({
    specialities: Array,
    hospitalId: Number,
    branches: Array,
});
const countries = Country.getAllCountries();
const states = ref([]);

const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);

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
const form = useForm({
    id:'',
    hospitalId: props.hospitalId || null,
    role: "",
    first_name: "",
    last_name: "",
    sex: "",
    email: "",
    mobile: "",
    password: password,
    password_confirmation: password,
    speciality: "",
    street_address1: "",
    street_address2: "",
    city: "",
    country: "",
    state: "",
    zip_code: "",
    skills: "", // Assistant only
    profile_photo_path: null,
    is_active:true,
});

const profilePhotoUrl = ref(null);

// Computed properties for safe image URLs
const profilePhotoPreview = computed(() => {
    if (form.profile_photo_path && form.profile_photo_path instanceof File) {
        // Clean up previous URL
        if (profilePhotoUrl.value) {
            URL.revokeObjectURL(profilePhotoUrl.value);
        }
        profilePhotoUrl.value = URL.createObjectURL(form.profile_photo_path);
        return profilePhotoUrl.value;
    }
    return null;
});
const onChangeFileUpload = (event) => {
    if (event.target.files && event.target.files[0]) {
        form.profile_photo_path = event.target.files[0];
    }
};
const skills = [
    "Scheduling",
    "Data Entry",
    "Customer Service",
    "Medical Transcription",
    "Billing Support",
];
const billers = [
    "Insurance Billing",
    "Medical Coding",
    "Accounts Receivable",
    "Claims Processing",
    "Payment Posting",
];  
 
const submitForm = () => {
    isValidated.value = true;
    form.post(route("admin.users.store"), {
        onSuccess: () => {
            isValidated.value = false;
            emit("submit");
            closeModal();
        },
    });
};

const closeModal = () => {
    emit("close");
};

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

</script>

<template>
    <form @submit.prevent="submitForm" novalidate  >
        <div class="row">
            <!-- avatar -->
            <div class="form-group mb-4 text-center">
                <label for="Upload profile picture" class="d-block mb-2">Profile Picture</label>
                <div class="position-relative d-inline-block">
                    <div class="profile-photo-preview mb-2" style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #e0e0e0; overflow: hidden; margin: 0 auto;">
                        <img v-if="profilePhotoPreview" :src="profilePhotoPreview" 
                            alt="Profile" style="width: 100%; height: 100%; object-fit: cover;" />
                        <div v-else class="d-flex align-items-center justify-content-center h-100 bg-light">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    </div>
                    <label for="inputFileUpload" class="btn btn-outline-primary btn-sm" style="cursor: pointer;">
                        <i class="fas fa-camera me-1"></i>{{ form.profile_photo_path ? 'Change' : 'Upload' }} Photo
                    </label>
                    <input type="file" class="d-none" id="inputFileUpload"
                        @change="onChangeFileUpload($event)" accept="image/*" />
                </div>
                <InputError class="mt-2" :message="form.errors.profile_photo_path" />
            </div>
            <!-- First Name -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name" required :error="form.errors.first_name" />
            </div>

            <!-- Last Name -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name" required :error="form.errors.last_name" />
            </div>

            <!-- Sex -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.sex" label="Sex" placeholder="Sex" :error="form.errors.sex">
                     <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </BaseSelect>
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.email" label="Email" placeholder="Email" required :error="form.errors.email" />
            </div>

            <!-- Mobile -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" required :error="form.errors.mobile" />
            </div>

            <!-- Role -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.role" label="Role" placeholder="Select Role" :error="form.errors.role" required>
                    <option value="">Select Role</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Virtual Assistant">Assistant</option>
                    <option value="Biller">Biller</option>
                </BaseSelect>
            </div>

            <!-- speciality -->
            <div class="col-md-6 mb-3" v-if="form.role === 'Doctor'">
                <BaseSelect v-model="form.speciality" label="Speciality" placeholder="Speciality" :error="form.errors.speciality">
                    <option value="">Select Speciality</option>
                    <template v-for="speciality in specialities" :key="speciality">
                        <option :value="speciality">{{ speciality }}</option>
                    </template>
                </BaseSelect>
            </div>

            <!-- skills -->
            <div class="col-md-6 mb-3" v-if="form.role === 'Virtual Assistant'">
                <BaseSelect v-model="form.skills" label="Skills" placeholder="Skills" :error="form.errors.skills">
                    <option value="">Select Skills</option>
                    <template v-for="skill in skills" :key="skill">
                        <option :value="skill">{{ skill }}</option>
                    </template>
                </BaseSelect>
            </div>

            <!-- biller -->
            <div class="col-md-6 mb-3" v-if="form.role === 'Biller'">
                <BaseSelect v-model="form.biller" label="Biller" placeholder="Biller" :error="form.errors.biller">
                    <option value="">Select Biller</option>
                    <template v-for="biller in billers" :key="biller">
                        <option :value="biller">{{ biller }}</option>
                    </template>
                </BaseSelect>
            </div>

            <!-- Street Address 1 -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address1" label="Street Address 1" type="text" placeholder="Street Address 1" required :error="form.errors.street_address1" />
            </div>

            <!-- Street Address 2 -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address2" label="Street Address 2" type="text" placeholder="Street Address 2" :error="form.errors.street_address2" />
            </div>

            <!-- City -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required :error="form.errors.city" />
            </div>

            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.country" label="Country" type="select" required placeholder="Select Country" :error="form.errors.country">
                    <template v-for="country in countries" :key="country.isoCode">
                        <option :value="country.name">{{ country.name }}</option>
                    </template>
                </BaseSelect>
            </div>

            <!-- states -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.state" label="State" type="select" placeholder="Select State" :error="form.errors.state">
                    <template v-for="state in states" :key="state.isoCode">
                        <option :value="state.name">{{ state.name }}</option>
                    </template>
                </BaseSelect>
            </div>

            <!-- Zip Code -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.zip" label="Zip Code" type="number" placeholder="Zip Code" required :error="form.errors.zip" />
            </div>

            <!-- Status -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" placeholder="Status" :error="form.errors.is_active">    
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                </BaseSelect>
            </div>

            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.hospitalId" label="Branch" placeholder="Select Branch">
                    <option value="">Select Branch</option>
                    <template v-for="branch in branches" :key="branch.id">
                        <option :value="branch.id">{{ branch.name }} ({{ branch.main_branch_id === null ? 'Main' : 'Sub' }})</option>
                    </template>
                </BaseSelect>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" @click="closeModal" :disabled="form.processing">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ form.processing ? 'Saving...' : 'Save' }}</button>
        </div>
    </form>
</template>
