<script setup>
import { router, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';
const form = useForm({
    name: '',
    email: '',
    phone: '',
    project: '',
});

const isSubmitting = ref(false)
 
const submit = () => {
    isSubmitting.value = true;

    form.post(route('demo-request.store'), {
        onSuccess: () => {
            isSubmitting.value = false;
            form.reset();
            // ✅ Redirect after 500ms
            setTimeout(() => {
                window.location.href = 'https://practice.akrahealth.com/';
            }, 500);
            $('.close-modal').click();
        },
        onError: () => {
            isSubmitting.value = false;
        },
        
    });
};


</script>
<template>
    <section>
        <div class="modal fade" id="patient-management-request-modal" size="lg" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6 d-none d-lg-block">
                                    <div class="mt-3">
                                        <div class="row mt-2">
                                            <h4 class="bold display-6 uppercase">Unlock Your <span
                                                    class="bold text-primary">Free Demo!</span></h4>
                                            <p>Enter your details to receive demo credentials.</p>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-sm-6">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img class="w-50px" src="/images/svg/stronger-data-security.svg" />
                                                    <p class="mb-0">Data Security</p>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img class="w-50px"
                                                        src="/images/svg/medication-e-prescription-management.svg" />
                                                    <p class="mb-0">e-Prescription</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img class="w-50px" src="/images/svg/ai-triage-management.svg" />
                                                    <p class="mb-0">AI Triage</p>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img class="w-50px" src="/images/svg/integrated-services.svg" />
                                                    <p class="mb-0">integrations</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 mt-2">
                                                <p>Note: <span class="italic">We value your privacy and will never send
                                                        irrelevant information</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6 p-4">
                                    <form @submit.prevent="submit">
                                        <!-- <div style="text-align: left" class="sender-form-field" data-sender-form-id="daf631e01869c16bsP5"></div> -->
                                        <InputLabel for="name" class="form-label font-weight-normal" value="Name" />
                                        <span class="text-danger">*</span>
                                        <TextInput type="text" class="form-control" id="name" v-model="form.name"
                                            placeholder="Enter your name" required />
                                        <div> <span class="mt-2 text-danger"> {{ form.errors.name }} </span></div>
                                        <InputLabel for="email" class="form-label mt-2 font-weight-normal"
                                            value="Email" /><span class="text-danger">*</span>
                                        <TextInput type="email" class="form-control" id="email" v-model="form.email"
                                            placeholder="Enter your email" required />
                                        <div> <span class="mt-2 text-danger"> {{ form.errors.email }} </span></div>
                                        <InputLabel for="phone" class="form-label mt-2 font-weight-normal"
                                            value="Phone" />
                                        <TextInput type="text" class="form-control" id="phone" v-model="form.phone"
                                            placeholder="Enter your phone number" />
                                        <div> <span class="mt-2 text-danger"> {{ form.errors.phone }} </span></div>
                                        <InputLabel for="project" class="form-label mt-2 font-weight-normal"
                                            value="Brief about your project" />
                                        <TextInput class="form-control" id="project" v-model="form.project"
                                            placeholder="Brief about your project" />
                                        <div> <span class="mt-2 text-danger"> {{ form.errors.project }} </span></div>
                                        <div class="mt-3">
                                            <button type="submit" name="submit" :disabled="isSubmitting" style="border-radius:9px;" class="btn btn-primary">{{ isSubmitting ? 'submitting...' : 'submit' }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
 