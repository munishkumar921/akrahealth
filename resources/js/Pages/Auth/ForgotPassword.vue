<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
<AppLayout title="Forgot Password" description="Reset your password securely">
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
                                                    <InputLabel for="password" value="Email*"/>
                                                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 "
                                                        required autocomplete="email" placeholder="Email" />
                                                    <InputError class="mt-2" :message="form.errors.email" />
                                            </div>
                                        <div class="w-100 mb-4">
                                                <PrimaryButton class="btn btn-primary float-right" type="submit"
                                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                    Email Password Reset Link 
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
                                <div  ><!---->
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
