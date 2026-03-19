<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
const props = defineProps({
    row: {}
});
</script>
<template>
    <section>
        <div class="modal fade" id="get-mvp-modal" size="lg" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6 d-none d-lg-block">
                                    <div class="mt-3">
                            <div class="row mt-2">
                                <h4 class="bold display-6 uppercase">Get Your <span class="bold text-primary">MVP!</span></h4>
                                <p>Ready to build something great? Enter your info and our expert will call you!</p>
                            </div>
                                <div class="row mt-2">
                                    <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-2">
                                    <img class="w-50px" src="/images/svg/stronger-data-security.svg" />
                                    <p class="mb-0">HIPAA-Compliant Data Security</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <img class="w-50px" src="/images/svg/medication-e-prescription-management.svg" />
                                    <p class="mb-0">Digital e-Prescription</p>
                                </div>
                                    </div>
                                    <div class="col-sm-6">
                                    <div class="d-flex align-items-center gap-2">
                                <img class="w-50px" src="/images/svg/ai-triage-management.svg" />
                                    <p class="mb-0">AI-Powered Symptom Triage</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <img class="w-50px" src="/images/svg/integrated-services.svg" />
                                    <p class="mb-0">Seamless EHR integrations</p>
                                </div>
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <p>Note: <span class="italic">We value your privacy and will never send irrelevant information</span></p>
                                </div>
                              </div>
                              </div>
                                </div>
                                <div class="col-md-12 col-lg-6">
                                    <div style="text-align: left" class="sender-form-field" data-sender-form-id="aM8Xrm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>