<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
 import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from "@/Partials/ListItems.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Dental" meta-title="Dental Practice Management Software | AkraHealth"
    description="Run your dental clinic smoothly with AkraHealth’s EMR—featuring charting, billing, scheduling, and patient communication. Cloud-based. Try it free today!">
    
    <HeroSection
      title="Elevate Your Dental Practice with Expert Management"
      description="From patient records to billing and appointment scheduling, we create a fully customized dental practice management system tailored to your clinic’s needs."
      >
     <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
                <img src="images/webpages/dental.webp" alt="...">
            </div>

            <div class="front iphone-x absolute d-none d-lg-block aos-init aos-animate mt-6 ms-xxl-n5" data-aos="fade-right">
                <div class="screen">
                    <img src="/images/EMR-4.jfif" alt="...">
                </div>
                <div class="notch"></div>
            </div>
        </div>
     </template>
      </HeroSection>

  
    <section class="quick-start-your-business mb-lg-n3 ">
      <div class="container con-box mb--4">
        <div class="col-sm-12 col-lg-12 mx-auto order-md-2">
          <div class="section-title">
            <h2>Dental EMR Software & Practice Management Solutions, Redefined</h2>
            <p class="text-center">
              Akrahealth provides robust cloud-based dental practice management software to automate your clinic's operations and improve patient care. Our single-source solution includes dental EMR, electronic medical charting, medical billing EMR, and inventory management, so your operations are seamless.
            </p>
            <p>We provide top-class dental practice management software with specializations in contemporary clinics and an integration of dental EMR software, patient management software, and billing management systems. Regardless of whether you require dental clinic management software, a complete dental management program, or need to learn the distinction between EMR and EHR, Akrahealth offers expandable, secure tools that match your requirements.
            </p>
            </div>
        </div>
      </div>
    </section>

  <!-- Key Features section starts here -->
    <section class="section path-success">
      <div class="shapes-container mt-3">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="video-container">
             <!-- <iframe class="rounded shadow-lg w-100" src="https://www.youtube.com/embed/M38V00gkDbc?si=ymq4iEhXq36xOMFF" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->
            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <ListItems :items="[
                { title: 'Patient Records Management', description: 'Access secure dental EMR, EMR medical records, and electronic medical charting to quickly make informed decisions.', icon: 'fa-check' },
                { title: 'Billing & Invoicing', description: 'Our comprehensive medical billing EMR and billing management system aid in minimizing errors and speeding up payment cycles.', icon: 'fa-check'},
                { title: 'Treatment Planning', description: 'Create personalized treatment plans with our intelligent dental management software to enhance patient satisfaction.', icon: 'fa-check'},
                { title: 'Inventory Management', description: 'Control supplies in web and iPad using our high-tech dental clinic management software and dental laboratory software for effortless reordering.', icon: 'fa-check'},
                { title: 'Appointment Scheduling & Reminders', description: 'Reduce no-shows with reminders using our dental software and keep the flow efficient through best patient management software.'},
                { title: 'AI Voice Recognition', description: 'Record and transcribe patient talks for precise medical records EMR and treatment notes, seamlessly integrated into your EMR system.'},
                { title: 'Reports & Analytics', description: 'Monitor performance, expenses, and quality of care with built-in insights into the best dental practice management software.'}
               ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

     <!-- Tailor-made solutions to suit your needs in dental practice section starts here -->
     <section class="section path-success bg-light py-4">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 me-lg-auto position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center mw-100">
                  <img class="img-fluid" src="/images/webpages/dental_1.webp" alt="emr-one" />
               <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/webpages/dental_1.webp" alt="emr-one" />
                    </div>
                    <span class="button"></span>
                  </div>

                   <div class="front iphone-x absolute  " data-aos="fade-right">
                <div class="screen">
                    <img src="/images/emr-one.webp" alt="...">
                </div>
                <div class="notch"></div>
            </div>
                </div>
              </div>

              <div class="col-md-5">
                <h2 class="bold mt-4 mt-md-0 fs-22">Custom Dental EMR & Practice Management Software Solutions</h2>
                <p>Tailor-made Solutions: We understand that each clinic is different. Akrahealth provides customizable dental practice management software that can be tailored to meet your clinic's unique requirements—be it custom features, EMR applications, or workflows.</p>
                <ul class="main-list">
                  <li>Scalable Solutions: With our cloud-based dental practice management software, you can scale up or down with ease as your clinic expands, providing ongoing support and flexibility.</li>
                  <li>Seamless Integration: Easily integrate with your current dental EMR systems, dental software, or any other EMR platform for smooth data flow and efficient operation.</li>
                </ul>
                <p>Our flexible dental management program grows with your clinic's needs—staying one step ahead with the best dental software, optimized for EMR medical records and superior care delivery.</p>
              </div>
            </div>
      </div>
    </section>
    <!-- AI-powered EMR and EHR Systems section ends here -->

    <!-- Call to action section starts here -->
    <Cta 
       title="Why AkraHealth for Dentists?"
       >
       <template v-slot:button>
        <p>Emphasize dental-specific workflows. List out pain points and how AkraHealth solves them.</p>
        <ul class="main-list mt-3">
          <li>Built-in<b> dental charting with odontograms</b></li>
          <li>Customizable <b>treatment plans</b></li>
          <li><b>Tooth-specific diagnosis recording</b></li>
          <li>Easy <b>progress notes & SOAP templates</b></li>
         </ul>
      </template>
       </Cta>
   <!-- Call to action section ends here -->

      <!-- Telemedicine & AI-Based Remote Monitoring section starts here -->
      <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
          <div class="section-title">
                <h2>Integrated Dental EMR & IT Solutions for Dental Clinics at Akrahealth</h2>
                <p>Akrahealth offers professional IT solutions for dental clinics to enhance efficiency and streamline patient care. With our extensive knowledge of dental practice management software and allied technologies, we offer bespoke solutions that meet every need of your clinic:</p>
            </div>
        </div>
        <div class="row gap-y align-items-center">
            <div class="col-md-6">
              <ListItems :items="[
                { title: 'System Integration', description: 'Integrate seamlessly with top dental EMR systems and platforms like Epic, eClinicalWorks, and Athena Health, maintaining compatibility with your existing EMR application.', icon: 'fa-check' },
                { title: 'Cloud-based Solutions', description: 'Our cloud-based dental practice software provides scalable and secure access to health records and patient information, facilitating enhanced accessibility and compliance.',  icon: 'fa-check'},
                { title: 'Dental EHR & EMR Systems', description: 'Install specialized dental EMR software and electronic medical record (EMR) systems to streamline patient record management and improve clinical workflows.',  icon: 'fa-check'},
                { title: 'Billing & Patient Engagement', description: 'Our medical billing EMR and patient management software automate your dental care services, reducing errors and improving engagement.', icon: 'fa-check'},
                { title: 'Data Security & Compliance', description: 'With security-laden architecture and HIPAA-compliant cloud storage, Akrahealth provides complete security for your medical records EMR and sensitive data.',  icon: 'fa-check'},
              ]" />

              </div>
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center mw-100">
                  <img class="img-fluid" src="/images/webpages/appss_4.webp" alt="emr-one" />
                </div>
              </div>
            </div>
        </div>
    </section>
    <!-- Telemedicine & AI-Based Remote Monitoring section ends here -->

    <!-- Use Cases & Success Stories __Section starts here -->
    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row justify-content-center">
           <div class="col-sm-12 col-md-8">
           <div class="section-title">
                <h2>Case Study: Revolutionizing Dental Practice Management with Akrahealth's Dental EMR Software</h2>
            </div>
           </div>
         </div>
         <div class="gap-y align-items-center">                           
            <div class="col-sm-12 dental-slider">
              <div class="center-xy op-1">
                <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                  style="width: 600px; height: 600px" data-aos="zoom-in"></div>
              </div>
              <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal pt-0 shadow rounded" :showItems="1"
                  :centeredSlides="true" :spaceBetween="10" :pagination="{
                      clickable: true,
                    }" :breakpoints="{
                  '640': {
                    slidesPerView: 1,
                    spaceBetween: 20,
                  },
                  '768': {
                    slidesPerView: 1,
                    spaceBetween: 40,
                  },
                  '1024': {
                    slidesPerView: 1,
                    spaceBetween: 50,
                  },
                  }" :modules="[Autoplay,Navigation,Virtual]" navigation :loop="true" :autoplay="{ delay: 2500 }">
                <SwiperSlide>
                  <img class="img-responsive" src="/images/webpages/appss_2.webp" alt="emr-two.webp" />
                </SwiperSlide>

                <SwiperSlide>
                  <img class="img-responsive" src="/images/webpages/appss_1.webp" alt="emr-three.webp" />
                </SwiperSlide>               
              </Swiper>              
            </div>
            <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-8">
              <h2 class="bold fs-20">Problem Statement:</h2>
              <ul class="main-list mt-4">
                <li>Poor upkeep of EMR electronic medical record and health records</li>
                <li>Consistent errors and delays in dental medical billing services</li>
                <li>Absence of a systematic approach for treatment planning and inventory management</li>
                <li>Excessive administrative burden deflecting the attention away from providing quality dental care services</li>
              </ul>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-8">
              <h3 class="bold fs-20">Our Solution:</h3>
              <ul class="main-list mt-4">
                <li>Akrahealth provided a completely bespoke cloud based dental practice management software that was specially designed to cater to the clinic's individual requirements:</li>
                <li>Patient Records Management: Provided secure and accessible dental EMR software for effective management of medical records EMR and electronic medical charting</li>
                <li>Billing & Invoicing: Automated medical billing EMR processes, enhancing claim accuracy and integrating optimally with EMR platform</li>
                <!-- <li>Treatment Planning: Provided advanced tools through our dental management program to design patient-specific treatment plans</li>
                <li>Inventory Management: Provided for smart supply tracking with our dental clinic management software and dental lab software</li>
                <li>User-Friendly Interface: Developed an easy-to-use EMR app that provided simplified navigation to staff and improved usability</li> -->
              </ul>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-8">
              <h3 class="bold fs-20">Results Achieved:</h3>
              <ul class="main-list mt-4">
                <li>30% reduction in administrative workload using Akrahealth's dental practice management software, enabling staff to focus more on dental care services.</li>
                <li>Enhanced billing precision with medical billing EMR, resulting in a 20% shorter payment cycle.</li>
                <li>Seamless workflows and customized treatment plans with our dental EMR software and electronic medical charting, increasing patient satisfaction.</li>
                <!-- <li>Streamlined inventory management through our dental clinic management software, helping to avoid stockouts and overstocking.</li> -->
              </ul>
            </div>
            </div>
          </div>
      </div>
    </section>
    
    <!-- <Cta2 
    title="Custom Dental Practice Management" 
    description="Streamline your clinic with cloud-based dental EMR software and medical billing EMR. Improve patient care and manage records effortlessly with our all-in-one dental management software."/> -->
    
  </AppLayout>
</template>
