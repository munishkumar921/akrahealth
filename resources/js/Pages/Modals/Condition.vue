<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import BaseDatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
const form = useForm({
    id: '',
    issue: '',
    rcopia_sync: '',
    type: '',
    reconcile: '',
    notes: '',
    date_active: '',
    date_inactive: '',
});

const submit = () => {

    form.post(route('doctor.conditions.store'), {
        onFinish: () => {
            closeModal();
            form.reset();
        },
    });
};


const update = (condition) => {
    Object.keys(form).forEach(key => {
        if (condition[key] !== undefined) {
            form[key] = condition[key];
        }
    });
};

const closeModal = () => {
    emit("close");
};
const emit = defineEmits(["close", "submit"]);


const optionConditionTypes = [
    { value: "Problem", label: "Problem" },
    { value: "MedicalHistory", label: "MedicalHistory" },
    { value: "SurgicalHistory", label: "SurgicalHistory" }
];

defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>
<template>
    <form @submit.prevent="submit">

        <div class="row">
            <div class="col">
                <BaseSelect v-model="form.type" label="Condition Type" placeholder="Select Condition Type" required :error="form.errors.type">
                    <option v-for="opt in optionConditionTypes" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </BaseSelect>
             </div>
            <div class="col">
                <BaseDatePicker v-model="form.date_active" type="date" label="Date Active" placeholder="Date Active" required :error="form.errors.date_active" />
              </div>
        </div>
        <div class="row">
            <div class="col">
            <BaseInput v-model="form.issue" type="text" label="Condition" placeholder="Condition" required :error="form.errors.issue" />
            
             </div>
        </div>

        <div class="row ">
            <div class="col">
                <BaseInput v-model="form.notes" type="textarea" label="Notes" placeholder="Note..." required :error="form.errors.notes" />
              </div>
        </div>

        <div class="gap-1 d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>
