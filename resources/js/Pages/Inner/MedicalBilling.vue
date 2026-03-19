<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import IconboxCard from "@/Partials/IconboxCard.vue";
import ListItems from '@/Partials/IconboxCard.vue';
import Cta2 from '@/Partials/Cta2.vue'

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Medical Billing" 
    meta-title="Medical Billing Solutions | Akrahealth"
    description="Streamline your practice with Akrahealth's medical billing solutions. Improve accuracy, reduce denials, and boost revenue with seamless EMR integration.">
    <HeroSection2
    title="Hassle-Free Medical Billing with AkraHealth" 
    description="Streamline your revenue cycle with AkraHealth’s fully integrated EMR and medical billing software. Ensure faster claims, fewer denials, and 100% HIPAA compliance—all in one easy-to-use platform."
    bannerImage="/images/webpages/medical_billing.webp"
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get Started Today</a>
    </template>
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>How Akrahealth Supports Medical Billers</h2>
                <p class="text-center">
                  AkraHealth empowers medical billers with a robust, cloud-based EMR and billing solution that streamlines every step of the revenue cycle. Our platform reduces manual errors, minimizes claim denials, and speeds up reimbursements through real-time eligibility checks, automated coding suggestions, and AI-powered analytics.
              </p>
            </div>
            </div>
        </section>

        <section class="section">
            <div class="container bring-to-front con-box pt-0 pt-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-lg-6 position-relative">
                        <div class="device-twin free-width align-items-center pb-4 pb-md-0">
                            <div class="browser shadow aos-init" data-aos=" fade-end ">
                                <img src="/images/8.webp" alt="how our emr system helps users.webp">
                            </div>
                            <div class="front iphone-x absolute d-none d-sm-block aos-init" data-aos="fade-up"
                                data-aos-delay=".5" style="right: 0; bottom: -1.5rem;">
                                <div class="screen">
                                    <img src="/images/Emr-two.webp" alt="...">
                                </div>
                                <div class="notch"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5 mx-auto">
                        <h2 class="bold fs-22">Simplify Medical Billing with Intelligent Automation</h2>
                        <ul class="main-list mt-3">
                          <li><span class="bold">Real-time reporting & insights</span></li>
                          <li><span class="bold">AI-driven denial management</span></li>
                          <li><span class="bold">End-to-end claims lifecycle tools</span></li>
                          <li><span class="bold">Scalable solutions for solo practices to multi-specialty clinics</span></li>
                        </ul>
                        <p>Whether you're managing billing for a small provider or a large healthcare group, AkraHealth delivers the accuracy, compliance, and efficiency today’s billers need.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to action section starts here -->
       <Cta 
       title="Tired of Billing Errors and Delayed Payments?">
       <template v-slot:button>
            <p class="text-left">Upgrade to AkraHealth’s all-in-one <b>electronic health record (EHR) </b>and <b>medical billing software </b>designed to eliminate errors, accelerate reimbursements, and boost your clinic’s financial performance.</p>
            <ul class="main-list mt-3">
                <li>Manual data entry and coding mistakes</li>
                <li>Denied or underpaid claims</li>
                <li>Slow and inconsistent reimbursements</li>
                <li>Disconnected EHR and billing systems</li>            
                <li>Lack of transparency in revenue cycle performance</li>            
              </ul>
        </template>
       </Cta>
   <!-- Call to action section ends here -->

      
      <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center justify-content-center">
              <div class="col-md-10">
                <div class="section-title">
                <h2 class="bold">Unique Features of Akrahealth’s Medical Billing</h2>
              </div>
            </div>
        </div>
        <div class="row">
           <div class="col-sm-6">
                <ul class="main-list">
                  <li><span class="bold">Integrated with Top EMR Systems:</span> Seamlessly syncs with your existing EMR software solutions for smooth billing operations.</li>
                  <li><span class="bold">ICD-10 & CPT Code Integration:</span> Ensure precise coding and optimized reimbursements with built-in medical coding tools.</li>
                  <li><span class="bold">HCPCS Compliance:</span> Fully supports billing for procedures, medications, and durable medical supplies.</li>
                  <li><span class="bold">Regulatory Compliance:</span> Adheres to HIPAA, FHIR, and SMOED standards to guarantee secure, compliant data exchange and claims processing.</li>
                  <li><span class="bold">Automated Workflows:</span> Eliminate manual processes with intelligent automation for claims and medical billing, enhancing speed and accuracy.</li>
                  <li><span class="bold">Custom Reporting & Real-Time Insights:</span> Monitor financial health with real-time dashboards and analytics, helping you improve billing efficiency and reduce errors.</li>

                </ul>
           </div>
        </div>
      </div>
    </section>

    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center justify-content-center">
              <div class="col-md-10">
                <div class="section-title">
                <h2 class="bold">Empowering Rebranding and Customization</h2>
              </div>
            </div>
        </div>
        <div class="row">
           <div class="col-sm-12">
            <p class="text-center">Want your healthcare platform to reflect your brand? Akrahealth offers white-label EMR and medical billing software that allows full rebranding and customization. Personalize our best practice management software to match your clinic’s unique identity—delivering a seamless, professional experience for both providers and patients.
            </p>
           </div>
        </div>
      </div>
    </section>

  
    <!-- <Cta2 
    title="Struggling with Billing Delays & Errors?" 
    description="Say goodbye to inefficiencies. Akrahealth’s EHR-integrated medical billing solution helps you automate claims, reduce billing errors, and accelerate reimbursements—ensuring seamless and accurate revenue cycle management."/> -->
 
 </AppLayout>
</template>
