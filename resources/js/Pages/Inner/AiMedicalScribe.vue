<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
 import HeroSection from '@/Partials/Herosection3.vue';
 import DemoRequest from '../Modals/DemoRequest.vue';
import DemoRequest2 from '../Modals/AIModal.vue';

defineProps({
  policy: String,
});

</script>

<template>
  <AppLayout title="AI Medical" meta-title=" AI Medical Scribe for SOAP Notes & Clinical Documentation"
    description="Automate clinical notes and SOAP charting with AkraHealth’s AI Medical Scribe. EMR-integrated, HIPAA-compliant AI for modern healthcare teams.">
   
    <HeroSection
      title="AI Medical Scribe for Automated Clinical Notes"
      subtitle="Accurate, real-time clinical notes powered by AI"
       description2="Our AI scribe medical solution reduces documentation time, improves note quality, and integrates seamlessly with your EMR / EHR, while remaining fully HIPAA-compliant and secure."
       bannerImage="/images/Clinic Dashborad.png"
       >
     <template v-slot:button>
      <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#Ai-request-modal"class="btn btn-primary btn-lg btn-rounded px-4 mt-3">Book A Demo</button>
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
                    <h2 class="bold text-center">What Is AkraHealth AI Medical Scribe?</h2>
                    <p class="text-center fs-16">AkraHealth’s <b> AI Medical Scribe</b>  is an ambient clinical documentation system that uses <b>speech-to-text and advanced LLMs</b>to automatically generate accurate clinical notes during patient visits.</p>
                   
                </div>
                <div class="col-md-12 col-lg-6">
                   
                    <div class="text-center">
                       <img src="/images/Medical Records.png" alt="AI Patient Intake Dashboard" class="img-fluid" style="max-width: 600px; border: 1px solid black; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); border-radius: 5px;" />
                     </div>
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 text-center text-md-start">
                    <p class="text-justify fs-16">It captures conversations and converts them into:</p>
                    <ul class="main-list mt-3 fs-12">
                    <li>SOAP Notes</li>
                    <li>SOAP Charting</li>
                    <li>Progress & encounter notes</li>
                    <li>Discharge summaries</li>
                    </ul>
                    <p class="text-justify fs-16 mt-3">The system works quietly in the background-often <b>ambient clinical documentation</b>- allowing providers to deliver care without manual note-taking.</p>
                </div>
            </div>
        </div>
    </section>
     <!-- Call to action section starts here -->
  <div id="content-page" class="mb-5">
   <Cta  title="How It Works" >
      <template v-slot:button>
         <ul class="main-list mt-3">
        <p class="text-justify fs-16">Turn every patient interaction into <b>clean, structured clinical notes</b> automatically. AkraHealth’s <b>AI Medical Scribe</b> listens to doctor–patient conversations and converts them into <b>SOAP Notes, clinical notes, and structured medical documentation </b>in real time- so clinicians can focus on care, not charting.</p>
          <li>Provider starts the visit</li>
          <li class="mt-3"><b>AI Medical Scribe </b>captures the conversation</li>
          <li class="mt-3">Speech is converted into structured clinical data</li>
          <li class="mt-3">LLM generates <b>clinical notes & SOAP Notes</b></li>
          <li class="mt-3">Provider reviews and approves</li>
          <li class="mt-3">Notes are saved directly to EMR / EHR</li>
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
                  <h3 class="mb-0">AI Clinical Notes Engine</h3>
                </div>
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>SOAP Notes & SOAP Charting</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>HPI / ROS</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Assessment & Plan</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Progress notes</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-brain fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">AI Medical Scribe</h3>
                </div>

                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Listens during in-person or telehealth visits</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Identifies speaker roles (doctor vs patient)</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Structures conversations into medical documentation</li>
                  <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>Reduces after-visit documentation time by 60–80%</li>
                </ul>

              </div>
            </div>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-5 mb-5">
            <div class="card h-85 shadow-sm capability-card" style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">
              <div class="card-body text-center">
                <div class="d-flex align-items-center mb-3">
                  <i class="fa-solid fa-cogs fa-2x text-primary me-3"></i>
                  <h3 class="mb-0">Smart Medical Memory</h3>
                </div>
                <ul class="list-unstyled mx-auto text-start aligned-list">
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Uses patient history, diagnoses, and prior visits</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Suggests what to document based on clinical context</li>
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Maintains continuity across encounters and specialties</li>
                </ul>

              </div>
            </div>
          </div>
          <div class="col-md-5 mb-5">
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
                  <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Audit logs and PHI protection</li>
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
                <img src="/images/Reports and analytics.png" alt="Reports and analytics" class="img-fluid" style="max-width: 600px; border: 1px solid black; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); border-radius: 5px;" />
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
                    Specialty practices (Primary Care, Mental Health, Dental, PT, etc.)
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
                <p>We don’t just add AI—we build <b>clinical-grade healthcare systems </b>.
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
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_medical-scripe/Dashboard.png" alt="emr-two.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_medical-scripe/Medical Records.png" alt="emr-three.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_medical-scripe/Patient History.png" alt="emr-four.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_medical-scripe/Patient Management.png" alt="emr-five.webp" />
                </SwiperSlide>    
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/AI_medical-scripe/Staff Management.png" alt="emr-six.webp" />
                </SwiperSlide>           
              </Swiper>   
                </div>
              </div>
              <div class="col-md-6">
                    <p class="text-justify fs-16">AkraHealth delivers:</p>
               <ul class="list-unstyled mt-3">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">AI Medical Scribe & Clinical Notes Automation
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                   EMR & Clinic Management Platforms
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Telehealth & Patient Applications
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <p class="m-0">
                    Secure, API-first healthcare infrastructure
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
      <h2 class="bold text-white">Ready to Add AI Medical Scribe to Your Platform?</h2>
      <h4 class="bold text-white mt-3">Whether you need:</h4>
      <ul class="single-line-list d-flex justify-content-center align-items-center mt-3 mb-0">
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>A standalone AI Medical Scribe</li>
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>An EMR-integrated clinical notes solution</li>
        <li><i class="fa-solid fa-check-circle text-white me-2"></i>Or a fully custom AI documentation engine</li>
      </ul>
      <a href="https://calendly.com/akrahealth/new-meeting" onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akrahealth/new-meeting'});return false;" class="btn btn-outline-light btn-rounded mt-3 px-4">Book a Free Demo</a>
       </div>
       </div> 
  </section> 
  </AppLayout>
  <DemoRequest/>
  <DemoRequest2 />
 </template>

<style scoped>
.capability-card:hover {
  box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

/* Center the list as a block and left-align its items so check icons and text start on the same column */


.aligned-list li {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-left: 0;
}

/* Ensure the check icons occupy a fixed width so text starts aligned */
.aligned-list li i.fa-check,
.aligned-list li i.fa-check-circle {
  width: 26px;
  min-width: 26px;
  text-align: center;
}

.aligned-list li + li {
  margin-top: 0.4rem;
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

</style>