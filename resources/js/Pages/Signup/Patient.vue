<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Country, State } from 'country-state-city';
import Checkbox from '@/Components/Checkbox.vue';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout2 from '@/Layouts/AppLayout2.vue';
import { Autoplay, Pagination } from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/bundle';
const page = usePage();
const props = defineProps({
    questions: Object,
    patient:Object
 });

const form = useForm({
    id: props.patient?.id ?? null,
    first_name:props.patient?.first_name ?? '',
    last_name: props.patient?.last_name ?? '',
    mobile: props.patient?.mobile ?? '',
    email: props.patient?.email ?? '',
    dob: props.patient?.dob ?? '',
    sex: props.patient?.sex ?? '',
    password: '',
    password_confirmation: '',
    street_address1:props.patient?.address_1 ?? '',
    street_address2:props.patient?.address_2 ?? '',
    city:props.patient?.city ?? '',
    state:props.patient?.state ?? '',
    zip:props.patient?.zip ?? '',
    country:'',
    question_id: '',
    secret_answer: '',
    terms: false,
    profile_photo: '',
    is_active: props.patient?.is_active ?? 0,
    referral_code: props.patient?.registration_code ?? '',

});
const genders=['Male','Female','Other'];
const profilePhotoUrl = ref(null);

const profilePhotoPreview = computed(() => {
    if (form.profile_photo && form.profile_photo instanceof File) {
        // Clean up previous URL
        if (profilePhotoUrl.value) {
            URL.revokeObjectURL(profilePhotoUrl.value);
        }
        // Create new URL
        profilePhotoUrl.value = URL.createObjectURL(form.profile_photo);
    }
    return profilePhotoUrl.value;
});

