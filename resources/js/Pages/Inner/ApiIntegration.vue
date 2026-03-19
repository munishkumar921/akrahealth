<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import IconboxCard from '@/Partials/IconboxCard.vue';
import ListItems from '@/Partials/ListItems.vue';

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="API Integration" 
   meta-title="HIPAA-Compliant EMR API Integration | AkraHealth "
    description="Achieve seamless interoperability with AkraHealth’s HIPAA-compliant EMR API integration. Connect EHR systems, automate workflows, and ensure secure patient data exchange.">
    <HeroSection2
    title="Interoperable API Integration for EHR & EMR Systems" 
    description="AkraHealth provides HIPAA-compliant API solutions for free EMR systems, web-based EHRs, and telemedicine EMR, ensuring seamless data exchange and interoperability."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get Started</a> 
    </template>
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="row">
                <div class="col-sm-12 col-lg-10 m-auto">
                  <div class="section-title">
                <h2>Seamless EMR API Integration</h2>
                <p class="text-center">
                  AkraHealth's custom API solutions enhance free EMR systems, web-based EHRs, and cloud-based EMR platforms, ensuring workflow efficiency, integrated data flows, and interface customization. We bridge the gap between EMR healthcare systems and third-party platforms, enabling secure data exchange for patient demographics, medications, vitals, diagnoses, immunizations, and more—without compromising confidentiality. Our HIPAA-compliant APIs support telemedicine EMR, eRx systems, and free electronic medical records software, optimizing healthcare interoperability.
              </p>
            </div>
                </div>
              </div>
            <IconboxCard :items="[
              { title: 'Data Exchange', description:'Securely share essential patient data—demographics, medications, immunizations, and diagnoses—to enhance interoperability across EHR software and electronic medical records systems.', icon: '/images/svg/instant-data-sharing.svg'},
              { title: 'API Standards (FHIR/HL7)', description:'Enable seamless EHR and EMR integration for small practices, ensuring smooth data flow with FHIR and HL7 standards.', icon: '/images/svg/accurate-claims-submission.svg' },
              { title: 'Custom Solutions', description: 'Tailored API integrations for small practice EMR, urgent care, and telemedicine EMR, optimizing healthcare workflows.', icon: '/images/svg/custom-emr-configurations-and-workflows.svg' },
              { title: 'Automation & Workflow Optimization', description:'Reduce manual tasks and enhance efficiency with automated data exchange in web-based EMR, cloud-based EHR, and eRx systems.', icon: '/images/svg/integrated-services.svg' } 
              ]" />
            </div>
        </section>

     <section class="section">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
            <div class="mb-3">
              <h2 class="bold fs-22">FHIR and HL7-Based EMR API Integrations</h2>
            </div>
            <ListItems :items="[
                      { 
                        title: 'FHIR Resource Implementations', 
                        description: 'Enhance interoperability for free online EMR, best free EHR systems, and telemedicine EMR.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'HL7v2 Messaging Capabilities', 
                        description: 'Enable real-time communication between free EHR for small practices, EHR software, and EMR telemedicine platforms.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Secure API Endpoints', 
                        description: 'Implement fortified API security for free medical record software, eRx systems, and electronic medical records software free.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Healthcare Data Protection & Compliance', 
                        description: 'Ensure HIPAA compliance for free online medical records, web-based EMRs, and white-label patient portals.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'OAuth 2.0 & IAM Configuration', 
                        description: 'Provide secure authentication for EMR apps, eRx medicine, and free medical charting software.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Monitoring & Logging Configuration', 
                        description: 'Maintain data integrity for free software for patient record keeping, EMR platforms, and EHR free solutions.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Complete API Documentation', 
                        description: 'Streamline EMR platform integrations with free EHR software, free patient database, and online electronic medical record solutions.', 
                        icon: 'fa-check' 
                      }
                    ]" />

          </div>

          <div class="col-md-6 position-relative">
            <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
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


  <!-- Key Features section starts here -->
    <section class="section path-success">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-lg-start">
          <div class="col-md-6 text-md-start">
            <div class="mb-3 mt-3 mt-md-0 text-center text-md-start">
              <h2 class="bold fs-22">Key Features of Our API Solutions</h2>
              <p>AkraHealth’s EMR API solutions streamline healthcare management by enabling free online EMR access, medical billing systems, and integrated patient management across free EHR systems and cloud-based EMR platforms.</p>
            </div>
            <ListItems :items="[
                { 
                  title: 'Provider Listings', 
                  description: 'Easily manage and retrieve healthcare provider data across web-based EMR systems and free medical record software.', 
                  icon: 'fa-check' 
                },
                { 
                  title: 'Demographics & Insurance', 
                  description: 'Share and access patient demographics, insurance details, and free electronic medical records, improving care coordination.', 
                  icon: 'fa-check' 
                },
                { 
                  title: 'Appointment Scheduling', 
                  description: 'Our best practice management software simplifies scheduling within free EHR for small practices, telemedicine EMR, and EMR platforms.', 
                  icon: 'fa-check' 
                },
                { 
                  title: 'Pharmacy & Prescriptions', 
                  description: 'Enable seamless eRx system integration for prescription management, medication exchange, and drug interaction tracking.', 
                  icon: 'fa-check' 
                },
                { 
                  title: 'Patient Documents', 
                  description: 'Securely manage patient medical records using white-label patient portals, free patient record software, and free software for patient record keeping.', 
                  icon: 'fa-check' 
                },
                { 
                  title: 'Clinical Information', 
                  description: 'Facilitate real-time data sharing across EMR online platforms, web-based EMRs, and electronic medical records software free, ensuring seamless interoperability.', 
                  icon: 'fa-check' 
                }
              ]" />
          </div>
          <div class="col-md-6">
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
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

     <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 me-lg-auto position-relative">
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
                <div class="mb-4 mt-3 mt-md-0">
                <h2 class="bold fs-22">Complex EMR API Integrations – FHIR/HL7</h2>
                <p>AkraHealth delivers seamless EMR API integrations for top EHR/EMR systems like Epic, Cerner, and DrChrono, ensuring healthcare interoperability through FHIR and HL7 standards.</p>
              </div>
              <ListItems :items="[
                 { title: 'HL7 Integration', description: 'Enhance patient data exchange across free EHR systems, telemedicine EMR, and healthcare IT systems.', icon:'fa-check'},
                 { title: 'RxNorm API', description: 'Optimize medication management by integrating eRx systems, GoodRx, and advanced e-prescriptions for healthcare apps and telemedicine services.', icon: 'fa-check'}
              ]" />
          </div>
        </div>
      </div>
    </section>

      <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <div class="mb-4">
                <h2 class="bold fs-22">Benefits of API Integration in Healthcare</h2>
                <p>API integration in healthcare enhances patient management systems, ensuring data accuracy, minimizing errors, and enabling timely interventions. Key benefits include:</p>
                </div>
                <ListItems :items="[
                      { 
                        title: 'Centralized Data Management', 
                        description: 'Consolidate patient records across free online EMR, best free EMR systems, and cloud-based EMR platforms, supporting EMR for psychiatry, EMR for pediatrics, and small practice EMR solutions.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Enhanced User Experience', 
                        description: 'Improve access to telemedicine EMR, free EHR systems, and web-based EMR systems, allowing patients to manage eRx medicine, schedule appointments, and access free online medical records.', 
                        icon: 'fa-check' 
                      },
                      { 
                        title: 'Improved Communication', 
                        description: 'Facilitate seamless messaging between providers and patients through healthcare apps, white-label patient portals, and free electronic medical records software, enhancing patient satisfaction and care coordination.', 
                        icon: 'fa-check' 
                      }
                    ]" />
  
              </div>
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
            </div>
      </div>
    </section>

   <!-- Call to action section starts here -->
     <Cta 
       title=" Looking for EMR/EHR API Integration?"
       >
       <template v-slot:button>
            <p>Connect your EMR and EHR platforms with secure, HIPAA-compliant APIs to enable real-time data sharing, seamless interoperability, and optimized workflows. Perfect for:</p>
            <ul class="main-list mt-3">
              <li>Hospitals & Multi-Specialty Clinics</li>
              <li>Telemedicine & Virtual Care Providers</li>
              <li>Healthcare Startups & mHealth App Developers</li>
              <li>Labs, Pharmacies & Insurance Payers</li>
              <li>Private Practices & Urgent Care Centers</li>
            </ul>
        </template>
     </Cta>
   <!-- Call to action section ends here -->
   
   <section class="section my-4">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 me-lg-auto position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 500px; height: 500px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="aos-init aos-animate" data-aos=" fade-left ">
                      <img class="img-fluid" src="/images/webpages/why-Choose.webp" alt="emr-one" />
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-4 mt-3 mt-md-0">
                <h2 class="bold">Why Choose AkraHealth for API Integration?</h2>
              </div>
              <ListItems :items="[
                  { 
                    title: 'Effortless Integration', 
                    description: 'Seamlessly integrate with top EMR systems, EHR software, and telemedicine platforms for streamlined patient care.', 
                    icon: 'fa-check' 
                  },
                  { 
                    title: 'Custom Solutions', 
                    description: 'Tailored API integrations for small practices, telemedicine, urgent care, and EMR for psychiatry.', 
                    icon: 'fa-check' 
                  },
                  { 
                    title: 'HIPAA Compliant', 
                    description: 'Ensure secure, HIPAA-compliant patient data exchange across your healthcare EMR systems and electronic health records software.', 
                    icon: 'fa-check' 
                  }
                ]" />

              </div>
            </div>
      </div>
    </section>

  </AppLayout>
</template>
