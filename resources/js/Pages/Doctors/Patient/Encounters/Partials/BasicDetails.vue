<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { mockDoctorNames, providerRoles } from "@/Data/commonData";
import { encounterLocations, encounterTypes } from "@/Data/MockData/encounters";
import { ref } from "vue";

const props = defineProps({
    encounterForm: Object,
});

const isValidated = ref(false);
</script>

<template>
<form class="list-options">
        <div class="mt-3 container-fluid">
            <BaseSelect v-model="encounterForm.provider" placeholder="Provider" label="Provider" required>
                <option v-for="option in mockDoctorNames" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </BaseSelect>

            <BaseInput v-model="encounterForm.chief_complaint" name="chief_complaint" placeholder="Chief Complaint"
                label="Chief Complaint" type="text" required />

            <BaseSelect v-model="encounterForm.encounter_type" placeholder="Encounter Type" label="Encounter Type"
                required>
                <option v-for="option in encounterTypes" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </BaseSelect>

            <BaseDatePicker v-model="encounterForm.date_of_service" name="date_of_service" placeholder="Date of service"
                label="Date of service" required />

            <BaseSelect v-model="encounterForm.encounter_location" placeholder="Encounter Location"
                label="Encounter Location" required>
                <option v-for="option in encounterLocations" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </BaseSelect>

            <BaseSelect v-model="encounterForm.assosciated_appointment" placeholder="Assosciated Appointment"
                label="Assosciated Appointment" required>
                <option value="choose">
                    Choose appointment to associate encounter!
                </option>
            </BaseSelect>

            <BaseSelect v-model="encounterForm.provider_role" placeholder="Provider Role" label="Provider Role"
                required>
                <option v-for="option in providerRoles" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </BaseSelect>
        </div>
    </form>
</template>
