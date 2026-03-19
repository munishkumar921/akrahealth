<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import "swiper/css";
import "swiper/css/bundle";
import DemoRequest from '@/Pages/Modals/DemoRequest.vue';

import Cta from '@/Partials/Cta.vue';
import HeroSection2 from "@/Partials/HeroSection2.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Billing Integration" meta-title="Healthcare Billing Integration with ICD-10 & HCPCS | Secure Medical Billing – AkraHealth"
    description="Streamline healthcare billing with secure medical billing integration. AkraHealth supports ICD-10, HCPCS, and EMR-connected billing workflows with enterprise-grade data security.">
    
    <HeroSection2
    title="Effortless Healthcare Billing Integration"
    subtitle="Built for accuracy, compliance, and data security."
    description="Optimize your healthcare billing operations with AkraHealth’s secure billing integration—designed to connect seamlessly with EMRs, reduce claim errors, and improve revenue flow while maintaining HIPAA-compliant data security."
    bannerImage="/images/screens/dash/Billing.webp">
    <template v-slot:button>
      <Button  data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2"> Start optimizing your billing</Button> 
    </template>
   </HeroSection2>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Unlock Seamless Billing Integration with Akrahealth</h2>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/accurate-claims-submission.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Accurate Claims Submission</h4>
                        <p class="description">Automate healthcare billing submissions using standardized coding to reduce rejections and accelerate reimbursements.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/custom-billing-solutions.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Custom Billing Solutions</h4>
                        <p class="description">Tailored medical billing integrations for clinics, urgent care, telemedicine, and specialty healthcare practices.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/interoperability-with-leading-platforms.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Interoperability with Leading Platforms</h4>
                        <p class="description">Connect billing systems with EMRs to ensure consistent data flow across healthcare operations.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/payment-processing-reconciliation.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Payment Processing & Reconciliation</h4>
                        <p class="description">Automated payment posting improves financial visibility while maintaining strong data security controls.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/insurance-verification-follow-up.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Insurance Verification & Follow-Up</h4>
                        <p class="description">Reduce delays with automated eligibility checks and claim follow-ups.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/hipaa-compliance-data-security.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">HIPAA-Compliant Data Security</h4>
                        <p class="description">Healthcare billing data is protected through encrypted workflows, access control, and regulatory compliance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/scalable-solutions.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Scalable Billing Architecture</h4>
                        <p class="description">Designed to grow with your healthcare organization without compromising performance or security.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                          <img src="/images/svg/real-time-updates-alerts.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Real-Time Billing Insights</h4>
                        <p class="description">Get real-time alerts on claim status, denials, and payments to improve cash flow.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Looking for Secure Healthcare Billing Integration?">
       <template v-slot:button>
           <p>We design billing systems that align with your clinical workflows, coding standards, and data security requirements.</p>
            <ul class="main-list mt-3">
              <li>Hospitals & Multi-Specialty Clinics</li>
              <li>Telemedicine & Virtual Care Providers</li>
              <li>Specialty Practices (Psychiatry, Pediatrics, Dental)</li>
              <li>Labs, Pharmacies & Diagnostic Centers</li>
              <li>Small Practices & Urgent Care</li>
            </ul>
                  <!-- <a href="#"  class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Talk to Billing Experts</a>  -->

         </template>
       </Cta>
   <!-- Call to action section ends here -->


   <section class="section my-3">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
              <h2 class="bold fs-20">ICD-10 Integration for Healthcare Billing</h2>
              <p>Ensure accurate diagnosis coding with automated ICD-10 integration built into your billing workflow.</p>
              <p>AkraHealth enables healthcare providers to maintain compliance, reduce coding errors, and submit cleaner claims—without manual intervention.
              </p>
              <ul class="main-list">
                 <li>Accurate ICD-10 diagnosis mapping</li>
                 <li>Compliance with evolving healthcare regulations.</li>
                 <li>Faster claim approvals and reduced denials.</li>
              </ul>
          </div>
          <div class="col-md-6 position-relative">
            <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 500px; height: 500px" data-aos="zoom-in"></div>
                </div>
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/emr-online-chart.webp" alt="emr-online-chart" />
              </div>
              <div class="notch"></div>
            </div>
          </div>
        </div>
      </div>
    </section>


  <!-- Why Choose AkraHealth for Your EMR Integration section starts here -->
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
            <div class="mb-4">
            <h2 class="bold fs-20">CPT Code Integration</h2>
              <p>Reliable CPT integration ensures accurate procedure billing and better reimbursement outcomes across healthcare services.</p>
              <!-- <p>Akrahealth's CPT code integration speeds up the ability of healthcare providers to accurately charge for and support billing for medical services with procedure codes. This integration increases billing accuracy and improves reimbursement rates, allowing providers to focus more on patient care.
              </p> -->
           </div>
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">
                    Accurate Procedure Coding
                  </h5>
                  <p class="m-0">
                    CPT codes for services rendered are integrated automatically.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Improved Reimbursement</h5>
                  <p class="m-0">
                    This helps to ensure that coding is performed correctly for faster and more reliable reimbursements.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Streamlining Workflow</h5>
                  <p class="m-0">
                    This significantly cuts back on time spent managing manual coding processes.
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

    <section class="section my-3">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
              <h2 class="bold fs-20">HCPCS Code Integration</h2>
              <p>Improve billing accuracy for medical supplies, equipment, and non-physician services with HCPCS code integration.</p>
              <p>AkraHealth integrates HCPCS codes directly into your healthcare billing workflow—reducing inconsistencies and improving claim acceptance.</p>
              <ul class="main-list">
                 <li>Accurate coding for supplies and services</li>
                 <li>Reduced billing delays and rework.</li>
                 <li>Faster insurance claim processing.</li>
              </ul>
          </div>
          <div class="col-md-6 position-relative">
            <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 500px; height: 500px" data-aos="zoom-in"></div>
                </div>
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/emr-online-chart.webp" alt="emr-online-chart" />
              </div>
              <div class="notch"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

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
            <div class="mb-4">
            <h2 class="bold fs-20">Custom Medical Billing Systems for Healthcare</h2>
              <p>Tailored Billing Integration That Fits Your Practice</p>
              <p>AkraHealth specializes in building custom healthcare billing systems aligned with your EMR, coding standards (ICD-10, HCPCS), and operational goals.</p>
           </div>
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">
                    Custom billing system architecture

                  </h5>
                  <p class="m-0">
                    EMR-connected billing workflows.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Secure healthcare data handling</h5>
                  <p class="m-0">
                    Integrates easily with any existing EMR/EHR system for a seamless workflow.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Ongoing compliance and system support</h5>
                  <p class="m-0">
                    Ongoing support and system updates to keep your system compliant with industry standards.
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
      <DemoRequest />
  </AppLayout>
</template>
