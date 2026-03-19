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
const billingCycle = ref('monthly');

const prices = {
    starter: { monthly: 12500, annual: 12500 * 12 },
    essential: { monthly: 18500, annual: 18500 * 12 },
    growth: { monthly: 38500, annual: 38500 * 12 },
};

const growthPrice = computed(() => prices.growth[billingCycle.value]);
const starterPrice = computed(() => prices.starter[billingCycle.value]);
const essentialPrice = computed(() => prices.essential[billingCycle.value]);

const billingPeriodText = computed(() => billingCycle.value === 'monthly' ? '/ month' : '/ year');
</script>

<template>
  <AppLayout title="AI Healthcare" meta-title="AI in Healthcare | Smart EMR Solutions"
    description="Explore AI in healthcare with Akrahealth’s smart EMR solutions, enhancing medical records, telemedicine, and operational efficiency with AI-driven technology.">
   
    <HeroSection
      title="AI Receptionist for Healthcare Appointment Booking,"
       description="THandle patient calls, book healthcare appointments, and respond instantly—using an AI receptionist designed to support modern healthcare operations, 24/7."
      >
      <template v-slot:images>
        <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/EMR-18.webp" alt="emr-six" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/screens/app/Screenshot2.webp" alt="emr-one" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
     <template v-slot:button>
      <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#Ai-request-modal"class="btn btn-primary btn-lg btn-rounded px-4 mt-3">Start Free Trial</button>
      <a href="https://calendly.com/akrahealth/new-meeting" onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akrahealth/new-meeting'});return false;" class="btn btn-light btn-lg btn-rounded px-4 mt-3 btn-watch-demo">
        Watch  Demo
      </a>
     </template>
      </HeroSection>
      <section>
       
    </section>
       <!-- Akrahealth AI-Powered Solutions have many benefits __Section starts here__ -->
   <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-md-8 col-sm-12">
                <div class="section-title text-center">
                  <h2>AI Receptionist Built for Healthcare Workflows</h2>
                 <p>Automate patient communication, appointment booking, and inquiry handling with an AI receptionist designed specifically for healthcare appointment management-secure, reliable, and always available.</p> 
                </div>
              </div>
          </div>
            <div class="row justify-content-center">               
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-phone-alt"></i>
                        </div>
                        <h4 class="title">AI Voice Receptionist</h4>
                        <p class="description">24/7 intelligent voice handling with natural conversations, call transcripts, and detailed analytics.
                          A fully AI-powered virtual receptionist designed for medical offices and clinics.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-comment-dots"></i>
                        </div>
                        <h4 class="title">SMS & Chat Widget</h4>
                        <p class="description">Embedded chat widget and SMS support with AI-powered responses and conversation tracking — ideal for healthcare practices needing real-time support.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4 class="title">Appointment Booking</h4>
                        <p class="description">Smart scheduling system with calendar sync, automated confirmations, and rescheduling.
                          Optimized as appointment scheduling software for medical offices and medical scheduling software.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-users"></i>
                        </div>
                        <h4 class="title">Lead Management</h4>
                        <p class="description">Comprehensive lead tracking with qualification scoring, detailed profiles, and conversion tracking — powered by our AI receptionist for medical offices.</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">               
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="title">Advanced Analytics</h4>
                        <p class="description">Real-time dashboards, call logs, usage metrics, and performance insights for your practice.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-microphone-alt"></i>
                        </div>
                        <h4 class="title">Voice Customization</h4>
                        <p class="description">Configure AI personality, greeting messages, practice hours, and custom FAQs for your brand.
                          Also available as a white-label AI receptionist for healthcare providers and startups.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-cogs"></i>
                        </div>
                        <h4 class="title">Integrations & APIs</h4>
                        <p class="description">Connect with external services via webhooks, n8n workflows, and third-party integrations to enhance your healthcare scheduling software workflows.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="icon">
                           <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="title">Admin & Security</h4>
                        <p class="description">Multi-user support, role-based access control, and secure API key management — built on a HIPAA-compliant AI receptionist framework.</p>
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
            <div class="device-twin m-auto align-items-center">
              <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
                <div class="screen">
                  <img src="/images/EMR-9.webp" alt="emr-six" />
                </div>
                <span class="button"></span>
              </div>
              <div class="iphone-x light front me-0">
                <div class="screen shadow-box">
                  <img src="/images/speech-to-text.jfif" alt="emr-one" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>
          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-28">Key Features of Akrahealth’s AI Solutions</h2>
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">
                    24/7 Patient Scheduling & Reminders
                  </h5>
                  <p class="m-0">
                    Never miss an appointment — AI books, confirms, and sends follow-ups using medical scheduling software technology.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Automated Intake & Data Capture</h5>
                  <p class="m-0">
                    Patient history, demographics, and forms flow directly into your EMR.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1"> Multilingual Patient Communication</h5>
                  <p class="m-0">
                    Speak to patients in their preferred language with our AI-powered virtual receptionist.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1"> Insurance Verification & Billing Support</h5>
                  <p class="m-0">
                    Automate checks, claims, and billing follow-ups.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">HIPAA-Compliant & Secure</h5>
                  <p class="m-0">
                    Enterprise-grade encryption ensures patient privacy — a true HIPAA-compliant AI receptionist.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1"> Seamless EMR Synchronization</h5>
                  <p class="m-0">
                    Every interaction is logged in real time.
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
                <h2>Integrated AI Assistant at Akrahealth</h2>
                <p>Akrahealth offers professional IT solutions for dental and medical practices to enhance efficiency and streamline patient care.
              With our extensive knowledge of practice management software and allied technologies, we provide bespoke solutions — including white-label AI receptionist capabilities.
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
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/webpages/ai-slider-1.webp" alt="emr-two.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/webpages/ai-slider-2.webp" alt="emr-three.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/webpages/ai-slider-3.webp" alt="emr-four.webp" />
                </SwiperSlide>
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/webpages/ai-slider-4.webp" alt="emr-five.webp" />
                </SwiperSlide>    
                <SwiperSlide>
                  <img class="img-responsive shadow" style="border-radius: 15px;" src="/images/webpages/ai-slider-5.webp" alt="emr-six.webp" />
                </SwiperSlide>           
              </Swiper>   
                </div>
              </div>
              <div class="col-md-6">
              <ListItems :items="[
                { title: 'Agent Analytics Dashboard', description: 'Comprehensive metrics and insights for voice AI calls. ', icon: 'fa-check' },
                { title: 'Voice AI Platform', description: 'The main management view for creating intelligent voice assistants and automating healthcare conversations.',  icon: 'fa-check'},
                { title: 'AI Assistants Table', description: 'Manage all your AI receptionist assistants including appointment-booking bots and patient intake bots.',  icon: 'fa-check'},
                { title: 'Chat Logs / Call Logs', description: 'Detailed history of all conversations from chat and AI calls.', icon: 'fa-check'},
                { title: 'Assistant Performance', description: 'Distribution of activity or usage across different AI assistants.',  icon: 'fa-check'},
              ]" />
              </div>
            </div>
        </div>
    </section>
     <section class="section">
        <div class="container bring-to-front con-box py-0 mb-3">
            <div class="row justify-content-center">
            <div class="col-md-10">
            <div class="shadow rounded text-center bg-primary p-5">
                <div class="text-contrast">
                    <h2 class="mb-3 text-contrast bold fs-20">Ready to Transform Your Practice?</h2>
                    <p class="font-size-16"> Join hundreds of protection using Akrahealth AI receptionist and appointment scheduling software foe medical officers to automate patient communication.</p>
                </div>
                <button  data-toggle="modal" data-target="#demo-request-modal" class="btn btn-outline-light btn-rounded mt-2 px-4">Request Demo</button>
            </div>
            </div>
        </div>
        </div>
    </section>
     
     <section class="section pricing">
      <div id="content-page" class="mt-3">
        <div class="container-fluid">
            <div class="section-title text-center">
            <h1 class="bold">Smarter EHR. Transparent Pricing. Built for Small Practices.</h1>
            <h5 class="bold">All the power of an advanced EMR — without inflated costs. Start free, scale into advanced automation & AI as your practice grows.</h5>
            <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-primary mt-3">Book a Demo</a>
            <div class="d-flex justify-content-center align-items-center mt-4">
                <span :class="{'text-primary bold': billingCycle === 'monthly'}">Monthly</span>
                <div class="form-check form-switch mx-3">
                    <input class="form-check-input" type="checkbox" id="billingToggle" 
                          @change="billingCycle = billingCycle === 'monthly' ? 'annual' : 'monthly'"
                          :checked="billingCycle === 'annual'">
                </div>
                <span :class="{'text-primary bold': billingCycle === 'annual'}">Annual</span>
            </div>
          </div>     
        </div>
    </div>
   
      <div class="container">
        <div class="row">
          <div class="col-lg-4 col-md-6 mb-4 h-95">
            <div class="pricing-plan card shadow-box">
              <div class="card-body">
                <h5 class="card-title bold">Starter</h5>
                <p class="card-subtitle mb-2 text-muted">Solo / Small Clinic</p>
                <div class="price my-3">
                  <span class="fs-2 bold">₹{{ starterPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-primary w-100 mb-3">Start Free Trial</a>
                <p class="bold">Includes:</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Up to 100 calls / 500 minutes</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>AI receptionist (24/7)</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Appointment booking / intake</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>SMS + WhatsApp follow-ups</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Call summaries & transcripts</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>HIPAA-aware handling</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4">
            <div class="pricing-plan card shadow-box h-95">
              <div class="card-body">
                <h5 class="card-title bold">Essential</h5>
                <p class="card-subtitle mb-2 text-muted">Scaling Practice</p>
                <div class="price my-3">
                  <span class="fs-2 bold">₹{{ essentialPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                 <!-- <p class="small">.</p> -->
                <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-primary w-100 mb-3">Subscribe Now</a>
                <p class="bold">Includes everything in Growth, plus:</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>300 calls / 1,500 minutes</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Multi-provider routing</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Insurance & FAQ handling</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>CRM / EHR integration</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Priority support</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6 mb-4">
            <div class="pricing-plan card shadow-box h-95 popular">
              <div class="card-body">
                <h5 class="card-title bold">Growth</h5>
                <p class="card-subtitle mb-2 text-muted">Multi-location / Specialty</p>
                <div class="price my-3">
                  <span class="fs-2 bold">₹{{ growthPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                 <!-- <p class="small text-white">.</p> -->
                <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-primary w-100 mb-3">Subscribe Now</a>
                <p class="bold">Includes everything in Starter, plus:</p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>1,000+ minutes</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Custom call flows</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>After-hours emergency routing</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Analytics dashboard</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Dedicated number(s)</li>
                </ul>
              </div>
            </div>
          </div>

          
        </div>    
      </div>
    </section>
       <!-- Call to action section starts here -->
       <div id="content-page" class="mb-5">
   <Cta  title="Why Practices Love AkraHealth’s AI Receptionist" >
      <template v-slot:button>
         <ul class="main-list mt-3">
          <li><b>24/7 Patient Support:</b><br>Patients can reach you anytime — even after hours and on weekends.</li>
          <li class="mt-3"><b>Instant Scheduling:</b><br>AI books, reschedules, and confirms appointments in real time.</li>
          <li class="mt-3"><b>Human-like Conversations:</b><br> Natural, friendly interactions that feel professional and reassuring.</li>
          <li class="mt-3"><b>Reduce Front Desk Load:</b><br>Free up staff from routine calls and admin tasks.</li>
          <li class="mt-3"><b>Integrated with Practice Workflow: </b><br>Works seamlessly with your practice management system and calendar.</li>
        </ul>
    </template>
      </Cta>
      </div>     
    <section class="section path-success">
      <div class="row">
         <div class="section-title mt-3">
                <h2>Why Practices Love Akrahealth's AI Receptionist</h2>
            </div>
         </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="row g-3">

              <!-- Card 1 -->
              <div class="col-6">
                <div class="p-4 shadow rounded bg-white h-100">
                  <div class="mb-3">
                    <i class="fa-regular fa-clock fa-2x text-primary"></i>
                  </div>
                  <h3 class="fw-bold fs-4">24/7</h3>
                  <p class="text-muted mb-0">Always Available</p>
                </div>
              </div>

              <!-- Card 2 -->
              <div class="col-6">
                <div class="p-4 shadow rounded bg-white h-100">
                  <div class="mb-3">
                    <i class="fa-solid fa-shield-halved fa-2x text-primary"></i>
                  </div>
                  <h3 class="fw-bold fs-4">100%</h3>
                  <p class="text-muted mb-0">Secure & Private</p>
                </div>
              </div>

              <!-- Card 3 -->
              <div class="col-6">
                <div class="p-4 shadow rounded bg-white h-100">
                  <div class="mb-3">
                    <i class="fa-solid fa-chart-line fa-2x text-primary"></i>
                  </div>
                  <h3 class="fw-bold fs-4">3x</h3>
                  <p class="text-muted mb-0">More Leads</p>
                </div>
              </div>

              <!-- Card 4 -->
              <div class="col-6">
                <div class="p-4 shadow rounded bg-white h-100">
                  <div class="mb-3">
                    <i class="fa-solid fa-bolt fa-2x text-primary"></i>
                  </div>
                  <h3 class="fw-bold fs-4">&lt;2s</h3>
                  <p class="text-muted mb-0">Response Time</p>
                </div>
              </div>
              <!-- Card 4 -->
              <div class="col-6">
                <div class="p-4 shadow rounded h-100">
            <div class="mb-3">
              <i class="fa-solid fa-user-group fa-3x text-primary"></i>
            </div>

            <h5 class="fw-bold">Improve Patient Experience</h5>
            <p class="text-muted mb-0">
              Deliver seamless and delightful interactions with an AI receptionist for medical offices.
            </p>
          </div>

              </div>
              <!-- Card 4 -->
              <div class="col-6">
                <div class="p-4 shadow rounded h-100">
            <div class="mb-3">
              <i class="fa-solid fa-chart-simple fa-3x text-primary"></i>
            </div>

            <h5 class="fw-bold">Boost Staff Productivity</h5>
            <p class="text-muted mb-0">
              Reduce admin workload and increase team efficiency
            </p>
          </div>

              </div>


            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1">
                    Answer every call and message instantly
                  </h5>                
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1">Track and qualify leads automatically</h5>
                  
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1"> Book appointments without human intervention using healthcare scheduling software</h5>
                 
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1"> Complete call transcripts and analytics</h5>
                 
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1">Custom AI voice and personality</h5>
                 
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1"> Webhook integrations for automation</h5>
                 
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1"> Real-time conversation monitoring</h5>
                 
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-2 rounded-circle d-flex align-items-center justify-content-center mx-auto me-md-1">
                  <i class="fa-solid fa-check fa-1x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="mt-1 mb-1"> Secure admin panel with role management</h5>
                 
                </div>
              </li>
             
            </ul>
          </div>
        </div>
      </div>
  </section>
  <section class="section bg-primary py-5 mt-5 mb-5 aos-init p-4">
    <div class="row align-items-center">
    <div class="col-lg-6 mt-4 mt-lg-0">
       <div class="device-twin free-width align-items-center mt-4 mt-lg-0" >
                        <div class="browser shadow aos-init aos-animate" data-aos="fade-left" style="  overflow: hidden;">
   <img src="images/screens/app/Screenshot3.webp" class="img-fluid" alt="emr-seven" />
                        </div>
                    </div>
    </div>
    <div class="col-md-6 text-white">
      <h2 class="bold text-white">Register Your AI Receptionist</h2>
      <p>Set up your AI receptionist in minutes and start managing healthcare appointments without missed calls or delays. Designed to work alongside your existing operations and scheduling workflows.</p>
      <h4 class="bold text-white mt-3">Key capabilities:</h4>
      <ul class="mt-3 mr--2">
        <li class="mb-2"><i class="fa-solid fa-check-circle text-white me-2"> </i>AI-powered healthcare appointment booking </li>
        <li class="mb-2"><i class="fa-solid fa-check-circle text-white me-2"> </i>Automated call handling & patient responses</li>
        <li class="mb-2"><i class="fa-solid fa-check-circle text-white me-2"> </i>Real-time scheduling and availability awareness</li>
        <li class="mb-2"><i class="fa-solid fa-check-circle text-white me-2"> </i>Free onboarding consultation included</li>
      </ul>
      <a href="/register" class="btn btn-outline-light btn-rounded mt-3 px-4">Register Now →</a>
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
