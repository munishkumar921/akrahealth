<script setup>
import AuthLayout2 from '@/Layouts/AuthLayout2.vue';
import { router } from '@inertiajs/vue3';

defineProps({
  counts: Object,
});

const handleCardClick = (routeName) => {
  router.visit(route(routeName));
};
</script>
<template>
<AuthLayout2 title="Dashboard" description="View your patient dashboard and key metrics" heading="">
        <div class="row">

            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.messages')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Messages</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.messages }}</span></h2>
                            </div>
                            <div class="iq-iconbox alert-info">
                                <i class="fa fa-envelope"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="alert-info" data-percent="0"
                                style="transition: width 2s ease 0s; width: 0%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.encounters')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Encounters to Complete</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.encounters_to_complete }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-info">
                                <i class="fa fa-stethoscope"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="bg-info" data-percent="40"
                                style="transition: width 2s ease 0s; width: 50%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.messages')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Telephone Messages</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.telephone_messages }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-phone"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="bg-success" data-percent="56"
                                style="transition: width 2s ease 0s; width: 66%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.booking.list')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Calendar</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.calendar.total }}</span></h2>
                                <div class="small text-muted">Pending: {{ counts.calendar.pending }} | Completed: {{ counts.calendar.completed }}</div>
                            </div>
                            <div class="iq-iconbox iq-spring-green ">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="iq-spring-green" data-percent="60"
                                style="transition: width 2s ease 0s; width: 70%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.booking.list')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Reminders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.reminders }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-pink">
                                <i class="fa fa-bell"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="iq-pink" data-percent="58"
                                style="transition: width 2s ease 0s; width: 68%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.documents')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Scanned Documents</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.documents }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-warning">
                                <i class="fa fa-file-o"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="iq-warning" data-percent="55"
                                style="transition: width 2s ease 0s; width: 65%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.billing')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Bills to Process</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.bills }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-purple">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="iq-purple" data-percent="60"
                                style="transition: width 2s ease 0s; width: 70%;"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height" @click="handleCardClick('patient.results')" style="cursor: pointer;">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Test Results to Review</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter fs-34">{{ counts.test_results }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-primary">
                                <i class="fa fa-flask"></i>
                            </div>
                        </div>
                        <div class="iq-progress-bar mt-5">
                            <span class="bg-primary" data-percent="60"
                                style="transition: width 2s ease 0s; width: 70%;"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout2>
</template>
