<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';
import { Autoplay, Pagination, Navigation, Parallax,Virtual } from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/bundle';

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout titile="Integration" meta-title="Integrated APIs | eRX | GoodRx - AKRAHEALTH"
    description="Our Patient Portal is integrated with various APIs ranging from Rx APIs to integration with various Pharmacies & Labs stimulating 100% accuracy in lisitng medicines, Pharmacies & Labs">
    <HeroSection
      title="Smart Healthcare Integrations for a Connected Future"
      description="Effortless EMR, API, and billing integrations to streamline operations, enhance patient care, and optimize revenue—secure, efficient, and HIPAA-compliant."
      >
     <template v-slot:images>
      <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/app-one.webp" alt="app-one" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/app-two.webp" alt="app-two" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
      </HeroSection>
  
    <!-- <header class="header integrations-header section">
      <div class="container">
                        <div class="col-md-12 text-center">
                            <div class="row gap-y align-items-center justify-content-around">
                             <div class="col-8">
                                    <h4 class="bold text-contrast text-center">
             We offer API-driven solutions and international compliance standards to ensure data interoperability and efficiency across various EMR platforms enabling custom interaction between healthcare systems and third-party platforms. 

                                    </h4>
                                    <p class="bold pb-2 font-size-16 text-contrast text-center">
                                      Our third-party integration services support hospitals, clinics, and healthcare networks worldwide by streamlining health operations, improving patient care, and maintaining Data security in accordance with HIPAA compliance.


                                    </p>
                                  </div>
                            </div>
                        </div>
                    </div>
      </header> -->
