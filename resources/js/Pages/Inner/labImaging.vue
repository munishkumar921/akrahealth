<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import ListItems from '@/Partials/ListItems.vue';

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Lab & Imaging"  ta-title="Health Lab & Imaging Management System | Akrahealth"
    description="Online Doctor Consultation is made easier by video/audio calls and flexible process of scheduling appointments with top doctors/healthcare providers on our EMR App">
    <HeroSection2
    title="Streamline Lab & Imaging Workflows with Leading Technology" 
    description="Boost diagnostics through AI-driven imaging, integrated lab result, and auto-managed data. Advance patient outcomes through effective, real-time data retrieval."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get a Demo</a> 
    </template>
   </HeroSection2>  
   <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Who We Are</h2>
                <p>Here at Akrahealth, we offer a fully integrated Lab & Imaging Management System designed to automate diagnosis procedures, maximize collaboration, and enhance patient care. Our software allows healthcare workers to upload diagnostic images, transfer diagnostic information, and grant immediate access to the patient via an online patient portal. If you require a leading imaging solution or effortless integration into existing lab infrastructures, Akrahealth supplies the technology solutions you require.</p>
            </div>
        </div>
  </section>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate pt-0">
            <div class="section-title">
              <h2>Why Akrahealth Lab & Imaging Management System?</h2>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/customizable-emr-templates.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Seamless Image & Data Upload</h4>
                        <p class="description">Facilitate healthcare professionals to upload, store, and share lab images, diagnostic reports, and results with ease.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/24-x-7-access.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Integrated Patient Portal</h4>
                        <p class="description">Allow patients to view their lab results and imaging reports in real-time, enhancing transparency and patient engagement.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/efficient-communication.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">AI-Driven Insights</h4>
                        <p class="description">Leverage AI-powered image analysis to aid diagnostics, minimizing errors and enhancing accuracy.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/e-prescriptions-icon-erx.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Interoperability with Current Systems</h4>
                        <p class="description">Smooth data transfer with current EHR and EMR systems to promote efficient workflow.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Get Started with EMR Integration Today!"
       description="Ready to transform your practice with seamless EMR integration? Our expert team is here to guide you through every step. Contact us now to get started and experience the benefits of a fully integrated healthcare system with the best EMR for small practices."
       >
       <template v-slot:button>
      <a href="https://akrahealth.com/signup" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get Started Now</a> 
    </template>
       </Cta>


  <!-- Why Choose AkraHealth for Your EMR Integration section starts here -->
    <section class="section path-success mt-4">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-lg-start">
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

          <div class="col-md-6 text-md-start">
            <h2 class="bold py-4 text-center text-md-start">Key Features</h2>
            <ListItems :items="[
              { title: 'Secure Storage of Data', description: 'Protect all lab and imaging data using encrypted cloud storage for easy accessibility and security.', icon: 'fa-check' },
              { title: 'Automated Processing of Images', description: 'Streamline diagnostic processes with AI-driven image analysis, increasing accuracy and decreasing manual load.', icon: 'fa-check' },
              { title: 'Patient Portal Integration Access to Real-Time Reports', description: 'Patient access to laboratory results, imaging scans, and diagnostic reports online through a safe portal.', icon: 'fa-check' },
              { title: 'Smooth Data Sharing', description: 'Enable patients to share their reports with specialists or second-opinion providers securely.', icon: 'fa-check' },
              { title: 'AI-Facilitated Image Analysis', description: 'Enhance accuracy of diagnosis by using machine-learning algorithms that support detecting abnormalities in medical imaging.', icon: 'fa-check' },
              { title: 'Predictive Health Insights', description: 'Leverage AI-powered analytics to detect impending health threats and help physicians make better-informed decisions.', icon: 'fa-check' },
              { title: 'Seamless EHR & EMR Integration', description: 'Integrate smoothly with leading Electronic Health Record platforms such as Epic, Cerner, and Allscripts for bidirectional data sharing in real-time.', icon: 'fa-check' },
              { title: 'FHIR-Compliant Data Sharing', description: 'Facilitate seamless interoperability among healthcare systems, streamlining clinician workflows.', icon: 'fa-check' }
          ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->


    <section class="section path-success mb-5">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
          <div class="col-md-6 col-lg-6 position-relative">
            <div class="center-xy op-1">
              <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>
                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/emr-one.webp" alt="emr-one" />
                    </div>
                    <span class="button"></span>
                  </div>
                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/emr-online-chart.webp" alt="online chart" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-4">
                  <h2 class="bold">Industry Security & Compliance</h2>
                  <p>HIPAA Compliant: Our platform guarantees all patient information is encrypted and secured. FHIR Compliant: Enables hassle-free data exchange between healthcare systems for better interoperability. GDPR Compliant: For EU practices, we are compliant with GDPR standards to maintain patient confidentiality. 24/7 Security Monitoring: Ongoing monitoring guarantees data security and inhibits unauthorized access.</p>
                </div>
              </div>
           </div>
      </div>
    </section>



    <Cta2 title="Run Your Diagnostic & Imaging Operations Efficiently with Akrahealth" description="Streamline lab and imaging processes, and enhance diagnostic accuracy, and patient engagement through Akrahealth's AI-based Lab & Imaging Management System. Enjoy hassle-free data integration, auto-diagnosis, and patient portal security for better healthcare outcomes." />


  </AppLayout>
</template>
