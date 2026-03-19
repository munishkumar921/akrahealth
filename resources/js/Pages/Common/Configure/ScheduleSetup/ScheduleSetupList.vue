<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";

const props = defineProps({
    name: String,
    form: Object,
});

const isValidated = ref(false);
const emit = defineEmits(["submit"]);

const fields = [
    { key: "monday_open_at", type: "time", placeholder: "Monday Open At" },
    { key: "monday_close_at", type: "time", placeholder: "Monday Close At" },
    { key: "tuesday_open_at", type: "time", placeholder: "Tuesday Open At" },
    { key: "tuesday_close_at", type: "time", placeholder: "Tuesday Close At" },
    {
        key: "wednesday_open_at",
        type: "time",
        placeholder: "Wednesday Open At",
    },
    {
        key: "wednesday_close_at",
        type: "time",
        placeholder: "Wednesday Close At",
    },
    { key: "thursday_open_at", type: "time", placeholder: "Thursday Open At" },
    {
        key: "thursday_close_at",
        type: "time",
        placeholder: "Thursday Close At",
    },
    { key: "friday_open_at", type: "time", placeholder: "Friday Open At" },
    { key: "friday_close_at", type: "time", placeholder: "Friday Close At" },
    { key: "saturday_open_at", type: "time", placeholder: "Saturday Open At" },
    {
        key: "saturday_close_at",
        type: "time",
        placeholder: "Saturday Close At",
    },
    { key: "sunday_open_at", type: "time", placeholder: "Sunday Open At" },
    { key: "sunday_close_at", type: "time", placeholder: "Sunday Close At" },
];
</script>

<template>
    <div>
        <section>
            <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center gap-3 pl-3">
                    <h3 class="d-flex align-items-center pt-2">
                        {{ name }}
                    </h3>
                </div>
            </div>
            <form novalidate class="needs-validation" :class="{ 'was-validated': isValidated }">
                <div class="row">
                    <div class="col-sm-6" v-for="field in fields" :key="field.key">
                        <BaseInput
                            v-model="form[field.key]"
                            :name="field.key"
                            :placeholder="field.placeholder"
                            :label="field.placeholder"
                            :type="field.type"
                            required
                        />
                    </div>
                </div>
            </form>
        </section>
    </div>
</template>