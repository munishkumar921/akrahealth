<script setup>
import BaseInput from "@/Components/Common/Input/BaseInput.vue";
import { ref, defineEmits } from "vue";
import axios from "axios";

const props = defineProps({
    form: Object,
});

const fieldsOne = [
    { key: "plan", type: "textarea", placeholder: "Recommendations" },
    { key: "followup", type: "text", placeholder: "Follow up" },
    { key: "duration", type: "number", placeholder: "Total face-to-face time (min)" },
];

const emit = defineEmits();
const getDateMeta = (key) => {

    let keyword = null;
    if (key == 'plan') {
        keyword = 'plan';
    }

    if(!keyword){
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
const isValidated = ref(false);
</script>

<template>
    <form novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
        <div v-for="field in fieldsOne" :key="field.key">
            <BaseInput v-model="form[field.key]" :name="field.key" :placeholder="field.placeholder" :type="field.type"
                :label="field.placeholder" required @click="getDateMeta(field.key)" />
        </div>
    </form>
</template>