const submit = () => {
    form.post(route('signup.patient.store'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    if (page.props.errors) {
        form.reset('password', 'password_confirmation');
    }
});

onUnmounted(() => {
    form.reset('password', 'password_confirmation');
});
const countries = Country.getAllCountries();
const personalStates = ref([]);

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
</script>
<template>
        <AppLayout2 title="Sign Up" description="Create your account">
          <section class="sign-in-page">
                <div class="container bg-white p-0">
                    <div class="row no-gutters">
                        <div class="col-sm-6 align-self-center">
                         <div class="col-sm-12 align-self-center">
                            <div class="sign-in-from p-4 p-md-5">
                                <!-- Header -->
                                 <div class="text-center mb-4">
                                    <h1 class="dark-signin mb-3 font-size-32 fw-bold">Create Your Account</h1>
                                    <p class="text-muted">Join AkraHealth and start your telehealth journey</p>
                                </div>
                                     <div class="app">
                                    <form @submit.prevent="submit" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
                                    
                                        <!-- Personal Information Section -->
                                        <div class="card mb-4 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Profile Photo -->
                                                <div class="form-group mb-4 text-center">
                                                    <InputLabel for="Upload profile picture" value="Profile Picture" class="d-block mb-2" />
                                                    <div class="position-relative d-inline-block">
                                                        <div class="profile-photo-preview mb-2" style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #e0e0e0; overflow: hidden; margin: 0 auto;">
                                                            <img v-if="profilePhotoPreview" :src="profilePhotoPreview" 
                                                                alt="Profile" style="width: 100%; height: 100%; object-fit: cover;" />
                                                            <div v-else class="d-flex align-items-center justify-content-center h-100 bg-light">
                                                                <i class="fas fa-user fa-3x text-muted"></i>
                                                            </div>
                                                        </div>
                                                        <label for="inputFileUpload" class="btn btn-outline-primary btn-sm" style="cursor: pointer;">
                                                            <i class="fas fa-camera me-1"></i>{{ form.profile_photo ? 'Change' : 'Upload' }} Photo
                                                        </label>
                                                        <input type="file" class="d-none" id="inputFileUpload"
                                                            @change="onChangeFileUpload($event)" accept="image/*" />
                                                    </div>
                                                    <InputError class="mt-2" :message="form.errors.profile_photo" />
                                                </div>

                                                <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <InputLabel for="first_name" value="First Name" class="required-field" required />
                                                    <TextInput id="first_name" type="text" v-model="form.first_name"
                                                        class="mt-1" required autofocus autocomplete="first_name" placeholder="First Name" />
                                                    <InputError class="mt-2" :message="form.errors.first_name" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <InputLabel for="last_name" value="Last Name" class="required-field" required/>
                                                    <TextInput id="last_name" type="text" v-model="form.last_name" class="mt-1 "
                                                    required autofocus   placeholder="Last Name" />
                                                    <InputError class="mt-2" :message="form.errors.last_name" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                            <InputLabel for="gender" class="required-field" value="Gender" required />
                                            <select v-model="form.sex" id="gender"
                                                class="form-control h-45 border-radius-8" required="required">
                                                <option disabled value="">Select your Gender</option>
                                                <template v-for="row in genders" :key="row">
                                                    <option :value="row">{{ row }}</option>
                                                </template>
                                            </select>
                                            <InputError class="mt-2" :message="form.errors.sex" />
                                        </div>
                                        
                                       </div>
                                       <div class="col-sm-6">
                                        <div class="form-group">
                                            <InputLabel for="dob" value="Date of Birth" class="required-field"/>
                                            <DatePicker id="dob" v-model="form.dob"  class="mt-1" 
                                             placeholder="Date of Birth" />
                                            <InputError class="mt-2" :message="form.errors.dob" />
                                        </div>
                                        </div>
                                         </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <InputLabel for="email" value="Email" class="required-field" />
                                                <TextInput id="email" v-model="form.email" type="email" class="mt-1 " readonly
                                                    placeholder="Email" required autocomplete="email" />
                                                <InputError class="mt-2" :message="form.errors.email" />
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="mobile" value="Mobile" />
                                                    <TextInput id="mobile" v-model="form.mobile" type="text"
                                                        class="mt-1  phone-validation" autocomplete="mobile"
                                                        placeholder="Mobile Number" minlength="10" maxlength="10" />
                                                    <InputError class="mt-2" :message="form.errors.mobile" />
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="address" value="Address 1" required/>
                                                    <TextInput id="address" v-model="form.street_address1" type="text" class="mt-1 "
                                                        required
                                                        placeholder="address 1" />
                                                    <InputError class="mt-2" :message="form.errors.street_address1" />
                                                </div>
                                            </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="address" value="Address 2" />
                                                    <TextInput id="address" v-model="form.street_address2" type="text" class="mt-1 "
                                                         placeholder="Address 2" />
                                                    <InputError class="mt-2" :message="form.errors.street_address2" />
                                                </div>
                                             </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="city" value="City" required/>
                                                    <TextInput id="city" v-model="form.city" type="text" class="mt-1 "
                                                        required
                                                        placeholder="City" />
                                                    <InputError class="mt-2" :message="form.errors.city" />
                                                </div>
                                             </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="state" value="Country" />
                                                    <select id="state" v-model="form.country" type="text" class="mt-1 form-control">
                                                    <option disabled value="">Select your coutry</option>
                                                    <template v-for="country in countries" :key="country.isoCode">
                                                        <option :value="country.name">{{ country.name }}</option>
                                                    </template>
                                                      </select>
                                                    <InputError class="mt-2" :message="form.errors.country" />
                                                </div>
                                            </div>
                                               <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="state" value="State" />
                                                    <select id="state" v-model="form.state" type="text" class="mt-1 form-control">
                                                    <option disabled value="">Select your state</option>
                                                     <template v-for="state in personalStates" :key="state.isoCode">
                                                        <option :value="state.name">{{ state.name }}</option>
                                                    </template>
                                                      </select>
                                                    <InputError class="mt-2" :message="form.errors.state" />
                                                </div>
                                            </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="zip" value="Zip Code" required/>
                                                    <TextInput id="zip" v-model="form.zip" type="text" class="mt-1 "
                                                        required
                                                        placeholder="Zip Code" />
                                                    <InputError class="mt-2" :message="form.errors.zip" />
                                                </div>
                                             </div>
                                              <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="password" value="Password" required
                                                        class="after:ml-0.5 after:text-red-500 block  font-medium text-slate-700" />
                                                    <TextInput id="password" v-model="form.password" type="password"
                                                        class="mt-1 " required autocomplete="password" placeholder="Password" />
                                                    <InputError class="mt-2" :message="form.errors.password" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <InputLabel for="password_confirmation" value="Confirm Password" required
                                                        class=" after:ml-0.5 after:text-red-500 block  font-medium text-slate-700" />
                                                    <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                                        placeholder="Confirm Password" type="password" class="mt-1 " required
                                                        autocomplete="password" />
                                                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                                </div>
                                            </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Security Questions Section -->
                                        <div class="card mb-4 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Questions</h5>
                                            </div>
                                            <div class="card-body">
                                        <div class="form-group">
                                            <InputLabel for="secret_question " value="Secret Question" class="required-field" required/>
                                            <select v-model="form.question_id" id="secret_question" name="secret_question"
                                                class="form-control h-45 border-radius-8" required
                                                placeholder="Select any secret question">
                                                <option value="">Select any secret question</option>
                                                <template v-for="row in questions" :key="row.id">
                                                    <option :value="row.id">{{ row.question }}</option>
                                                </template>
                                            </select>
                                            <InputError class="mt-2" :message="form.errors.question_id" />
                                        </div>
                                        <div class="form-group">
                                            <InputLabel for="secret_question " value="Secret Question Answer" class="required-field" required />
                                            <TextInput id="secret_answer" v-model="form.secret_answer" type="text" class="mt-1 "
                                                name="secret_answer" required autocomplete="secret_answer" 
                                                placeholder="Secret Question Answer" />
                                            <InputError class="mt-2" :message="form.errors.secret_answer" />
                                        </div>
                                            <div class="form-group">
                                                <InputLabel for="referral_code " value="Referral code" class="required-field" />
                                                <TextInput id="referral_code" v-model="form.referral_code" type="text" class="mt-1 "
                                                    name="referral_code" required autocomplete="referral_code"
                                                    placeholder="referral_code" />
                                                <InputError class="mt-2" :message="form.errors.referral_code" />
                                            </div>

                                                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4 p-3 bg-light rounded">
                                                    <div class="d-flex align-items-start">
                                                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="mt-1" />
                                                        <label class="ms-2 mb-0" for="terms" style="cursor: pointer;">
                                                            I agree to the
                                                            <a target="_blank" :href="route('terms.show')" class="text-primary text-decoration-none">
                                                                Terms of Service
                                                            </a>
                                                            and
                                                            <a target="_blank" :href="route('policy.show')" class="text-primary text-decoration-none">
                                                                Privacy Policy
                                                            </a>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Section -->
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                                            <Link :href="route('login')" class="text-primary text-decoration-none">
                                                <i class="fas fa-arrow-left me-1"></i>Already registered? Login here
                                            </Link>
                                            <PrimaryButton 
                                                class="btn btn-primary btn-lg px-5" 
                                                type="submit"
                                                :class="{ 'opacity-50': form.processing }" 
                                                :disabled="form.processing"
                                            >
                                                <span v-if="form.processing">
                                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
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
             </div>
              <div class="col-sm-6 d-none d-md-block">
                           <div class="d-flex h-100 align-items-start bg-primary position-sticky overflow-hidden" style="top: 0; height: 100vh;">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); opacity: 0.9;"></div>
                            <Swiper ref="swiperRef" :slidesPerView="1" :modules=[Autoplay,Pagination] :pagination="{ clickable: true }"
                                :loop="true" :autoplay="{ delay: 3000, disableOnInteraction: false }" class="mySwiper position-relative w-100" style="z-index: 1; height: 100vh;">
                                <SwiperSlide>
                                    <div class="sign-in-detail text-center p-5 d-flex flex-column justify-content-start" style="min-height: 100vh; padding-top: 5rem;">
                                        <img src="/images/doctor.webp" class="img-fluid mb-4" alt="logo" style="max-height: 300px; object-fit: contain;">
                                        <h3 class="mb-3 text-white fw-bold">Join AkraHealth Today</h3>
                                        <h5 class="mb-3 text-white">Sign Up and Enjoy the Benefits of Free EMR/EHR Services</h5>
                                        <p class="text-white-50">Start your telehealth journey with our comprehensive platform</p>
                                    </div>
                                </SwiperSlide>
                                <SwiperSlide>
                                    <div class="sign-in-detail text-center p-5 d-flex flex-column justify-content-start" style="min-height: 100vh; padding-top: 5rem;">
                                        <img src="/images/lab.webp" class="img-fluid mb-4" alt="logo" style="max-height: 300px; object-fit: contain;">
                                        <h3 class="mb-3 text-white fw-bold">Modern Healthcare Solutions</h3>
                                        <h5 class="mb-3 text-white">Access Advanced Tools and Features</h5>
                                        <p class="text-white-50">Manage your practice efficiently with our integrated platform</p>
                                    </div>
                                </SwiperSlide>
                            </Swiper> 
                           </div>
                        </div>
                    </div>
                </div>
         </section>
        </AppLayout2>

</template>