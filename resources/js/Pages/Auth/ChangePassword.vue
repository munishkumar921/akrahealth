<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
const props = defineProps({
    status: String,
});

const form = useForm({
    current_password:'',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('update.password'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

</script>

<template>
<AppLayout title="Change Password" description="Update your password securely">
            <section class="sign-in-page mb-3 bg-color-white-lilac">
                <div class="container bg-white mt-3 p-0 ">
                    <div class="row no-gutters">
                        <div class="col-md-6 align-self-center">
                            <div class="sign-in-from">
                                <h1 class="mb-0">Change Password</h1>
                                <p></p>
                                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                                    {{ status }}
                                </div>
                                <div class="app">
                                    <form @submit.prevent="submit" class="custom-input mt-4">
                                            <div class="form-group">
                                                    <InputLabel for="password" value="Current Password*"/>
                                                    <TextInput id="password" v-model="form.current_password" type="password" class="mt-1 "
                                                        required autocomplete="password" placeholder="Current Password" />
                                                    <InputError class="mt-2" :message="form.errors.current_password" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="password" value="New Password*" />
                                                <TextInput id="password" v-model="form.password" type="password" class="mt-1 "
                                                    required autocomplete="password" placeholder="New Password" />
                                                <InputError class="mt-2" :message="form.errors.password" />
                                            </div>
                                            <div class="form-group">
                                                <InputLabel for="password_confirmation" value="Confirm Password*"/>
                                                <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                                    placeholder="Confirm Password" type="password" class="mt-1" required
                                                    autocomplete="password" />
                                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                            </div>
                                        <div class="w-100 mb-4">
                                                <PrimaryButton class="btn btn-primary float-right" type="submit"
                                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                    Change Password
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
