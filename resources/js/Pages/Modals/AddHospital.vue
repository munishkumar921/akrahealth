<script setup>
import { ref,watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import DatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import InputError from "@/Components/InputError.vue";
import { Country, State } from 'country-state-city';
 
const form = useForm({
    name: "",
    email: "",
    phone: "",
    address: "",
    city: "",
    state: "",
    zip: "",
    street_address1: "",
    street_address2: "",
    country: "",
    website: "",
    primary_contact: "",
    npi: "",
    tax_id: "",
    about: "",
    weight_unit: "",
    height_unit: "",
    temp_unit: "",
    timings: [],
});

const isValidated = ref(false);
const countries = Country.getAllCountries();
const personalStates = ref([]);

const emit = defineEmits(["close", "submit"]);

const submitForm = () => {
    isValidated.value = true;
    
    form.post(route('superAdmin.hospital.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: () => {
            // Keep validation state on error
            console.error('Form submission failed');
        }
    });
};

const closeModal = () => {
    form.reset();
    isValidated.value = false;
    emit("close");
};

const addTiming = () => {
    form.timings.push({
        day_of_week: "",
        is_closed: false,
        open_time: "09:00",
        close_time: "17:00",
    });
};

const removeTiming = (index) => {
    form.timings.splice(index, 1);
};

