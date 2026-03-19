<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { ref, defineEmits } from "vue";
import VitalSigns from "../VitalSigns.vue";
import axios from "axios";

const props = defineProps({
    form: Object,
});

const fieldsOne = [
    { key: "pe", type: "textarea", placeholder: "Physical Exam" },
];

const isValidated = ref(false);
const emit = defineEmits();
const getDateMeta = (key) => {

    let keyword = key;
    if (!keyword) {
        return;
    }
    const form = new FormData();
    form.append('id', keyword);
    axios.post(route('doctor.get.templates'), form)
        .then(response => {
            response.category = keyword;
            response.field = key;
            emit('templateData', response);
        });
}
</script>

<template>
<form>
        <strong>Vital Signs</strong>
        <hr class="mb-4" />

        <!-- <VitalSigns :form="form" class="mt-4" /> -->
<div class="card mb-4">
    <div class="card-body">
        <VitalSigns :form="form" />
    </div>
</div>
        <!-- <hr class="mb-4 mt-4" /> -->
        <div class="col-md-12 mb-3">
            <div v-for="field in fieldsOne" :key="field.key" class="col-md-12 mb-3">
                <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder"
                    :label="field.placeholder" :type="field.type" required @click="getDateMeta(field.key)" />
            </div>
        </div>
    </form>
</template>
