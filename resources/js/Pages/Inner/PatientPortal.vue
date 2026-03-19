<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue';
import HeroSection2 from "@/Partials/HeroSection2.vue";
import ListItems from "@/Partials/ListItems.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Patient Portal EMR" meta-title="Patient Portal EMR | Secure & Engaging Care Access "
    description="AkraHealth’s Patient Portal EMR enables secure messaging, telemedicine, record access, and online scheduling to boost engagement and streamline care delivery.">
    <HeroSection2
    title="Seamless Patient Experience with Next-Generation EMR Solutions" 
    description="Improve patient engagement with a secure EMR application that streamlines communication, scheduling, and access to electronic medical records, making it efficient for providers and patients."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get a Demo</a> 
    </template>
   </HeroSection2>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-sm-10">
              <div class="section-title">
                <h2>Seamless Patient Management with AkraHealth</h2>
                <p>AkraHealth's top patient management system streamlines healthcare by combining EMR software for smooth appointment scheduling, secure messaging, and easy access to electronic medical records software. Patients can easily manage their health, while providers simplify workflows with an easy-to-use electronic healthcare record system for improved efficiency and care coordination.</p>
            </div>
            </div>
          </div>
        </div>
    </section>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-sm-10">
              <div class="section-title">
                <h2>AkraHealth Patient Portal – Empowering Patients, Enriching Care</h2>
                <p>AkraHealth's superior patient management system boosts patient engagement, automates workflows, and maximizes care delivery—enabling providers to provide a frictionless patient experience.</p>
            </div>
            </div>
          </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/accurate-claims-submission.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Secure Messaging</h4>
                        <p class="description">Provides secure, HIPAA-compliant communication between patients and providers.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/custom-billing-solutions.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Online Appointment Scheduling</h4>
                        <p class="description">Patients easily book, reschedule, or cancel appointments.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/interoperability-with-leading-platforms.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Automated Reminders</h4>
                        <p class="description">Eliminates no-shows through timely SMS and email reminders.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/payment-processing-reconciliation.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Access to Health Records</h4>
                        <p class="description">Securely view lab results, prescriptions, and medical history.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/insurance-verification-follow-up.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Personalized Electronic Forms</h4>
                        <p class="description">Enables effortless form completion that integrates into electronic medical records software.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/hipaa-compliance-data-security.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Telemedicine Integration</h4>
                        <p class="description">Facilitates virtual visits for remote healthcare delivery.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/scalable-solutions.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Medication Management</h4>
                        <p class="description">Assists patients in tracking prescriptions and refill notifications.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/real-time-updates-alerts.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Insurance & Billing Access</h4>
                        <p class="description">Offers real-time billing, claims, and payment history transparency.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

           <!-- Call to action section starts here -->
     <Cta 
       title="Empower Patients. Simplify Care. Stay Compliant."
       >
       <template v-slot:button>
          <ul class="main-list mt-3">
            <p>Deliver a seamless, secure, and patient-friendly experience with AkraHealth’s HIPAA-compliant Patient Portal. From online scheduling to medical record access, give your patients control while boosting provider efficiency.</p>
            <li>Secure HIPAA-Compliant Messaging</li>
            <li>Easy Appointment Booking & Rescheduling</li>
            <li>Real-Time Lab Results & Prescription Access</li>
            <li>Automated Reminders to Reduce No-Shows</li>
            <li>Integrated Telemedicine for Remote Care</li>
          </ul>
        </template>
       </Cta>
   <!-- Call to action section ends here -->

    <section class="section my-3">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
            <div class="mb-3">
              <h2 class="bold fs-20">Patient Portal Compliance – Secure & Standardized Healthcare</h2>
              <p>AkraHealth's top patient management system guarantees complete compliance with regulatory requirements, offering a secure and hassle-free experience for patients and providers alike.</p>
              <p>Providers can stay compliant while providing effective, patient-focused care using AkraHealth's electronic medical records software through a secure and reliable healthcare EMR system.</p>
             </div>
             <ListItems :items="[
                  { title: 'HIPAA-Compliant EMR Software', description: 'Safeguards patient information with robust encryption and secure access controls.'},
                  { title: 'FHIR Integration in EMR Systems', description: 'Facilitates seamless interoperability between electronic healthcare records software.'},
                  { title: 'CCD & CCR Support', description: 'Facilitates proper data exchange between electronic medical record systems to improve care coordination.'}
              ]"/>
            
          </div>
          <div class="col-md-6 position-relative">
            <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 500px; height: 500px" data-aos="zoom-in"></div>
                </div>
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/emr-online-chart.webp" alt="emr-online-chart" />
              </div>
              <div class="notch"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- <Cta2 title="Get Started with AkraHealth" description="Discover the best EMR software with a HIPAA-compliant patient portal for hassle-free electronic medical records access and better patient engagement. Schedule a demo today!" /> -->

  </AppLayout>
</template>
