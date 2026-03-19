<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Country, State } from 'country-state-city';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import TextInput from '@/Components/TextInput.vue';
import AppLayout2 from '@/Layouts/AppLayout2.vue';
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
const page = usePage();
const props = defineProps({
    specialities: Object,
    questions: Object,
    status: String,
    subscription_plans:{
        type: Array,
    },
});

// Initialize countries and states data
const countries = Country.getAllCountries();
const personalStates = ref([]);
const practiceStates = ref([]);

const form = useForm({
    first_name: '',
    last_name: '',
    mobile: '',
    email: '',
    speciality_id: '',
    sex: '',
    password: '',
    password_confirmation: '',
    street_address1: '',
    street_address2: '',
    city: '',
    state: '',
    zip: '',
    country: '',
    question_id: '',
    secret_answer: '',
    terms: false,
    profile_photo: '',
    subscription_plan_id:'',
    practice_name: '',
    practice_street_address1: '',
    practice_street_address2: '',
    practice_city: '',
    practice_state: '',
    practice_zip: '',
    practice_country: '',
    practice_mobile: '',
    practice_email: '',
    practice_logo: '',
    practice_primary_contact: '',

});
const isValidated = ref(false);

// Track collapse states for icon switching
const additionalPersonalDetailsOpen = ref(false);
const practicePersonalDetailsOpen = ref(false);

// Set subscription plan from query parameter or default to starter plan
onMounted(() => {
 if (props.subscription_plans && props.subscription_plans.length > 0) {
    // Find trial plan as default
    const trialPlan = props.subscription_plans.find(
        plan => plan.title?.toLowerCase() === 'trial'
    )

    if (trialPlan) {
        form.subscription_plan_id = String(trialPlan.id)
    } else {
        // Fallback: select first plan
        form.subscription_plan_id = String(props.subscription_plans[0].id)
    }
}

    // Set up Bootstrap collapse event listeners for icon switching
    const additionalDetailsCollapse = document.getElementById('additionalPersonalDetails');
    const practiceDetailsCollapse = document.getElementById('PracticePersonalDetails');
    
    if (additionalDetailsCollapse) {
        additionalDetailsCollapse.addEventListener('shown.bs.collapse', () => {
            additionalPersonalDetailsOpen.value = true;
        });
        additionalDetailsCollapse.addEventListener('hidden.bs.collapse', () => {
            additionalPersonalDetailsOpen.value = false;
        });
    }
    
    if (practiceDetailsCollapse) {
        practiceDetailsCollapse.addEventListener('shown.bs.collapse', () => {
            practicePersonalDetailsOpen.value = true;
        });
        practiceDetailsCollapse.addEventListener('hidden.bs.collapse', () => {
            practicePersonalDetailsOpen.value = false;
        });
    }
});

const genders = ['Male', 'Female', 'Other'];

// Object URL refs for cleanup
const profilePhotoUrl = ref(null);
const practiceLogoUrl = ref(null);

// Computed properties for safe image URLs
const profilePhotoPreview = computed(() => {
    if (form.profile_photo && form.profile_photo instanceof File) {
        // Clean up previous URL
        if (profilePhotoUrl.value) {
            URL.revokeObjectURL(profilePhotoUrl.value);
        }
        profilePhotoUrl.value = URL.createObjectURL(form.profile_photo);
        return profilePhotoUrl.value;
    }
    return null;
});

const practiceLogoPreview = computed(() => {
    if (form.practice_logo && form.practice_logo instanceof File) {
        // Clean up previous URL
        if (practiceLogoUrl.value) {
            URL.revokeObjectURL(practiceLogoUrl.value);
        }
        practiceLogoUrl.value = URL.createObjectURL(form.practice_logo);
        return practiceLogoUrl.value;
    }
    return null;
});

const onChangeFileUpload = (event) => {
    if (event.target.files && event.target.files[0]) {
        form.profile_photo = event.target.files[0];
    }
};

