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
import IconboxCard from '@/Partials/IconboxCard.vue';

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Practice Management" meta-title="Best Practice Management Software for Clinics & Small Practices – AkraHealth"
    description="Discover AkraHealth’s cloud-based practice management system – designed for appointment scheduling, billing, EHR, and telemedicine in small clinics and urgent care.">
    <HeroSection2
    title="Simplify Your Practice Management" 
    description="Simplify daily routines and enhance patient care with the finest practice management software and leading medical practice management solutions."
    >
    <!-- <template v-slot:button>
      <a href="#" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Try it free</a> 
    </template> -->
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Who We Are</h2>
                <p class="text-center">
                  Akrahealth provides end-to-end practice management solutions to make healthcare processes easy, automate labor-intensive work, and increase precision. Our software comes integrated with EMR software, EHR software, and electronic medical record systems to enable efficient scheduling of patients, billing, and compliance monitoring for providers. In addition to that, we also provide tailored EMR solutions for mental health, urgent care, pediatrics, ophthalmology, and plastic surgery so that there's efficient management in all specialties.
              </p>
            </div>
            <IconboxCard :items="[
              { title: 'Streamline Operations', description:'Automate routine administrative tasks such as scheduling, billing, and insurance verification to save time and resources using our practice management system.', icon: '/images/svg/streamline-operations.svg'},
              { title: 'Seamless Integration', description:'Can easily be integrated with EHR systems, billing applications, and other third-party software to provide a seamless, streamlined process for your practice.', icon: '/images/svg/seamless-integration.svg' },
              { title: 'Cloud-Based & Secure', description: 'With HIPAA-compliant, real-time access to data, our cloud-based solution maintains your practice\'s data in a secure and accessible location anywhere in the world.', icon: '/images/svg/cloud-based-secure-icon.svg' },
              { title: 'Scalable & Customizable', description:'Designed for solo practices, specialty clinics, or multi-site healthcare providers, our system expands with your expanding practice through customizable practice management solutions.', icon: '/images/svg/integrated-services.svg' } 
              ]" />
               
            </div>
        </section>

     <section class="section">
      <div class="container">
        <div class="row">
          <div class="section-title">
            <h2>Key Features</h2>
          </div>
        </div>
        <div class="row align-items-center gap-y">
          <div class="col-md-6">
            <div class="">
               <h4 class="bold fs-20">Scheduling & Appointment Management</h4>
               <ul class="main-list mt-3">
                 <li>Smart Scheduling: Optimally automate and manage your appointments, avoid overbooking and no-shows with our practice management system.</li>
                <li>Patient Self-Scheduling: Allow patients to easily schedule or reschedule their appointments online, offering convenience and reducing administrative tasks.</li>
               </ul>
            </div>
            <div class="">
               <h4 class="bold fs-20">Billing & Insurance Management</h4>
               <ul class="main-list mt-3">
                 <li>Automated Billing: Streamline insurance claim submission and payment tracking to ensure timely reimbursement and reduce human error with our medical billing solutions.
                </li>
                <li>Insurance Verification: Validate insurance information in real-time to ensure accuracy and avoid denied claims, enhancing revenue cycle management.
                </li>
               </ul>
            </div>
          </div>
          <div class="col-md-6">
             <div>
               <h4 class="bold fs-20 mt-4">Patient Engagement Tools</h4>
               <ul class="main-list mt-3">
                  <li>Patient Portal: Provide patients with a simple, secure portal to access medical records, send messages to physicians, and schedule appointments, all within your EHR system.
                  </li>
                  <li>Text & Email Reminders: Prevent no-shows with automated email and text reminders about upcoming appointments, improving patient engagement and decreasing administrative burden.
                  </li>
               </ul>
             </div>
             <div class="">
                <h4 class="bold fs-20">Analytics & Reporting
                </h4>
               <ul class="main-list mt-3">
                  <li>Comprehensive Reporting: Get a sense of how your practice is performing with detailed reports on revenue, patient outcomes, and operational efficiency, offering rich insights into your practice management system.</li>
                  <li>Real-Time Dashboards: Monitor key indicators such as revenue cycle, patient visits, and staff performance through a simple-to-use dashboard, offering improved control and efficiency.
                  </li>
               </ul>
             </div>
          </div>

        </div>
      </div>
    </section>


  <!-- Key Features section starts here -->
    <section class="section">
      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 col-lg-6 me-lg-auto position-relative order-md-2">
                <img class="img-fluid" src="/images/webpages/appss_5.webp" alt="emr-one" />
          </div>
         
          <div class="col-md-6 text-center text-md-start">
            <div class="mb-3">
              <h2 class="bold fs-22">Integration with Current Tools</h2>
              <p>Simply integrate Akrahealth's Practice Management System with your current tools. We integrate nicely with EMR/EHR tools such as DrChrono, MirthConnect, and OpenEMR for seamless data sharing. With live synchronization to your medical billing solution and telemedicine tools, you have a unified system designed to your practice's needs. Our solid APIs and pre-configured integrations have setup happening in a flash without hassle.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

       <!-- Call to action section starts here -->
       <Cta 
       title="Why Choose Akrahealth?"
       >
       <template v-slot:button>
        <ul class="main-list mt-3">
          <li><b>Free EMR + Practice Management Combo</b></li>
          <li><b>Fast Onboarding for Teams</b></li>
          <li><b>Customizable Workflow Templates</b></li>
          <li><b>HIPAA-Compliant & Secure Cloud Infrastructure</b></li>
         </ul>
      </template>
     </Cta>
   <!-- Call to action section ends here -->

     <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 me-lg-auto position-relative">
                  <img class="img-fluid" src="/images/webpages/appss_5.webp" alt="emr-one" />
              </div>
              <div class="col-md-6">
                <div class="mb-4">
                <h2 class="bold fs-22">Industry Security & Compliance</h2>
                <p>At Akrahealth, we value the security and compliance of your patient data, with our practice management system following international regulatory norms. We develop our system with cutting-edge security measures to safeguard sensitive information and ensure healthcare environments remain compliant.</p>
              </div>
              <ListItems :items="[
                { title: 'HIPAA Compliant', description: 'Our practice management software employs end-to-end encryption and secure data storage to guard patient records and remain fully HIPAA compliant.', icon: 'fa-check' },
                { title: 'FHIR Compliant', description: 'We are FHIR standards compliant to enable data exchange and integration with EHR systems and healthcare IT services with ease.', icon: 'fa-check'},
                { title: 'GDPR Compliant', description: 'We adhere to GDPR procedures to ensure patient data security, particularly for clinics in the EU.', icon: 'fa-check'},
                { title: '24/7 Monitoring', description: 'Our system provides constant monitoring and security updates, so that your healthcare services or medical group remain secure at all times.', icon: 'fa-check'}
               ]" />
              </div>
            </div>
      </div>
    </section>

     <!-- Use Cases & Success Stories __Section starts here -->
     <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
         <div class="section-title">
                <h2>Customer Case Study: Dental Practice Management</h2>
            </div>
         </div>
         <div class="gap-y align-items-center">
            <div class="col-md-6 col-lg-12 position-relative">
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
            <div class="row mt-4">
              <div class="col-md-4">
                <h4 class="bold fs-20">Challenges:</h4>
                <ul class="main-list mt-3">
                  <li><span class="bold">Inefficient Patient Record Management:</span>The clinic used physical files rather than a patient management system, which made it hard to access and update patient records in an efficient manner.</li>
                  <li><span class="bold">Manual Scheduling & Appointments:</span> With no practice management system, appointments were scheduled manually, resulting in double bookings, missed visits, and inefficient use of time.</li>
                  <li><span class="bold">Billing and Insurance Claims Issues:</span> Lack of an integrated medical billing system for healthcare resulted in delays and errors in billing, with continued difficulty in handling insurance verification and monitoring patient payments, affecting overall revenue cycle management.</li>
                  <li><span class="bold">Restricted Treatment Planning and Communication:</span> In the absence of a clinical software, treatment plans were not consistent, leading to ineffective communication and obscure care provision between dental professionals and patients.</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h4 class="bold fs-20">Solutions:</h4>
                <ul class="main-list mt-3">
                  <li><span class="bold">Electronic Patient Records:</span> The system digitized patient records through an advanced patient management system, making it easy to access and update, enhancing organization, and minimizing errors.</li>
                  <li><span class="bold">Automated Scheduling:</span> With automated scheduling capabilities in our practice management software, instant booking was made possible—avoiding appointment conflicts and enhancing operational efficiency.</li>
                  <li><span class="bold">Integrated Billing and Insurance Claims:</span> The practice management system had medical billing software for healthcare and real-time insurance verification, facilitating automated claim submission and accelerated payment cycles—simplifying revenue cycle management.</li>
                  <li><span class="bold">Comprehensive Treatment Planning:</span> The system provided treatment planning tools within clinical software, enhancing the clarity of communication between dentists and patients while facilitating smooth delivery of care.</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h4 class="bold fs-20">Results Achieved:</h4>
                <ul class="main-list mt-3">
                  <li><span class="bold">50% Reduction in Administrative Time:</span> Simplified procedures with a practice management system reduced manual labor, enabling staff to devote more time to patient care.</li>
                  <li><span class="bold">30% Reduction in Payment Processing Time:</span> Automated workflows through integrated medical billing solutions and quicker revenue cycle management enhanced cash flow and claim turnaround time.</li>
                  <li><span class="bold">40% Patient Satisfaction Boost:</span> Enhanced scheduling and communication through our patient management system resulted in quicker treatments and a smoother patient experience.</li>
                  <li><span class="bold">25% Staff Productivity Increase:</span> Routine tasks automated through our practice management software minimized burnout and improved operational effectiveness.</li>
                </ul>
              </div>
            </div>                          
         </div>
      </div>
    </section>
    
    <!-- <Cta2 
    title="Done with Complicated Practice Management?" 
    description="Streamline your workflow with Akrahealth's simple-to-use EMR software, EHR software, and telemedicine solutions—all in one secure practice management system."/>    -->

  </AppLayout>
</template>
