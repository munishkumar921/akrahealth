<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import ListItems from '@/Partials/ListItems.vue';

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Prescription" meta-title="e-Prescription System | AI-Driven Medication Management"
    description="AkraHealth’s AI-powered e-Prescription system streamlines prescribing, ensures compliance, integrates with EHR, and improves patient safety with real-time access.">
    <HeroSection2
    title="Simplify e-Prescriptions with State-of-the-Art Technology" 
    description="Improve medication management with AI-driven prescription automation, effortless EHR integration, and automated data processing. Enhance patient outcomes with streamlined, real-time prescription access."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get a Demo</a> 
    </template>
   </HeroSection2>  
   <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Who We Are</h2>
                <p>At Akrahealth, we offer a seamlessly integrated e-Prescription (eRX) System that is meant to maximize medication management, facilitate collaboration, and enhance patient care. Our system allows healthcare providers to create, send, and manage electronic prescriptions securely while providing real-time access for patients via a secure patient portal. Whether you require sophisticated prescription automation or seamless integration with current healthcare systems, Akrahealth provides the technology you require.</p>
            </div>
        </div>
  </section>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate pt-0">
            <div class="section-title">
                <h2>Why Akrahealth e-Prescription (eRX) System?</h2>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/e-prescription-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Simplified e-Prescription Management</h4>
                        <p class="description">Empower healthcare providers to create, save, and exchange digital prescriptions with ease.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/patient-portal.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Integrated Patient Portal</h4>
                        <p class="description">Patients are able to view their prescriptions in real-time, enhancing transparency and medication compliance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/ai-driven-solutions-for-enhanced-documentation.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">AI-Driven Insights</h4>
                        <p class="description">Leverage AI-powered analysis to aid in prescribing decisions, minimizing errors and increasing accuracy.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/hipaa-compliance-data-security.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">HIPAA & GDPR Compliant</h4>
                        <p class="description">Implement high security and privacy standards for sensitive patient information.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

       <!-- Call to action section starts here -->
       <Cta 
       title=" Prescriptions, Reimagined with AI Precision"       
       >
       <template v-slot:button>
         <ul class="main-list mt-3">
            <p>Upgrade to AkraHealth’s e-Prescription (eRX) System — a secure, AI-powered platform designed to simplify prescribing, improve patient safety, and boost care efficiency. From real-time drug interaction checks to seamless pharmacy coordination, experience smarter medication management for better outcomes.</p>
            <li>AI-Driven Prescription Accuracy</li>
            <li>Real-Time Patient Portal Access</li>
            <li>HIPAA, GDPR & FHIR Compliant</li>
            <li>Seamless EHR & EMR Integration</li>
            <li>Automated Drug Interaction Alerts</li>
          </ul>
       </template>
       </Cta>
   <!-- Call to action section ends here -->


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
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
            <ListItems :items="[
              { title: 'Secure Data Storage', description: 'Secure all prescription information through encrypted cloud storage, with guaranteed accessibility and protection.', icon: 'fa-check' },
              { title: 'Automated Prescription Processing', description: 'Maximize efficiency using AI-based prescription generation, optimizing accuracy and decreasing manual labor.', icon: 'fa-check' },
              { title: 'Real-Time Prescription Access', description: 'Patients can view and manage their prescriptions through a secure online portal.', icon: 'fa-check' },
              { title: 'Easy Pharmacy Coordination', description: 'Allow patients to send prescriptions directly to their preferred pharmacy for seamless medication fulfillment.', icon: 'fa-check' },
              { title: 'AI-Powered Drug Interaction Screens', description: 'Enhance prescription safety through machine-learning algorithms that identify potential drug interactions.', icon: 'fa-check' },
              { title: 'Predictive Health Insights', description: 'Utilize AI-powered analytics to recommend best-in-class medication regimens and support doctors in making evidence-based decisions.', icon: 'fa-check' },
              { title: 'Seamless EHR & EMR Integration', description: 'Easily connect to key Electronic Health Record systems such as Epic, Cerner, and Allscripts to exchange data in real-time.', icon: 'fa-check' }
          ]" />
          </div>
        </div>
      </div>
    </section>



   <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Secure & Compliant e-Prescription Data Sharing</h2>
            </div>
                <div class="row gap-y">
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/seamless-integration-with-major-emr-systems.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">FHIR-Compliant Data Sharing</h5>
                            <p class="mb-0">
                              Foster seamless interoperability among healthcare systems, streamlining clinicians' workflows. Industry Security & Compliance.
                            </p>
                           </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/custom-emr-configurations-and-workflows.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">HIPAA Compliant</h5>
                            <p class="mb-0">
                              Our system maintains all patient data encrypted and secure
                            </p>
                           </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/improved-workflow-efficiency.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">FHIR Compliant</h5>
                            <p class="mb-0">
                              Enables hassle-free exchange of data among healthcare systems for enhanced interoperability.
                            </p>
                           </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/data-security.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">GDPR Compliant</h5>
                            <p class="mb-0">
                              For practices in the EU, we follow GDPR guidelines to safeguard patient privacy.
                            </p>
                           </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/support-for-complex-data-migrations.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">24/7 Security Monitoring</h5>
                            <p class="mb-0">
                              Ongoing monitoring provides data security and blocks unauthorized access.
                            </p>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

     <!-- key Features section ends here -->

  </AppLayout>
</template>
