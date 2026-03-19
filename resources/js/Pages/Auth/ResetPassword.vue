<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
<AppLayout title="Reset Password" description="Create a new secure password for your account">
        <section class="sign-in-page mb-3 bg-color-white-lilac">
            <div class="container bg-white mt-3 p-0 ">
                <div class="row no-gutters">
                    <div class="col-md-6 align-self-center">
                        <div class="sign-in-from">
                            <h1 class="mb-0">Forgot Password</h1>
                            <p></p>
                            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                                {{ status }}
                            </div>
                            <div class="app">
                                <form @submit.prevent="submit" class="custom-input mt-4">
                                    <div class="form-group">
                                        <InputLabel for="email" value="Email" />
                                        <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                            required autofocus autocomplete="username" />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div class="mt-4 form-group">
                                        <InputLabel for="password" value="Password" />
                                        <TextInput id="password" v-model="form.password" type="password"
                                            class="mt-1 block w-full" required autocomplete="new-password" />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div class="mt-4 form-group">
                                        <InputLabel for="password_confirmation" value="Confirm Password" />
                                        <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                            type="password" class="mt-1 block w-full" required
                                            autocomplete="new-password" />
                                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                    </div>

                                    <div class="flex items-center justify-end float-right  mt-4">
                                        <PrimaryButton class="btn btn-primary " :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing">
                                            Reset Password
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 text-center">
                        <div class="sign-in-detail text-white">
                            <img src="/images/request-for-demo.svg" alt="logo" class="img-fluid mb-4">
                            <h4 class="mb-1 text-white">Manage your orders</h4>
                            <p>It is a long established fact that a reader will be distracted by the
                                readable content.</p>
                            <div><!---->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
