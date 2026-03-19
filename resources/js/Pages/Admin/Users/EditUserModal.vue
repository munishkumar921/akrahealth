<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onBeforeUnmount } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import InputError from "@/Components/InputError.vue";
import { Country, State } from "country-state-city";

const props = defineProps({
    specialities: Array,
    hospitalId: Number,
    branches: Array,
    user: {
        type: Object,
        default: null
    },
});

const countries = Country.getAllCountries();
const states = ref([]);
const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);
const skipCountryReset = ref(false);

// Form initialization
const form = useForm({
    id: '',
    doctorId: '',
    hospitalId: props.hospitalId || '',
    role: "",
    first_name: "",
    last_name: "",
    sex: "",
    email: "",
    mobile: "",
    specialities: [],
    street_address1: "",
    street_address2: "",
    city: "",
    country: "",
    state: "",
    zip_code: "",
    zip: "",
    skills: "",
    biller: "",
    is_active: 1,
    profile_photo_path: '',
});

// Clean up URL objects when component unmounts
onBeforeUnmount(() => {
    if (profilePhotoUrl.value) {
        URL.revokeObjectURL(profilePhotoUrl.value);
    }
});

// Profile photo preview
const profilePhotoUrl = ref(null);

// Computed properties to check if user has specific role
const isDoctorRole = computed(() => {
    return form.role === 'Doctor' || form.role.includes('Doctor');
});

const isAssistantRole = computed(() => {
    return form.role === 'Virtual Assistant' || form.role.includes('Virtual Assistant');
});

const isBillerRole = computed(() => {
    return form.role === 'Biller' || form.role.includes('Biller');
});

