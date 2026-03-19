<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Cta from '@/Partials/Cta.vue';
import HeroSection from '@/Partials/EMRHeroSection.vue';
import EMRModal from '@/Pages/Modals/EMRModal.vue';
import WatchDemo from '@/Pages/Modals/WatchDemo.vue';


</script>
<template>
    <AppLayout title="EMR" meta-title="Emr"
        description="Akrahealth offers the best EMR software with secure electronic medical records, telemedicine support & seamless integration for healthcare providers.">
    <HeroSection
       description="Effortlessly manage electronic medical records online with a lightning-fast, secure, and cloud-based EMR system—tailored to healthcare professionals, small practices, and telemedicine providers."
      >
      <template v-slot:title>
        <h1 class="display-lg-4 mt-3 text-center text-md-start">
          <span class="bold">
           Affordable EMR Software <div class="display-lg-5">Built For Modern Healthcare</div>
          </span>
        </h1>
        </template>
     <template v-slot:images>
        <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/EMR-12.webp" alt="emr-six" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/EMR-1.webp" alt="emr-one" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
     <template v-slot:button>
        <button   data-toggle="modal" data-target="#emr-request-modal" class="btn btn-primary btn-lg btn-rounded px-4 mt-3 mr-2">Try for Free</button>
         <button class="btn btn-light btn-lg btn-rounded px-4 mt-3 btn-watch-demo" data-toggle="modal" data-target="#watch-demo-modal">
            Watch Demo
        </button>
     </template>
      </HeroSection>
        <section class="mt-5">
            <div class="container con-box py-0">       
                <div class="col-sm-12">
                    <div class="section-title pb-0">
                        <h2>Cloud-Based White-Label EMR Software for Healthcare</h2>
                    </div>
                </div>
                <div class="row text-center text-lg-start align-items-center">
                    <div class="col-md-12 col-lg-6 me-md-auto">
                       
                        <p class="text-left">AkraHealth provides a cloud-based, white-label EMR and EHR platform built to simplify patient record management and streamline care delivery. Designed for physicians, healthcare organizations, and telehealth providers, our all-in-one EMR supports appointments, electronic health records, and telehealth—while allowing you to offer the platform fully under your own brand. AkraHealth adapts to modern healthcare workflows with a secure, scalable system that improves efficiency and patient experience.
