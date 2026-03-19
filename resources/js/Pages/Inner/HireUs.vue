<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import ListItems from '@/Partials/ListItems.vue';
import IconboxCard from "@/Partials/IconboxCard.vue";
import Cta2 from '@/Partials/Cta2.vue'
import { nextTick, onMounted } from 'vue';

defineProps({
  policy: String,
});

onMounted(() => {
  nextTick(() => {
    if (typeof senderForms !== 'undefined') {
      console.log(typeof senderForms)
      senderForms.render();
    } else {
      window.addEventListener('SenderLoaded', () => {
        if (typeof senderForms !== 'undefined') {
          senderForms.render();
        } else {
          console.error('senderForms is still not defined');
        }
      });
    }
  });
});
</script>

<template>
  <AppLayout  title="Hire EMR Integrators" meta-title="Hire Expert EMR Developers for Custom Solutions"
    description="Build secure, scalable EMR systems with our HIPAA-compliant developers. Get custom integrations and virtual care tools. Talk to our experts today.">
    <HeroSection2
    title="Hire EMR Developers" 
    description="Build Cutting-Edge Healthcare Solutions with AkraHealth"
    >
    <template v-slot:button>
      <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#demo-request-modal" id="emrIntegrators" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Try it free</button> 
    </template>
   </HeroSection2> 
   
   

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Why Choose EMR Developers from AkraHealth?</h2>
                <p>Healthcare requires accuracy, safety, and innovation—abilities that are central to the EMR development services at AkraHealth. Other generic development companies do not; we do not simply develop software; we craft intelligent, adaptable, and interconnected EMR applications based on your specific requirements. Our EMR integration developers specialize in effortless API-driven EMR integration to complement market leaders like Epic, Cerner, NexTech, and DrChrono without ever breaching strict HIPAA and HL7 regulations.</p>
                <p>We exceed code. We build high-performing EMR systems that extend clinical workflows, simplify patient management, and maximize healthcare operations—while providing a seamless and intuitive experience.</p>
                <p>If you desire a future-proof EMR solution, designed for scalability, security, and smooth data flow, AkraHealth is your go-to partner.</p>
            </div>
        </div>
    </section>

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Our EMR Development Services</h2>
            </div>
            <IconboxCard :items="[
                { title: 'Custom EMR Software Development', description: 'We create completely customized EMR systems that fit your healthcare facility\'s specific needs, from appointment scheduling and medical charting to e-prescriptions and patient engagement.', icon: '/images/svg/improve-care-coordination.svg'},
                { title: 'EMR Integration Services', description: 'Our EMR integration experts are well-versed in integrating your current healthcare platforms, third-party apps, and EHR systems such as Epic, Cerner, DrChrono, and others to maintain seamless data flow.', icon: '/images/svg/evidence-based-decision-support.svg'},
                { title: 'API Development for Healthcare Systems', description: 'We create secure and scalable APIs that improve interoperability among various healthcare applications and medical devices.', icon: '/images/svg/real-time-collaboration.svg' },
                { title: 'Telehealth & Mobile Health Solutions', description: 'We incorporate telehealth capabilities into EMR platforms to facilitate virtual consultations, remote patient monitoring, and hassle-free telemedicine experiences.', icon: '/images/svg/personalized-patient-care.svg'},
            ]"
             />
            </div>
        </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Need Trusted EMR Developers for Your Healthcare Project?"
       >
       <template v-slot:button>
            <ul class="main-list mt-3">
              <p>Hire dedicated developers from AkraHealth to build custom EMR software, integrate Epic/Cerner/DrChrono APIs, or power your telehealth app — all with full HIPAA & HL7 compliance. Ideal for:</p>
              <li>Hospitals & Specialty Clinics</li>
              <li>Urgent Care & Remote Practices</li>
              <li>Mental Health, Dentistry & Plastic Surgery</li>
              <li>Mobile Health Startups & AI-Driven Platforms</li>
            </ul>
        </template>
       </Cta>
    <!-- Call to action section ends here -->





  <!-- Why Choose AkraHealth for Your EMR Integration section starts here -->
    <section class="section path-success mt-4">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row g-4 align-items-center text-lg-start">
          <div class="col-md-6 order-md-2">
              <img class="img-fluid w-80" src="/images/webpages/why_akrahealth.webp" alt="security-hipaa" />
          </div>

          <div class="col-md-6 text-md-start">
            <h2 class="bold py-4 text-center text-md-start fs-20">Why Hire AkraHealth's EMR Developers?</h2>
            <ListItems :items="[
              { title: 'Healthcare IT Expertise', description: 'We are skilled healthcare EMR system developers focused on producing high-quality products to industry standards.', 
                icon: 'fa-check'
              },
              { title: 'HIPAA & HL7 Compliance', description: 'Security and health regulation compliance are always top priorities in all of our healthcare applications.',
                icon: 'fa-check'
               },
              { title: 'Interoperability Guaranteed', description: 'Our systems ensure smooth interoperability between incompatible EMR/EHR systems.', 
                icon: 'fa-check'
              },
              { title: 'Track Record', description: 'We have regularly developed tailored EMR software for small practices, hospitals, and medical specialties.', 
                icon: 'fa-check'
              },
              { title: 'Dedicated Support & Maintenance', description: 'We provide ongoing support to keep your system up and running.', 
                icon: 'fa-check'
              }

            ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->


   <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-md-8 col-sm-12">
                <div class="section-title text-center">
                  <h2>Industries We Serve</h2>
                </div>
              </div>
          </div>
            <div class="row">
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                      <div class="mb-2 justify-content-center">
                            <img src="/images/svg/hospitals-clinics.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Hospitals & Clinics</h4>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/urgent-care-centers.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Urgent Care Centers</h4>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/mental-health-psychiatry.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Mental Health & Psychiatry</h4>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/dentistry-orthodontics.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Dentistry & Orthodontics</h4>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/plastic-surgery.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Plastic Surgery</h4>
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/ophthalmology.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Ophthalmology</h4>
                    </div>
                </div>
                <!-- <div class="col-md-3 col-lg-2 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/svg/integration-of-telemedicine.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Telemedicine Providers</h4>
                    </div>
                </div> -->
            </div>
        </div>
    </section>

    

    <!-- <Cta2 
    title="Hire Dedicated EMR Developers Today!" 
    description="Enhance your healthcare services with custom EMR solutions tailored to your needs. Whether you need to build a new electronic medical records system, integrate with existing EHR platforms, or optimize your current setup, AkraHealth has the expertise to help."/> -->
    


    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-md-8 col-sm-12">
                <div class="section-title text-center">
                  <h2>Our Technology Stack</h2>
                </div>
              </div>
          </div>
            <div class="row">
            <div class="col-sm-12">
       <Swiper :slidesPerView="8" :space-between="20" :modules=[Autoplay]
                :pagination="{ clickable: true }" :autoplay="{ delay: 2500, disableOnInteraction: false, }"
                :breakpoints="{
                    320: {
                slidesPerView: 1,
                spaceBetween: 20
                },
                // when window width is >= 480px
                480: {
                slidesPerView: 1,
                spaceBetween: 30
                },
                647: {
                slidesPerView: 2,
                spaceBetween: 30
                },
                // when window width is >= 740px
                727: {
                slidesPerView: 1,
                spaceBetween: 40
                },
                740: {
                slidesPerView: 2,
                spaceBetween: 40
                },
                1080: {
                slidesPerView: 8,
                spaceBetween: 40
                },
                }"
                class="mySwiper py-4">
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/php.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">PHP</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/js.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">JavaScript</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/nodejs.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Node.js</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/python.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Python</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/laravel.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Laravel</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/mysql.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">MySQL</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/aws.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">AWS</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/bootstrap4.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Bootstrap</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/html5.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">HTML 5</h4>
                    </div>
                </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2">
                            <img src="/images/techStack/postman.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Postman</h4>
                    </div>
                </div>
                </SwiperSlide>
            </Swiper>

          </div>

            </div>
        </div>
    </section>
    <section class="team">
      <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-md-8 col-sm-12">
                <div class="section-title text-center">
                  <h2>Trusted by Professionals Worldwide</h2>
                </div>
              </div>
          </div>
          <div class="row">
             <div class="col-sm-6">
              <div class="shadow rounded">
                <div class="d-flex justify-content-between p-4">
                  <span class="btn btn-icon btn-primary btn-lg shadow-primary pe-none">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24"><path d="M3.691 6.292C5.094 4.771 7.217 4 10 4h1v2.819l-.804.161c-1.37.274-2.323.813-2.833 1.604A2.902 2.902 0 0 0 6.925 10H10a1 1 0 0 1 1 1v7c0 1.103-.897 2-2 2H3a1 1 0 0 1-1-1v-5l.003-2.919c-.009-.111-.199-2.741 1.688-4.789zM20 20h-6a1 1 0 0 1-1-1v-5l.003-2.919c-.009-.111-.199-2.741 1.688-4.789C16.094 4.771 18.217 4 21 4h1v2.819l-.804.161c-1.37.274-2.323.813-2.833 1.604A2.902 2.902 0 0 0 17.925 10H21a1 1 0 0 1 1 1v7c0 1.103-.897 2-2 2z" fill="currentColor"></path></svg>
                  </span>
                  <div class="d-flex">
                    <!-- <button class="btn btn-md btn-secondary btn-prev btn-icon btn-sm me-2" type="button" id="testimonials-prev" aria-label="Previous"><i class="bx bx-chevron-left"></i></button><button class="btn btn-md btn-secondary btn-next btn-icon btn-sm ms-2" type="button" id="testimonials-next" aria-label="Next"><i class="bx bx-chevron-right"></i></button> -->
                  </div>
                </div>
              <Swiper :slidesPerView="1" :space-between="30" :modules=[Autoplay,Pagination]
                :pagination="{ clickable: true }" :autoplay="{ delay: 2500, disableOnInteraction: false, }"
                :breakpoints="{
                    320: {
                slidesPerView: 1,
                spaceBetween: 20
                },
                // when window width is >= 480px
                480: {
                slidesPerView: 1,
                spaceBetween: 30
                },
                647: {
                slidesPerView: 1,
                spaceBetween: 30
                },
                // when window width is >= 740px
                727: {
                slidesPerView: 1,
                spaceBetween: 40
                },
                740: {
                slidesPerView: 1,
                spaceBetween: 40
                },
                1080: {
                slidesPerView: 1,
                spaceBetween: 40
                },
                }"
                class="mySwiper">
                <SwiperSlide>
                  <div class="card shadow-none border-0">
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/ankit-chaudhary.webp" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Ankit Chaudhary</h5>
                          <h6 class="card-subtitle text-muted">Sr. Full Stack Developer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Ankit showcased impressive Full Stack PHP skills while working on the Whistleblower Application from start to finish, and his communication was top-notch. He played a key role in getting the application delivered smoothly."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Tim Prinz</p>
                        <small class="text-muted">Founder | Privacy24</small>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/ankita.webp" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Ankita Singh</h5>
                          <h6 class="card-subtitle text-muted">Project Manager</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Ankita is great to work with and I am super happy to have the opportunity to work with her."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Natasha Green</p>
                        <small class="text-muted">WeIntervene</small>
                      </div>
                    </div>
                  </div>
                  
                </SwiperSlide>
                <SwiperSlide>
                  <div class="card shadow-none border-0">
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/Rajindar.webp" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Rajinder Kumar</h5>
                          <h6 class="card-subtitle text-muted">Sr. Full Stack Developer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"It was an absolute pleasure working with Raaj. He is very attentive to details, communication skills are high and made everything so much easier.he completed work very quickly and was able to help out in the process where we were unfamiliar."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Tim Prinz</p>
                        <small class="text-muted">Founder | Datanschutz Prinz GMBH</small>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/manish-kumar.webp" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Manish Kumar</h5>
                          <h6 class="card-subtitle text-muted">Sr. Full Stack Developer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Great delivery by Manish. He completed the task as expected. I will definitely be in touch with him again when I need more work done! Manish, he was very quick, diligent and made himself available whenever needed. I would highly recommend him for any of the web design projects in future."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Tim Prinz</p>
                        <small class="text-muted">Privacy24</small>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="card shadow-none border-0">
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/munish.jpg" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Munish Kumar</h5>
                          <h6 class="card-subtitle text-muted">PHP Developer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"We have worked together for almost two years, and he was consistent and did his job to the best of his ability. Startups have a lot of moving components and he was able to navigate successfully."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Jaya</p>
                        <small class="text-muted">Founder | FTCSOLAR</small>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/abdullah.webp" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Abdullah</h5>
                          <h6 class="card-subtitle text-muted">Python Developer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Abdullah did a fantastic job with his Python development assignments and effectively added the security feature for website scanning. As a valuable member of the Akratech Team, he contributed greatly, and we wish him and the entire team all the best!"</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Tim Prinz</p>
                        <small class="text-muted">Privacy24</small>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
                <SwiperSlide>
                  <div class="card shadow-none border-0">
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/lakshmi.png" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Lakshmi</h5>
                          <h6 class="card-subtitle text-muted">Software Tester</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Lakshmi and the team from AKRA TECH helped develop a children's software for us that was crucial for our success as a start-up. We are grateful for their efforts and work."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Bob Wood</p>
                        <small class="text-muted">Founder | Scriptive</small>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="d-flex align-items-center">
                        <img src="/images/webpages/team/janani.png" class="rounded-circle w-10 mr-3" alt="Client Image">
                        <div class="">
                          <h5 class="card-title">Janani</h5>
                          <h6 class="card-subtitle text-muted">Content Writer</h6>
                        <span class="text-warning font-l">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                      </div>
                      <p class="card-text">"Janani was very fluent in English and was reliable and always provided the amount of work requested. She was always quick to respond and very communicative. It has been a good experience working with her and the rest of the AKRA TECH team."</p>
                      <div class="client-name mt-3">
                        <p class="h5 text-primary m-0">Bob Wood</p>
                        <small class="text-muted">Founder | Scriptive</small>
                      </div>
                    </div>
                  </div>
                </SwiperSlide>
            </Swiper>
           </div>
          </div>
          <div class="col-sm-6">
              <div class="d-flex justify-content-center">
                  <img src="/images/webpages/review1.png" />
              </div>
          </div>
          </div>
          </div>
    </section>
  </AppLayout>
</template>
