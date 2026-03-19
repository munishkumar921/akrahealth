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
import DoctorOnCallModal from "../Modals/DoctorOnCallModal.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Doctor On Call App | AkraHealth Scheduling "
    description="AkraHealth’s Doctor On Call app automates scheduling, ensures fair duty distribution, and streamlines healthcare team management for better efficiency.">

    <HeroSection
      title="Smarter On-Call Scheduling for Modern Healthcare Teams"
      description="Simplify complex scheduling procedures...">
      <template #button>
        <button type="submit" href="https://oncall.akrahealth.com/" data-toggle="modal" data-target="#doctor-on-call-request-modal" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>

      <template #images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
          <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
            <img src="images/mvp/doctor-dashboard.png" alt="...">
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
                <h2>Optimized Duty Management for Modern Healthcare Teams</h2>
              </div>
            </div>
            <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
              <p class="text-center">The AkraHealth Doctor On Call app reduces the stress...</p>
            </div>
          </div>
          <div class="col-md-6">
            <img src="/images/mvp/doctor-on-call-4.png" class="img-responsive shadow" alt="Doctor App">
          </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
            <ListItems :items="[
              { title: 'Automated Scheduling', description: 'Rule-based roster production for any date period.', icon: 'fa-check' },
              { title: 'Fair Distribution', description: 'Distributes holidays, weekends and hours fairly.', icon: 'fa-check' },
              { title: 'Custom Rules', description: 'Custom seasonal/holiday rules combination.', icon: 'fa-check' },
              { title: 'Analytics Dashboard', description: 'Units assigned, distribution, adjustments.', icon: 'fa-check' },
              { title: 'Admin Tools', description: 'Excel import/export, email notifications.', icon: 'fa-check' }
            ]" />
          </div>
        </div>
      </div>
    </section>

    <Cta 
      title=" Put Your On-Call Schedules on Autopilot"
    >
     <template v-slot:button>
       <ul class="main-list mt-3">
          <p>Say goodbye to manual rosters and last-minute scheduling chaos. With AkraHealth’s Doctor On Call app, you can automate shift planning, balance workloads fairly, and manage seasonal rules with ease — all in one intuitive dashboard.</p>
          <li>Automated Rule-Based Scheduling</li>
          <li>Fair Weekend & Holiday Distribution</li>
          <li>Custom Shift & Seasonal Rules</li>
          <li>Real-Time Analytics & Adjustments</li>
          <li>Easy Excel Import/Export & Email Alerts</li>
        </ul>
      </template>
    </Cta>

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
      title="Ready to take control of your scheduling process?"
      description="Explore the ways in which our advanced scheduling system..."
    /> -->
    <DoctorOnCallModal />
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
