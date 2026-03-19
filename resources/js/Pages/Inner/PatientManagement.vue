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
import PatientManagementModal from "../Modals/PatientManagementModal.vue";


defineProps({
  policy: String,
});
</script>
 
<template>
  <AppLayout title="Patient Management" meta-title="Patient Management System | AkraHealth Solutions"
    description="AkraHealth’s patient management system unites appointments, pharmacy, labs, and home care into one easy platform for faster, smarter healthcare.">
    <HeroSection
      title="Comprehensive Patient Management from Start to Finish, Fully Integrated"
      description="Book doctors, order medicines, register lab tests, manage care - all with just one application"
      >
     <template v-slot:button>
        <button type="submit" href="https://patient.akrahealth.com/" data-toggle="modal" data-target="#patient-management-request-modal" target="_blank" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>
          <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
                <img src="images/mvp/patient-management.png" alt="healthcare app">
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
                  <h2>Modern Patient Management for an Accelerated, More Intelligent Care Experience</h2>
              </div>
          </div>
        <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
          <p class="text-center">AkraHealth's complete patient management integrates the flow of care between patients, doctors, pharmacies, and labs, into one seamless experience. Built for speed and accessibility, AkraHealth is changing the delivery of healthcare and the way care is received.</p>
        </div>
      </div>
          <div class="col-md-6">
            <img src="/images/mvp/healthcare-app-6.png" class="img-responsive shadow" alt="our ultimate saas based emr system.webp">
          </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
            <ListItems :items="[
              {
                title: 'Doctor Consultations',
                description: '3-tap easy booking with video/audio support.',
                icon: 'fa-check'
              },
              {
                title: 'Medicine Ordering',
                description: 'Upload prescriptions, track deliveries, paid via UPI.',
                icon: 'fa-check'
              },
              {
                title: 'Lab Test',
                description: 'Book home pick-up and receive a digital report.',
                icon: 'fa-check'
              },
              {
                title: 'Emergency Services',
                description: 'SOS button, ambulances, first-aid assistance.',
                icon: 'fa-check'
              },
              {
                title: 'Home Care Services',
                description: 'Nursing or physiotherapy appointment, with support for different providers.',
                icon: 'fa-check'
              }
            ]" />
          </div>
        </div>
      </div>
    </section>
  

       <!-- Call to action section starts here -->
       <Cta 
       title="Deliver Care Without the Complexity"
       >
       <template v-slot:button>
       <ul class="main-list mt-3">
          <p>With AkraHealth’s all-in-one patient management platform, you can connect every touchpoint — from doctor consultations and lab bookings to medicine orders and home care — in a single, HIPAA-compliant system. Give your patients faster access, better coordination, and a seamless care journey.</p>
          <li>One App for All Services</li>
          <li>Instant Doctor Appointments (Video/Audio)</li>
          <li>Pharmacy, Lab & Home Care Integration</li>
          <li>SOS & Emergency Assistance</li>
          <li>UPI & Digital Payment Support</li>
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
<PatientManagementModal />
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