With AkraHealth’s white-label EMR, you can:</p>
                        <ul class="main-list mt-3">
                            <li>Schedule and manage patient appointments with ease</li>
                            <li>Access and update electronic health records (EHR) securely</li>
                            <li>Review patient history and clinical notes in one place</li>
                            <li>Conduct virtual consultations using built-in telehealth</li>
                            <li>Operate EMR and telehealth services under your own brand</li>
                            <li>Improve care delivery and operational efficiency</li>
                        </ul>                  
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
                                <img src="/images/screens/app/Calender.webp" alt="emr-three" />
                                </div>
                                <span class="button"></span>
                            </div>
                            <div class="iphone-x front me-0">
                                <div class="screen shadow-box">
                                <img src="/images/EMR-4.jfif" alt="emr-four" />
                                </div>
                                <div class="notch"></div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </section>
        <section class="section lightweight-template">
            <div class="container con-box">
                <div class="row text-center text-lg-start align-items-center">
                       <div class="col-md-12 col-lg-6">
                           <div class="center-xy op-1">
                               <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                                 style="width: 600px; height: 600px" data-aos="zoom-in">
                               </div>
                            </div>
                            <div class="device-twin mx-auto align-items-center">
                                <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                                    <div class="screen">
                                    <img src="/images/EMR-9.webp" alt="emr-one" />
                                    </div>
                                    <span class="button"></span>
                                </div>
                                <div class="iphone-x front me-0">
                                    <div class="screen shadow-box">
                                    <img src="/images/screens/app/Frame1.webp" alt="emr-five" />
                                    </div>
                                    <div class="notch"></div>
                                </div>
                            </div>
                        </div>
                    <div class="col-md-12 col-lg-6 me-auto pl-lg-5 mt-5 mt-lg-0">
                        <h2 class="bold fs-24">Our All-in-One Cloud-Based EMR Platform</h2>
                        <p class="text-justify">AkraHealth is a modern<span class="bold"> EMR and EHR software</span> built to centralize patient records, appointments, prescriptions, billing, and telehealth in one secure platform. Designed as an <span class="bold">all-in-one EMR</span>, it simplifies how healthcare teams manage daily operations without relying on multiple disconnected systems.</p>
                        <p class="text-justify">With built-in telehealth, appointment scheduling, electronic medical records, e-prescriptions, and billing workflows, AkraHealth enables seamless care delivery through a scalable, cloud-based EMR. Our platform supports efficient data flow, secure access, and streamlined healthcare management across care settings.</p>
                    </div>
                  
                </div>
            </div>
        </section>

        <!---- CTA -->
        <Cta 
          title="Get Started with Your Customizable EMR Register Free Today!"
          >
          
        <template v-slot:button>
            <p class="text-left">Doctors, healthcare providers, and virtual assistants can sign up for AkraHealth’s cloud-based EMR/EHR platform - completely affordable. Our web-based solution offers:</p>
            <ul class="main-list mt-3">
                <li>Affordable  online medical records management</li>
                <li>Built-in telemedicine EMR for virtual care</li>
                <li>Integrated e-prescription (eRx) system</li>
                <li>HIPAA-compliant and secure documentation tools</li>
            </ul>
            <p>Whether you're operating a solo practice, a virtual clinic, or a specialty care service, AkraHealth helps you simplify operations and improve patient care with a flexible, easy-to-use EMR system.</p>
        </template>
          </Cta>
 <!-- Emr Features section starts here -->
        <section id="features" class="features">
        <div class="container aos-init aos-animate">
            <div class="row justify-content-center">
                <div class="col-sm-12 col-lg-7">
                    <div class="section-title pb-0 pb-xl-5">
                        <h2> Power Your Healthcare Operations with a Modern EMR</h2>
                        <p>A cloud-based EMR and EHR platform designed to support telehealth, clinical workflows, patient management, and operational efficiency—all from one integrated system.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="icon-box mt-5 mt-lg-0">
                        <i class="bx bx-receipt"></i>
                        <h4>Appointment & Scheduling</h4>
                        <p>Control patient appointments more effectively with an internet-based EMR system. Reduce no-shows with auto-reminders and real-time scheduling.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-capsule"></i>
                        <h4>Clinical Workflow</h4>
                        <p>Optimize patient visits through electronic medical records software and custom workflows designed specifically for small practices and specialty care.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-file"></i>
                        <h4>eRx & Medication Management</h4>
                        <p>Send prescriptions safely with an in-built eRx system. Safeguard medication using drug interaction warnings in your EMR software.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-injection"></i>
                        <h4>Billing & Insurance Claims</h4>
                        <p>Maximize revenue with top medical billing software. Automate coding, check eligibility, and process insurance claims seamlessly within your EMR system.</p>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="icon-box">
                        <i class="bx bx-body"></i>
                        <h4>Patient Portal & Engagement</h4>
                        <p>Provide patients with secure access to online medical records, lab results, and appointments. Improve communication through telemedicine EMR capabilities.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-clinic"></i>
                        <h4>Lab Integration & Diagnostics</h4>
                        <p>Integrate with labs for test orders and real-time diagnostics within our cloud-based EMR—a central capability of leading EMR solutions.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-shield-plus pl-1"></i>
                        <h4>Reports & Insights</h4>
                        <p>Receive actionable insights with patient care and billing custom reports. Integrated analytics make it one of the finest EMR systems on the market.</p>
                    </div>
                    <div class="icon-box mt-5">
                        <i class="bx bx-food-menu pl-1"></i>
                        <h4>ICD-10 & CPOE</h4>
                        <p>Be compliant with ICD-10 and ease lab/imaging orders with Computerized Provider Order Entry (CPOE) in our EHR solution.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

        <section class="section section ">
          <div class="container con-box">
            <div class="row">
              <div class="col-sm-12 col-lg-10 m-auto">
                <div class="section-title">
                    <h2>Best Cloud-Based EMR for Telehealth</h2>
                    <p>AkraHealth's cloud-based EMR software provides streamlined, automated maintenance to alleviate the burden on IT teams. Our web-based EMR system securely stores electronic medical records to provide healthcare interoperability and immediate access to patient portals for improved decision-making.</p>
                </div>
             </div>
            </div>
                
        <ul class="nav nav-tabs nav-outlined nav-rounded justify-content-around justify-content-md-center mb-5 custom_tabs" role="tablist">
            <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#info-security">Information Security & Compliance</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#customized-clinical">Structured Clinical Documentation</a>
            </li>
           <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#better-accessibility">Anywhere Access for Care Teams</a>
           </li>
           <li class="nav-item">
             <a class="nav-link" data-toggle="tab" href="#online-patient">Patient Access & Engagement</a>
           </li>
           <!-- <li class="nav-item">
             <a class="nav-link" data-toggle="tab" href="#payer-provider">Payer & Provider Collaboration</a>
           </li> -->
        </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div id="info-security" class="tab-pane active">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  order-md-2  ms-md-auto mb-4 ">
                <div class="device-twin m-auto align-items-center">
                    <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
                        <div class="screen">
                        <img src="/images/EMR-6.webp" alt="emr-six" />
                        </div>
                        <span class="button"></span>
                    </div>

                    <div class="iphone-x light front me-0">
                        <div class="screen shadow-box">
                        <img src="/images/EMR-13.jfif" alt="emr-thirteen" />
                        </div>
                        <div class="notch"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 me-md-auto">
                <h2 class="bold fs-20"> Information Security & Compliance</h2>
                <p>AkraHealth is built with healthcare-grade security to protect electronic medical records and sensitive patient data. Our EMR platform uses encrypted storage, role-based access, and audit logs to support compliance-ready operations.</p>
                <p>Designed with <span class="bold">HIPAA and GDPR readiness</span> in mind, AkraHealth ensures that EMR and EHR data access is controlled, traceable, and secure across appointments, telehealth sessions, prescriptions, and billing records</p>
            </div>
        </div>
    </div>
    <div id="customized-clinical" class="tab-pane fade">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  me-md-auto  position-relative mb-4">
                <div class="device-twin m-auto align-items-center">
                    <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
                        <div class="screen">
                        <img src="/images/EMR-28.jfif" alt="emr-twentyeight" />
                        </div>
                        <span class="button"></span>
                    </div>

                    <div class="iphone-x light front me-0">
                        <div class="screen shadow-box">
                        <img src="/images/EMR-25.jfif" alt="emr-twentyfive" />
                        </div>
                        <div class="notch"></div>
                    </div>
                    </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <h2 class="bold fs-20">Structured Clinical Documentation</h2>
                <p>AkraHealth’s EMR supports structured clinical documentation aligned with real healthcare workflows. Providers can document visits using <span class="bold">SOAP notes</span>, diagnoses, prescriptions, and follow-up instructions directly within the electronic medical record.
                </p>
                <p>
                    This structured approach improves documentation consistency, supports clinical accuracy, and keeps all encounter data linked to the patient’s EMR—reducing manual effort and improving care continuity.
                </p>
            </div>
        </div>
    </div>
    <div id="better-accessibility" class="tab-pane fade">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  order-md-2  ms-md-auto mb-4 ">
                <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/EMR-2.jfif" alt="emr-two" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/EMR-5.jfif" alt="emr-five" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <h2 class="bold fs-20">Anywhere Access for Care Teams</h2>
                <p>Our cloud-based EMR provides secure, real-time access to patient records, appointments, prescriptions, and visit history. Authorized users can manage EMR data from any location without compromising security.</p>
                <p>
                    Whether supporting in-person care or <span class="bold">telehealth consultations</span>, AkraHealth enables efficient access to electronic health records while maintaining role-based permissions and audit controls.
                </p>
            </div>
        </div>
    </div>

    <div id="online-patient" class="tab-pane fade">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  me-md-auto mb-4 ">
                <div class="device-twin m-auto align-items-center">
                    <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
                        <div class="screen">
                        <img src="/images/EMR-18.webp" alt="emr-eighteen" />
                        </div>
                        <span class="button"></span>
                    </div>

                    <div class="iphone-x light front me-0">
                        <div class="screen shadow-box">
                        <img src="/images/EMR-3.jfif" alt="emr-three" />

                        </div>
                        <div class="notch"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 ">
                <h2 class="bold fs-20">Patient Access & Engagement</h2>
                <p>AkraHealth includes patient access features that allow individuals to view appointments, prescriptions, visit summaries, and invoices through a secure portal. Patients can also join <span class="bold">telehealth consultations</span> directly from scheduled visits.</p>
                <p>By keeping patients informed and connected to their electronic health records, AkraHealth supports transparency, engagement, and smoother communication within the care journey.</p>
            </div>
        </div>
    </div>
    <div id="payer-provider" class="tab-pane fade">
      <div class="row">
        <div class="col-lg-10 col-sm-12 m-auto text-center">
            <h2 class="bold fs-20">Payer & Provider Collaboration</h2>
            <p>Our Internet-based EMR system streamlines billing management, enhancing payer-provider coordination and accelerating payment, but minimizing administrative hassles.</p>
        </div>
      </div>
    </div>
   </div>         
  </div>
