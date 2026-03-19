<script setup>
import { ref, computed } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from "@inertiajs/vue3";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseFile from '@/Components/Common/Input/BaseFileInput.vue';

// Props definition with better typing
const props = defineProps({
    states: {
        type: Object,
        default: () => ({}),
    },
    countries: {
        type: Object,
        default: () => ({}),
    },
    hospitals: {
        type: Object,
        default: () => ({}),
    },

});

// Form initialization
const form = useForm({
    main_branch_id: '',
    name: "",
    email: "",
    phone: "",
    street_address1: "",
    street_address2: "",
    city: "",
    state: "",
    country: "",
    zip: "",
    website: "",
    primary_contact: "",
    npi: "",
    medicare: "",
    tax_id: "",
    default_pos_id: "11",
    documents_dir: "akradocuments",
    weight_unit: "lbs",
    height_unit: "in",
    temp_unit: "F",
    hc_unit: "in",
    encounter_template: "medical",
    additional_message: "",
    reminder_interval: "Default",
    billing_street_address1: "",
    billing_street_address2: "",
    billing_city: "",
    billing_state: "",
    billing_zip: "",
    phaxio_api_key: "",
    phaxio_api_secret: "",
    birthday_extension: "y",
    birthday_message: "Happy Birthday !",
    appointment_extension: "y",
    appointment_interval: "604800",
    appointment_message: "",
    sms_url: "",
    practice_logo: null,
});

// Reactive state for logo preview
const logoPreview = ref(null);
const logoFileInput = ref(null);

// Computed properties
const isFormValid = computed(() => {
    return form.name && form.documents_dir && form.billing_street_address1 && form.billing_city && form.billing_state && form.billing_zip;
});

const statesArray = computed(() => {
    return props.states ? Object.values(props.states) : [];
});

const countriesArray = computed(() => {
    return props.countries ? Object.values(props.countries) : [];
});



const handleLogoChange = (file) => {
    logoError.value = "";

    if (!file) {
        logoPreview.value = null;
        return;
    }

    if (!file.type.startsWith("image/")) {
        logoError.value = "Please select an image file.";
        logoPreview.value = null;
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        logoError.value = "File must be less than 2MB.";
        logoPreview.value = null;
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route("admin.hospitals.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Reset logo preview on success
            logoPreview.value = null;
        },
    });
};
</script>

