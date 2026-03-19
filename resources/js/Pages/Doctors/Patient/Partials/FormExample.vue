<script setup>
import { ref } from "vue";
import BaseInput from "../../../../Components/Common/Input/BaseInput.vue";
import BaseSelect from "../../../../Components/Common/Input/BaseSelect.vue";
import BaseRadio from "../../../../Components/Common/Input/BaseRadio.vue";
import BaseCheckbox from "../../../../Components/Common/Input/BaseCheckbox.vue";

const form = ref({
    name: "",
    email: "",
    position: "",
    password: "",
    gender: "",
    confirm: false,
});

const isValidated = ref(false);

const genderOptions = [
    { value: "male", label: "Male" },
    { value: "female", label: "Female" },
    { value: "secret", label: "Secret" },
];

const submitForm = () => {
    isValidated.value = true;

    if (
        !form.value.name ||
        !form.value.email ||
        !form.value.position ||
        !form.value.password ||
        !form.value.gender ||
        !form.value.confirm
    ) {
        console.info("Form is not valid!");
    } else {
        console.info("Form submitted:", form.value);
    }
};
</script>

<template>
    <form
        @submit.prevent="submitForm"
        novalidate
        class="needs-validation"
        :class="{ 'was-validated': isValidated }"
    >
        <BaseInput
            v-model="form.name"
            name="name"
            placeholder="Full Name"
            required
        />
        <BaseInput
            v-model="form.email"
            type="email"
            name="email"
            placeholder="E-mail Address"
            required
        />
        <BaseSelect v-model="form.position" placeholder="Position" required>
            <option value="jweb">Junior Web Developer</option>
            <option value="sweb">Senior Web Developer</option>
            <option value="pmanager">Project Manager</option>
        </BaseSelect>
        <BaseInput
            v-model="form.password"
            type="password"
            name="password"
            placeholder="Password"
            required
        />
        <BaseRadio
            v-model="form.gender"
            :options="genderOptions"
            label="Gender"
            required
        />
        <BaseCheckbox
            v-model="form.confirm"
            label="I confirm that all data are correct"
            required
        />

        <div class="form-button mt-3">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>
</template>
