<script setup>
import { useForm } from '@inertiajs/vue3';

import { ref,reactive } from "vue";
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import { routeOptions } from "@/Data/commonData";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import BaseCheckbox from "@/Components/Common/Input/BaseCheckbox.vue";
import inputError from "@/Components/InputError.vue";
const props = defineProps({
    keyword: String,
    encounters: Object,
    immunizations: Object,
});
const form = useForm({
    id: "",
    patient_id: props.encounters?.patient_id || null,
    doctor_id: props.encounters?.doctor_id || null,
    encounter_id: props.encounters?.id || null,
    immunization: '',
    elsewhere:false,
    vis:false,
    dosage: '',
    dosage_unit: '',
    sequence: '',
    route: '',
    body_site: '',
    manufacturer: '',
    date: '',
    action: '',
    expiration: '',
});
const fieldsOne = [
    { key: "immunization", type: "text", label: "Immunization", placeholder: "Enter immunization name" },
    { key: "sequence", type: "text", label: "Sequence", placeholder: "Enter sequence (e.g., first, booster)" },
];

const fieldsTwo = [
    { key: "dosage", type: "text", label: "Dosage", placeholder: "Enter dosage amount" },
    { key: "dosage_unit", type: "text", label: "Dosage Unit", placeholder: "Enter dosage unit (e.g., ml, mg)" },
    { key: "manufacturer", type: "text", label: "Manufacturer", placeholder: "Enter manufacturer name" },
];

const isValidated = ref(false);

const bodySiteOptions = [
    { value: "right_deltoid", label: "Right Deltoid" },
    { value: "left_deltoid", label: "Left Deltoid" },
    { value: "right_glutes", label: "Right Glutes" },
    { value: "left_glutes", label: "Left Glutes" },
    { value: "right_thigh", label: "Right Thigh" },
    { value: "left_thigh", label: "Left Thigh" },
];

const actionAfterSaving = [
    { value: "do_nothing", label: "Do Nothing" },
    { value: "pull_from_supplements_inventory", label: "Pull from Supplements Inventory" },
];


const emit = defineEmits(["close", "submit"]);

const closeModal = () => {
    emit("close");
};
const submitForm = () => {
    isValidated.value = true;
    form.post(route('doctor.immunizations.store'), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const update = (immunization) => {

  Object.keys(form).forEach(key => {
    if (immunization[key] !== undefined) {
      form[key] = immunization[key];
    }
  });
};

defineExpose({
  update,
  resetForm: () => form.reset(),
});
 
</script>
<template>
     <form @submit.prevent="submitForm" novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div v-for="field in fieldsOne" :key="field.key">
             
            <BaseInput v-model="form[field.key]"  :label="field.label" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                required />
             <inputError class="mt-2" :message="form.errors[field.key]" />
 
        </div>

            <BaseCheckbox v-model="form.elsewhere" label="Given elsewhere" />
            <BaseCheckbox v-model="form.vis" label="Vis Given" />
         <div class="mb-3">
           
            <BaseSelect v-model="form.body_site" placeholder="Body site" label="Body Site" required>
                <option v-for="site in bodySiteOptions" :key="site.value" :value="site.value">
                    {{ site.label }}
                </option>
            </BaseSelect>
            <inputError class="mt-2" :message="form.errors.body_site" />

        </div>
        <div class="mb-3">
 
            <BaseSelect v-model="form.route" placeholder="Select Route" label="Route"
             required>

                <option v-for="route in routeOptions" :key="route" :value="route">
                    {{ route }}
                </option>
            </BaseSelect>
            <inputError class="mt-2" :message="form.errors.route" />
            
        </div>

        <div v-for="field in fieldsTwo" :key="field.key">
             <BaseInput v-model="form[field.key]" :name="field.key" :label="field.label"
              :placeholder="field.placeholder" :type="field.type"
                required />
                <inputError class="mt-2" :message="form.errors[field.key]" />
                
        </div>
        <div class="mb-3">
             <BaseDatePicker v-model="form.expiration" :name="form.expiration" label="Expiration Date"
              placeholder="Expiration Date"
                required />
                        <inputError class="mt-2" :message="form.errors.expiration" />

        </div>
        <div class="mb-3">
             <BaseDatePicker v-model="form.date" :name="form.date" label="Date Active"
              placeholder="Date Active" required />
              <inputError class="mt-2" :message="form.errors.date" />
 
              
        </div>
        <!-- <div class="mb-3">
            <label for="action_after_saving">Action After Saving</label>
            <BaseSelect v-model="form.action_after_saving" placeholder="Action After Saving" required>
                <option v-for="action in actionAfterSaving" :key="action.value" :value="action.value">
                    {{ action.label }}
                </option>
            </BaseSelect>
        </div> -->
        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
           <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>