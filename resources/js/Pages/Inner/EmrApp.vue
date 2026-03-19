<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue'
import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';
import "swiper/css";
import "swiper/css/bundle";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="EMR App"  meta-title="EMR App" 
    description="AkraHealth’s EMR App offers video consultations, secure e-prescriptions, and full patient record access. Free for doctors, clinics, and virtual assistants.">
   
    <HeroSection
      title="Transform Patient Care with the Akrahealth EMR App"
      description="Manage appointments, consult via video, and access medical records—all in one secure platform."
      >
    
     <template v-slot:images>
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
     </template>
     <template v-slot:button>
        <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-primary btn-lg btn-rounded px-4 mt-3">Sign up for FREE</button>
     </template>
      </HeroSection>
    
    <section class="quick-start-your-business mb-lg-n3 ">
      <div class="container con-box mb--4">
        <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10">
          <p class="text-center">
            <b>AkraHealth’s mobile EMR app</b> complements our <b>cloud-based electronic medical records (EMR) system</b>, giving providers seamless access to <b>telehealth appointments, virtual doctor consultations</b>, and <b>secure e-prescriptions</b>. Designed for speed and security, our app eliminates long clinic wait times by offering instant video visits. With a <b>HIPAA-compliant architecture</b>, it safeguards sensitive <b>patient medical records</b> and offers real-time access to<b> digital health history</b>—ensuring high-quality, remote patient care.
          </p>
        </div>
        <div class="col-12 mt-5">
          <div class="screenshots coverflow">
            <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
          :centeredSlides="true" :spaceBetween="10" :pagination="{
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
            }" :modules="[Autoplay,Navigation,Virtual]" navigation :loop="true" :autoplay="{ delay: 2500 }">
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-1.webp" style="width: 250.6px" alt="emr-one.png" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-2.jfif" style="width: 250.6px" alt="emr-two.jfif" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-15.jfif" style="width: 250.6px" alt="emr-fifteen.jfif" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-14.jfif" style="width: 250.6px" alt="emr-fourteen.jfif" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-17.jfif" style="width: 250.6px" alt="emr-seventeen.jfif" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-16.jfif" style="width: 250.6px" alt="emr-sixteen.jfif" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-8.jfif" style="width: 250.6px" alt="emr-eight.jfif" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-9.webp" style="width: 250.6px" alt="emr-nine.png" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-10.jfif" style="width: 250.6px; margin-right: 20px"
              alt="emr-ten.jfif" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-18.webp" style="width: 250.6px; margin-right: 20px"
              alt="emr-eighteen.jfif" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/EMR-19.webp" style="width: 250.6px; margin-right: 20px"
              alt="emr-nineteen.png" />
          </SwiperSlide>
        </Swiper>
          </div>
        </div>
       </div>
      </div>
    </section>
   
    <section class="section section">
      <div class="container con-box">
        <div class="row justify-content-center">
              <div class="col-sm-10">
                <div class="section-title">
                    <h2>We ensure efficient healthcare interoperability through online
                      consultation with top doctors</h2>
                </div>
             </div>
            </div>

        <ul class="nav nav-tabs nav-outlined nav-rounded justify-content-around justify-content-md-center mb-5 custom_tabs" role="tablist">
            <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#schedule-an-appointment">Schedule an Appointment</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#consult-doctors-online">Consult Doctors Online</a>
            </li>
           <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#live-chat-with-octors">Live Chat with Doctors</a>
           </li>
           <li class="nav-item">
             <a class="nav-link" data-toggle="tab" href="#patient-health-record">Patient Health Record</a>
           </li>
        </ul>

        <div class="tab-content pt-4">
          <div class="tab-pane active" id="schedule-an-appointment">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                  </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/EMR-18.webp" alt="emr-eighteen" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-3.jfif" alt="Emr-Appointment" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <h2 class="bold fs-22">Schedule an Appointment</h2>
                <p>
                  Through our EMR App, users can select their preferred
                  doctor(s)/healthcare provider and book an appointment. The
                  user can select a convenient date & time for an appointment
                  and can re-schedule or cancel an appointment if needed. We
                  take care of Patient Scheduling and Appointment Management for
                  our users. Doctors can view patients’ medical history once the
                  user schedules an appointment and can go through it before the
                  consultation.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="consult-doctors-online">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 order-md-2 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/EMR-14.jfif" alt="emr-fourteen" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-20.webp" alt="emr-twenty" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <h2 class="bold fs-20">Consult Doctors Online</h2>
                <p>
                  Users can view the top doctors on our EMR App and select the
                  relevant healthcare provider or doctor based on various
                  aspects such as health issues/symptoms, specialization,
                  interoperability, availability of timings & charges. Users are
                  capable of consulting doctors within an hour of the
                  appointment scheduled and we ensure our users get access to
                  well-experienced doctors through audio/video calls to get the
                  maximum advantage of online consultation.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="live-chat-with-octors">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/EMR-21.webp" alt="emr-twentyone" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-22.webp" alt="emr-twentytwo" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <h2 class="bold fs-20">Live Chat with Doctors</h2>
                <p>
                  Our EMR App has a unique feature of chatting with the
                  doctor(s) already consulted to ask questions missed during the
                  consultation, review medicines prescribed by the doctor, share
                  lab reports, health updates, health records, feedback & more
                  to get in touch with the doctors directly establishing a
                  strong relationship of a Patient with a doctor through our
                  effective EMR Platform. Our EMR Platform takes care of
                  providing you with the right healthcare assistance through our
                  advanced online healthcare platform features.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="patient-health-record">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 order-md-2 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/EMR-9.webp" alt="emr-nine" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/EMR-16.jfif" alt="emr-three" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="bold fs-20">Patient Health Record</h2>
                <p>
                  Our EMR Platform provides numerous benefits to users in the
                  form of advanced features taking care of all basic needs of a
                  patient’s health. On our EMR App, our users can prepare their
                  medical history comprising their personal health information,
                  past online consultations, and prescribed medicines. This
                  health record can be shared with healthcare providers during
                  consultation on our EMR Platform which is secure and reliable.
                </p>
              </div>
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
          <div class="col-md-6 col-lg-5 ms-lg-auto order-md-2">
            <div class="device-twin align-items-center">
              <div class="mockup absolute right aos-init aos-animate" data-aos=" fade-right ">
                <div class="screen">
                  <img src="/images/EMR-5.jfif" alt="emr-five" />
                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="images/EMR-13.jfif" alt="emr-thirteen" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-activity stroke-primary">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                  </svg>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">
                    View Diet Plans & Medical Symptoms
                  </h5>
                  <p class="m-0">
                    To consult a doctor online, users can select their health
                    symptoms from a range of categories such as Fever, Cold,
                    Stomach Pain, Obesity, Acne, and more to Eye related issues
                    such as redness, watery eyes, etc. Users are also capable of
                    viewing Diet & Nutrition section to view diets helpful to
                    them.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-5">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-activity stroke-primary">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Appointment Record</h5>
                  <p class="m-0">
                    We ensure that users keep a track of their appointments and
                    therefore, our EMR App provides a useful feature for users
                    to have a record of their past and upcoming appointments
                    with doctors. The Appointment Record also specifies the
                    appointments completed, pending, and canceled.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-5">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center me-md-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-activity stroke-primary">
                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Track and Manage Your Appointments with Ease</h5>
                  <p>
                    Our EMR App includes a built-in <b>Appointment Record</b> feature that helps users stay organized by tracking all past and upcoming consultations. Whether appointments are <b>completed, scheduled, or canceled</b>, users can view their full history and plan their healthcare journey more efficiently—all in one place.
                  </p>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
   
       <!-- Call to action section starts here -->
       <Cta 
       title="Looking for a Mobile EMR App Built for Speed & Simplicity?"
       >
       <template v-slot:button>
            <p>Explore a powerful, HIPAA-compliant EMR app designed for virtual consultations, e-prescriptions, and remote patient care—perfect for on-the-go providers. Ideal for:</p>
            <ul class="main-list mt-3">
              <li>Solo Practitioners & Small Clinics</li>
              <li>Telemedicine & Virtual Care Providers</li>
              <li>Mental Health & Psychiatry Specialists</li>
              <li>Urgent Care & Walk-In Clinics</li>
              <li>Mobile Health Teams & Home Visit Services</li>
            </ul>
        </template>
       </Cta>
    <!-- Call to action section ends here -->

    
    <section class="section why-people-love-us">
      <div class="shapes-container overflow-clear">
        <div class="shape shape-circle shape-circle-1">
          <div data-aos="fade-up-left" class="aos-init aos-animate"></div>
        </div>
        <div class="shape shape-circle shape-circle-2">
          <div data-aos="fade-up-right" data-aos-delay="200" class="aos-init"></div>
        </div>
        <div class="shape pattern pattern-dots pattern-dots-1"></div>
      </div>
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">            
              <h2 class="bold fs-22">How is EMR App helpful to users?</h2>
            <p>Our EMR App provides users with unique and useful Telemedicine and Telehealth services. Our EMR App offers:</p>
            <ul class="list-unstyled">
              <li>
                <div class="d-flex align-items-start pb-3">
                  <div class="icon-md bg-primary p-2 rounded-circle center-flex me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="feather feather-arrow-right stroke-contrast">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </div>
                  <div class="flex-fill">
                    <p class="my-0">
                      Consult top doctors online through audio or video calls
                    </p>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex align-items-start pb-3">
                  <div class="icon-md bg-primary p-2 rounded-circle center-flex me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="feather feather-arrow-right stroke-contrast">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </div>
                  <div class="flex-fill">
                    <p class="my-0">
                      Create and share Personal Health Records securely with
                      Healthcare Providers
                    </p>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex align-items-start pb-3">
                  <div class="icon-md bg-primary p-2 rounded-circle center-flex me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="feather feather-arrow-right stroke-contrast">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </div>
                  <div class="flex-fill">
                    <p class="my-0">Flexible Scheduling of Appointments</p>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex align-items-start pb-3">
                  <div class="icon-md bg-primary p-2 rounded-circle center-flex me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="feather feather-arrow-right stroke-contrast">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </div>
                  <div class="flex-fill">
                    <p class="my-0">
                      Live chat with healthcare providers/doctors
                    </p>
                  </div>
                </div>
              </li>
              <li>
                <div class="d-flex align-items-start pb-3">
                  <div class="icon-md bg-primary p-2 rounded-circle center-flex me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="feather feather-arrow-right stroke-contrast">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </div>
                  <div class="flex-fill">
                    <p class="my-0">Upload medicine prescriptions</p>
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <div class="col-md-6 position-relative">
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/EMR-9.webp" alt="emr-nine" />
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
                  <img src="/images/EMR-6.webp" alt="emr-six" />
                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="images/EMR-18.webp" alt="emr-eighteen" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-md-start">
            <h2 class="bold py-4 text-center text-md-start fs-22">Key Features</h2>
            <ListItems :items="[
              { title: 'E-Prescription Management', description: 'Doctors can issue digital prescriptions, which are automatically sent to partnered pharmacies for quick processing, reducing paperwork and errors.', 
                icon: 'fa-check'
              },
              { title: 'Real-Time Prescription Tracking', description: 'Track prescription status from issuance to fulfillment, ensuring patients receive their medications on time.',
                icon: 'fa-check'
               },
              { title: 'Seamless Doctor-Pharmacist Collaboration', description: 'Pharmacists and healthcare providers can communicate securely for clarifications on dosages, substitutions, and patient concerns.', 
                icon: 'fa-check'
              },
              { title: 'Automated Medication Reminders', description: 'Patients receive alerts for refill schedules, enhancing medication adherence and reducing missed doses.', 
                icon: 'fa-check'
              },
              { title: 'Pharmacy Locator & Recommendations', description: 'Doctors can recommend nearby or preferred pharmacies, helping patients access their medications with ease.', 
                icon: 'fa-check'
              },
              { title: 'Regulatory Compliance & Security', description: 'Our Pharmacy App follows strict HIPAA and healthcare industry regulations, ensuring secure handling of prescriptions and patient data.', 
                icon: 'fa-check'
              },
              { title: 'Comprehensive Patient Medication History', description: 'Maintain a detailed log of prescribed medications, helping healthcare providers track treatment progress efficiently.', 
                icon: 'fa-check'
              }

            ]" />
          </div>
        </div>
      </div>
    </section>

    
    <!-- <Cta2 
    title="Register on our EMR/EHR Platform as a Doctor, Virtual Assistant, Pharmacy or Lab and access our outpatient management services for FREE."  /> -->

    
  </AppLayout>
</template>
