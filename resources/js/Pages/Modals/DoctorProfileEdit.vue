<script setup>
import { useForm } from '@inertiajs/vue3';
import { Country, State } from 'country-state-city';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import VueMultiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import { ref, watch } from 'vue';
import BaseSelect from '../../Components/Common/Input/BaseSelect.vue';
import BaseInput from '../../Components/Common/Input/BaseInput.vue';
import BaseFileInput from '../../Components/Common/Input/BaseFileInput.vue';

const props = defineProps({
     doctor: Object,
     specialties: Array,
    doctorSpecialty: Object,
    row: {}
});

const form = useForm({
    id: '',
    first_name: '',
    last_name: '',
    mobile: '',
    sex: '',
    experience:'',
    certification:'',
    about: '',
    street_address1: '',
    street_address2: '',
    city: '',
    state: '',
    zip: '',
    country: '',
    specialities: [],
    profile_photo_path: null,
});

// Profile photo preview
const photoPreview = ref(null);
const photoInput = ref(null);
const countries = Country.getAllCountries();
const personalStates = ref([]);
const emit = defineEmits(['close', 'submit']);

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
// Handle file upload
const onChangeFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_photo_path = file;
        // Clear any previous errors for profile_photo
        if (form.errors.profile_photo_path) {
            form.clearErrors('profile_photo_path');
        }
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Clear photo file input
const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
    form.profile_photo_path = null;
    photoPreview.value = null;
};

// Update form with doctor data
const update = (doctor) => {
    form.clearErrors();
    form.reset();
    form.id = doctor?.id || '';
    form.user_id = doctor?.user?.id || '';
    form.first_name = doctor?.first_name || '';
    form.last_name = doctor?.last_name || '';
    form.sex = doctor?.user?.sex || '';
    form.mobile = doctor?.user?.mobile || '';
    form.experience=doctor?.experience||'';
    form.certification=doctor?.certification||'';
    form.description = doctor?.description || '';
    form.street_address1 = doctor?.user?.address?.address_1 || '';
    form.street_address2 = doctor?.user?.address?.address_2 || '';
    form.city = doctor?.user?.address?.city || '';
    form.state = doctor?.user?.address?.state || '';
    form.zip = doctor?.user?.address?.zip || '';
    form.country = doctor?.user?.address?.country || '';
    form.specialities = doctor?.specialities?.map(s => s.name) || [];
    form.profile_photo_path = null; // Reset file input
    form.about = doctor?.about || '';
    // Set photo preview if profile_photo_url exists
    if (doctor?.profile_photo_url) {
        photoPreview.value = doctor.profile_photo_url;
    } else if (doctor?.profile_photo_path) {
        photoPreview.value = `/storage/${doctor.profile_photo_path}`;
    } else {
        photoPreview.value = null;
    }
    // Clear file input
    clearPhotoFileInput();
};

// Submit form
const submit = () => {
        form.post(route('doctor.profile.update'), {
            onSuccess: () => {
                form.reset();
                photoPreview.value = null;
                clearPhotoFileInput();
                emit('submit');
                closeModal();
            },
            onError: () => {
             }
        });
};

const closeModal = () => {
    form.clearErrors();
    emit("close");
}
defineExpose({
    update
});

// Watch for doctor prop changes
watch(() => props.doctor, (newDoctor) => {
    if (newDoctor) {
        update(newDoctor);
    }
}, { immediate: true });
</script>

<template>
    <form @submit.prevent="submit">
        <div class="row">
            <!-- Profile Photo Upload -->
            <div class="col-md-12">
                <div class="form-group">
                    <div class="custom-file">
                        <BaseFileInput 
                            id="inputFileUpload"
                            ref="photoInput"
                            @change="onChangeFileUpload($event)" 
                            accept="image/*" 
                        />
                        <InputError class="mt-2" :message="form.errors.profile_photo_path" />
                    </div>
                </div>
            </div>
            
            <!-- Form Fields -->
            <div class="col-md-12">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="form-group">
                            <BaseInput v-model="form.first_name" label="First Name" placeholder="First Name" required :error="form.errors.first_name" />
                        </div>
                    </div>
                    
                    <div class="col-sm-12 col-md-6">
                        <div class="form-group">
                              <BaseInput v-model="form.last_name" label="Last Name" placeholder="Last Name" required :error="form.errors.last_name" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                        <BaseInput v-model="form.mobile" label="Mobile" placeholder="Mobile" required :error="form.errors.mobile" />                            
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                          <BaseSelect label="Gender" v-model="form.sex">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                          </BaseSelect>
                          <InputError class="mt-2" :message="form.errors.gender" />
                        </div>
                </div>
                </div>
                 <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                                <BaseSelect v-model="form.specialities" label="Specialty" type="select" multiple  placeholder="Select Specialty" :error="form.errors.specialities">
                                <template v-for="row in specialties" :key="row">
                                    <option :value="row.name">{{ row.name }}</option>
                                </template>
                             </BaseSelect>
                         </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <BaseInput v-model="form.experience" label="Experience" type="text" placeholder="Experience" :error="form.errors.experience" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <BaseFileInput id="certification" label="Certification"   v-model="form.certification" :error="form.errors.certification" />
                         </div>
                    </div>
                    
                      <!-- Street Address 1 -->
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address1" label="Street Address 1" type="text" placeholder="Street Address 1" required :error="form.errors.street_address1" />
                </div>

                <!-- Street Address 2 -->
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address2" label="Street Address 2" type="text" placeholder="Street Address 2" :error="form.errors.street_address2" />
                </div>
                        <div class="col-sm-6">
                        <div class="form-group">
                     <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required :error="form.errors.city" />

                        </div>
                    </div>
                </div>
                <div class="row">
                 
                    <div class="col-md-4">
                         <BaseSelect v-model="form.country" label="Country" type="select" required placeholder="Select Country" :error="form.errors.country">
                         <template v-for="country in countries" :key="country.isoCode">
                        <option :value="country.name">{{ country.name }}</option>
                        </template>
                    </BaseSelect>
                    </div>
            
                    <div class="col-md-4">
                        <BaseSelect v-model="form.state" label="State" type="select"   placeholder="Select State" :error="form.errors.state">
                          <template v-for="state in personalStates" :key="state.isoCode">
                         <option :value="state.name">{{ state.name }}</option>
                        </template>
                     </BaseSelect>
                    </div>
                    <div class="col-md-4">
                    <BaseInput v-model="form.zip" label="Zip Code" type="number"   placeholder="Zip Code" required :error="form.errors.zip" />
                 </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <BaseInput type="textarea" v-model="form.about" label="About" placeholder="about" required :error="form.errors.about" />    
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Submit Button -->
                     <div class="mt-4 d-flex justify-content-end gap-2">

             <button 
                type="submit" 
                class="btn btn-primary"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Processing...</span>
                <span v-else>{{ form.slug ? 'Update' : 'Submit' }}</span>
            </button>
            <button 
                type="button" 
                class="btn btn-danger"
                @click="closeModal"
                :disabled="form.processing"
            >
                Close
            </button>
            </div>
     </form>
</template>
 