const profilePhotoPreview = computed(() => {
    if (form.profile_photo_path && form.profile_photo_path instanceof File) {
        if (profilePhotoUrl.value) {
            URL.revokeObjectURL(profilePhotoUrl.value);
        }
        profilePhotoUrl.value = URL.createObjectURL(form.profile_photo_path);
        return profilePhotoUrl.value;
    }

    if (props.user?.profile_photo_path && !form.profile_photo_path) {
        return props.user.profile_photo_path;
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

// Method to update form with user data
const userRoles = ref([]);
const update = (user) => {
    if (user) {
        form.id = user.id;
        form.hospitalId = user?.doctor?.hospital_id || user?.hospital_id || props.hospitalId || '';
        form.doctorId = user?.doctor?.id || null;

        userRoles.value = user.roles;
        // Handle role assignment
        if (user.roles && Array.isArray(user.roles)) {
            if (user.roles.length > 1) {
                const roleNames = user.roles.map(role => role.name).filter(Boolean);
                form.role = roleNames.join(", ");
            } else {
                form.role = user.roles[0]?.name || "";
            }
        } else {
            form.role = "";
        }

        // Basic user info
        form.first_name = user?.doctor?.first_name || "";
        form.last_name = user?.doctor?.last_name || "";
        form.sex = user.sex || "";
        form.email = user.email || "";
        form.mobile = user.mobile || "";

        // Address info
        form.street_address1 = user.address?.address_1 || "";
        form.street_address2 = user.address?.address_2 || "";
        form.city = user.address?.city || "";

        // Set country first (watcher will populate states), then set state
        skipCountryReset.value = true;
        form.country = user.address?.country || "";
        // After country is set and states are populated, set the state
        if (user.address?.state || user.state) {
            const targetState = user.address?.state || user.state || "";
            // Ensure states are populated before setting form.state
            if (states.value.length > 0) {
                const matchingState = states.value.find(s => s.name === targetState);
                form.state = matchingState ? matchingState.name : targetState;
            } else {
                form.state = targetState;
            }
        } else {
            form.state = "";
        }
        skipCountryReset.value = false;

        form.zip_code = user.address?.zip || user.zip_code || "";
        if (user.doctor?.specialities) {

            let selectedSpecialities = user.doctor.specialities.map(s => s.name);
            form.specialities = selectedSpecialities;
        }
        form.skills = user.skills || "";
        form.biller = user.biller || "";

        // Status
        form.is_active = user.is_active ? 1 : 0;
        form.profile_photo_path = null;
    }
};

// Watch for country changes to populate states
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

    // Reset state if country changes, but skip during programmatic updates
    if (!skipCountryReset.value) {
        form.state = '';
    }
});

const submitForm = () => {
    isValidated.value = true;
    form.post(route("admin.users.store"), {
        preserveScroll: true,
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

// Expose the update method to parent
defineExpose({ update });
</script>

<template>
    <form @submit.prevent="submitForm" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div class="row">
            <!-- Profile Picture -->
            <div class="form-group mb-4 text-center">
                <label for="inputFileUpload" class="d-block mb-2">Profile Picture</label>
                <div class="position-relative d-inline-block">
                    <div class="profile-photo-preview mb-2"
                        style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #e0e0e0; overflow: hidden; margin: 0 auto;">
                        <img v-if="profilePhotoPreview" :src="profilePhotoPreview" alt="Profile"
                            style="width: 100%; height: 100%; object-fit: cover;" />
                        <div v-else class="d-flex align-items-center justify-content-center h-100 bg-light">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    </div>
                    <label for="inputFileUpload" class="btn btn-outline-primary btn-sm" style="cursor: pointer;">
                        <i class="fas fa-camera me-1"></i>{{ form.profile_photo_path ? 'Change' : 'Upload' }} Photo
                    </label>
                    <input type="file" class="d-none" id="inputFileUpload" @change="onChangeFileUpload"
                        accept="image/*" />
                </div>
                <InputError class="mt-2" :message="form.errors.profile_photo_path" />
            </div>

            <!-- First Name -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name" required
                    :error="form.errors.first_name" />
            </div>

            <!-- Last Name -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name" required
                    :error="form.errors.last_name" />
            </div>

            <!-- Sex -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.sex" label="Sex" placeholder="Select Sex" :error="form.errors.sex" required>
                    <option value="">Select Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </BaseSelect>
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.email" label="Email" placeholder="Email" type="email" required
                    :error="form.errors.email" readonly />
            </div>

            <!-- Mobile -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" :error="form.errors.mobile" />
            </div>

            <!-- Role -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.role"  label="Role" placeholder="Select Role" :class="form.id?'d-none':''" required
                           :error="form.errors.role">
                    <option value="">Select Role</option>
                    <option value="Doctor">Doctor</option>
                    <option value="Virtual Assistant">Assistant</option>
                    <option value="Biller">Biller</option>
                </BaseSelect>
            </div>

            <!-- Speciality - Only for Doctor -->
            <div class="col-md-6 mb-3" v-if="isDoctorRole">
                <BaseSelect :multiple="true" v-model="form.specialities" label="Speciality"
                    placeholder="Select Speciality" :error="form.errors.speciality" required>
                    <option value="">Select Speciality</option>
                    <option v-for="speciality in specialities" :key="speciality" :value="speciality">
                        {{ speciality }}
                    </option>
                </BaseSelect>
            </div>

            <!-- Skills - Only for Virtual Assistant -->
            <div class="col-md-6 mb-3" v-if="isAssistantRole">
                <BaseSelect v-model="form.skills" label="Skills" placeholder="Select Skills" :error="form.errors.skills"
                    required>
                    <option value="">Select Skills</option>
                    <option v-for="skill in skills" :key="skill" :value="skill">
                        {{ skill }}
                    </option>
                </BaseSelect>
            </div>

            <!-- Biller - Only for Biller -->
            <div class="col-md-6 mb-3" v-if="isBillerRole">
                <BaseSelect v-model="form.biller" label="Biller" placeholder="Select Biller" :error="form.errors.biller"
                    required>
                    <option value="">Select Biller</option>
                    <option v-for="biller in billers" :key="biller" :value="biller">
                        {{ biller }}
                    </option>
                </BaseSelect>
            </div>

            <!-- Street Address 1 -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address1" label="Street Address 1" type="text"
                    placeholder="Street Address 1" required :error="form.errors.street_address1" />
            </div>

            <!-- Street Address 2 -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.street_address2" label="Street Address 2" type="text"
                    placeholder="Street Address 2 (Optional)" :error="form.errors.street_address2" />
            </div>

            <!-- City -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required
                    :error="form.errors.city" />
            </div>

            <!-- Country -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.country" label="Country" required placeholder="Select Country"
                    :error="form.errors.country">
                    <option value="">Select Country</option>
                    <option v-for="country in countries" :key="country.isoCode" :value="country.name">
                        {{ country.name }}
                    </option>
                </BaseSelect>
            </div>

            <!-- States -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.state" label="State" placeholder="Select State" :error="form.errors.state">
                    <option value="">Select State</option>
                    <option v-for="state in states" :key="state.isoCode" :value="state.name">
                        {{ state.name }}
                    </option>
                </BaseSelect>
            </div>

            <!-- Zip Code -->
            <div class="col-md-6 mb-3">
                <BaseInput v-model="form.zip_code" label="Zip Code" type="text" placeholder="Zip Code"
                    :error="form.errors.zip" />
            </div>

            <!-- Status -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.is_active" label="Status" placeholder="Select Status"
                    :error="form.errors.is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </BaseSelect>
            </div>

            <!-- Branch -->
            <div class="col-md-6 mb-3">
                <BaseSelect v-model="form.hospitalId" label="Branch" placeholder="Select Branch">
                    <option value="">Select Branch</option>
                    <template v-for="branch in branches" :key="branch.id">
                        <option :value="branch.id">{{ branch.name }}<span class="text-muted"> ({{ branch.main_branch_id === null ? 'Main' : 'Sub' }})</span></option>
                    </template>
                </BaseSelect>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : (form.id ? 'Update User' : 'Add User') }}
            </button>
             <button type="button" class="btn btn-danger" @click="closeModal" :disabled="form.processing">
                Close
            </button>
        </div>
    </form>
</template>