<!--  
    <section class="quick-start-your-business mb-lg-n3 mt-5">
        <div class=" container con-box">
            <div class="col-10 col-lg-12 mx-auto order-md-2 ms-4  ">
                <p>With APIs so powerful, Rx for medicines just turns out handy for all healthcare providers and Virtual
                    Assistants on our EMR/EHR Platform. Through this, our patient experience for doctors boosts up alongside
                    patient experience improvement and engagement. Our GoodRx APIs turn out to be one of our patient
                    experience key drivers encouraging us to build a safer and bigger platform.
                    Healthcare Providers and Virtual Assistants keen to support us on our Patient Portal can request a 
                    <a class="text-danger bold text-decoration-underline" style="cursor: pointer;"onclick="smoothScroll(document.getElementById('second'))">FREE PLAN</a> with us and see how our virtual healthcare platform is the best to
                    assist patients.
                </p>
                <div class="section-title mt-3 col-10 ms-lg-6 ">
                    <a href="#"style="cursor: pointer;"onclick="smoothScroll(document.getElementById('Registration'))"class="btn btn-primary"> Join Us Now</a>
                </div>
            </div>
        </div>
    </section> -->
    <section id="features" class="section how-it-works">
        <div class="container">
          <div class="row justify-content-center">
              <div class="col-sm-10">
                <div class="section-title">
                    <h2>3rd Party EMR Integration</h2>
                    <p>We specialize in integrating with the most prominent EMR systems, acting as a bridge across platforms to build smooth custom workflows, feature interfaces, and integrated data flows that existing systems may not support. Whether your practice uses NexTech, Epic, Cerner, DrChrono, or any other EMR, our integration solutions allow seamless communication and ensure data interoperability for demographics, vitals, medications, conditions, immunizations, allergies, and more.</p>
                </div>
              </div>
          </div>
           
            <div class="row gap-y text-center text-md-start">
                <div class="col-md-4 py-4 text-center position-relative">
                    <div class="shapes-figure shapes-container">
                        <div class="shape center-x"></div>
                    </div>
                    <figure class="mockup mb-4">
                        <img src="/images/data-exchange.webp"
                            class="mb-3 img-step" alt="Assistance inHealthcareServices">
                    </figure>
                    <h5 class="bold">Data Exchange</h5>
                    <p class="text-muted ">Securely share key patient data such as demographics, medications, diagnosis/conditions, vitals, lifestyle, and lab results.</p>
                </div>
                <div class="col-md-4 py-4 text-center position-relative">
                    <div class="shapes-figure shapes-container">
                        <div class="shape  center-x"></div>
                    </div>
                    <figure class="mockup mb-4">
                        <img src="/images/api-integration.webp" class="mb-3 img-step"
                            alt="RemoteCareServices-1">
                    </figure>
                    <h5 class="bold">API Integration (FHIR/HL7)</h5>
                    <p class="text-muted">We support modern API standards for smooth interoperability, ensuring all connected systems work in harmony.</p>
                </div>
                <div class="col-md-4 py-4 text-center position-relative">
                    <div class="shapes-figure shapes-container">
                        <div class="shape  center-x"></div>
                    </div>
                    <figure class="mockup mb-4">
                        <img src="/images/Custom-Integration.webp"
                            class="mb-3 img-step" alt="Assistance in HealthcareServices">
                    </figure>
                    <h5 class="bold">Custom Integration</h5>
                    <p class="text-muted">Go beyond the constraints of typical enterprise platforms with our tailor-made solutions to meet your unique needs.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="section bg-light bg-light-gradient edge bottom-right emr-plateform">
        <div class="container bring-to-front emr-plateform">
          <div class="row justify-content-center">
              <div class="col-sm-10">
                <div class="section-title">
                    <h2>Standard Integrations</h2>
                    <p>We provide standardized integration solutions that allow healthcare organizations to connect various health IT systems without extensive customization. Our standard integrations help:</p>
                </div>
              </div>
          </div>
          <div class="row gap-y">
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/seamless-integration-with-major-emr-systems.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">Connect EMR and EHR systems</h5>
                            <p class="mb-0">
                              seamlessly with third-party applications.
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
                            <h5 class="bold">Automate data synchronization</h5>
                            <p class="mb-0">across clinical and operational tools.</p>
                           </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/improved-workflow-efficiency.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold">Ensure compliance with regulatory frameworks</h5>
                            <p class="mb-0">
                              including HIPAA, HL7, and FHIR standards.
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
                            <h5 class="bold">Support interoperability</h5>
                            <p class="mb-0">with laboratory, imaging, and pharmacy systems.
                            </p>
                           </div>
                        </div>
                    </div>
                </div>            
        </div>
    </section>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-sm-10">
            <div class="section-title">
                <h2>Billing Integrations</h2>
                <p>Managing healthcare billing efficiently requires seamless integration between EMRs, revenue cycle management (RCM) systems, and financial platforms. Our billing integrations ensure accurate and timely transactions, reducing administrative burden and improving cash flow.</p>
            </div>
           </div>
          </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/e-prescription-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">EMR & RCM System Integrations</h4>
                        <p class="description">Automate the billing process by connecting EMRs with leading billing platforms like Kareo, DrChrono, and Athenahealth.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/orders-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Claims Management</h4>
                        <p class="description">Streamline claim submissions and reduce denials with real-time eligibility checks.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/payors.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Automated Invoicing & Payments</h4>
                        <p class="description">Enable secure transactions and electronic invoicing.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/schedule-setup.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Compliance with HIPAA & Regulatory Standards</h4>
                        <p class="description">Ensure that all billing transactions meet industry compliance standards.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



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
                <h2 class="bold fs-20">Integrated Reports - Connecting Labs, Pharmacies, and Doctors</h2>
                <p>Say goodbye to juggling multiple systems for reporting. Our integration solutions unify lab reports, pharmacy data, and doctor updates, providing healthcare teams with a holistic view of patient care across different healthcare providers.</p>
              </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section my-3">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6">
              <h2 class="bold fs-20">Our Integrated API Features</h2>
              <p>Our integrated APIs streamline healthcare operations by managing and linking hidden datasets across various third-party EMRs, ensuring smooth information exchange.</p>
              <ul class="main-list">
                 <li>Provider Listings</li>
                 <li>Demographics and Insurance</li>
                 <li>Appointment Scheduling</li>
                 <li>Pharmacy & Prescriptions</li>
                 <li>Patient Documents</li>
                 <li>Clinical Information</li>
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
            <h2 class="bold py-4 text-center text-md-start fs-20">API & Standard Integrations (FHIR/HL7)</h2>
            <p>We offer API and standardized integrations with leading EHR/EMRs through marketplaces and direct API integration (FHIR/HL7). Our integration services facilitate seamless interactions between different applications and support HL7 interfaces.
            </p>
            <ListItems :items="[
              { title: 'FHIR APIs', description: 'Retrieve required patient and clinical data using FHIR APIs and fill any missing data through HL7 feeds.', 
                icon: 'fa-check'
              },
              { title: 'GoodRx API', description: 'Enable prescription and medication management with RxNorm RESTful Web API and GoodRx API.',
                icon: 'fa-check'
               },
              { title: 'e-Rx integrations', description: 'Advanced e-Rx integrations support seamless electronic prescription workflows.', 
                icon: 'fa-check'
              },
              { title: 'DrChrono API Integration', description: 'Seamlessly connect with DrChrono’s EMR system for automated data exchange, appointment scheduling, billing, and e-prescriptions.', 
                icon: 'fa-check'
              },
              { title: 'Other EMR Integrations', description: 'We support integrations with Athenahealth, Allscripts, eClinicalWorks, and more, ensuring interoperability with your preferred healthcare system.'}

            ]" />
          </div>
        </div>
      </div>
    </section>


    <section class="section why-choose-us">
      <div class="shapes-container">
        <div class="pattern pattern-dots"></div>
      </div>

      <div class="container pb-8 bring-to-front">
        <div class="row gap-y">
          <div class="col-md-6">
            <div class="mb-5">
              <h2 class="bold fs-20">
                How our Integrated APIs are utilized for the convenience of
                Users
              </h2>
            </div>

            <div class="d-flex align-items-start pb-3">
              <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="feather feather-box stroke-primary">
                  <path
                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                  </path>
                  <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                  <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
              </div>

              <div class="flex-fill">
                <h5 class="bold">Gather health-related data</h5>
                <p>Our Integrated APIs allow healthcare providers to consolidate patient data into a single, secure platform. This approach facilitates secure sharing of personal health information across providers, ensuring accurate diagnoses and timely interventions.
                </p>
              </div>
            </div>
            <div class="d-flex align-items-start pb-3">
              <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center me-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="feather feather-layers stroke-primary">
                  <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                  <polyline points="2 17 12 22 22 17"></polyline>
                  <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
              </div>

              <div class="flex-fill">
                <h5 class="bold">Improve User Experience</h5>
                <p>Enhance patient access to telemedicine and telehealth services with customizable features, including:</p>
                <ul class="main-list">
                  <li>Appointment Reminders:  Automated notifications for upcoming visits.</li>
                  <li>Medical Prescription Uploads: Simplified prescription management.</li>
                  <li>Quick Medication Searches: Fast and user-friendly search functions.</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="rotated-mockups device-twin">
              <div class="browser absolute-lg shadow-lg">
                <img src="/images/integrated-apis.webp" alt="IntegratedAPIs" />
              </div>

              <div class="front iphone-x">
                <div class="screen">
                  <img src="/images/one-apis.webp" alt="one" />
                </div>

                <div class="notch"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
   
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
                <h2 class="bold fs-20">Why Choose AkraHealth’s Integrated Solutions?</h2>
              </div>
              <ListItems :items="[
                  { 
                    title: 'Comprehensive Integration Services', 
                    description: 'Our solutions cover EMR, API, standard, and billing integrations, ensuring that healthcare providers can streamline operations, enhance patient care, and optimize financial workflows', 
                    icon: 'fa-check' 
                  },
                  { 
                    title: 'Data Security & Compliance', 
                    description: 'All integrations are developed in compliance with HIPAA, HL7, and FHIR standards, ensuring secure and regulation-compliant data exchange.', 
                    icon: 'fa-check' 
                  },
                  { 
                    title: 'Customizable & Scalable Solutions', 
                    description: 'We provide tailor-made integrations that adapt to healthcare organizations of all sizes, from small clinics to large hospitals and networks.', 
                    icon: 'fa-check' 
                  }
                ]" />

              </div>
            </div>
      </div>
    </section>

  </AppLayout>
</template>
