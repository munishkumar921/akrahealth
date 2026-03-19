<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue';
import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';

import { Swiper, SwiperSlide } from "swiper/vue";
import { Autoplay, Pagination, Navigation } from "swiper";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

import { Link } from "@inertiajs/vue3";
import NursesOnCallModal from "../Modals/NursesOnCallModal.vue";


defineProps({
  policy: String,
});
</script>
 
<template>
  <AppLayout title="Nurses On Call" meta-title="NursesOnCall – Fast Shift Management for Healthcare"
    description="Launch NursesOnCall in days. Brandable shift management app for nurses & HCAs with payroll, alerts, and admin tools. Book your walkthrough today.">
    <HeroSection
      title="NursesOnCall – Shift Coverage at Your Fingertips"
      description="This ready-to-use MVP helps manage agency nurses shift workflow — from uploading credentials to getting paid. We built it fast. Want your version? We’ll brand and customize it for your team."
      >
     <template v-slot:button>
        <button type="submit" href="https://nurses-on-call.akrahealth.com/" data-toggle="modal" data-target="#nurses-on-call-request-modal" target="_blank" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>
      <template v-slot:images>
      <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="images/mvp/nurses-on-call-2.png" alt="emr-six" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="images/mvp/nurses-on-call-11.png" alt="emr-one" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
      </HeroSection>
   
    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
           <div class="row justify-content-center">
                <div class="col-sm-10">
                    <div class="section-title pb-0">
                        <h2>A Fast, Clean App for Agency Nurses & Healthcare Aides</h2>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
                   <p class="text-center"><b>Nurses on Call</b> helps healthcare staff find verified shifts, manage credentials, and stay organized. From emergency requests to scheduled availability, users get real-time alerts, reminders, and updates. Features like referral tracking, profile badges, and auto-pay are built in and fully customizable</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                  </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="images/mvp/nurses-on-call-4.png" alt="nurses-on-call-4" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="images/mvp/nurses-on-call-5.png" alt="nurses-on-call-5" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-22">Key Features</h2>
            <ListItems :items="[
              {
                title: 'Secure Login',
                description: 'Role-based login with fast onboarding for new users.',
                icon: 'fa-check'
              },
              {
                title: 'Credential Upload',
                description: 'Upload ID, certifications, and licenses for admin verification.',
                icon: 'fa-check'
              },
              {
                title: 'Verified Profiles',
                description: 'Badge and status update once profile is verified.',
                icon: 'fa-check'
              },
              {
                title: 'Real-Time Shift Feed',
                description: 'See open shifts sorted by distance, type, and urgency.',
                icon: 'fa-check'
              },
              {
                title: 'Instant Application',
                description: ' Tap to apply, with confirmation and fallback if rejected.',
                icon: 'fa-check'
              },
              {
                title: 'Emergency Shift Alerts',
                description: 'Notifications for last-minute urgent shift openings.',
                icon: 'fa-check'
              }
              
            ]" />
          </div>
        </div>
      </div>
    </section>
  

       <!-- Call to action section starts here -->
       <Cta 
       title="Want a Version for Your Region or Workforce?">
       <template v-slot:button>
            <ul class="main-list mt-3">
                    <p>Nurses On Call is a ready-to-deploy shift management platform for nurses, HCAs, and healthcare teams. From credential uploads to payroll tracking, it offers a seamless, mobile-first workflow—fully branded and tailored to your staffing model. We handle the setup so you can focus on your team.</p>
                    <li>Branded & Deployed in Days</li>
                    <li>Credential Upload & Verified Profiles</li>
                    <li>Real-Time Shift Matching & Alerts</li>
                    <li>Integrated Payroll & Earnings Dashboard</li>
                    <li>Mobile-First, Scalable, and Secure</li>
                    </ul>
        </template>
       </Cta>
    <!-- Call to action section ends here -->
<!-- Carousel for Healthcare Apps -->
   <section id="featured-services" class="featured-services">
      <div class="container aos-init aos-animate">
        <div class="section-title text-center">
          <h2>Discover More From Our Healthcare Applications</h2>
        </div>

        <Swiper
          :modules="[Navigation, Autoplay]"
          :slides-per-view="3"
          :space-between="30"
          :loop="true"
          :autoplay="{ delay: 3000, disableOnInteraction: false }"
          navigation
          class="mt-4"
        >
          <SwiperSlide v-for="(app, index) in apps" :key="index">
            <Link class="app-box" :href="app.link">
              <img :src="app.image" class="img-fluid" :alt="app.alt" />
            </Link>
          </SwiperSlide>
        </Swiper>
      </div>
    </section>
    <NursesOnCallModal/>
  </AppLayout>
</template>
<script>
const apps = [
  {
    image: "images/mvp/app-covers/Pharmacy App.png",
    link: "/pharmacy-app",
    alt: "Pharmacy App",
  },
  {
    image: "images/mvp/app-covers/Doctor On Call.png",
    link: "/doctor-on-call",
    alt: "Doctor On Call",
  },
  {
    image: "images/mvp/app-covers/Clinic Management.png",
    link: "/clinic-management",
    alt: "Clinic Management",
  },
  {
    image: "images/mvp/app-covers/EMR App.png",
    link: "/emr-app",
    alt: "EMR App",
  },
  {
    image: "images/mvp/app-covers/Patient Management.png",
    link: "/patient-management",
    alt: "Patient Management",
  },
  {
    image: "images/mvp/app-covers/MAR.png",
    link: "/medication-administration-records",
    alt: "Medication Administration Records",
  },
  {
    image: "images/mvp/app-covers/Mobile IV.png",
    link: "/mobile-iv",
    alt: "Mobile-IV",
  },
  {
    image: "images/mvp/app-covers/Nurses On Call.png",
    link: "/nurses-on-call",
    alt: "Nurses On Call",
  },
  {
    image: "images/mvp/app-covers/Prescription.png",
    link: "/prescription",
    alt: "Prescrption",
  },
];
</script>
