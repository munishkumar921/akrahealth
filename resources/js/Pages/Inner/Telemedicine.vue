<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from "@/Partials/HeroSection2.vue";
import ListItems from "@/Partials/ListItems.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Telemedicine" meta-title="Telemedicine EMR Platform for Virtual Care Delivery"
    description="Offer virtual consultations, e-prescriptions, and EMR access with our HIPAA-compliant telemedicine platform. Register now to digitize your care delivery.">
   <HeroSection2
    title="Is Your Practice Ready for Telemedicine?" 
    description="Akrahealth’s Telemedicine platform enables real-time patient connections, enhances efficiency, and ensures secure virtual care. With e-prescriptions and remote monitoring, it bridges convenience and quality in modern healthcare."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Try it free</a> 
    </template>
   </HeroSection2>

   <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-sm-8">
            <div class="section-title pb-0">
                <h2>Beyond EMR: A Comprehensive Telemedicine Solution</h2>
                <p>A Comprehensive Telemedicine Solution. Integration with the best EMR systems ensures seamless electronic medical records (EMR) and electronic health records (EHR) management. Patients receive diagnoses, e-prescriptions, and follow-up care via our secure efficient EMR system. It is designed for small practices, hospitals, and all healthcare providers. Our electronic medical record system is set up for a smooth and effective patient-doctor experience.</p>
            </div>      
          </div>     
          <div class="col-sm-12 screenshots coverflow mt-5">
            <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
          :centeredSlides="true" :spaceBetween="10" :swiperInstance="{ object, Object }" :pagination="{
            clickable: true,
          }" :breakpoints="{
          '640': {
            slidesPerView: 2,
            spaceBetween: 20,
          },
          '768': {
            slidesPerView: 4,
            spaceBetween: 40,
          },
          '1024': {
            slidesPerView: 5,
            spaceBetween: 50,
          },
        }"
        :modules="[Autoplay,Navigation,Virtual]" navigation :loop="true" :autoplay="{ delay: 2500 }">
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-two.webp" style="width: 250.6px" alt="app-two.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-three.webp" style="width: 250.6px" alt="app-three.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-four.webp" style="width: 250.6px" alt="app-four.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-five.webp" style="width: 250.6px" alt="app-five.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-six.webp" style="width: 250.6px" alt="app-six.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-one.webp" style="width: 250.6px" alt="app-one" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-two.webp" style="width: 250.6px" alt="app-two.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-three.webp" style="width: 250.6px" alt="app-three.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-four.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-four.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-five.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-five.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-six.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-six.webp" />
          </SwiperSlide>
        </Swiper>
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
            <div class="device-twin align-items-center">
              <div class="mockup absolute right aos-init aos-animate" data-aos="fade-right ">
                <div class="screen">
                  <img src="/images/emr-six.webp" alt="security-hipaa" />
                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="images/emr-banner-imag.webp" alt="emr-banner-imag" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-20">Key Telemedicine Features</h2>
            <ListItems :items="[
              { title: 'Book Virtual Consultations', description: 'Connect With Trusted Doctors Over Safe Video Consultations.', icon: 'fa-check' },
              { title: 'Get E-Prescriptions', description: 'Get Your Prescription Digitally Approved After The Consultation.', icon: 'fa-check'},
              { title: 'Manage Electronic Medical Records (EMR)', description: 'Securely store, access, and share health records.', icon: 'fa-check'},
              { title: 'Live Track Appointments', description: 'Use our real-time geolocation feature to track scheduled consultations.', icon: 'fa-check'}
            ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Telemedicine Services</h2>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/encounter-management.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Upload Medical Documents</h4>
                        <p class="description">You can effortlessly upload and handle electronic health records (EHR), prescriptions, and lab reports. The best EMR software will safely store your medical history for a smooth consultation.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/medication-management-e-prescription.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Specialized Medical Consultation Categories</h4>
                        <p class="description">Advanced filters are included in our system to assist users in selecting the specialist best suited for their needs. Patients can search for top-rated EMR-integrated doctors based on their healthcare needs.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/clinical-workflow-automation.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Share Health Information</h4>
                        <p class="description">Patients can safely exchange their medical history, consultation information, and prescriptions with healthcare professionals. This aspect increases coordinated care and enhances treatment effectiveness.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/comprehensive-patient-history.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Appointment & Consultation Tracking</h4>
                        <p class="description">Users are able to view and manage their upcoming appointments and consultation history from our live tracking feature. ICD-10 code integration enhances billing and documentation and helps facilitate interactions between the patient and doctor.</p>
                    </div>
                </div>
            </div>
            <!-- <div class="row mt-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/alerts-reminders.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">E-Prescriptions & Medication Management</h4>
                        <p class="description">Receive prescriptions digitally and manage medication history with ease.</p>
                    </div>
                </div>
            </div> -->
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
            <div class="device-twin align-items-center">
              <div class="mockup absolute right aos-init aos-animate" data-aos="fade-right ">
                <div class="screen">
                  <img src="/images/emr-six.webp" alt="security-hipaa" />
                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="images/emr-banner-imag.webp" alt="emr-banner-imag" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-20">How Our Telemedicine Platform Benefits Users</h2>
            <ListItems :items="[
              { title: 'Dedicated Accounts', description: 'Individual accounts for patients and healthcare providers according to their roles.', icon: 'fa-check' },
              { title: 'Smart Appointment Booking', description: 'Select experts and book virtual visits easily.', icon: 'fa-check'},
              { title: 'Advanced Search & Filters', description: 'Search healthcare professionals by specialty, availability, and location.', icon: 'fa-check'},
              { title: 'AI-Powered Healthcare Solutions', description: 'Our AI-based EMR system suggests the most appropriate healthcare solutions.', icon: 'fa-check'},
              { title: 'Secure Data Handling', description: 'Completely HIPAA, FHIR, and ICD-10 compliant for absolute patient data security.', icon: 'fa-check'}
            ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

         <!-- Call to action section starts here -->
         <Cta 
          title="Power Your Practice With Secure Telemedicine"
          >
          <template v-slot:button> 
            <ul class="main-list mt-3">
                <p>AkraHealth’s fully integrated telemedicine platform delivers real-time video consults, AI-powered scheduling, e-prescriptions, and EMR/EHR automation — all from one HIPAA-compliant dashboard. Build a smarter virtual care workflow, designed for clinics, hospitals, and mobile healthcare providers.</p>
                <li>Virtual Visits With Live Tracking</li>
                <li>AI-Based Smart Booking & Filters</li>
                <li>EHR + EMR + ICD-10 + eRx Integration</li>
                <li>Secure Messaging & Patient Accounts</li>
                <li>HIPAA/FHIR-Compliant Telemedicine</li>
              </ul>
          </template>
       </Cta>
   <!-- Call to action section ends here -->

   <section id="faq" class="faq section-bg">
        <div class="container">
          <div class="row">
             <div class="col-sm-4">
              <div class="fs-20">
                <h2>Frequently Asked Questions</h2>
                <p>Not sure how this platform can help you? Wonder why you need to use our services?<br>
                    Here are the
                    answers to some of the most common questions we hear from our appreciated customers.
                </p>
            </div>
             </div>
             <div class="col-sm-8">
              <ul class="faq-list">
                <li>
                    <div data-toggle="collapse" class="collapsed question" href="#faq1">Do you have a patient portal? 
                        <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i>
                    </div>
                    <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                        <p>
                          Registrants can log in and access their medical information securely online.
                        </p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq2" class="collapsed question">Can doctors customize their practice groups?
                        <i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                        <p>
                          Yes! We do offer practice group customization to healthcare providers.
                        </p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq3" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Do you support live chat?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq3" class="collapse">
                        <p>Yes, our platform supports live chat which allows for immediate communication.</p>
                    </div>
                </li>
            </ul>
             </div>
          </div>
            
        </div>
    </section>


  </AppLayout>
</template>
