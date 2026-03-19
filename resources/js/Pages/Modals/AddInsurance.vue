<script setup>
import { ref, watch } from "vue";
import { useForm, } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import InputError from "@/Components/InputError.vue";
import { Country, State } from 'country-state-city';

const props = defineProps({
    states: Array,
    countries: Array,
 });
const form = useForm({
    facility: "",
    phone: "",
    email: "",
    address: "",
    city: "",
    state: "",
    pincode: "",
    country: "",
    comments: "",
});

 const fieldsOne = [
    { key: "facility", type: "text", placeholder: "Facility",  lable: "Facility" },
    { key: "phone", type: "text", placeholder: "Phone", lable: "Phone" },
    { key: "email", type: "email", placeholder: "E-mail Address", lable: "E-mail Address" },
    { key: "address", type: "text", placeholder: "Address", lable: "Address" },
    { key: "city", type: "text", placeholder: "City", lable: "City" },
];

const fieldsTwo = [
    { key: "pincode", type: "text", placeholder: "Pincode", lable: "Pincode" },
    { key: "comments", type: "text", placeholder: "Comments", lable: "Comments" },
];

const isValidated = ref(false);

const emit = defineEmits(["close", "submit"]);

const submitForm = () => {
    form.post(route('doctor.insurance.store'), {
        onSuccess: () => {
            closeModal();
        },
    });
};
 
const closeModal = () => {
    emit("close");
};
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
    
});

 </script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <div v-for="field in fieldsOne" :key="field.key">
            <label :for="field.key" class="form-label">{{ field.lable }}</label>
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :type="field.type"
                required
            />
            <InputError :message="form.errors[field.key]" />
        </div>
                 <label :for="'state'" class="form-label">Country</label>
                 <BaseSelect v-model="form.country" placeholder="Select Country" required>
            <option v-for="country in countries" :key="country.isoCode" :value="country.name">
                {{ country.name }}
            </option>
        </BaseSelect>
           <InputError :message="form.errors.country" />

         <label :for="'state'" class="form-label">State</label>
        <BaseSelect v-model="form.state" placeholder="Select State" required>
            <option v-for="state in personalStates" :key="state.isoCode" :value="state.name">
                {{ state.name }}
            </option>
        </BaseSelect>
           <InputError :message="form.errors.state" />

        <div v-for="field in fieldsTwo" :key="field.key">
            <label :for="field.key" class="form-label">{{ field.lable }}</label>
            <BaseInput
                v-model="form[field.key]"
                :name="field.key"
                :placeholder="field.placeholder"
                :type="field.type"
                required
            />
            <InputError :message="form.errors[field.key]" />
        </div>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button
                type="button"
                class="btn btn-danger"
                @click="closeModal"
            >
                Close
            </button>
        </div>
    </form>
</template>