const weight_units = ['kg', 'lb'];
const height_units = ['cm', 'ft/in'];
const temp_units = ['°C', '°F'];
const days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
 

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
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <div class="row">
            <div class="col-md-6">
                <label for="name" class="form-label">Clinic Name *</label>
                <BaseInput
                    v-model="form.name"
                    name="name"
                    placeholder="Enter Clinic name"
                    type="text"
                    required
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <BaseInput
                    v-model="form.email"
                    name="email"
                    placeholder="Enter email address"
                    type="email"
                />
                <InputError :message="form.errors.email" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <BaseInput
                    v-model="form.phone"
                    name="phone"
                    placeholder="Enter phone number"
                    type="text"
                />
                <InputError :message="form.errors.phone" />
            </div>
            <div class="col-md-6">
                <label for="website" class="form-label">Website</label>
                <BaseInput
                    v-model="form.website"
                    name="website"
                    placeholder="Enter website URL"
                    type="url"
                />
                <InputError :message="form.errors.website" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="primary_contact" class="form-label">Primary Contact</label>
                <BaseInput
                    v-model="form.primary_contact"
                    name="primary_contact"
                    placeholder="Enter primary contact"
                    type="text"
                />
                <InputError :message="form.errors.primary_contact" />
            </div>
            <div class="col-md-6">
                <label for="npi" class="form-label">NPI</label>
                <BaseInput
                    v-model="form.npi"
                    name="npi"
                    placeholder="Enter NPI"
                    type="text"
                />
                <InputError :message="form.errors.npi" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="street_address1" class="form-label">Street Address 1</label>
                <BaseInput
                    v-model="form.street_address1"
                    name="street_address1"
                    placeholder="Enter street address 1"
                    type="text"
                />
                <InputError :message="form.errors.street_address1" />
            </div>
            <div class="col-md-6">
                <label for="street_address2" class="form-label">Street Address 2</label>
                <BaseInput
                    v-model="form.street_address2"
                    name="street_address2"
                    placeholder="Enter street address 2"
                    type="text"
                />
                <InputError :message="form.errors.street_address2" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="city" class="form-label">City</label>
                <BaseInput
                    v-model="form.city"
                    name="city"
                    placeholder="Enter city"
                    type="text"
                />
                <InputError :message="form.errors.city" />
            </div>
            <div class="col-md-6">
                <label for="zip" class="form-label">Zip Code</label>
                <BaseInput
                    v-model="form.zip"
                    name="zip"
                    placeholder="Enter zip code"
                    type="text"
                />
                <InputError :message="form.errors.zip" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label for="country_id" class="form-label">Country</label>
                <BaseSelect v-model="form.country" placeholder="Select Country">
                     <option
                        v-for="country in countries"
                        :key="country.isoCode"
                        :value="country.name"
                    >
                        {{ country.name }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.country_id" />
            </div>
            <div class="col-md-6">
                <label for="state" class="form-label">State</label>
                <BaseSelect v-model="form.state" placeholder="Select State">
                     <option
                        v-for="state in personalStates"
                        :key="state.isoCode"
                        :value="state.name"
                    >
                        {{ state.name }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.state" />
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <label for="weight_unit" class="form-label">Weight Unit</label>
                <BaseSelect v-model="form.weight_unit" placeholder="Select Weight Unit">
                     <option
                        v-for="unit in weight_units"
                        :key="unit"
                        :value="unit"
                    >
                        {{ unit }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.weight_unit" />
            </div>
            <div class="col-md-4">
                <label for="height_unit" class="form-label">Height Unit</label>
                <BaseSelect v-model="form.height_unit" placeholder="Select Height Unit">
                     <option
                        v-for="unit in height_units"
                        :key="unit"
                        :value="unit"
                    >
                        {{ unit }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.height_unit" />
            </div>
            <div class="col-md-4">
                <label for="temp_unit" class="form-label">Temperature Unit</label>
                <BaseSelect v-model="form.temp_unit" placeholder="Select Temperature Unit">
                     <option
                        v-for="unit in temp_units"
                        :key="unit"
                        :value="unit"
                    >
                        {{ unit }}
                    </option>
                </BaseSelect>
                <InputError :message="form.errors.temp_unit" />
            </div>
        </div>
        
        <div class="mb-3">
            <label for="about" class="form-label">About</label>
            <textarea
                v-model="form.about"
                name="about"
                class="form-control"
                placeholder="Enter description about the hospital"
                rows="3"
            ></textarea>
            <InputError :message="form.errors.about" />
        </div>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Operating Hours</label>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="addTiming">
                    <i class="fa fa-plus me-1"></i>Add Timing
                </button>
            </div>
            
            <div v-if="form.timings.length === 0" class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>No operating hours added yet. Click "Add Timing" to get started.
            </div>
            
            <div v-for="(timing, index) in form.timings" :key="index" class="border p-3 mb-2 rounded">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Day</label>
                        <BaseSelect v-model="timing.day_of_week" placeholder="Select Day">
                             <option
                                v-for="day in days_of_week"
                                :key="day"
                                :value="day"
                            >
                                {{ day }}
                            </option>
                        </BaseSelect>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                v-model="timing.is_closed" 
                                class="form-check-input" 
                                :id="'closed-' + index" 
                            />
                            <label class="form-check-label" :for="'closed-' + index">
                                Closed
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3" v-if="!timing.is_closed">
                        <label class="form-label">Open Time</label>
                        <DatePicker
                            v-model="timing.open_time"
                            type="time"
                        />
                    </div>
                    <div class="col-md-3" v-if="!timing.is_closed">
                        <label class="form-label">Close Time</label>
                        <DatePicker
                            v-model="timing.close_time"
                            type="time"
                        />
                    </div>
                    <div :class="timing.is_closed ? 'col-md-7' : 'col-md-1'">
                        <button 
                            type="button" 
                            class="btn btn-danger btn-sm" 
                            @click="removeTiming(index)"
                            title="Remove timing"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <InputError v-if="form.errors[`timings.${index}.day_of_week`]" :message="form.errors[`timings.${index}.day_of_week`]" />
                <InputError v-if="form.errors[`timings.${index}.open_time`]" :message="form.errors[`timings.${index}.open_time`]" />
                <InputError v-if="form.errors[`timings.${index}.close_time`]" :message="form.errors[`timings.${index}.close_time`]" />
            </div>
        </div>
        
        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button 
                type="submit" 
                class="btn btn-primary" 
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
            <button
                type="button"
                class="btn btn-danger"
                @click="closeModal"
                :disabled="form.processing"
            >
                Cancel
            </button>
        </div>
    </form>
</template>