<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import BaseFileInput from '@/Components/Common/Input/BaseFileInput.vue';
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";

import { Country, State } from 'country-state-city';


const props = defineProps({
    hospital: Object,
})

const emit = defineEmits(['close']);

const activeTab = ref(null);
const isValidated = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: '',
    name: '',
    main_branch_id: props.hospital?.main_branch_id === null ? props.hospital?.id : '',
    street_address1: "",
    street_address2: "",
    city: "",
    state: "",
    zip: "",
    country: "",
    phone: "",
    email: "",
    weight_unit: "kg",
    height_unit: "cm",
    hc_unit: "cm",
    practice_logo: props.hospital?.practice_logo || '',
    profile_photo_path: '',
    is_active: 1,
    timezone: "",
    timings: [],
});

// Days of the week for schedule
const daysOfWeek = [
    { value: 'Monday', label: 'Monday' },
    { value: 'Tuesday', label: 'Tuesday' },
    { value: 'Wednesday', label: 'Wednesday' },
    { value: 'Thursday', label: 'Thursday' },
    { value: 'Friday', label: 'Friday' },
    { value: 'Saturday', label: 'Saturday' },
    { value: 'Sunday', label: 'Sunday' },
];

// Add schedule entry
const addSchedule = () => {
    form.timings.push({
        weekends: 1,
        time_zone: 'Asia/Calcutta',
        day_of_week: "",
        open_time: '09:00',
        close_time: '21:00',
        is_closed: false
    });
};

// Remove schedule entry
const removeTiming = (index) => {
    form.timings.splice(index, 1);
};

const submitForm = () => {
    // Convert schedules to timings format for backend
    const timings = form.timings.map(schedule => ({
        id: schedule.id,
        weekends: schedule.weekends,
        time_zone: schedule.time_zone,
        day_of_week: schedule.day_of_week,
        open_time: schedule.is_closed ? null : schedule.open_time,
        close_time: schedule.is_closed ? null : schedule.close_time,
        is_closed: schedule.is_closed || false,
    }));

    form.timings = timings;

    form.post(route('admin.hospitals.store'), {
        onSuccess: () => {
            closeModal();
            window.location.reload();
        },
    });
};

const closeModal = () => {
    emit("close");
    form.reset();
    form.timings = [];
    isValidated.value = false;
    isEdit.value = false;
    activeTab.value = null;
}
const countries = Country.getAllCountries();
const personalStates = ref([]);



const update = (data, tab) => {
    activeTab.value = tab;
    isEdit.value = true;
    form.id = data.id;
    form.name = data.name;
    form.main_branch_id = data.main_branch_id;
    form.street_address1 = data.street_address1;
    form.street_address2 = data.street_address2;
    form.city = data.city;
    form.state = data.state;
    form.zip = data.zip;
    form.country = data.country;
    form.phone = data.phone;
    form.email = data.email;
    form.weight_unit = data.weight_unit;
    form.height_unit = data.height_unit;
    form.hc_unit = data.hc_unit;
    form.practice_logo = data.practice_logo;
    form.profile_photo_path = data?.user?.profile_photo_path || '';
    form.timezone = data.timezone || '';
    form.is_active = data.is_active ? data.is_active.toString() : '1';
    // Load schedules from data
    if (data.timings && data.timings.length > 0) {
        form.timings = data.timings.map(schedule => ({
            weekends: schedule.weekends,
            time_zone: schedule.time_zone,
            id: schedule.id,
            day_of_week: schedule.day_of_week ? schedule.day_of_week.charAt(0).toUpperCase() + schedule.day_of_week.slice(1) : '',
            open_time: schedule.open_time || '',
            close_time: schedule.close_time || '',
            is_closed: !schedule.open_time && !schedule.close_time
        }));
    } else {
        form.timings = [];
    }

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
    if (form.profile_photo_path) {
        photoPreview.value = form.profile_photo_path;
    }
    if (form.practice_logo) {
        practice_logoPreview.value = form.practice_logo;
    }
}

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

});
defineExpose({ update });

const photoPreview = ref(null);
const practice_logoPreview = ref(null);

// Handle file upload
const onChangeFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_photo_path = file;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
const onChangePracticeLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.practice_logo = file;
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            practice_logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};
const timezones = (() => {
    let zones = [];

    try {
        // Modern browsers (IANA canonical list)
        zones = Intl.supportedValuesOf('timeZone');
    } catch (e) {
        // Fallback list
        zones = [
             'Asia/Kolkata',
            'America/New_York',
            'Europe/London',
            'Asia/Dubai',
            'Asia/Singapore',
        ];
    }

    return zones
        .map(tz => {
            let offset = '';

            try {
                const now = new Date();
                const formatter = new Intl.DateTimeFormat('en-US', {
                    timeZone: tz,
                    timeZoneName: 'shortOffset',
                });

                const parts = formatter.formatToParts(now);
                const tzPart = parts.find(p => p.type === 'timeZoneName');
                offset = tzPart ? tzPart.value.replace('GMT', 'UTC') : '';
            } catch {
                offset = '';
            }

            return {
                value: tz,
                label: `${tz.replace(/_/g, ' ')} ${offset}`,
            };
        })
        .sort((a, b) => a.label.localeCompare(b.label));
})();


