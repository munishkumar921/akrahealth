<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';

</script>
<template>
    <AppLayout title="HIPAA | FHIR | ICD10" meta-title="" description="Our Healthcare Management Services incorporate Security practices such as HIPAA, FHIR, ICD10 to protect patient information while sharing with healthcare providers ensuring confidentiality, integrity, and availability.">
      <HeroSection2
    title="Security Standards" 
    description="Secure & Reliable Solutions to ensure better healthcare services"
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get Started</a> 
    </template>
   </HeroSection2>  
       
        <div class="position-relative">
            <div class="shape-divider shape-divider-bottom shape-divider-fluid-x text-contrast">
                <svg viewBox="0 0 2880 48" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z"></path>
                </svg>
            </div>
        </div>
        <section class="quick-start-your-business">
            <div class="container con-box">
                <div class="section-heading text-center">
                    <h1 class="display-md-3 display-lg-5 mt-3 text-center bold">Set of Security Standards we look up to</h1>
                </div>
                <div class="row gap-y align-items-center ">
                    <div class="col-10 col-lg-5 mx-auto order-md-2 ms-4">
                        <div class="mb-3 text-lg-start">
                            <h2 class="bold fs-20">HIPAA</h2>
                            <p>
                                The Health Insurance Portability and Accountability Act of 1996 (HIPAA) is an integrated law
                                essential for developing national standards to secure sensitive patient health data
                                (Protected Health Information - PHI) from getting exposed without the consent/knowledge of the patient.
                            </p>
                            <p>
                                AkraHealth EMR ensures that every user’s health data is carefully secured while permitting the
                                exchange of a user’s health data required to access and promote better quality healthcare &
                                safeguard the user’s health & well-being, payment & healthcare operations. We verify the
                                confidentiality, integrity, and availability of all e-PHI as well as Patient Privacy Rights
                                under HIPAA..
                            </p>
                            <p>
                                HIPAA Compliance Checklist for healthcare providers:<br>
                                Each healthcare provider irrespective of the size of practice provides electronic
                                consultation to transmit health information with regard to specific transactions. These transactions are:
                            </p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                    <p class="my-0">Claims</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                    <p class="my-0">Benefit eligibility inquiries</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                    <p class="my-0">Referral authorization requests</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                    <p class="my-0">
                                        Other transactions for which HHS has established standards under the HIPAA
                                        Transactions Rule.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="col-10 col-lg-6 position-relative ms-4 mb-5">
                        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
                            <div class="browser shadow aos-init aos-animate" data-aos=" fade-end ">
                                <img src="/images/security-hipaa.webp" alt="security-hipaa">
                            </div>

                            <div class="front iphone-x absolute d-none d-sm-block aos-init" data-aos="fade-up"
                                data-aos-delay=".5" style="right: 0; bottom: -1.5rem;">
                                <div class="screen">
                                    <img src="/images/security-hipaa-1.webp" alt="security-hipaa-1">
                                </div>
                                <div class="notch"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <section class="section bg-light">
            <div class="container con-box">
                <div class="row g-4">
                    <div class="col-lg-4 col-xl-4 mx-auto py-4 py-lg-0 ">
                        <div class="row g-4">
                            <h2 class="text-center bold fs-20">FHIR</h2>
                            <div class="col-md-6 col-lg-12">
                                <div class="card shadow-sm text-muted border-0 lift-hover">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class=" me-3 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-activity stroke-primary">
                                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                                </svg>
                                            </div>

                                            <div class="flex-fill">
                                                <h5 class="text-dark bold"></h5>
                                                <p>Fast Healthcare Interoperability Resources declares the approach to
                                                    exchanging healthcare information between different computer systems
                                                    irrespective of how they are stored in those systems.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-12">
                                <div class="card shadow-sm text-muted border-0 lift-hover">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class=" me-3 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-upload-cloud stroke-primary">
                                                    <polyline points="16 16 12 12 8 16"></polyline>
                                                    <line x1="12" y1="12" x2="12" y2="21">
                                                    </line>
                                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path>
                                                    <polyline points="16 16 12 12 8 16"></polyline>
                                                </svg>
                                            </div>

                                            <div class="flex-fill">
                                                <h5 class="text-dark bold"></h5>
                                                <p>
                                                    It permits healthcare data incorporating clinical data and administrative
                                                    data to be securely available to those who require it and have access to it
                                                    along with those who are authorized to do so for the benefit of the
                                                    patient receiving healthcare.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7  d-flex align-items-center position-relative">
                        <div class="browser shadow aos-init aos-animate" data-aos=" fade-end ">
                            <img src="/images/security-fhir.webp" class="img-responsive " alt="security-fhir">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="quick-start-your-business">
            <div class="container con-box">
                <div class="row gap-y align-items-center ">
                    <div class="col-10 col-lg-6 mx-auto order-md-2 m-auto">
                        <h2 class="bold fs-20">AkraHealth EMR can implement FHIR for:
                        </h2>
                        <div class="text-lg-start  ">

                            <ul class="list-unstyled">
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>
                                        Free to Use
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>
                                        To access rapid, efficient real-time data for effective Care Coordination,
                                        Patient Management & Monitoring
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>
                                        To properly minimize load to report quality measures and follow HL7 FHIR to enhance Better Patient Engagement
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>Boosting interoperability
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>Reducing the burden to execute new measures
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>Enabling HL7 healthcare standards and CDA to co-exist and support each other
                                    </div>
                                </li>
                                <li class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-right stroke-contrast">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </div>Receiving support from major EHR vendor
                                    </div>
                                </li>
                            </ul>

                        </div>
                    </div>
                    <div class="col-lg-6  d-flex align-items-center position-relative">
                        <div class="browser shadow aos-init aos-animate" data-aos=" fade-end ">
                            <img src="/images/security-fhir.webp" class="img-responsive " alt="">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section singl-testimonial shadow">
            <div class="container-fluid py-0 con-box">
                <div class="row align-items-center gradient gradient-primary-dark text-contrast">
                    <div class="col-md-3 col-10 mx-auto py-4    ms-5 py-4  ms-xxl-9  ">
                        <p class="font-md bold mt-1 text-contrast">
                            Get complete Patient Information protection on our Patient Portal
                        </p>
                        <hr class="my-4">
                        <a href="#" data-toggle="modal" data-target="#demo-request-modal"
                            class="btn btn-success text-contrast btn-rounded mt-4">
                            Book a Free Demo
                        </a>
                    </div>
                    <div class="col-12 col-md-7 image-background cover my-4"
                        style="background-image: url('/images/security-cta.webp')">
                    </div>
                </div>
            </div>
        </section>
        <section class="quick-start-your-business">
            <div class="container con-box">
                <div class="row gap-y align-items-center">
                    <div class="col-10 col-lg-5 mx-auto order-md-2 ms-4 mt-5 ">
                        <div class="mb-5 text-lg-start  ">
                            <h2 class="bold fs-20">ICD-10</h2>
                            <p>
                                The International Classification of Diseases (ICD) has the tenth revision - ICD 10 is
                                recognized by the World Health Organization (WHO). ICD is the basis to identify health trends & is an
                                international standard to report diseases along with health conditions. Hospitals and
                                providers globally utilize the ICD codes to group illnesses, diseases & injuries.
                            </p>
                            <p>
                                AkraHealth EMR can implement ICD10 to organize workflows, establish improved communication
                                among providers and bring transparency to the patients. We use it to:
                            </p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>

                                    <p class="my-0">Improvise clinical documents and quality of care</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>

                                    <p class="my-0">Trace patient’s health conditions & establishing protective care</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>

                                    <p class="my-0">Minimize errors & uncertainty during the payment process</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>

                                    <p class="my-0">Improve information gathering to assist in the identification of the
                                        hidden relationships between health conditions</p>
                                </div>
                            </li>
                            <li class="py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-md bg-primary p-2 rounded-circle center-flex  me-3 ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-arrow-right stroke-contrast">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>

                                    <p class="my-0">Identify potential fraud & overemphasized claims</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="col-10 col-lg-6 ms-4 position-relative mb-4 mt-5">
                        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
                            <div class="browser shadow aos-init aos-animate" data-aos=" fade-end ">
                                <img src="/images/security-icd-10.webp" alt="security-icd-10">
                            </div>

                            <div class="front iphone-x absolute d-none d-sm-block aos-init aos-animate" data-aos="fade-up"
                                data-aos-delay=".5" style="right: 0; ">
                                <div class="screen">
                                    <img src="/images/screens/app/Security-ICD10.webp" alt="security-icd-10">
                                </div>

                                <div class="notch"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section bg-contrast">
            <div class="container bring-to-front con-box ">
                <div class="shadow rounded text-center bg-dark p-5">
                    <div class="text-contrast">

                        <h2 class="mb-5 text-contrast">Our <span class="bold"> PLAN</span> to access and try out our EMR/EHR
                            Software ensures you with complete security of your personal details.

                        </h2>
                    </div>

                    <a href="#" style="cursor: pointer;" onclick="smoothScroll(document.getElementById('Registration'))"
                        class="btn btn-success text-contrast btn-rounded mt-4"> Sign Up on
                        Akrahealth now!
                    </a>

                </div>
            </div>
        </section>

    </AppLayout>
</template>
