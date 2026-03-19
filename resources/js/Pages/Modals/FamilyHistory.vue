<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseInput from '@/Components/Common/Input/BaseInput.vue';
import BaseSelect from '@/Components/Common/Input/BaseSelect.vue';
import BaseTagsInput from '@/Components/Common/Input/BaseTagsInput.vue';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import { relationship } from "@/Data/commonData";
const emit = defineEmits(["close", "submit"]);

const props = defineProps({
    familyHistory: Object,
});

// ✅ Correct useForm initialization
const form = useForm({
    id: '',
    type: "familyHistory",
    name: "",
    relationship: "",
    living_status: "Alive",
    gender: "Male",
    dob: "",
    marital_status: "Single",
    mother: "",
    father: "",
    medical_history: [],
    old_history:[],
});

const closeModal = () => {
    emit("close");
};

const Submit = () => {
    form.post(route('doctor.family-history.store'), {
        onSuccess: () => {
            emit("submit", form); // ✅ no need for .value
            closeModal();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

// ✅ Update form fields dynamically (for edit)
const update = (history) => {
    console.log(history);
     Object.keys(form).forEach((key) => {
        if (history[key] !== undefined) {
            form[key] = history[key];
        }
    });
};

// ✅ Expose methods to parent
defineExpose({
    update,
    resetForm: () => form.reset(),
});
</script>

<template>
      <form @submit.prevent="Submit" class="space-y-3">
         <div class="form-group">
            <BaseInput label="Name" v-model="form.name" placeholder="Name" required />
        </div>

        <div class="form-group">

            <BaseSelect label="Relationship" v-model="form.relationship">
                <option v-for="relation in relationship" :key="relation" :value="relation.value">
                    {{ relation.label }}
                </option>

            </BaseSelect>
        </div>

        <div class="form-group">
            <BaseSelect label="Living Status" v-model="form.living_status">
                <option value="Alive">Alive</option>
                <option value="Deceased">Deceased</option>
            </BaseSelect>
        </div>

        <div class="form-group">
            <BaseSelect label="Gender" v-model="form.gender">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </BaseSelect>
        </div>

        <div class="form-group">
            <DatePicker label="Date of Birth" v-model="form.dob" type="date" id="dob" required />
        </div>

        <div class="form-group">
            <BaseSelect label="Marital Status" v-model="form.marital_status">
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Divorced">Divorced</option>
                <option value="Widowed">Widowed</option>
            </BaseSelect>
        </div>

        <div class="form-group">
            <BaseInput label="Mother" v-model="form.mother" id="mother" placeholder="Enter Mother Name" />
        </div>

        <div class="form-group">
            <BaseInput label="Father" id="father" v-model="form.father" placeholder="Enter Faather Name" />
        </div>
         <div class="form-group">
             <BaseTagsInput v-model="form.medical_history" label="Medical History" id="medical"
                placeholder="Type a few letters..." />
        </div>

        <div class="form-button mt-4 px-3 d-flex justify-content-end gap-3">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-danger" @click="closeModal">
                Close
            </button>
        </div>
    </form>
</template>
