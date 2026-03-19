<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
 import HeroSection from '@/Partials/Herosection3.vue';
import ListItems from '@/Partials/ListItems.vue';
import DemoRequest from '../Modals/DemoRequest.vue';
import DemoRequest2 from '../Modals/AIModal.vue';
import { ref, computed, h } from 'vue';
import WatchDemo from "../Modals/WatchDemo.vue";

defineProps({
  policy: String,
});

</script>

<template>
  <AppLayout  title="AI Patient Intake" meta-title="AI Patient Intake Automation | Digital Intake for Healthcare"
    description="Automate patient intake with AI-powered digital intake and workflow automation. Reduce paperwork, improve EMR data quality, and streamline healthcare onboarding.">
   
    <HeroSection
      title="AI Patient Intake"
      subtitle="Automation for Modern Healthcare"
       description2="AkraHealth’s AI Patient Intake Automation replaces manual paperwork with intelligent digital intake workflows."
       description3="Built using AI automation and workflow automation, AkraHealth transforms traditional patient intake into a scalable, EMR-ready intelligence layer."
       bannerImage="/images/Appointment.png"
       >
     <template v-slot:button>
      <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#Ai-request-modal"class="btn btn-primary btn-lg btn-rounded px-4 mt-3">Book A Free Demo</button>
      <!-- <a href="https://calendly.com/akrahealth/new-meeting" onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akrahealth/new-meeting'});return false;" class="btn btn-light btn-lg btn-rounded px-4 mt-3 btn-watch-demo">
        Watch  Demo
      </a> -->
     </template>
      </HeroSection>
      <section>
       
    </section>
    <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 section-title">
                    <h2 class="bold text-center">What Is AI Patient Intake Automation?</h2>
                    <p class="text-center fs-16">AkraHealth’s <b>AI Patient Intake</b> system combines <b>digital intake forms, LLMs, and AI workflow automation</b> to collect and structure patient information before the visit.</p>
                   
                </div>
                <div class="col-md-12 col-lg-6">   
                    <div class="text-center">
                       <img src="/images/Setting.png" alt="AI Patient Intake Dashboard" class="img-fluid" style="max-width: 600px; border: 1px solid black; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); border-radius: 5px;" />
                     </div>
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 text-center text-md-start">
                    <p class="text-justify fs-16">It enables healthcare teams to: </p>
                    <ul class="main-list mt-3 fs-12">
                    <li>Capture patient data digitally</li>
                    <li>Ask intelligent, adaptive follow-up questions</li>
                    <li>Convert free-text responses into structured medical data</li>
                    <li>Auto-populate EMR / EHR systems</li>
                    <li>Reduce waiting room delays and manual data entry</li>
                    </ul>
                    <p class="text-justify fs-16 mt-3">This shifts intake from static paperwork to AI-driven automation that supports clinical decision-making.</p>
                </div>
            </div>
        </div>
    </section>
     <!-- Call to action section starts here -->
  <div id="content-page" class="mb-5">
   <Cta  title="How It Works" >
      <template v-slot:button>
         <ul class="main-list mt-3">
          <li>Patient receives a digital intake link (SMS, Email, or App)</li>
          <li class="mt-3">AI-guided intake conversation begins</li>
          <li class="mt-3">Patient enters symptoms and history</li>
          <li class="mt-3">AI asks adaptive follow-up questions</li>
          <li class="mt-3">Structured data flows into the EMR</li>
          <li class="mt-3">Provider receives a ready-to-review patient summary</li>
        </ul>
    </template>
  </Cta>
