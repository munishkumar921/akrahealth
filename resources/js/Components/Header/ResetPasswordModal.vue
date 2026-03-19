<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import BaseInput from "../Common/Input/BaseInput.vue";


const form = useForm({
    current_password:'',
    password: '',
    password_confirmation: '',
});


const emit = defineEmits(["close"]);

  
const passwordsMatch = computed(() => {
    return form.password === form.confirm_password;
});

const submit = () => {
    form.post(route('update.password'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    emit("close");
};
</script>

<template>
    <form @submit.prevent="submit" class="custom-input mt-4">
        <div class="form-group">
            <BaseInput id="password" v-model="form.current_password" type="password" class="mt-1"
                label="Current password" required autocomplete="password" placeholder="Current Password"
                :error="form.errors.current_password" />
        </div>
        <div class="form-group">
            <BaseInput id="password" v-model="form.password" type="password" class="mt-1" label="New password" required
                autocomplete="password" placeholder="New Password" :error="form.errors.password" />
        </div>
        <div class="form-group">
            <BaseInput id="password_confirmation" v-model="form.password_confirmation" label="Confirm password"
                placeholder="Confirm Password" type="password" class="mt-1" required autocomplete="password"
                :error="form.errors.password_confirmation" />
        </div>
        <div class="mt-4 d-flex justify-content-end gap-2">
            <button class="btn btn-primary float-right" type="submit" :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing">
                Change Password
            </button>
            <button class="btn btn-danger float-right" @click="closeModal">Close</button>

        </div>
    </form>
</template>
