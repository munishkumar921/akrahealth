<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import axios from "axios";
import Template from '@/Pages/Common/template.vue';
import { ref, defineEmits } from "vue";
const props = defineProps({
    form: Object,
});

const fieldsOne = [
    { key: "chief_complaint", type: "text", placeholder: "Chief Complaint" },
    { key: "hpi", type: "textarea", placeholder: "History of Present Illness" },
    { key: "ros", type: "textarea", placeholder: "Review of Systems" },
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
    <form novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div v-for="field in fieldsOne" :key="field.key" class="col-md-12 mb-3">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder"
                :label="field.placeholder" :type="field.type" required @click="getDateMeta(field.key)" />
        </div>
    </form>
</template>
