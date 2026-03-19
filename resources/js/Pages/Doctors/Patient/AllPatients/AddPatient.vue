<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { Country, State } from 'country-state-city';
import BasInput from '@/Components/Common/Input/BaseInput.vue';
import InputError from "@/Components/InputError.vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";

const props = defineProps({
    patient: Object,
 });

const emit = defineEmits(["close", "submit"]);

const isValidated = ref(false);

const countries = Country.getAllCountries();
const personalStates = ref([]);

const form = useForm({
    id: props.patient?.user?.id ?? null,
    first_name: props.patient?.user?.first_name ?? "",
    last_name: props.patient?.user?.last_name ?? "",
    sex: props.patient?.user?.sex ?? "",
    email: props.patient?.user?.email ?? "",
    mobile: props.patient?.user?.mobile ?? "",
    is_active: props.patient?.is_active ?? 1,
    street_address1: props.patient?.user?.address?.address_1 ?? "",
    street_address2: props.patient?.user?.address?.address_2 ?? "",
    city: props.patient?.user?.address?.city ?? "",
    state: props.patient?.user?.address?.state ?? "",
    country: props.patient?.user?.address?.country ?? "",
    zip: props.patient?.user?.address?.zip ?? "",
 });

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


const submitForm = () => {
    isValidated.value = true;

    const isEdit = !!form.id;
    const routeName = isEdit
        ? route("doctor.patient.update", form.id)
        : route("doctor.patient.store");

    const method = isEdit ? "put" : "post";

    form[method](routeName, {
        onSuccess: () => {
            emit("submit");
            closeModal();
        },
    });
};

const closeModal = () => {
    emit("close");
    form.reset();
};

</script>


<template>
     
    <div class="modal-body">
        <form @submit.prevent="submitForm"  novalidate class="needs-validation"
            :class="{ 'was-validated': isValidated }">
            <div class="row g-3">
                <!-- avatar -->
                <!-- <div class="form-group mb-4 text-center">
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
                                                            <i class="fas fa-camera me-1"></i>{{ form.profile_photo_path ? 'Change' : 'Upload' }} Photo
                                                        </label>
                                                        <input type="file" class="d-none" id="inputFileUpload"
                                                            @change="onChangeFileUpload($event)" accept="image/*" />
                                                    </div>
                                                    <InputError class="mt-2" :message="form.errors.profile_photo_path" />
                                                </div> -->
                <!-- First Name -->
                <div class="col-md-6">
                      <BasInput v-model="form.first_name"  label="First Name" required placeholder="First Name" :error="form.errors.first_name"/>
                  </div>

                <!-- Last Name -->
                <div class="col-md-6">
                     <BasInput v-model="form.last_name"  label="Last Name" required placeholder="Last Name"  :error="form.errors.last_name"/>
                  </div>

                <!-- Sex -->
                <div class="col-md-6">
                    <BaseSelect v-model="form.sex" label="Gender" type="select" placeholder="Select Gender">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Non-binary">Non-binary</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </BaseSelect>

                    <InputError :message="form.errors.sex"/>
                  </div>

                <!-- Email -->
                <div class="col-md-6">
                     <BaseInput v-model="form.email" label="Email" type="email" placeholder="Email" required :error="form.errors.email" /> 
                 </div>

                <!-- Mobile -->
                <div class="col-md-6">
                   <BaseInput v-model="form.mobile" label="Mobile" type="text" placeholder="Mobile" required :error="form.errors.mobile" />
                 </div>

                
                <!-- Street Address 1 -->
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address1" label="Street Address 1" type="text" placeholder="Street Address 1" required :error="form.errors.street_address1" />
                </div>

                <!-- Street Address 2 -->
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address2" label="Street Address 2" type="text" placeholder="Street Address 2" :error="form.errors.street_address2" />
                </div>

                <!-- City -->
                <div class="col-md-6">
                    <BaseInput v-model="form.city" label="City" type="text" placeholder="City" required :error="form.errors.city" />
                </div>

                <!-- Country -->
                <div class="col-md-6">
                    <BaseSelect v-model="form.country" label="Country" type="select" required placeholder="Select Country" :error="form.errors.country">
                         <template v-for="country in countries" :key="country.isoCode">
                        <option :value="country.name">{{ country.name }}</option>
                        </template>
                    </BaseSelect>
                 </div>
                <!-- State -->
                <div class="col-md-6">
                     <BaseSelect v-model="form.state" label="State" type="select"   placeholder="Select State" :error="form.errors.state">
                          <template v-for="state in personalStates" :key="state.isoCode">
                         <option :value="state.name">{{ state.name }}</option>
                        </template>
                     </BaseSelect>
                 </div>
                <!-- Zip Code -->
                <div class="col-md-6">
                    <BaseInput v-model="form.zip" label="Zip Code" type="text" placeholder="Zip Code" required :error="form.errors.zip" />
                 </div>
                 <!-- Status -->
                <div class="col-md-6">
                    <BaseSelect v-model="form.is_active" label="Status" type="select" required placeholder="Select Status" :error="form.errors.status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </BaseSelect>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ form.processing ? 'Saving...' : 'Submit' }}</button>
                   <button type="button" class="btn btn-danger" @click="closeModal" :disabled="form.processing">Close</button>
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