const onChangePractice_logo = (event) => {
    if (event.target.files && event.target.files[0]) {
        form.practice_logo = event.target.files[0];
    }
};

// Watch for personal country changes to populate personal states
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
    form.state = '';
});

// Watch for practice country changes to populate practice states
watch(() => form.practice_country, (newCountry) => {
    if (newCountry) {
        const selectedCountry = countries.find(country => country.name === newCountry);
        if (selectedCountry) {
            practiceStates.value = State.getStatesOfCountry(selectedCountry.isoCode);
        } else {
            practiceStates.value = [];
        }
    } else {
        practiceStates.value = [];
    }
    // Reset state when country changes
    form.practice_state = '';
});

// Cleanup object URLs on unmount
onUnmounted(() => {
    if (profilePhotoUrl.value) {
        URL.revokeObjectURL(profilePhotoUrl.value);
    }
    if (practiceLogoUrl.value) {
        URL.revokeObjectURL(practiceLogoUrl.value);
    }
});
const submit = () => {
    isValidated.value = true;

    form.post(route('signup.store'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            // Errors are automatically handled by Inertia
            console.log('Form errors:', errors);
        },
    });
};
const planLabel = (plan) => {
    if (plan.title?.toLowerCase() === 'trial') {
        return '(Try it free, no credit card needed)'
    }

    return plan.frequency ? `(${plan.frequency})` : ''
}

</script>

