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
import MobileIVModal from "../Modals/MobileIVModal.vue";


defineProps({
  policy: String,
});
</script>
 
<template>
  <AppLayout  title="Mobile IV Therapy" meta-title="Mobile IV Therapy App | Serenity IV by AkraHealth"
    description="Streamline mobile IV therapy with Serenity IV—digital intake, consent, scheduling, and HIPAA-compliant documentation for safer, faster home-based care.">
    <HeroSection
      title="End-to-End IV Therapy Management on Mobile"
      description="Empower your care team with streamlined digital workflows — from patient intake to IV documentation — designed for safe, efficient mobile infusion services."
      >
      <template v-slot:images>
        <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/mobile-iv-one.webp" alt="emr-six" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/mobile-iv-two.webp" alt="emr-one" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
     <template v-slot:button>
        <button type="submit" href="https://mobile-iv.akrahealth.com/" data-toggle="modal" data-target="#mobile-iv-request-modal" target="_blank" class="btn btn-success btn-lg btn-rounded px-4 mt-3">Get this MVP</button>
      </template>
          
      </HeroSection>
   
    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
           <div class="row justify-content-center">
          <div class="col-sm-10">
              <div class="section-title pb-0">
                  <h2>Simplified IV Therapy Tracking for Home-Based Care</h2>
              </div>
          </div>
        <div class="col-sm-12 col-lg-10 order-md-2 ms-4">
          <p class="text-center">Serenity IV is a mobile-first application designed for nurses and providers offering IV infusion services at home or in mobile settings. It centralizes intake, consent, medication logs, appointments, and IV documentation into one seamless workflow. With features like geo-tagged appointments, digital consent capture, and timestamped IV procedure logs, Serenity IV enhances patient safety and improves care team coordination — all while ensuring HIPAA-compliant data management.</p>
        </div>
      </div>
      <div class="col-md-12 col-lg-6">
          <div class="center-xy op-1">
              <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                style="width: 600px; height: 600px" data-aos="zoom-in">
              </div>
          </div>
          <div class="device-twin mx-auto align-items-center">
              <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                  <div class="screen">
                  <img src="/images/mobile-iv-five.webp" alt="emr-one" />
                  </div>
                  <span class="button"></span>
              </div>
              <div class="iphone-x front me-0">
                  <div class="screen shadow-box">
                  <img src="/images/mobile-iv-four.webp" alt="emr-five" />
                  </div>
                  <div class="notch"></div>
              </div>
          </div>
      </div>

          <div class="col-md-6 text-center text-md-start pl-md-5">
            <h2 class="bold py-4 text-center text-md-start fs-22">Key Features</h2>
            <ListItems :items="[
              {
                title: 'Patient Intake Forms',
                description: ' Capture Name, DOB, vitals, medical history, and assessment in a mobile-responsive layout.',
                icon: 'fa-check'
              },
              {
                title: 'Consent & Acknowledgment',
                description: 'Upload or digitally sign informed consent with secure, time-stamped records.',
                icon: 'fa-check'
              },
              {
                title: 'Medication & IV Logs',
                description: 'Record dose, volume, IV site, insertion technique, and reassessment notes.',
                icon: 'fa-check'
              },
              {
                title: 'Appointment Scheduler',
                description: 'Calendar view with nurse assignment, geo-location tracking, and appointment status.',
                icon: 'fa-check'
              },
              {
                title: 'IV Documentation Tracker',
                description: 'Visual checklist for IV steps: vitals, insertion, dressing, reassessment, final notes.',
                icon: 'fa-check'
              },
              {
                title: 'Inventory Monitoring',
                description: 'Manage stock of meds, IV kits, and receive low-supply alerts.',
                icon: 'fa-check'
              }
              
            ]" />
          </div>
        </div>
      </div>
    </section>
  

       <!-- Call to action section starts here -->
       <Cta 
       title="Deliver IV Therapy Smarter, Faster & Safer">
       <template v-slot:button>
            <ul class="main-list mt-3">
                    <p>From patient intake to final documentation, Serenity IV puts your entire mobile infusion workflow in one HIPAA-compliant app — reducing paperwork and boosting care team efficiency.</p>
                    <li>Digital Intake & Consent Forms</li>
                    <li>Real-Time Scheduling & Geo-Tracking</li>
                    <li>Medication & IV Logs with Time Stamps</li>
                    <li>Inventory Monitoring & Low-Supply Alerts</li>
                    <li>HIPAA-Compliant Data Security</li>
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
    title=" Ready to digitize your IV therapy process?"
    description=" Discover how Serenity IV empowers care teams to deliver safer, faster, and more accountable home-based IV services. Say goodbye to paperwork and hello to real-time care coordination."
    /> -->
<MobileIVModal/>
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
