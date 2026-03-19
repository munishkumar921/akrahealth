<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { router } from "@inertiajs/vue3";
import BaseSelect from "@/Components/Common/Input/BaseSelect.vue";
import BaseDatePicker from "@/Components/Common/Input/BaseDatePicker.vue";
import { ref, onMounted } from "vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    encounterForm: Object,
    data: Object,
    isSaving: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['save']);

const saveEncounter = () => {
    emit('save');
}
const isValidated = ref(false);

// Mock dropdowns for new fields
const complexities = [
    { value: "low", label: "Low" },
    { value: "medium", label: "Medium" },
    { value: "high", label: "High" },
];

const motorVehicleAccidentStates = [
    { value: "alabama", label: "Alabama" },
    { value: "alaska", label: "Alaska" },
    { value: "arizona", label: "Arizona" },
];

const yesNoOptions = [
    { value: "yes", label: "Yes" },
    { value: "no", label: "No" },
];

onMounted(() => {
    // Set default values for dropdowns if they are not already set
    if (!props.encounterForm.doctor_id && props.data.doctors?.length > 0) {
        props.encounterForm.doctor_id = props.data.doctors[0].id;
    }
    if (!props.encounterForm.encounter_type && props.data.encounter_types?.length > 0) {
        props.encounterForm.encounter_type = props.data.encounter_types[0].id;
    }
    if (!props.encounterForm.encounter_location && props.data.locations?.length > 0) {
        props.encounterForm.encounter_location = props.data.locations[0].id;
    }
    if (!props.encounterForm.encounter_role && props.data.provider_roles?.length > 0) {
        props.encounterForm.encounter_role = props.data.provider_roles[0].id;
    }
    // Ensure encounter date of service is set (required field)
    if (!props.encounterForm.encounter_date_of_service) {
        const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
        props.encounterForm.encounter_date_of_service = today;
    }
});
</script>

<template>
<form class="list-options">
        <div class="mt-3 container-fluid">

            <!-- Provider -->
            <BaseSelect v-model="encounterForm.doctor_id" placeholder="Provider" label="Provider" required>
                <option v-for="doctor in data.doctors" :key="doctor.id" :value="doctor.id">
                    {{ doctor.user.name }}
                </option>
            </BaseSelect>
            <InputError :message="encounterForm.errors.doctor_id" />

            <!-- Chief Complaint -->
            <BaseInput v-model="encounterForm.chief_complaint" name="chief_complaint" placeholder="Chief Complaint"
                label="Chief Complaint" type="text" />
            <InputError :message="encounterForm.errors.chief_complaint" />

            <!-- Encounter Type -->
            <BaseSelect v-model="encounterForm.encounter_type" placeholder="Encounter Type" label="Encounter Type"
                required>
                <option v-for="type in data.encounter_types" :key="type.id" :value="type.id">
                    {{ type.name }}
                </option>
            </BaseSelect>
            <InputError :message="encounterForm.errors.encounter_type" />

            <!-- Date of Service -->
            <BaseDatePicker v-model="encounterForm.encounter_date_of_service"  name="encounter_date_of_service" placeholder="Enter date"
                label="Encounter Date" required />
            <InputError :message="encounterForm.errors.encounter_date_of_service" />

            <!-- Encounter Location -->
            <BaseSelect v-model="encounterForm.encounter_location" placeholder="Encounter Location"
                label="Encounter Location" required>
                <option v-for="location in data.locations" :key="location.id" :value="location.id">
                    {{ location.name }}
                </option>
            </BaseSelect>
            <InputError :message="encounterForm.errors.encounter_location" />

            <!-- Associated Appointment -->
            <BaseSelect v-model="encounterForm.appointment_id" placeholder="Associated Appointment"
                label="Associated Appointment" >
                <option v-for="appointment in data.appointments" :key="appointment.id" :value="appointment.id">
                    {{ appointment?.patient?.name||appointment?.patient?.user?.name }}, Mobile: {{ appointment?.patient?.user?.mobile }}, Time: 
                    {{appointment.appointment_date }} - {{appointment.appointment_time }}
                </option>
            </BaseSelect>
            <InputError :message="encounterForm.errors.appointment_id" />

            <!-- Provider Role -->
            <BaseSelect v-model="encounterForm.encounter_role" placeholder="Provider Role" label="Provider Role"
                required>
                <option v-for="role in data.provider_roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                </option>
            </BaseSelect>
            <InputError :message="encounterForm.errors.encounter_role" />

            <div v-if="!route().current('doctor.encounters.create')">
                <!-- Complexity of Encounter -->
                <BaseSelect v-model="encounterForm.complexity_of_encounter" placeholder="Complexity of Encounter"
                    label="Complexity of Encounter">
                    <option v-for="option in complexities" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
                <InputError :message="encounterForm.errors.complexity_of_encounter" />

                <!-- Referring Provider -->
                <BaseInput v-model="encounterForm.referring_provider" name="referring_provider"
                    placeholder="Referring Provider" label="Referring Provider" type="text" />
                <InputError :message="encounterForm.errors.referring_provider" />

                <!-- 
                <BaseInput v-model="encounterForm.encounter_condition_work" name="condition_related_to_work"
                    placeholder="Condition Related To Work" label="Condition Related To Work" type="text" />
                <InputError :message="encounterForm.errors.encounter_condition_work" />

              
                <BaseSelect v-model="encounterForm.encounter_condition_auto"
                    placeholder="Condition Related To Motor Vehicle Accident"
                    label="Condition Related To Motor Vehicle Accident">
                    <option v-for="option in yesNoOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
                <InputError :message="encounterForm.errors.encounter_condition_auto" />

                
                <BaseSelect v-model="encounterForm.encounter_condition_auto_state" placeholder="State Where Motor Vehicle Accident Occurred"
                    label="State Where Motor Vehicle Accident Occurred">
                    <option v-for="option in motorVehicleAccidentStates" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
                <InputError :message="encounterForm.errors.encounter_condition_auto_state" />

                
                <BaseSelect v-model="encounterForm.encounter_condition_other"
                    placeholder="Condition Related To Other Accident" label="Condition Related To Other Accident">
                    <option v-for="option in yesNoOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </BaseSelect>
                <InputError :message="encounterForm.errors.encounter_condition_other" />

               
                <BaseInput v-model="encounterForm.encounter_condition" name="other_condition" placeholder="Other Condition"
                    label="Other Condition" type="text" />
                <InputError :message="encounterForm.errors.encounter_condition" /> -->
            </div>
        </div>
        <div class="container">
            <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="button" class="btn btn-primary" @click="saveEncounter" :disabled="isSaving">
                        <span v-if="isSaving" class="spinner-border spinner-border-sm me-1"></span>
                        Save
                    </button>
                    <button type="button" class="btn btn-danger"
                        @click="() => router.visit(route('doctor.encounters.index'))">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