<template>
    <AppLayout2 title="Sign Up" description="Create your account">
        <section class="sign-in-page">
            <div class="container bg-white p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6 align-self-center">
                        <div class="sign-in-from p-4 p-md-5">

                            <!-- Header -->
                            <div class="text-center mb-4">
                                <h1 class="dark-signin mb-3 font-size-32 fw-bold">Create Your Account</h1>
                                <p class="text-muted">Join AkraHealth and start your telehealth journey</p>
                            </div>

                            <!-- Status Messages -->
                            <div v-if="status" class="mb-4">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ status }}
                                    <button type="button" class="btn-close" aria-label="Close"></button>
                                </div>
                            </div>

                            <!-- General Error Messages from Form -->
                            <div v-if="form.errors.error" class="mb-4">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Error:</strong> {{ form.errors.error }}
                                    <button type="button" class="btn-close" @click="form.clearErrors('error')"
                                        aria-label="Close"></button>
                                </div>
                            </div>

                            <!-- General Success Messages -->
                            <div v-if="page.props.flash?.success" class="mb-4">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ page.props.flash.success }}
                                    <button type="button" class="btn-close" @click="page.props.flash.success = null"
                                        aria-label="Close"></button>
                                </div>
                            </div>

                            <!-- General Error from Flash -->
                            <div v-if="page.props.flash?.error" class="mb-4">
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Error:</strong> {{ page.props.flash.error }}
                                    <button type="button" class="btn-close" @click="page.props.flash.error = null"
                                        aria-label="Close"></button>
                                </div>
                            </div>

                            <div class="app">
                                <form @submit.prevent="submit" novalidate class="needs-validation"
                                    :class="{ 'was-validated': isValidated }">

                                    <!-- Subscription Plan Selection - Improved UI -->
                                    <div class="mb-4 shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0 text-white"><i class="fas fa-crown me-2"></i>Select Your
                                                Plan</h5>
                                        </div>
                                        <div class="card-body">

                                           <div class="row g-3">
                                            <BaseSelect
                                                id="subscription_plan_id" 
                                                v-model="form.subscription_plan_id"
                                                placeholder="Select a Plan"
                                                required
                                            >
                                                <option v-for="plan in subscription_plans" :key="plan.id" :value="plan.id">
                                                    {{ plan.title }} {{ planLabel(plan) }}
                                                </option>
                                            
                                            </BaseSelect>
                                        </div>                                             
                                            <InputError class="mt-2" :message="form.errors.subscription_plan_id" />
                                                                                        <small class="mt-2 d-block mb-3">
                                                 <i class="fas fa-info-circle me-1"></i>Choose a subscription plan to continue with your registration.
                                            </small>
                                            
                                        </div>
                                    </div>

                                    <!-- Personal Information Section -->
                                    <div class="card mb-4 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                                            <small class=" mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>You're signing up as a Practice Admin. You can switch to Doctor anytime.
                                            </small>
                                        </div>
                                        <div class="card-body">
                                            <!-- Profile Photo -->
                                            <div class="form-group mb-4 text-center">
                                                <InputLabel for="Upload profile picture" value="Profile Picture"
                                                    class="d-block mb-2" />
                                                <div class="position-relative d-inline-block">
                                                    <div class="profile-photo-preview mb-2"
                                                        style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #e0e0e0; overflow: hidden; margin: 0 auto;">
                                                        <img v-if="profilePhotoPreview" :src="profilePhotoPreview"
                                                            alt="Profile"
                                                            style="width: 100%; height: 100%; object-fit: cover;" />
                                                        <div v-else
                                                            class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                            <i class="fas fa-user fa-3x text-muted"></i>
                                                        </div>
                                                    </div>
                                                    <label for="inputFileUpload" class="btn btn-outline-primary btn-sm"
                                                        style="cursor: pointer;">
                                                        <i class="fas fa-camera me-1"></i>{{ form.profile_photo ?
                                                            'Change' : 'Upload' }} Photo
                                                    </label>
                                                    <input type="file" class="d-none" id="inputFileUpload"
                                                        @change="onChangeFileUpload($event)" accept="image/*" />
                                                </div>
                                                <InputError class="mt-2" :message="form.errors.profile_photo" />
                                            </div>

                                            <div class="row justify-content-end">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <InputLabel for="name" value="Name" class="required-field"
                                                            required />
                                                        <div class="input-group mt-1">
                                                            <span class="input-group-text fw-semibold">Dr.</span>
                                                            <TextInput id="name" type="text" v-model="form.first_name"
                                                                style="border-top-left-radius:0; border-bottom-left-radius:0;"
                                                                required autofocus autocomplete="name"
                                                                placeholder="Name" />
                                                        </div>
                                                        <InputError class="mt-2" :message="form.errors.first_name" />
                                                    </div>
                                                </div>
                                                <!-- <div class="col-sm-6">
                                                <div class="form-group">
                                                    <InputLabel for="last_name" value="Last Name" class="required-field"/>
                                                    <TextInput id="last_name" type="text" v-model="form.last_name" class="mt-1 "
                                                     autofocus   placeholder="Last Name" />
                                                    <InputError class="mt-2" :message="form.errors.last_name" />
                                                </div>
                                            </div> -->

                                                <div class="form-group col-md-6">
                                                    <InputLabel for="email" value="Email" class="required-field"
                                                        required />
                                                    <TextInput id="email" v-model="form.email" type="email"
                                                        class="mt-1 " placeholder="Email" required
                                                        autocomplete="email" />
                                                    <InputError class="mt-2" :message="form.errors.email" />
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="mobile" value="Mobile" required />
                                                        <TextInput id="mobile" v-model="form.mobile" type="text"
                                                            class="mt-1  phone-validation" autocomplete="mobile"
                                                            required placeholder="Mobile Number" minlength="10"
                                                            maxlength="10" />
                                                        <InputError class="mt-2" :message="form.errors.mobile" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="password" value="Password" required
                                                            class="after:ml-0.5 after:text-red-500 block  font-medium text-slate-700" />
                                                        <TextInput id="password" v-model="form.password" type="password"
                                                            class="mt-1 " required autocomplete="password"
                                                            placeholder="Password" />
                                                        <InputError class="mt-2" :message="form.errors.password" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="password_confirmation" value="Confirm Password"
                                                            required
                                                            class=" after:ml-0.5 after:text-red-500 block  font-medium text-slate-700" />
                                                        <TextInput id="password_confirmation"
                                                            v-model="form.password_confirmation"
                                                            placeholder="Confirm Password" type="password" class="mt-1 "
                                                            required autocomplete="password" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.password_confirmation" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6 align-content-center text-end">
                                                    <div class="text-primary pointer" data-toggle="collapse"
                                                        data-target="#additionalPersonalDetails"
                                                        aria-expanded="false" aria-controls="additionalPersonalDetails">
                                                        <i class="fas" :class="additionalPersonalDetailsOpen ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                                                        {{ additionalPersonalDetailsOpen ? 'Less' : 'More' }} Personal
                                                        Details
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="row collapse" id="additionalPersonalDetails">

                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <InputLabel for="gender" class="required-field"
                                                            value="Gender" />
                                                        <BaseSelect v-model="form.sex" id="gender" placeholder="Select Gender">                                                           
                                                             <template v-for="row in genders" :key="row">
                                                                <option :value="row">{{ row }}</option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2" :message="form.errors.sex" />
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">

                                                        <InputLabel for="specialty" class="required-field"
                                                            value="Speciality" />
                                                        <BaseSelect v-model="form.speciality_id" id="specialty"
                                                            placeholder="Select Speciality" >
                                                             <template v-for="row in specialities" :key="row.id">
                                                                <option :value="row.id">{{ row.name }}</option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2" :message="form.errors.speciality_id" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="state" value="Country" />
                                                        <BaseSelect id="country" v-model="form.country" placeholder="Select Country" >
                                                             <template v-for="country in countries"
                                                                :key="country.isoCode">
                                                                <option :value="country.name">{{ country.name }}
                                                                </option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2" :message="form.errors.country" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="address" value="Address 1" />
                                                        <TextInput id="address" v-model="form.street_address1"
                                                            type="text" class="mt-1 " placeholder="address 1" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.street_address1" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="address" value="Address 2" />
                                                        <TextInput id="address" v-model="form.street_address2"
                                                            type="text" class="mt-1 " placeholder="Address 2" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.street_address2" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="city" value="City" />
                                                        <TextInput id="city" v-model="form.city" type="text"
                                                            class="mt-1 " placeholder="City" />
                                                        <InputError class="mt-2" :message="form.errors.city" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="state" value="State" />
                                                        <BaseSelect id="state" v-model="form.state" placeholder="Select State"
                                                            >
                                                            <option disabled value="">Select your state</option>
                                                            <template v-for="state in personalStates"
                                                                :key="state.isoCode">
                                                                <option :value="state.name">{{ state.name }}</option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2" :message="form.errors.state" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="zip" value="Zip Code" />
                                                        <TextInput id="zip" v-model="form.zip" type="text" class="mt-1"
                                                            placeholder="Zip Code" />
                                                        <InputError class="mt-2" :message="form.errors.zip" />
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>

                                    <!-- Practice Details Section -->
                                    <div class="card mb-4 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="fas fa-hospital me-2"></i>Practice Details
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row justify-content-end" >
                                                <div class="form-group mb-4 text-center">
                                                    <InputLabel for="Upload practice Logo" value="Practice Logo"
                                                        class="d-block mb-2" />
                                                    <div class="position-relative d-inline-block">
                                                        <div class="profile-photo-preview mb-2"
                                                            style="width: 120px; height: 120px; border-radius: 8px; border: 3px solid #e0e0e0; overflow: hidden; margin: 0 auto;">
                                                            <img v-if="practiceLogoPreview" :src="practiceLogoPreview"
                                                                alt="Practice Logo"
                                                                style="width: 100%; height: 100%; object-fit: cover;" />
                                                            <div v-else
                                                                class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                                <i class="fas fa-building fa-3x text-muted"></i>
                                                            </div>
                                                        </div>
                                                        <label for="inputFileLogoUpload"
                                                            class="btn btn-outline-primary btn-sm"
                                                            style="cursor: pointer;">
                                                            <i class="fas fa-image me-1"></i>{{ form.practice_logo
                                                            ?'Change' : 'Upload' }} Logo
                                                        </label>
                                                        <input type="file" class="d-none" id="inputFileLogoUpload"
                                                            @change="onChangePractice_logo($event)" accept="image/*" />
                                                    </div>
                                                    <InputError class="mt-2" :message="form.errors.practice_logo" />
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="practice_name" value="Practice Name"
                                                            required />
                                                        <TextInput id="practice_name" v-model="form.practice_name"
                                                            type="text" class="mt-1 " placeholder="Practice Name"
                                                            required />
                                                        <InputError class="mt-2" :message="form.errors.practice_name" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="practice_Email" value="Practice Email"
                                                            required />
                                                        <TextInput id="practice_email" v-model="form.practice_email"
                                                            type="email" class="mt-1 " placeholder="Practice Email"
                                                            required />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_email" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="mobile" value="Practice Mobile" />
                                                        <TextInput id="mobile" v-model="form.practice_mobile"
                                                            type="text" class="mt-1  phone-validation"
                                                            autocomplete="mobile" placeholder="Mobile Number"
                                                            minlength="10" maxlength="10" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_mobile" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="address" value="Address 1" />
                                                        <TextInput id="address" v-model="form.practice_street_address1"
                                                            type="text" class="mt-1 " placeholder="address 1" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_street_address1" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="state" value="Country" />
                                                        <BaseSelect id="country" v-model="form.practice_country" placeholder="Select Country" 
                                                            >
                                                             <template v-for="country in countries"
                                                                :key="country.isoCode">
                                                                <option :value="country.name">{{ country.name }}
                                                                </option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_country" />
                                                    </div>
                                                </div>
                                             <div class="col-md-6 align-content-center text-end">
                                                    <div class="text-primary pointer" data-toggle="collapse"
                                                        data-target="#PracticePersonalDetails"
                                                        aria-expanded="false" aria-controls="PracticePersonalDetails">
                                                        <i class="fas" :class="practicePersonalDetailsOpen ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                                                        {{ practicePersonalDetailsOpen ? 'Less' : 'More' }} Practice
                                                        Details
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row collapse" id="PracticePersonalDetails">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="mobile" value="Practice Primary Contact" />
                                                        <TextInput id="practice_primary_contact"
                                                            v-model="form.practice_primary_contact" type="text"
                                                            class="mt-1  practice_primary_contact"
                                                            autocomplete="practice_primary_contact"
                                                            placeholder="practice Primary contact Number" minlength="10"
                                                            maxlength="10" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_primary_contact" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="address" value="Address 2" />
                                                        <TextInput id="address" v-model="form.practice_street_address2"
                                                            type="text" class="mt-1 " placeholder="Address 2" />
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_street_address2" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="practice_city" value="City" />
                                                        <TextInput id="practice_city" v-model="form.practice_city"
                                                            type="text" class="mt-1 " placeholder="City" />
                                                        <InputError class="mt-2" :message="form.errors.practice_city" />
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="state" value="State" />
                                                        <BaseSelect id="state" v-model="form.practice_state" placeholder="Select State">
                                                            
                                                            <template v-for="state in practiceStates"
                                                                :key="state.isoCode">
                                                                <option :value="state.name">{{ state.name }}
                                                                </option>
                                                            </template>
                                                        </BaseSelect>
                                                        <InputError class="mt-2"
                                                            :message="form.errors.practice_state" />
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <InputLabel for="practice_zip" value="Zip Code" />
                                                        <TextInput id="practice_zip" v-model="form.practice_zip"
                                                            type="text" class="mt-1 " placeholder="Zip Code" />
                                                        <InputError class="mt-2" :message="form.errors.practice_zip" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Security Questions Section -->
                                    <div class="card mb-4 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security
                                                Questions
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <InputLabel for="secret_question " value="Secret Question"
                                                    class="required-field" required />
                                                <BaseSelect v-model="form.question_id" id="secret_question"
                                                    name="secret_question"
                                                    placeholder="Select any secret question" required>
                                                    <option value="">Select any secret question</option>
                                                    <template v-for="row in questions" :key="row.id">
                                                        <option :value="row.id">{{ row.question }}</option>
                                                    </template>
                                                </BaseSelect>
                                                <InputError class="mt-2" :message="form.errors.question_id" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="secret_question " value="Secret Question Answer"
                                                    class="required-field" required />
                                                <TextInput id="secret_answer" v-model="form.secret_answer" type="text"
                                                    class="mt-1 " name="secret_answer" required
                                                    autocomplete="secret_answer" placeholder="Secret Question Answer" />
                                                <InputError class="mt-2" :message="form.errors.secret_answer" />
                                            </div>

                                            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                                                class="mt-4 p-3 bg-light rounded">
                                                <div class="d-flex align-items-start">
                                                    <Checkbox id="terms" v-model:checked="form.terms" name="terms"
                                                        required class="mt-1" />
                                                    <label class="ms-2 mb-0" for="terms" style="cursor: pointer;">
                                                        I agree to the
                                                        <a target="_blank" :href="route('terms.show')"
                                                            class="text-primary text-decoration-none">
                                                            Terms of Service
                                                        </a>
                                                        and
                                                        <a target="_blank" :href="route('policy.show')"
                                                            class="text-primary text-decoration-none">
                                                            Privacy Policy
                                                        </a>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Submit Section -->
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                                        <Link :href="route('login')" class="text-primary text-decoration-none">
                                            <i class="fas fa-arrow-left me-1"></i>Already registered? Login here
                                        </Link>
                                        <PrimaryButton class="btn btn-primary btn-lg px-5" type="submit"
                                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                            <span v-if="form.processing">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                                    aria-hidden="true"></span>
                                                Creating Account...
                                            </span>
                                            <span v-else>
                                                <i class="fas fa-user-plus me-2"></i>Create Account
                                            </span>
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Right Side - Video Background -->
                    <div class="col-sm-6 d-none d-md-block bg-primary position-relative overflow-hidden rounded-end-4">
                        <div class="position-relative  d-flex  p-2 justify-content-center align-items-center text-center" style="margin-top: 250px;">
                                     <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal pt-0 shadow rounded" :showItems="1"
                  :centeredSlides="true" :spaceBetween="10" :pagination="{
                      clickable: true,
                    }" :breakpoints="{
                  '640': {
                    slidesPerView: 1,
                    spaceBetween: 20,
                  },
                  '768': {
                    slidesPerView: 1,
                    spaceBetween: 40,
                  },
                  '1024': {
                    slidesPerView: 1,
                    spaceBetween: 50,
                  },
                  }" :modules="[Autoplay,Virtual,Pagination]" :loop="true" :autoplay="{ delay: 2500 }">
                <SwiperSlide>
                  <img class="img-responsive" src="/images/webpages/appss_2.webp" alt="emr-two.webp" />
                </SwiperSlide>

                <SwiperSlide>
                  <img class="img-responsive" src="/images/webpages/appss_1.webp" alt="emr-three.webp" />
                </SwiperSlide>               
              </Swiper>     
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout2>
</template>

<style scoped>
.plan-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.plan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.plan-card.border-primary {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-width: 2px !important;
}

.transition-all {
    transition: all 0.3s ease;
}

.cursor-pointer {
    cursor: pointer;
}

.profile-photo-preview {
    transition: all 0.3s ease;
}

.profile-photo-preview:hover {
    border-color: #0d6efd !important;
    transform: scale(1.05);
}

.card {
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
    border-bottom: 2px solid #f0f0f0;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.alert {
    border-radius: 8px;
    border: none;
}

.mySwiper {
    height: 100vh;
    overflow: hidden;
}

.mySwiper .swiper-slide {
    height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}

.sign-in-detail {
    width: 100%;
    max-width: 100%;
}

@media (max-width: 768px) {
    .sign-in-from {
        padding: 1.5rem !important;
    }

    .card-body {
        padding: 1rem !important;
    }
}
</style>