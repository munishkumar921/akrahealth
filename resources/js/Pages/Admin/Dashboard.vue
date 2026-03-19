<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const dashboardData = page.props.dashboardData || {};
</script>
<template>
<AuthLayout title="Dashboard" description="Admin dashboard overview and management" heading="">
        <div class="row">

           
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Messages</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.messages || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox alert-info">
                                <i class="fa fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Encounters</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.encounters?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-warning">
                                <i class="fa fa-file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Patients</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.patients?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-success">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Appointments</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.calendars?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-spring-green ">
                                <i class="fa fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Reminders</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.reminders || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-pink">
                                <i class="fa fa-bell"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Documents</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.documents?.total || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-warning">
                                <i class="fa fa-file-o"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Bills to Process</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.bills_to_process || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-purple">
                                <i class="fa fa-money"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                    <div class="iq-card-body">
                        <div class="text-center"><span>Test Results to Review</span></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="value-box">
                                <h2 class="mb-0"><span class="counter">{{ dashboardData.test_results_to_review || 0 }}</span></h2>
                            </div>
                            <div class="iq-iconbox iq-bg-primary">
                                <i class="fa fa-flask"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between bg-white rounded">
                        <div class="iq-header-title">
                            <h4 class="card-title">Inventory Alerts</h4>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <div class="alert alert-primary" role="alert">
                            <div class="iq-alert-text">A simple <b>primary</b> alert—check it out!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthLayout>
</template>
