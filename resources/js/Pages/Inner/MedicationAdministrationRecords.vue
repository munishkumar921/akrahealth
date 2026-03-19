<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue';
import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';

import { Autoplay, Navigation } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";

import "swiper/css";
import "swiper/css/navigation";

import { Link } from "@inertiajs/vue3";
import MARModal from "../Modals/MARModal.vue";


defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Medication Administration Records" meta-title="EMAR System for Smart Medication Management | AkraHealth"
    description="Streamline medication administration with AkraHealth’s EMAR system. Ensure safe, timely, and compliant care documentation through automated, digital records.">
    
    <HeroSection
      title="Smarter Medication Tracking for Safer Resident Care"
      description="Simplify medication management with accurate, digital MARs that ensure timely delivery, reduce human errors, and support staff with intuitive documentation tools."
    >
      <template v-slot:button>
        <button type="submit" href="https://emr.akrahealth.com/" data-toggle="modal" data-target="#MAR-request-modal" target="_blank"
          class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>

      <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
          <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
            <img src="images/mvp/medication-1.png" alt="healthcare app">
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
                <h2>Simplified Medication Administration for Healthcare Providers</h2>
              </div>
            </div>
            <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
              <p class="text-center">
                The AkraHealth EMAR system eliminates paper-based medication logs by offering a secure,
                centralized digital platform for medication tracking. Designed for long-term care and clinical
                environments, it enables care staff to record, verify, and audit medication administration
                efficiently. Features like real-time alerts, customizable MAR templates, and audit trails help
                reduce medication errors and maintain compliance with care protocols and regulatory
                standards.
              </p>
            </div>
          </div>

          <div class="col-md-6">
            <img src="/images/mvp/medication-6.png" class="img-responsive shadow"
              alt="our ultimate saas based emr system.webp">
          </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-22">Key Features</h2>
            <ListItems :items="[
              { title: 'Digital MAR Sheets', description: 'Structured, timestamped logs for all medications.', icon: 'fa-check' },
              { title: 'Real-Time Alerts', description: 'Never miss a dose with intelligent reminders and flags.', icon: 'fa-check' },
              { title: 'ADLs & Care Notes', description: 'Record daily living activities and wellness reports.', icon: 'fa-check' },
              { title: 'Role-Based Access', description: 'Secure access for nurses, doctors, and admins.', icon: 'fa-check' },
              { title: 'Reports & Exports', description: 'Easily generate care logs, shift summaries, and compliance reports.', icon: 'fa-check' }
            ]" />
          </div>
        </div>
      </div>
    </section>

    <Cta title="Are You Looking To Digitize Your Medication Records?">
      <template v-slot:button>
        <ul class="main-list mt-3">
          <li>Accurate Medication Tracking</li>
          <li>Promotes Staff Accountability</li>
          <li>Elevates Care Delivery</li>
          <li>Join a Personalized Consultation</li>
        </ul>
      </template>
    </Cta>

    <!-- Carousel Section -->
    <section id="featured-services" class="featured-services py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8 col-sm-12">
            <div class="section-title text-center">
              <h2>Discover More From Our Healthcare Applications</h2>
            </div>
          </div>
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
        <a class="app-box d-block" :href="app.link" target="_blank">
          <img :src="app.image" class="img-fluid rounded shadow" :alt="app.alt">
        </a>
      </SwiperSlide>
    </Swiper>
  </div>
</section>

    <!-- <Cta2
      title="Ready to modernize your medication administration process?"
      description="Explore how our EMAR platform empowers your care team to reduce manual errors, meet compliance, and streamline documentation — all within one intelligent system."
    /> -->
    <MARModal/>
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