<template>
    <AuthLayout title="Add Practice" description="Add Practice" heading="Add Practice">
        <div class="row">

            <div class="iq-card mb-3 col-md-12 p-0">
                <div class="iq-card-header d-flex justify-content-between align-items-center bg-primary">
                    <h5 class="mb-0 text-white">Add your Practice detail</h5>
                </div>

                <form @submit.prevent="submit">

                    <div class="iq-card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <BaseInput v-model="form.name" type="text" name="name" label="Name" :required="true"
                                    :error="form.errors.name" />
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.email" type="email" name="email" label="Email"
                                    :error="form.errors.email" />
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <BaseInput v-model="form.phone" type="text" name="phone" label="Phone"
                                        :error="form.errors.phone" />
                                </div>
                                <div class="col-md-6">
                                    <BaseInput v-model="form.website" type="text" name="website" label="Website"
                                        :error="form.errors.website" />
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <BaseInput v-model="form.street_address1" type="text" name="street_address1"
                                        label="Street Address 1" :error="form.errors.street_address1" />
                                </div>
                                <div class="col-md-6">
                                    <BaseInput v-model="form.street_address2" type="text" name="street_address2"
                                        label="Street Address 2" :error="form.errors.street_address2" />
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <BaseInput v-model="form.city" type="text" name="city" label="City"
                                        :error="form.errors.city" />
                                </div>
                                <div class="col-md-4">
                                    <BaseSelect v-model="form.state" label="State" placeholder="Select State"
                                        :error="form.errors.state">
                                        <option v-for="(state, index) in statesArray" :key="index" :value="state?.name">
                                            {{ state?.name }}
                                        </option>
                                    </BaseSelect>
                                </div>
                                <div class="col-md-4">
                                    <BaseSelect v-model="form.country" label="Country" placeholder="Select Country"
                                        :error="form.errors.country">
                                        <option v-for="(country, index) in countriesArray" :key="index"
                                            :value="country?.name">
                                            {{ country?.name }}
                                        </option>
                                    </BaseSelect>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <BaseInput v-model="form.zip" type="text" name="zip" label="Zip"
                                        :error="form.errors.zip" />
                                </div>
                                <div class="col-md-4">
                                    <BaseSelect v-model="form.main_branch_id" type="text" name="hospital_id"
                                        label="Practice ID" :error="form.errors.hospital_id">
                                        <option v-for="(hospital, index) in hospitals" :key="index"
                                            :value="hospital?.id">
                                            {{ hospital?.name }}
                                        </option>
                                    </BaseSelect>
                                </div>


                            </div>
                        </div>
                        <h5 class="mt-4">Upload Practice Logo</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="space-y-3">
                                    <BaseInput label="Practice Logo" type="file" accept="image/*"
                                        v-model="form.practice_logo" @update:modelValue="handleLogoChange" />

                                    <div v-if="logoError" class="text-danger text-sm">
                                        {{ logoError }}
                                    </div>

                                    <div v-if="logoPreview" class="mt-3">
                                        <img :src="logoPreview" alt="Logo Preview"
                                            class="h-24 rounded border object-contain" />
                                    </div>
                                </div>

                            </div>
                        </div>
                        <h5 class="mt-4">Practice Settings</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseInput v-model="form.primary_contact" type="text" name="primary_contact"
                                    label="Primary Contact" :error="form.errors.primary_contact" />
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.npi" type="text" name="npi" label="Practice NPI"
                                    :error="form.errors.npi" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseInput v-model="form.medicare" type="text" name="medicare"
                                    label="Practice Medicare Number" :error="form.errors.medicare" />
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.tax_id" type="text" name="tax_id"
                                    label="Practice Tax ID Number" :error="form.errors.tax_id" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseSelect v-model="form.default_pos_id" label="Default Practice Location"
                                    placeholder="Select Practice Location" :error="form.errors.default_pos_id">
                                    <option value="1">Pharmacy</option>
                                    <option value="3">School</option>
                                    <option value="11">Office</option>
                                    <option value="12">Home</option>
                                    <option value="13">Assisted Living Facility</option>
                                    <option value="14">Group Home</option>
                                    <option value="15">Mobile Unit</option>
                                    <option value="16">Temporary Lodging</option>
                                    <option value="17">Walk-in Retail Health Clinic</option>
                                    <option value="20">Urgent Care Facility</option>
                                    <option value="21">Inpatient Hospital</option>
                                    <option value="22">Outpatient Hospital</option>
                                    <option value="23">Emergency Room - Hospital</option>
                                    <option value="24">Ambulatory Surgical Center</option>
                                    <option value="25">Birthing Center</option>
                                    <option value="26">Military Treatment Facility</option>
                                    <option value="31">Skilled Nursing Facility</option>
                                    <option value="32">Nursing Facility</option>
                                    <option value="41">Ambulance - Land</option>
                                    <option value="42">Ambulance - Air or Water</option>
                                    <option value="49">Independent Clinic</option>
                                    <option value="50">Federally Qualified Health Center</option>
                                    <option value="54">Intermediate Care Facility</option>
                                    <option value="60">Mass Immunization Center</option>
                                    <option value="71">Public Health Clinic</option>
                                    <option value="72">Rural Health Clinic</option>
                                    <option value="99">Other Place of Service</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.documents_dir" type="text" name="documents_dir"
                                    label="Documents Directory" :required="true" :error="form.errors.documents_dir" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <BaseSelect v-model="form.weight_unit" label="Weight Unit"
                                    placeholder="Select Weight Unit" :required="true" :error="form.errors.weight_unit">
                                    <option value="lbs">Pounds</option>
                                    <option value="kg">Kilograms</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-3">
                                <BaseSelect v-model="form.height_unit" label="Height Unit"
                                    placeholder="Select Height Unit" :required="true" :error="form.errors.height_unit">
                                    <option value="in">Inches</option>
                                    <option value="cm">Centimeters</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-3">
                                <BaseSelect v-model="form.temp_unit" label="Temperature Unit"
                                    placeholder="Select Temperature Unit" :required="true"
                                    :error="form.errors.temp_unit">
                                    <option value="F">Fahrenheit</option>
                                    <option value="C">Celcius</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-3">
                                <BaseSelect v-model="form.hc_unit" label="Head Circumference Unit"
                                    placeholder="Select HC Unit" :required="true" :error="form.errors.hc_unit">
                                    <option value="in">Inches</option>
                                    <option value="cm">Centimeters</option>
                                </BaseSelect>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseSelect v-model="form.encounter_template" label="Default Encounter Template"
                                    placeholder="Select Encounter Template" :required="true"
                                    :error="form.errors.encounter_template">
                                    <option value="medical">Medical Encounter</option>
                                    <option value="phone">Phone Encounter</option>
                                    <option value="virtual">Virtual Encounter</option>
                                    <option value="standardpsych">Annual Psychiatric Evaluation</option>
                                    <option value="standardpsych1">Psychiatric Encounter</option>
                                    <option value="clinicalsupport">Clinical Support Visit</option>
                                    <option value="standardmtm">Medical Therapy Management Encounter</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-6">
                                <BaseSelect v-model="form.reminder_interval" label="Appointment Reminder Interval"
                                    placeholder="Select Reminder Interval" :error="form.errors.reminder_interval">
                                    <option value="Default">Default (48 hours prior)</option>
                                    <option value="24">24 hours prior</option>
                                    <option value="12">12 hours prior</option>
                                    <option value="6">6 hours prior</option>
                                    <option value="3">3 hours prior</option>
                                </BaseSelect>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <BaseInput v-model="form.additional_message" type="textarea" name="additional_message"
                                    label="Additional Message for Appointment Reminders" :rows="5"
                                    :error="form.errors.additional_message" />
                            </div>
                        </div>
                        <h5 class="mt-4">Billing Settings</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseInput v-model="form.billing_street_address1" type="text"
                                    name="billing_street_address1" label="Street Address" :required="true"
                                    :error="form.errors.billing_street_address1" />
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.billing_street_address2" type="text"
                                    name="billing_street_address2" label="Street Address Line 2"
                                    :error="form.errors.billing_street_address2" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <BaseInput v-model="form.billing_city" type="text" name="billing_city" label="City"
                                    :required="true" :error="form.errors.billing_city" />
                            </div>
                            <div class="col-md-4">
                                <BaseSelect v-model="form.billing_state" label="State" placeholder="Select State"
                                    :required="true" :error="form.errors.billing_state">
                                    <option v-for="(state, index) in statesArray" :key="index" :value="state?.name">
                                        {{ state?.name }}
                                    </option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-4">
                                <BaseInput v-model="form.billing_zip" type="text" name="billing_zip" label="Pin code"
                                    :required="true" :error="form.errors.billing_zip" />
                            </div>
                        </div>
                        <h5 class="mt-4">Extensions</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseInput v-model="form.phaxio_api_key" type="text" name="phaxio_api_key"
                                    label="Phaxio API Key" :error="form.errors.phaxio_api_key" />
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.phaxio_api_secret" type="text" name="phaxio_api_secret"
                                    label="Phaxio API Secret" :error="form.errors.phaxio_api_secret" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseSelect v-model="form.birthday_extension" label="Birthday Message Enabled"
                                    placeholder="Select Option" :error="form.errors.birthday_extension">
                                    <option value="n">No</option>
                                    <option value="y">Yes</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-6">
                                <BaseInput v-model="form.sms_url" type="text" name="sms_url" label="SMS URL"
                                    :error="form.errors.sms_url" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <BaseInput v-model="form.birthday_message" type="textarea" name="birthday_message"
                                    label="Birthday Message" :rows="5" :error="form.errors.birthday_message" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <BaseSelect v-model="form.appointment_extension" label="Appointment Reminder Enabled"
                                    placeholder="Select Option" :error="form.errors.appointment_extension">
                                    <option value="n">No</option>
                                    <option value="y">Yes</option>
                                </BaseSelect>
                            </div>
                            <div class="col-md-6">
                                <BaseSelect v-model="form.appointment_interval"
                                    label="Appointment Interval (minimum time lapsed from last appointment)"
                                    placeholder="Select Interval" :error="form.errors.appointment_interval">
                                    <option value="604800">1 week</option>
                                    <option value="1209600">2 weeks</option>
                                    <option value="2629743">1 month</option>
                                    <option value="5259486">2 months</option>
                                    <option value="7889229">3 months</option>
                                    <option value="15778458">6 months</option>
                                    <option value="31556926">1 year</option>
                                </BaseSelect>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <BaseInput v-model="form.appointment_message" type="textarea" name="appointment_message"
                                    label="Continuing Care Reminder Message" :rows="5"
                                    :error="form.errors.appointment_message" />
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary"
                                    :disabled="form.processing || !isFormValid">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>
                                    {{ form.processing ? 'Saving...' : 'Save Practice' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthLayout>
</template>
