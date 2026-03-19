<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import IconboxCard from "@/Partials/IconboxCard.vue";
import ListItems from '@/Partials/IconboxCard.vue';
import Cta2 from '@/Partials/Cta2.vue'

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Clinical Management" meta-title="Clinical Management Software for Clinics & Physicians | Akrahealth"
    description="Explore Akrahealth’s AI-powered clinical management system built for small practices, telehealth providers, and specialty clinics. Streamline patient care, records, and workflows.">
    <HeroSection2
    title="Simplify Clinical Management with AI-Powered EHR" 
    description="Streamline workflows, improve patient care, and maximize medical records with our intelligent, automated healthcare IT solutions."
    >
    <template v-slot:button>
      <button data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Get a Demo</button> 
    </template>
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Who We Are</h2>
                <p class="text-center">
                  Akrahealth's Clinical Management System streamlines patient care, workflow, and administrative hassles. Instant access to patient information, decision support, and efficient documentation with our solution helps achieve smoother operations. Our system is tailored to specialties such as cardiology, dermatology, neurology, and primary care to boost productivity and improve care quality. From the best EMR software to top-notch EHR systems, to the finest practice management software, we've got you covered. Our health care apps and health care applications are designed to enhance patient management systems, with ease of integration into electronic medical records and electronic health records.
              </p>
            </div>
            <IconboxCard :items="[
                { title: 'Enhance Care Coordination', description: 'Facilitate communication among healthcare professionals and departments with seamless access to patient history, treatment plans, and lab results through our state-of-the-art electronic medical records software and digital medical records software.', icon: '/images/svg/improve-care-coordination.svg'},
                { title: 'Evidence-Based Decision Support', description: 'Leverage AI-based medical apps and health apps that aid practitioners with real-time, precise, and evidence-supported clinical choices. Our applications integrate directly with EMR platforms and EHR platforms, reinforcing decision-making throughout.', icon: '/images/svg/evidence-based-decision-support.svg'},
                { title: 'Real-Time Collaboration', description: 'Facilitate real-time collaboration across care teams on secure, cloud-based platforms to enhance outcomes with more rapid sharing of information and enhanced patient management systems.', icon: '/images/svg/real-time-collaboration.svg' },
                { title: 'Personalized Patient Care', description: 'Tailor clinical processes to each patient\'s specific needs with our comprehensive electronic health records software and medical EMR software, allowing for tailored treatment paths and better quality care.', icon: '/images/svg/personalized-patient-care.svg'},
                { title: 'Flexible Enough to Support All Clinical Environments', description: 'From small practices to large, multi-specialty hospitals, our clinical software scales. Whether you need the top EMR for small practice or enterprise-level EHR software, Akrahealth fits your setting with robust, intuitive solutions.', icon: '/images/svg/scalable-for-all-clinical-settings.svg'}
            ]"
             />
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
               <h4 class="bold fs-20">Clinical Decision Support</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">AI-Powered Diagnosis Assistance:</span> Augment clinical decision-making with AI-based tools that help diagnose conditions, interpret lab results, and suggest treatment plans — all part of your EHR healthcare platform.</li>
                <li><span class="bold">Clinical Alerts & Notifications:</span> Real-time notifications for abnormal test results, critical conditions, and treatment progress updates to facilitate timely intervention.</li>
               </ul>
            </div>
            <div class="">
               <h4 class="bold fs-20">Patient Care & Treatment Management</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">Personalized Care Pathways:</span> Create individualized treatment plans for patients based on their clinical information to provide optimized, patient-centered care through a coordinated electronic healthcare record system.</li>
                <li><span class="bold">Multi-Disciplinary Collaboration:</span> Ensure care coordination between various specialists, providing complete care to complex-medical-condition patients.</li>
               </ul>
            </div>
            <div class="">
               <h4 class="bold fs-20">Clinical Data Integration</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">Unified Patient Records:</span> Combine all clinical information, such as lab test results, imaging studies, and historical information, into a unified, accessible system for each patient using advanced electronic medical records technology.</li>
                <li><span class="bold">Data Interoperability:</span> Integrate clinical information from diverse sources, facilitating seamless care transitions and better collaboration among providers with the most effective EMR systems for maximum data sharing.</li>
               </ul>
            </div>
          </div>
          <div class="col-md-6">
             <div class="">
               <h4 class="bold fs-20">Clinical Documentation Automation</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">Smart Note Taking:</span> Automatically create structured clinical notes from patient encounters, decreasing documentation time and increasing accuracy with electronic medical records in healthcare.</li>
                <li><span class="bold">Voice-to-Text Documentation:</span> Utilize speech recognition technology for fast and accurate clinical documentation, reducing clinician burnout.</li>
               </ul>
             </div>
             <div class="">
                <h4 class="bold fs-20">Clinical Reporting & Analytics</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">Clinical Outcomes Tracking:</span> Track patient improvement, treatment effectiveness, and long-term outcomes using sophisticated reporting capabilities, increasing the value of your clinical software solutions and healthcare apps.</li>
                <li><span class="bold">Predictive Analytics:</span> Apply predictive models to uncover possible health risks, forecast patient outcomes, maximize resource allocation, leveraging the integration with EMR software systems.</li>
               </ul>
             </div>
             <div class="">
                <h4 class="bold fs-20">Telehealth & Remote Care</h4>
               <ul class="main-list mt-3">
                <li><span class="bold">Virtual Consultations:</span> Allow clinicians to conduct remote consultations, maintaining care continuity for patients who cannot visit in person, all on the best telemedicine EMR platforms.</li>
                <li><span class="bold">Remote Patient Monitoring:</span> Track patient health metrics in real-time using wearable devices, enabling clinicians to modify treatment plans ahead of time utilizing the most advanced healthcare software.</li>
               </ul>
             </div>
          </div>

        </div>
      </div>
    </section>

     <section class="section">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
            <div class="mb-3">
              <h2 class="bold fs-22">Smooth Integration with Your Own Systems</h2>
              <p>Akrahealth's Clinical Management System seamlessly integrates with leading EHR systems such as Epic, Cerner, and Allscripts, providing real-time access to patient information. It integrates with electronic medical records software, EMR systems, healthcare IT services, mobile health services, and online health services to provide a smooth, efficient workflow. Regardless of whether you're utilizing the best EMR for small practice, telemedicine EMR, or sophisticated electronic medical record systems, our solution accommodates seamless, scalable integration.</p>
            </div>
          </div>
          <div class="col-md-6 position-relative">
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

       <!-- Call to action section starts here -->
       <Cta 
       title="Who Uses Akrahealth Clinical Management?"
       >
       <template v-slot:button>
        <ul class="main-list mt-3">
          <li><b>Small & Solo Practices</b></li>
          <li><b>Psychiatry Clinics</b></li>
          <li><b>Urgent Care Clinics</b></li>
          <li><b>Telemedicine Startups</b></li>
          <li><b>Women’s Health & Pediatrics</b></li>
          <li><b>Physical Therapy Centers</b></li>
         </ul>
      </template>
     </Cta>
   <!-- Call to action section ends here -->

  <!-- Key Features section starts here -->
    <section class="section path-success">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 justify-content-center">
          <div class="col-sm-12 col-md-8">
          <div class="section-title">
            <h2 class="bold">Industry Security & Compliance</h2>
            <p>With Akrahealth, you know that data security for the patient is a priority in healthcare clinical practice. Our Clinical Management System follows industry-high standards for making your data both secure and compliant.</p>
          </div>
          </div>
          </div>
            <ListItems :items="[
                { title: 'HIPAA Compliant', 
                  description: 'HIPAA Compliant: Our system meets all of HIPAA\'s Health Insurance Portability and Accountability Act requirements so you can rest assured that your patients\' data are encrypted and kept safe.', 
                  icon: '/images/svg/hipaa-compliant.svg' 
                },
                { 
                  title: 'FHIR Compliant', 
                  description: 'Enables hassle-free data sharing between healthcare systems and guarantees clinical data interoperability, essential to utilize healthcare EMR systems.', 
                  icon: '/images/svg/fhir-compliant.svg' 
                },
                { 
                  title: 'GDPR Compliant', 
                  description: 'In practices located within the European Union, we abide by GDPR rules to protect patient privacy.', 
                  icon: '/images/svg/gdpr-compliant.svg' 
                },
                { 
                  title: '24/7 Security Monitoring', 
                  description: 'Around-the-clock monitoring of system activity ensures that there is no unauthorized access and data is secured.', 
                  icon: '/images/svg/24-7-security-monitoring.svg' 
                }
              ]" />
          </div>
    </section>


      <!-- Use Cases & Success Stories __Section starts here -->
      <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
         <div class="section-title">
                <h2>Customer Case Study: Clinical Care Optimization</h2>
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
                  <li><span class="bold">Delayed Clinical Decision Making:</span> Restricted access to current patient information and absence of clinical decision support tools slowed down timely and correct decisions, affecting clinical outcomes.</li>
                  <li><span class="bold">Inefficient Care Coordination:</span> Disjointed patient records and uncoordinated communication between specialists resulted in uneven care delivery. Implementation of electronic medical records software would solve this problem by aggregating patient information.</li>
                  <li><span class="bold">Documentation Burden on Clinicians:</span> Ineffective clinical note-taking and manual data entry led to clinician burnout and less time spent with patients. This can be addressed by implementing electronic health records software and intelligent note-taking tools to decrease documentation time.</li>
                  <li><span class="bold">Limited Remote Patient Monitoring:</span> The absence of strong remote care solutions hindered monitoring patients beyond the clinic, particularly in chronic disease management. Remote patient monitoring and telemedicine EMR solutions would facilitate care continuity and real-time health monitoring.</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h4 class="bold fs-20">Solutions:</h4>
                <ul class="main-list mt-3">
                  <li><span class="bold">AI-Directed Decision Support:</span> Integrated clinical decision support mechanisms enabled doctors to make quicker and more precise diagnosis and treatment choices with sophisticated EMR systems.</li>
                  <li><span class="bold">Unified Data Handling:</span> A unifying system provided for effortless fusion of all patients' records to facilitate improved coordination of specialties, irrespective of whether one had the best EMR available for small medical practices or hospitals.</li>
                  <li><span class="bold">Automated Documentation:</span> Artificial intelligence-powered note-taking minimized manual effort, enabling clinicians to devote more time to patient interaction and care, enhancing clinical software usability.</li>
                  <li><span class="bold">Remote Monitoring Integration:</span> Remote monitoring features were integrated into the system, enabling clinicians to monitor patients' health parameters and make real-time changes to care plans.</li>
                </ul>
              </div>
              <div class="col-md-4">
                <h4 class="bold fs-20">Results Achieved:</h4>
                <ul class="main-list mt-3">
                  <li><span class="bold">60% Quicker Decision-Making:</span> With AI-driven medical scribe integration, clinicians could make quicker, more precise diagnoses and treatment decisions with the help of clinical decision support tools.</li>
                  <li><span class="bold">40% Care Coordination Improvement:</span> Centralized patient information and enhanced communication resulted in enhanced multidisciplinary care and patient outcomes with electronic health records software and EMR systems.</li>
                  <li><span class="bold">50% Documentation Time Reduction:</span> Automated documentation software released considerable time for clinicians, improved job satisfaction, and decreased burnout through reduced manual entry of data with electronic medical records.</li>
                  <li><span class="bold">30% Better Chronic Disease Management:</span> Remote monitoring enabled more proactive management, resulting in improved patient management and fewer hospitalizations with telemedicine EMR solutions.</li>
                </ul>
              </div>
            </div>
        </div>
      </div>
    </section>

    <!-- <Cta2 
    title="Struggling with Clinical Operations?" 
    description="Enhance patient care, optimize processes, and empower your team with AI-driven clinical management. Experience seamless data integration, smart decision support, and greater efficiency—all with the industry's highest-rated EHR and EMR solutions for telehealth and small practices."/> -->

  </AppLayout>
</template>
