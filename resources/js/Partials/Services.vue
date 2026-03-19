<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
</script>

<template>
    <section id="features" class="features">
        <div class="container aos-init aos-animate">
            <div class="section-title pb-0 pb-xl-5">
                <h2>Essential EMR Features That Scale With You</h2>
                <p class="fs-16">From solo providers to growing care teams, AkraHealth supports secure, efficient, and customizable healthcare delivery.</p>
            </div>
            <div class="row">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="icon-box mt-5 mt-lg-0">
                        <i class="bx bx-receipt"></i>
                        <h4>e-Prescription</h4>
                        <p>Send electronic prescriptions instantly to pharmacies with our EMR software, streamlining medication management and enhancing care delivery.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-cube-alt"></i>
                        <h4>ICD-10 Coding</h4>
                        <p>Maintain accurate billing and clinical documentation with integrated ICD-10 support—natively included in our electronic medical records software.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-images"></i>
                        <h4>Computerized Provider Order Entry (CPOE)</h4>
                        <p>Order medications, labs, and treatments through a secure electronic healthcare record system, minimizing errors and maximizing efficiency.</p>
                    </div>
                    
                </div>
                <div class="image col-lg-6 order-1 order-lg-2">
                    <div class="icon-box">
                        <i class="bx bx-shield"></i>
                        <h4>Security</h4>
                        <p>Your data is secured using enterprise-level security protocols such as HIPAA, FHIR, and Blockchain technology, making us one of the industry's leading EMR systems.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-shield-plus"></i>
                        <h4>Healthcare Mental Services</h4>
                        <p>Empower mental health professionals using our top-of-the-line EMR for psychiatry—providing software to customize treatments, monitor results, and provide quality healthcare interventions.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="fa fa-tooth pl-1"></i>
                        <h4>Dental Practice Management</h4>
                        <p>Our dental practice management functionalities are integrated into an effective electronic health records software, enhancing dental clinics' workflows with the best software for doctors.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container bring-to-front con-box py-0">
            <div class="row justify-content-center">
            <div class="col-md-10">
            <div class="shadow rounded text-center bg-primary p-5">
                <div class="text-contrast">
                    <h2 class="mb-3 text-contrast bold">Starting New or Switching EMRs?</h2>
                    <p> AkraHealth is a lightweight, cloud-based EMR built for solo practitioners and small care teams who need instant setup, lower costs, and flexible features—without the frustration of traditional EHR systems.</p>
                </div>
                <Link :href="route('pricing')" class="btn btn-outline-light btn-rounded mt-2 px-4">Request Demo</Link>
            </div>
            </div>
        </div>
        </div>
    </section>
    <section class="section bg-light py-5 mt-5 mb-5 aos-init p-4" >
        <div class="container bring-to-front con-box py-0"> 
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class=" text-start">
                        <h2 class="bold">EMR & Healthcare Integrations</h2>
                        <p class="text-muted mb-4">Built for secure healthcare data interoperability.</p>
                    </div>
                    <p class="mb-3">AKRAHEALTH integrates with leading EMR systems using FHIR and HL7 standards to ensure seamless, bi-directional data exchange with enterprise-grade HIPAA-compliant data security.</p>
                    <p class="mb-3">From patient records and scheduling to prescriptions and clinical data, our integration layer works with your existing EMR-no disruption, just smarter connectivity.</p>
                    <p class="mb-3">We work with modern and legacy EMRs-including DrChrono-to sync patient records, appointments, prescriptions, labs, and clinical data, all while continuing to use your existing EMR.</p>
                    <Link :href="route('emr-integration')" class="btn btn-primary btn-rounded mt-3 px-4">Learn More</Link>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                  <img src="images/webpages/appss_1.webp" class="img-fluid shadow" alt="emr-online-chart" style="border-radius: 15px;" />
                </div>
            </div>
        </div>
    </section>
    <section id="services" class="services">
        <div class="container">
            <div class="section-title">
                <h2>Comprehensive Healthcare Management at Your Fingertips</h2>
                <p>An everything-in-one healthcare application built to automate triage, session management, electronic medical records in healthcare, and more.</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/ai-triage-management.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">AI Triage Management</h4>
                    <p class="description">Automate patient intake with AI-powered triage instruments, patient selection, and real-time status—perfect for telemedicine EMR and virtual health services.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box" >
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/ai-triage-summary.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">AI Triage Summary</h4>
                    <p class="description">Have a complete overview of the patient, AI-based diagnoses, and care recommendations—fully integrated with your electronic medical record systems.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/session-management.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Session Management</h4>
                    <p class="description">Experience live video sessions with recording and fluid switching—powered by EMR software and EHR software for immediate care.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/transcription-tools.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Transcription Tools</h4>
                    <p class="description">View editable, timestamped transcripts—raising the documentation experience within electronic healthcare records software.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/ai-suggestions.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">AI Suggestions</h4>
                    <p class="description">Augment decision-making with AI-proposed diagnoses, editable and configurable—intelligence entering EMR systems and healthcare EMR solutions.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/report-generation.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Report Generation</h4>
                    <p class="description">Create, preview, and export clinical reports in full compliance, protected within your electronic health records software.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/integrated-dashboard.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Integrated Dashboard</h4>
                    <p class="description">View triage outcomes, EHR information, and SOAP notes simultaneously—ideal for top EMR software for small practice and best EMR for small practice.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/notifications.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Notifications</h4>
                    <p class="description">Consolidated notifications and status indicators—ideal for medical applications aiming to be efficient.</p>
                </div>
                <div class="col-lg-4 col-md-6 icon-box">
                    <div class="mb-2 justify-content-center">
                        <img src="/images/svg/settings-account-management.svg" alt="emr-online-chart" />
                    </div>
                    <h4 class="title">Settings & Account Management</h4>
                    <p class="description">Control your account, notifications, and security—well-suited for providers working on best EMR software for small practices or a large network.</p>
                </div>
            </div>
        </div>
    </section><!-- End Services Section -->
</template>