</script>
<template>
    <div class="form-container">
        <div class="row" v-if="activeTab === 'profile'">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="custom-file">
                        <BaseFileInput id="inputFileUpload" @change="onChangeFileUpload($event)" accept="image/*"
                            label="Profile Photo" />
                        <InputError class="mt-2" :message="form.errors.profile_photo" />
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-file">
                        <BaseFileInput id="inputFileUploadPracticeLogo" @change="onChangePracticeLogoUpload($event)"
                            accept="image/*" label="Practice Logo" />
                        <InputError class="mt-2" :message="form.errors.practice_logo" />
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" v-if="!activeTab">
                <BaseInput v-model="form.name" label="Branch Name" :error="form.errors.name"
                    placeholder="Enter branch name" />
            </div>

        </div>
         <div class="row g-3" v-if="!activeTab || activeTab === 'contact'">
            <div class="col-md-6">
                <BaseInput v-model="form.phone" label="Phone" :error="form.errors.phone" required
                    placeholder="Enter phone number" />
            </div>
            <div class="col-md-6">
                <BaseInput v-model="form.email" label="Email" :error="form.errors.email" type="email" required
                    placeholder="Enter email" />
            </div>
        </div>

        <div v-if="!activeTab || activeTab === 'location'">
            <div class="row">
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address1" label="Street Address 1" :error="form.errors.street_address1"
                        placeholder="Enter street address 1" required />
                </div>
                <div class="col-md-6">
                    <BaseInput v-model="form.street_address2" label="Street Address 2"
                        placeholder="Enter street address 2" />
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <BaseInput v-model="form.city" label="City" :error="form.errors.city" placeholder="Enter city" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <BaseSelect v-model="form.state" :error="form.errors.state">
                        <option v-for="state in personalStates" :key="state.isoCode" :value="state.name">
                            {{ state.name }}
                        </option>
                    </BaseSelect>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Country</label>
                    <BaseSelect v-model="form.country" :error="form.errors.country" required>
                        <option v-for="country in countries" :key="country.isoCode" :value="country.name">
                            {{ country.name }}
                        </option>
                    </BaseSelect>
                </div>

            </div>
        </div>


        <div v-if="!activeTab">
            <div class="row g-3">
                <div class="col-md-4">
                    <BaseInput v-model="form.zip" label="ZIP Code" :error="form.errors.zip" placeholder="Enter ZIP code" />
                </div>
                <div class="col-md-4">
                    <BaseInput v-model="form.timezone" label="Timezone" :error="form.errors.timezone"
                        placeholder="Enter timezone" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <BaseSelect v-model="form.is_active">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </BaseSelect>
                </div>
            </div>
        </div>
        <div class="mt-3" v-if="!activeTab || activeTab === 'schedule'">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Operating Hours</label>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="addSchedule">
                    <i class="fa fa-plus me-1"></i>Add Timing
                </button>
            </div>

            <div v-if="form.timings.length === 0" class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>No operating hours added yet. Click "Add Timing" to get started.
            </div>

            <div v-for="(timing, index) in form.timings" :key="index" class="border p-3 mb-2 rounded">
                <div class="row align-items-end">
                <div class="col-md-6 mb-3">
                     <BaseSelect v-model="timing.weekends" placeholder="Include Weekends in the Schedule"
                        label="Include Weekends in the Schedule" :error="form.errors.weekends">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </BaseSelect>
                    </div>
                    <div class="col-md-6 mb-3"> 
                    <BaseSelect v-model="timing.time_zone" placeholder="Select Timezone" label="Timezone"
                        :error="form.errors.time_zone">
                        <option v-for="tz in timezones" 
                                :key="tz.value" 
                                :value="tz.value">
                            {{ tz.label }}
                        </option>
                    </BaseSelect>
                    </div>

                    <div class="col-md-3">
                        <BaseSelect v-model="timing.day_of_week" label="Day"
                            :error="form.errors[`timings.${index}.day_of_week`]" placeholder="Select Day">
                            <option v-for="day in daysOfWeek" :key="day.value" :value="day.value">
                                {{ day.label }}
                            </option>
                        </BaseSelect>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input type="checkbox" v-model="timing.is_closed" class="form-check-input"
                                :id="'closed-' + index" />
                            <label class="form-check-label" :for="'closed-' + index">
                                Closed
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3" v-if="!timing.is_closed">
                        <BaseDatePicker v-model="timing.open_time"
                            label="Open Time" type="time" placeholder="Open time" :error="form.errors[`timings.${index}.open_time`]" />
                    </div>
                    <div class="col-md-3" v-if="!timing.is_closed">
                        <BaseDatePicker v-model="timing.close_time" type="time"
                            label="Close Time" placeholder="Close time" :error="form.errors[`timings.${index}.close_time`]" />
                    </div>
                    <div :class="timing.is_closed ? 'col-md-7 mb-2' : 'col-md-1 mb-2'">
                        <button type="button" class="btn btn-danger " @click="removeTiming(index)"
                            title="Remove timing">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">

            <button class="btn btn-primary" @click="submitForm">
                {{ isEdit ? 'Update' : 'Create' }}
            </button>
            <button class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </div>
</template>
 