</section>
        <section class="section bg-light bg-light-gradient edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="section-title">
                            <h2>What Our Cloud-Based EMR Platform Delivers</h2>
                            <p>A unified EMR and EHR system designed to support secure data management, telehealth consultations, clinical documentation, billing, and operational visibility-built for modern healthcare workflows </p>
                        </div>
                    </div>
                </div>
            
                <div class="row gap-y">
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/stronger-data-security.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold fs-20">Better Data Security</h5>
                            <p class="mb-0">Our cloud-based EMR application leverages blockchain technology to safeguard electronic medical records (EMRs) and electronic health records (EHRs), guaranteeing data integrity and confidentiality. Providers maintain full control over identities without third-party authentication—enabling HIPAA compliance and improved healthcare IT security.</p>
                           </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/efficient-online.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold fs-20">Effective Online Patient Portal</h5>
                            <p class="mb-0">With our web-based EMR system, patients can easily control their electronic healthcare records. Some of the features include automated reminders for appointments by email/SMS, direct access to lab results, review of medical documents, and the facility to add prescribed drugs to their Telehealth cart for easy ordering—perfect for telemedicine EMR requirements.</p>
                           </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/payer-provider.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold fs-20">Payer & Provider Collaboration</h5>
                            <p class="mb-0">Akrahealth's SaaS EMR solution simplifies medical billing software operations by enhancing payer-provider collaboration. Take advantage of seamless claim tracking, payment processing, and quicker reimbursements—all with a lighter administrative burden for healthcare EMR systems.</p>
                           </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                           <div class="w-20">
                              <img class="img-fluid" src="/images/svg/integrated-services.svg" />
                           </div>
                           <div class="w-80">
                            <h5 class="bold fs-20">Integrated Healthcare Solutions</h5>
                            <p class="mb-0">Our EMR software is integrated with CRM systems, mHealth apps, marketing and reputation management platforms, and pharmacy systems. We also enable interoperability with leading electronic health records software and EMR systems—offering a fully connected healthcare EMR experience to clinics and practices of every size.</p>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Integrative EMR Solutions for Smarter Healthcare</h2>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/e-prescription-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">e-Prescription</h4>
                        <p class="description">Akrahealth's EMR software allows quick and safe e-prescribing (eRx), minimizing medication errors and optimizing workflow efficiency. Prescriptions are transmitted directly to pharmacies using our cloud-based EMR platform, facilitating smooth medication management.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/orders-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Orders Management</h4>
                        <p class="description">Our web-based EMR software allows doctors to electronically record and track lab tests, imaging, and orders for treatment. The feature serves healthcare EMR systems by making real-time updates and tracking easier to enhance delivery of care.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/payors.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Payor Management</h4>
                        <p class="description">Streamline billing and insurance claims with the top EMR software from Akrahealth. Our electronic health records software reduces claims processing time and optimizes revenue cycle management (RCM), enhancing the financial performance of practices of every size.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/schedule-setup.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Appointment Scheduling</h4>
                        <p class="description">Through our cost-affordable EMR for small practice and large providers, patients have the capability of booking and maintaining appointments online. The above healthcare application functionality enhances efficiency during scheduling as well as patient participation throughout the continuum of care.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-md-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/notification-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Alerts & Notifications</h4>
                        <p class="description">Integrated automated reminders and alerts for e-prescriptions, lab results, and billing notifications are part of our EMR software systems. These assist care teams in staying on top of work and providing timely care within a secure electronic health record system.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/messaging.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Secure Messaging</h4>
                        <p class="description">Our electronic medical records system features an encrypted, HIPAA-compliant messaging module allowing secure provider-to-patient communications. Inbox integration, drafts, and replies in the EMR software minimize external tool reliance for messaging.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/patient-portal.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Patient Portal</h4>
                        <p class="description">Take control and make patients our healthcare electronic medical records by giving them access to a secure patient portal with medical histories, test results, and appointment reminders—all from within a single health care EMR system that builds transparency and patient trust.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/document-management.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Document Management</h4>
                        <p class="description">Store, upload, and manage medical records such as treatment plans and clinical notes. Our electronic health record system provides safe document handling and rapid retrieval between devices, facilitating virtual healthcare services.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/medical-templates.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Custom Medical Templates</h4>
                        <p class="description">Physicians can construct clinical templates based on their specialty with our EMR software, which is customizable. From pediatrics to psychiatry, the templates decrease documentation time and enhance care consistency on leading EMR systems.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/lab-integration-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Lab Integration & Diagnostic Reporting</h4>
                        <p class="description">Smoothly integrate diagnostic and lab data into our electronic health records software. Test results are available to physicians in real-time, improving care coordination and reducing manual entry in our EMR for small medical practices.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/result.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Results Management</h4>
                        <p class="description">Remotely access and manage lab results using our EMR for small practices, built for agility and precision. Physicians can view findings within the system, enhancing workflows and patient outcomes.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/referral.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Referrals Management</h4>
                        <p class="description">Our referral order functionality allows the electronic creation and tracking of referrals between providers. Akrahealth's electronic healthcare records solution makes care transitions more seamless in leading EMR systems.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


        <section class="section">
            <div class="container bring-to-front con-box pt-0 pt-lg-5">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-lg-6 position-relative">
                        <div class="device-twin m-auto align-items-center">
                            <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
                                <div class="screen">
                                    <img src="/images/EMR-25.jfif" alt="emr-twentyfive" />                           
                                </div>
                                <span class="button"></span>
                            </div>

                            <div class="iphone-x light front me-0">
                                <div class="screen shadow-box">
                                <img src="/images/EMR-1.webp" alt="emr-one" />
                                </div>
                                <div class="notch"></div>
                            </div>
                            </div>
                    </div>
                    <div class="col-12 col-lg-5 mx-auto">
                        <h2 class="bold fs-24">How Our EMR Platform Empowers Users</h2>
                        <p>Our electronic medical records software for affordable increases ease of access with innovative Telemedicine EMR and Telehealth, providing smooth patient management and connectivity. With our web-based EMR system, our users can:</p>
                        <ul class="main-list mt-3">
                          <li>Consult healthcare professionals remotely through secure audio/video calls using our telemedicine EMR software, optimized for innovative healthcare apps and virtual care.</li>
                          <li>Access e-prescriptions and electronic medical records anywhere, anytime with our cloud-based EMR software — a top EMR solution for medical professionals.</li>
                          <li>Access e-prescriptions and electronic medical records anywhere, anytime with our cloud-based EMR software — a top EMR solution for medical professionals.</li>
                          <li>Securely store and share medical records with clinicians through our electronic health record system, improving healthcare interoperability and coordinated care.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>    

        <!----FAQS-->
        <section class="section edge bottom-right">
            <div class="container con-box">
                <div class="row">
                    <div class="col-md-4">
                      <div class="mb-4 text-center text-md-start">
                        <h2 class="fs-20"> <span class="bold"> Got Questions?</span></h2>
                        <p class="font-size-18">Thinking about how our EMR solution can revolutionize your healthcare business? Interested in the top advantages of our electronic medical records system?</p>
                        <p class="font-size-18">Discover answers to the most frequently asked questions from healthcare practitioners like you!</p>
                      </div>
                    </div>

                    <div class="col-md-8">
                        <div class="accordion accordion-clean" id="faqs-accordion">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title" data-toggle="collapse" data-target="#v1-q0">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        Why Choose Akrahealth's Affordable EMR Software?
                                    </div>
                                </div>
                                <div id="v1-q0" class="collapse show" data-parent="#faqs-accordion">
                                    <div class="card-body">
                                        <p>Akrahealth's complimentary EMR software comes with editable templates, HIPAA-compliant cloud-based electronic medical records, and web-based accessibility from any computer. Created with small practices and telemedicine doctors in mind, our electronic healthcare records software is easy to use, low in cost, and designed to improve clinical workflows.</p>
                                        <p>With no up-front fees, it's a clever, low-cost solution for doctors, clinics, and health professionals. White-label EMR systems are available too, which allow you to brand the EMR platform to provide a bespoke patient experience.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title collapsed question collapsed" data-toggle="collapse"
                                        data-target="#v1-q1">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        What's the Difference Between Practice Management Software and an EMR System?
                                    </div>
                                </div>

                                <div id="v1-q1" class="collapse" data-parent="#faqs-accordion">
                                <div class="card-body">
                                    <p>Practice Management Software manages admin functions such as scheduling, billing, and simple documentation. It might have some clinical functionality, but it's not as robust as a full electronic medical record system.</p>
                                    <p>An EMR such as Akrahealth's is designed around meticulous clinical information—diagnoses, treatments, medications, lab work—enabling improved interoperability and coordination. Our web-based EMR allows safe access to electronic health records at any time, from anywhere.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title collapsed question collapsed" data-toggle="collapse"
                                        data-target="#v1-q2">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        How to Choose the Best EMR for Small Practices?
                                    </div>
                                </div>
                                <div id="v1-q2" class="collapse" data-parent="#faqs-accordion">
                                    <div class="card-body">
                                        <p class="bold mb-2">When choosing the best EMR for small practices, remember these:</p>
                                        <ul class="main-list">
                                            <li>Affordable EMR – Select affordable EMR for small practice with no additional fees</li>
                                            <li>Simple interface – For physicians, staff, and virtual assistants</li>
                                            <li>Customization – Utilize custom EMR templates and white-label patient portals</li>
                                            <li>Integration-ready – Make sure it is compatible with telemedicine EMR, billing software, and e-prescriptions</li>
                                        </ul>
                                        <p>Visit our affordable  EMR Demo to see why we're one of the best EMR systems for solo practitioners and small clinics.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title collapsed question collapsed" data-toggle="collapse"
                                        data-target="#v1-q3">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        Do You Have a Patient Portal?
                                    </div>
                                </div>

                                <div id="v1-q3" class="collapse" data-parent="#faqs-accordion">
                                    <div class="card-body">
                                        Yes! Our patient portal is white-label, which means patients can view their electronic medical records, prescriptions, and appointments securely—enabling them to manage their own healthcare. It's one of the highlights in our healthcare EMR systems.
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title  collapsed question collapsed" data-toggle="collapse"
                                        data-target="#v1-q4">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        Is Practice Group Configuration Supportable for Physicians?
                                    </div>
                                </div>

                                <div id="v1-q4" class="collapse " data-parent="#faqs-accordion">
                                    <div class="card-body">
                                        Yes. Akrahealth supports group practice EMR setup, allowing shared access to patient information, secure collaboration, and coordinated care by teams. Great for family practice, clinics, and multi-specialty groups.
                                    </div>
                                </div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="card-title collapsed" data-toggle="collapse" data-target="#v1-q5">
                                        <i class="fas fa-angle-right angle float-end"></i>
                                        Does the EMR App Offer Live Chat Support?
                                    </div>
                                </div>
                                <div id="v1-q5" class="collapse " data-parent="#faqs-accordion">
                                    <div class="card-body">
                                        Yes! Our health app provides 24/7 live chat support, facilitating doctors and patients to communicate effortlessly. Enhance engagement and satisfaction with integrated messaging within your EMR software system.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    
    </AppLayout>
    <EMRModal />
    <WatchDemo/>
</template>
