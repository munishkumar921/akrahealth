<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import 'swiper/css';
import 'swiper/css/bundle';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
});

// Get status from props or URL query parameter or flash message
const page = usePage();

// Check for session expiry message in sessionStorage (fallback for axios requests)
const sessionExpiredMessage = ref('');
if (typeof window !== 'undefined') {
    const storedMessage = sessionStorage.getItem('session_expired_message');
    if (storedMessage) {
        sessionExpiredMessage.value = storedMessage;
        sessionStorage.removeItem('session_expired_message');
    }
}

// Check for session_expired query parameter from redirect
const sessionExpiredQuery = computed(() => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('session_expired') || '';
    }
    return '';
});

const successMessage = computed(() => {
    if (props.status) {
        return props.status;
    }
    // Check URL query parameter and decode it
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    if (statusParam) {
        return decodeURIComponent(statusParam);
    }
    // Check flash success message
    return page.props.flash?.success || '';
});

const errorMessage = computed(() => {
    // Priority: session_expired query param > sessionStorage message > flash message from backend
    return sessionExpiredQuery.value || sessionExpiredMessage.value || page.props.flash?.error || '';
});

// Clear the session_expired query parameter from URL after displaying the message
onMounted(() => {
    if (typeof window !== 'undefined' && sessionExpiredQuery.value) {
        const url = new URL(window.location.href);
        url.searchParams.delete('session_expired');
        window.history.replaceState({}, document.title, url.toString());
    }
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showResendActivation = ref(false);
const resendForm = useForm({
    email: '',
});

const submitResend = () => {
    resendForm.post(route('signup.resend-activation'), {
        onFinish: () => resendForm.reset('email'),
    });
};

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
<AppLayout title="Log in" description="Sign in to access your AkraHealth account" heading="Log in">
        <section class="sign-in-page">
            <div class="container bg-white p-0">
                <div class="row no-gutters">
                    <div class="col-sm-6">
                        <div class="d-flex flex-column h-100 justify-content-center sign-in-from">
                            <div v-if="!showResendActivation">
                                <h1 class="mb-0 dark-signin">Sign in</h1>
                                <p>Enter your email address and password to access admin panel.</p>
                            </div>
                            <div v-else>
                                <h1 class="mb-0 dark-signin">Resend Activation</h1>
                                <p>Enter your email address to receive a new activation link.</p>
                            </div>

                            <div v-if="successMessage" class="mb-4 font-medium text-sm text-green-600 alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ successMessage }}
                            </div>

                            <div v-if="errorMessage" class="mb-4 font-medium text-sm text-red-600 alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ errorMessage }}
                            </div>

                            <form v-if="!showResendActivation" @submit.prevent="submit">
                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                                        required autocomplete="username" />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="password" value="Password" />
                                    <TextInput id="password" v-model="form.password" type="password"
                                        class="mt-1 block w-full" required autocomplete="current-password" />
                                    <InputError class="mt-2" :message="form.errors.password" />
                                </div>

                                <div class="d-flex items-center justify-end mt-4">
                                    <Link v-if="canResetPassword" :href="route('password.request')"
                                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Forgot your password?
                                    </Link>
                                    <div  @click="showResendActivation = true"
                                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer ml-2">
                                        Resend Activation?
                                </div>
                                </div>
                                <div class="d-inline-block w-100">
                                    <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                                        <input type="checkbox" class="custom-control-input" v-model="form.remember"
                                            name="remember" id="customCheck1">
                                        <label class="custom-control-label" for="customCheck1">Remember Me</label>
                                    </div>
                                      <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }"
                                        class="btn btn-primary float-right">Sign in
                                    </PrimaryButton>
                                </div>
                              
                                 <div class="sign-info">
                                    <span class="dark-color d-inline-block line-height-2">Don't have an account? 
                                    <a :href="route('signup')" class="text-primary">Sign Up  <i class="fa fa-arrow-right mr-2"></i> </a>   
                                    </span>
                                </div>
                            </form>

                            <form v-else @submit.prevent="submitResend">
                                <div>
                                    <InputLabel for="resend_email" value="Email" />
                                    <TextInput id="resend_email" v-model="resendForm.email" type="email" class="mt-1 block w-full"
                                        required autocomplete="email" />
                                    <InputError class="mt-2" :message="resendForm.errors.email" />
                                </div>
                                <div class="d-inline-block w-100 mt-4">
                                    <button type="button" @click="showResendActivation = false" class="btn btn-danger">Back</button>
                                    <PrimaryButton type="submit" :class="{ 'opacity-25': resendForm.processing }"
                                        class="btn btn-primary float-right">Send Link
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-sm-6 text-center d-none d-md-block">
                        <div class="sign-in-detail bg-primary">
                            <img src="/images/Login.svg" class="img-fluid mb-4" alt="logo">
                            </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
