<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Cta from '@/Partials/Cta.vue';
 import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';

import { Swiper, SwiperSlide } from "swiper/vue";
import { Autoplay, Navigation } from "swiper";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

import { Link } from "@inertiajs/vue3";
import ClinicManagementModal from "../Modals/ClinicManagementModal.vue";


defineProps({
  policy: String,
});
</script>
 
<template>
  <AppLayout title="Clinic Management" mata-title="Clinic Management System | AkraHealth Solutions"
    description="AkraHealth’s Clinic Management System streamlines appointments, billing, and patient records, empowering healthcare teams with efficient daily operations.">
    <HeroSection
      title="Power Your Clinic with a Smarter Clinic Management"
      description="Manage the complete workflow of your clinic check-ins, prescriptions, and payments through a single, streamlined interface."
      >
     <template v-slot:button>
        <button type="submit" href="https://clinic.akrahealth.com/" data-toggle="modal" data-target="#clinic-management-request-modal" target="_blank" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
     </template>
          <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
                <img src="images/mvp/clinic-managemant.png" alt="Clinic Management Dashboard">
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
                  <h2>Everything Your Clinic Needs, All in One Management System</h2>
              </div>
          </div>
        <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
          <p class="text-center">The Clinic Management System is created to provide front-desk personnel, physicians, and administrative staff with all the necessary tools - including appointments, records, billing, and more - at their fingertips.</p>
        </div>
      </div>
          <div class="col-md-6">
            <img src="/images/mvp/clinic-management-3.png" class="img-responsive shadow" alt="our ultimate saas based emr system.webp">
          </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
            <ListItems :items="[
              { title: 'Appointment Manager', description: 'See, edit, and track patient appointments as they unfold, in real-time.', 
                icon: 'fa-check'
              },
              { title: 'Check-in & Status Updates', description: 'Easily manage status updates and keep track of patients as you mark them as \'Waiting\', \'In Consultation\', \'Completed\', etc.',
                icon: 'fa-check'
               },
              { title: 'Patient Directory', description: 'Access to full patient histories and patient profiles with one click.', 
                icon: 'fa-check'
              },
              { title: 'Billing & Revenue', description: 'Easy invoicing with up-to-the-minute revenue reporting.', 
                icon: 'fa-check'
              },
              { title: 'Role-Based Access', description: 'Individualized logins for doctors, nurses, and reception, with access that is tailored to the role.', 
                icon: 'fa-check'
              }
            ]" />
          </div>
        </div>
      </div>
    </section>
  

       <!-- Call to action section starts here -->
       <Cta 
       title="Run Your Clinic Like Clockwork"
       >
       <template v-slot:button>
       <ul class="main-list mt-3">
          <p>From patient check-ins to prescriptions and billing, AkraHealth’s Clinic Management System puts everything in one easy-to-use platform — giving your team more time for care and less time on admin.</p>
          <li>Real-Time Appointment & Status Tracking</li>
          <li>One-Click Access to Patient Histories</li>
          <li>Easy Invoicing & Revenue Reports</li>
          <li>Role-Based Secure Access</li>
          <li>Centralized Admin & Coordination</li>
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
    title="Ready to Simplify Scheduling, Billing, and Records, All in One Place?"
    description="Centralize your clinic's daily operations with one management system, and enhance your visibility, coordination and service delivery."
    /> -->
    <ClinicManagementModal />
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