</div>
<section class="section path-success">
  <div class="container bring-to-front">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="section-title text-center">
          <h2>Core Capabilities</h2>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-file-alt fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">Smart Digital Intake Forms</h3>
                </div>
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Web and mobile-based digital intake</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Dynamic questions based on symptoms and history</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>No static templates—AI adapts in real time</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Faster patient onboarding with fewer errors</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-brain fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">AI Intake Engine</h3>
                </div>
                <!-- <p class="mb-2">Understands patient input in natural language</p>
                <p class="mb-2">Extracts and structures:</p> -->
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Chief complaint</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Symptoms and duration</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Medications and allergies</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Past medical history</li>
                </ul>
                <!-- <p class="mb-2">Converts data into EMR-ready structured formats</p>
                <p>This is AI in automation designed specifically for healthcare intake workflows.</p> -->
              </div>
            </div>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-cogs fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">AI Workflow Automation</h3>
                </div>
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Pre-visit data collection</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Intelligent routing to the right provider</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Flags urgent or high-risk cases automatically</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Prepares structured summaries and HPI drafts</li>
                </ul>
                <!-- <p>By combining AI automation and workflow automation, clinicians receive context before the visit even starts.</p> -->
              </div>
            </div>
          </div>
          <div class="col-md-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-shield-alt fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">Security & Compliance</h3>
                </div>
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>HIPAA-aligned architecture</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Encrypted data at rest and in transit</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Role-based access controls</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Full audit logs and data governance</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
  <!-- Key Features section starts here -->
 <section class="section path-success">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>
      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="screen">
                <img src="/images/Dashboard_Overview.png" alt="dashboard-overview" class="img-fluid" style="max-width: 600px; border: 1px solid black; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); border-radius: 5px;"/>
            </div>
          </div>
          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-28">Who This Is For</h2>
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">Clinics and group practices
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Telehealth platforms
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Hospitals
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Healthtech startups building AI-driven systems
                  </p>
                </div>
              </li>
             
            </ul>
          </div>
        </div>
      </div>
  </section>
    <!-- key Features section ends here -->
     <!-- AI-powered EMR and EHR Systems section starts here -->  
     <section class="section path-success">
      <div class="container bring-to-front mb-3">
        <div class="row">
         <div class="section-title">
                <h2>Why AkraHealth</h2>
                <p>We don’t build generic forms. We build <b>intelligentpatient intake automation </b>.
              </p>
            </div>
         </div>
        <div class="row gap-y align-items-center">
           
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center mw-100">
                 <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal  pt-0" style="border-radius: 15px;" :showItems="1"
                  :centeredSlides="true" :spaceBetween="10"  :pagination="{
                      clickable: true,
                    }" :breakpoints="{
                  '640': {
                    slidesPerView: 1,
                    spaceBetween: 20,
                  },
                  '768': {
                    slidesPerView: 1,
                    spaceBetween: 40,
                  },
                  '1024': {
                    slidesPerView: 1,
                    spaceBetween: 50,
                  },
                  }" :modules="[Autoplay,Navigation,Virtual]" navigation :loop="true" :autoplay="{ delay: 2500 }">
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_patient_intake/Appointments.png" alt="emr-two.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_patient_intake/Dashboard_Overview.png" alt="emr-three.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_patient_intake/Messages.png" alt="emr-four.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_patient_intake/Setting.png" alt="emr-five.webp" />
                </SwiperSlide>            
              </Swiper>   
                </div>
              </div>
              <div class="col-md-6">
                    <p class="text-justify fs-16">With AkraHealth, you get:</p>
               <ul class="list-unstyled mt-3">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">Healthcare-first AI architecture
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                   Deep EMR and workflow expertise
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Secure AI automation at scale
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Custom integrations with existing systems
                  </p>
                </div>
              </li>
             
            </ul>
              </div>
            </div>
        </div>
    </section>
  <section class="section bg-primary py-5 mt-5 mb-5 aos-init p-4">
    <div class="align-items-center">
   
    <div class="col-md-12 text-white text-center">
      <h2 class="bold text-white">Ready to Automate Patient Intake?</h2>
      <h4 class="bold text-white mt-3">Whether you need:</h4>
      <ul class="single-line-list d-flex justify-content-center align-items-center mt-3 mb-0">
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>A full AI Patient Intake Automation system</li>
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>An EMR-integrated digital intake module</li>
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>Or a custom AI workflow automation engine</li>
      </ul>
      <a href="https://calendly.com/akrahealth/new-meeting" onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akrahealth/new-meeting'});return false;" class="btn btn-outline-light btn-rounded mt-3 px-4">Book a Free Demo</a>
       </div>
       </div> 
  </section>
    
      <!-- Faq section starts here -->
      <!-- <section id="faq" class="faq section-bg">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
            </div>
            <ul class="faq-list">
                <li>
                    <div data-toggle="collapse" class="collapsed question" href="#faq1">Will AI Receptionist replace human staff?
                        <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i>
                    </div>
                    <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                        <p> Not at all. It complements your team by handling repetitive tasks, freeing staff for patient-facing care.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq2" class="collapsed question">Does it work with my existing EMR?
                        <i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                        <p>Yes. AkraHealth integrates seamlessly with major EMR platforms.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq3" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Can patients still call my clinic directly?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq3" class="collapse">
                        <p> Absolutely — AI Receptionist manages calls, messages, and online bookings.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq4" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Is patient data secure?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq4" class="collapse">
                        <p> Yes. AkraHealth is fully HIPAA-compliant with end-to-end encryption.</p>
                    </div>
                </li>
            </ul>
        </div>
    </section> -->
  </AppLayout>
  <DemoRequest/>
  <DemoRequest2 />
 </template>

<style scoped>
.capability-card:hover {
  box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

/* Single-line list used in CTA section */
.single-line-list {
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 1rem;
  white-space: nowrap;
  overflow: hidden;
}
.single-line-list li {
  display: inline-flex;
  align-items: center;
  color: inherit;
  font-weight: 500;
}

/* Center the capability lists as a block and left-align their items so check icons and text start on the same column */


.aligned-list li {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-left: 0;
}

/* Ensure the check icons occupy a fixed width so text starts aligned */
.aligned-list li i.fa-check {
  width: 26px;
  min-width: 26px;
  text-align: center;
}

.aligned-list li + li {
  margin-top: 0.4rem;
}
</style>