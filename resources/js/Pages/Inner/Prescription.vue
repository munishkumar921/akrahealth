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
import PrescriptionModal from "../Modals/PrescriptionModal.vue";


defineProps({
  policy: String,
});
</script>
 
<template>
  <AppLayout  meta-title="AkraHealth Prescription Management App Solutions"
   title="Prescription"
    description="AkraHealth prescription management app streamlines patient records, pharmacy ops, inventory tracking, compliance, and role-based dashboards.">
    <HeroSection
      title="Akrahealth – Smarter Prescription Management at Your Fingertips"
      description="Ready-to-use prescription app for doctors, pharmacies, regulators, and compliance."
      >
     <template v-slot:button>
        <button type="submit" href="https://prescription.akrahealth.com/" data-toggle="modal" data-target="#prescription-request-modal" target="_blank" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>
          <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
                <img src="images/mvp/prescription.png" alt="healthcare app">
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
                  <h2>A Modern App to Simplify Prescription Workflows</h2>
              </div>
          </div>
        <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
          <p class="text-center">Akrahealth Prescription brings together doctors, pharmacists, and regulators on one streamlined platform. It handles everything from patient details and prescription approvals to medicine dispensing and compliance checks. Built mobile-first, it delivers instant notifications, secure role-based access, and automated processes that save time. Features like stock monitoring, compliance dashboards, and quick prescription actions are built in, and fully customizable for your team’s needs.</p>
        </div>
      </div>
          <div class="col-md-6">
            <img src="/images/mvp/prescription-feature.png" class="img-responsive shadow" alt="our ultimate saas based emr system.webp">
          </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
            <ListItems :items="[
              {
                title: 'Secure Login',
                description: 'Role-based access for doctors, pharmacists, and regulators with quick onboarding.',
                icon: 'fa-check'
              },
              {
                title: 'Patient Records',
                description: 'Maintain complete patient details, demographics, and prescription history.',
                icon: 'fa-check'
              },
              {
                title: 'Prescription Lifecycle',
                description: 'Manage requests, approvals, rejections, and dispensing in one place.',
                icon: 'fa-check'
              },
              {
                title: 'Controlled Substances',
                description: 'Special handling for red/yellow category medicines with alerts.',
                icon: 'fa-check'
              },
              {
                title: 'Inventory Tracking',
                description: 'Monitor stock levels, low-stock alerts, and pending deliveries.',
                icon: 'fa-check'
              },
              {
                title: 'Compliance Dashboard',
                description: 'Track compliance scores, violations, and regulatory audits.',
                icon: 'fa-check'
              }
            ]" />
          </div>
        </div>
      </div>
    </section>
  

       <!-- Call to action section starts here -->
       <Cta 
       title="Looking for a Custom Prescription Platform?"
       >
       <template v-slot:button>
       <ul class="main-list mt-3">
          <p>Akrahealth adapts to hospitals, clinics, pharmacies, and regional health authorities. We’ll brand it your way, extend functionality as needed, and align with your workflows—making rollout smooth and seamless.</p>
          <li><b>Custom Branding</b> Reflect your hospital or clinic identity</li>
          <li><b>Feature Extensions</b> Add modules that fit your unique needs.</li>
          <li><b>Workflow Integration</b> Connect easily with your existing systems.</li>
          <li><b>Quick Rollout</b> Get started fast without disruptions.</li>
          <li><b>Scalable Solution</b> Grow effortlessly as your healthcare services expand.</li>
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

    <!-- <Cta2 
    title="Are you seeking to provide patients with a unified platform that addresses all their healthcare requirements?"
    description="Enhance the delivery of care by integrating appointments, diagnostics, pharmacy, and home care into a single streamlined process."
    /> -->
<PrescriptionModal />
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
