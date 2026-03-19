<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection from '@/Partials/HeroSection.vue';
import ListItems from '@/Partials/ListItems.vue';


defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="AI in Healthcare" meta-title="AI in Healthcare | Smart EMR Solutions"
    description="Explore AI in healthcare with Akrahealth’s smart EMR solutions, enhancing medical records, telemedicine, and operational efficiency with AI-driven technology.">
   
    <HeroSection
      title="Revolutionize Healthcare with Akrahealth AI"
      description="Enhance care with AI, medical scribes, and predictive analytics."
      >
     <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate" data-aos="fade-left">
                <img src="images/webpages/coi_app.webp" alt="...">
            </div>

            <!-- <div class="front iphone-x absolute d-none d-lg-block aos-init aos-animate mt-6 ms-xxl-n5" data-aos="fade-right">
                <div class="screen">
                    <img src="images/screens/app/emr-bannerImag.webp" alt="...">
                </div>
                <div class="notch"></div>
            </div> -->
        </div>
     </template>
      </HeroSection>


    <section class="quick-start-your-business mb-lg-n3 ">
      <div class="container con-box">
        <div class="row">
          <div class="col-sm-12 col-lg-10 m-auto">
              <div class="section-title">
                <h2 class="mt-3 text-center">
                  Akrahealth AI: Transforming Healthcare Through Innovation
                </h2>
                <p class="text-center">At Akrahealth, we harness the power of AI in healthcare to streamline clinical operations, enhance medical billing accuracy, and improve electronic health record (EHR) efficiency. Our intelligent health tech solutions—including an advanced<b> AI medical scribe, clinical decision support AI</b>, and<b> predictive analytics tools</b>—help providers save time, reduce errors, and make better, data-informed decisions.</p>
                <p class="text-left">What We Offer:</p>
                <ul class="main-list mt-3 text-left">
                    <li><b>AI-Powered Medical Scribe</b>to automate clinical documentation</li>
                    <li><b>AI-Driven Medical Billing </b>for faster reimbursements and fewer denials</li>
                    <li><b>Cloud-Based EHR Software</b> for real-time patient record access</li>
                    <li><b>Free Web-Based EMR System</b> built for <b>small practices</b> and <b>telemedicine workflows</b></li>
                    <li><b>White-Label Patient Portal </b>to support personalized, branded care experiences</li>
                    </ul>
                <p class="text-left">Akrahealth’s suite of <b>AI healthcare tools</b> is built to improve clinical productivity, reduce provider burnout, and empower modern medical practices to thrive in a digital-first healthcare environment.</p>

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
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="">
              <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
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
            <img class="img-responsive" src="/images/webpages/appss_1.webp" alt="ai Solutions" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/webpages/appss_2.webp" alt="ai Solutions" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/webpages/appss_3.webp" alt="ai Solutions" />
          </SwiperSlide>
        </Swiper>
            </div>

            <!-- <div class="device-twin align-items-center">
              <div class="mockup absolute right aos-init aos-animate" data-aos="fade-right ">
                <div class="screen">

                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="images/emr-banner-imag.webp" alt="emr-banner-imag" />
                </div>
                <div class="notch"></div>
              </div>
            </div> -->
          </div>

          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-20">Key Features of Akrahealth’s AI Solutions</h2>
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">
                    AI Medical Scribe
                  </h5>
                  <p class="m-0">
                    Automate documentation with precise, time-saving AI-powered scribing.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">AI in EHR Management</h5>
                  <p class="m-0">
                    Simplify electronic medical records handling with intelligent EHR integration.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">AI for Medical Billing</h5>
                  <p class="m-0">
                    Improve revenue cycle management with AI-driven medical billing automation.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>
                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Clinical AI Tools</h5>
                  <p class="m-0">
                    Enhance diagnostics and decision-making with cutting-edge clinical AI.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">AI in Patient Care</h5>
                  <p class="m-0">
                    Deliver personalized healthcare through smart AI for health solutions.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-3">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <i class="fa-solid fa-check fa-2x text-primary"></i>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Seamless Integrations</h5>
                  <p class="m-0">
                    Connect effortlessly with legacy systems and top EMR platforms.
                  </p>
                </div>
              </li>
             
            </ul>
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->


        <!-- nav design -->
        <section class="section section custom_tabs2 bg-light">
            <div class="container con-box">
        <ul class="flex-column flex-md-row nav nav-tabs nav-outlined nav-rounded gap-2 gap-md-4 mb-5" role="tablist">
            <li class="">
            <a class="nav-link active p-3" data-toggle="tab" href="#info-security">AI Chatbots</a>
            </li>
            <li class="nav-item">
            <a class="nav-link p-3" data-toggle="tab" href="#customized-clinical">AI Voiceovers</a>
            </li>
           <li class="nav-item">
             <a class="nav-link p-3" data-toggle="tab" href="#online-patient">AI Text To Speech</a>
           </li>
           <li class="">
             <a class="nav-link p-3" data-toggle="tab" href="#payer-provider">AI Speech to Text</a>
           </li>
        </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div id="info-security" class="tab-pane active">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  order-md-2  ms-md-auto mb-4 ">
              <img src="/images/webpages/ai-chatbots.webp" alt="information security.webp"
              class="img-responsive">
            </div>
            <div class="col-md-12 col-lg-6 me-md-auto">
                <h2 class="bold fs-20">AI Chatbots for Healthcare</h2>
                <p>Enhance patient engagement with AI chatbots that provide instant, personalized interactions and 24/7 support while ensuring HIPAA-compliant data security.</p>
            </div>
        </div>
    </div>
    <div id="customized-clinical" class="tab-pane fade">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  me-md-auto position-relative mb-4">
              <img src="/images/webpages/ai-voiceover.webp" alt="customized clinical forms.webp"
              class="img-responsive">
            </div>
            <div class="col-md-12 col-lg-6">
                <h2 class="bold fs-20">AI Voiceovers for Healthcare</h2>
                <p>Transform patient communication with AI voiceovers for appointment reminders, medication instructions, and more—ensuring a seamless, professional experience.</p>
            </div>
        </div>
    </div>

    <div id="online-patient" class="tab-pane fade">
        <div class="row gap-y align-items-center">
            <div class="col-md-12 col-lg-6  me-md-auto mb-4 ">
              <img src="/images/webpages/ai-text-to-speech.webp" class="img-responsive"
                        alt="high-quality healthcare for patients.webp">
                
            </div>
            <div class="col-md-12 col-lg-6 ">
                <h2 class="bold fs-20">AI Text to Speech for Healthcare</h2>
                <p>Improve accessibility by converting written content into natural-sounding speech, making healthcare information more engaging and inclusive.</p>
            </div>
        </div>
    </div>
    <div id="payer-provider" class="tab-pane fade">
      <div class="row gap-y align-items-center">
        <div class="col-md-12 col-lg-6">
              <h2 class="bold fs-20">AI Speech to Text</h2>
              <p>Convert voice conversations into real-time, accurate text for medical documentation, improving efficiency while maintaining HIPAA compliance.</p>
            </div>
            <div class="col-md-12 col-lg-6  me-md-auto mb-4 ">
              <img src="/images/webpages/ai-speech-to-text.webp" class="img-responsive"
              alt="high-quality healthcare for patients.webp">
            </div>
        </div>
    </div>
   </div>         
  </div>
</section>

   <!-- Call to action section starts here -->
   <Cta 
      title="Leverage AI for Smarter Healthcare"
      >
      <template v-slot:button>
        <p>Boost efficiency and improve patient outcomes with AI-powered healthcare tools. From AI chatbots for patient communication to voice-enabled charting and real-time speech-to-text transcription, Akrahealth helps healthcare professionals save time, reduce burnout, and enhance care delivery.</p>
        <ul class="main-list mt-3">
          <li>Streamline workflows with clinical automation</li>
          <li>Improve patient engagement with intelligent chat assistants</li>
          <li>Dictate notes effortlessly with AI voiceovers and transcription</li>
          <li>Family Medicine & Primary Care Practices</li>
          <li>HIPAA-compliant & cloud-based infrastructure</li>
        </ul>
    </template>
      </Cta>
   <!-- Call to action section ends here -->

     <!-- AI-powered EMR and EHR Systems section starts here -->
     <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
         <div class="col-sm-12 col-lg-10 m-auto">
          <div class="section-title text-center">
          <h2 class="mt-3">
            AI-Driven Telehealth, Remote Monitoring, and Fitness Integration
          </h2>
          <p>Akrahealth’s AI-driven telehealth solutions transform remote care with real-time diagnostics, virtual consultations, and continuous patient monitoring. Our AI medical assistants optimize workflows and enable proactive healthcare interventions within a seamless digital ecosystem.</p>
        </div>
         </div>
        </div>
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
                <h2 class="bold text-center text-md-start mt-4 mt-md-0 fs-22">Leverage AI to Enhance Your Practice’s EMR & EHR Experience</h2>
                <ul class="main-list mt-4">
                  <li><span class="bold">On-Demand Virtual Consultations:</span> Connect with healthcare professionals anytime, anywhere.</li>
                  <li><span class="bold text-nowrap">AI-Enabled Health Monitoring:</span>Track vital signs and receive real-time health insights.</li>
                  <li><span class="bold text-nowrap">Tailored AI Care Plans</span>Get personalized treatment and recovery plans based on patient data.</li>
                  <li><span class="bold text-nowrap">Comprehensive Fitness Integration</span>Sync with Apple Watch, Fitbit, Oura, Whoop, and more.</li>
                  <li><span class="bold text-nowrap">Simplified User Dashboard</span>Access all health insights in one intuitive interface.</li>
                </ul>
              </div>
            </div>
      </div>
    </section>
    <!-- AI-powered EMR and EHR Systems section ends here -->

    <section class="section path-success">
      <div class="shapes-container">
        <div class="shape-ring absolute right top aos-init aos-animate" data-aos="fade-up"></div>
      </div>

      <div class="container bring-to-front">
        <div class="row">
         <div class="col-sm-12 col-lg-10 m-auto">
          <div class="section-title text-center">
          <h2 class="mt-3">Akrahealth's AI-Powered Personalized Healthcare Platform.</h2>
          <p>Akrahealth AI delivers personalized healthcare through secure data management, interactive patient-provider support, and intelligent insights—ensuring better outcomes and efficiency.</p>
        </div>
         </div>
        </div>
        <div class="row g-4 align-items-center text-center text-lg-start">
          <div class="col-md-6 order-md-2">
            <div class="">
              <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
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
                        <img class="img-responsive" src="/images/webpages/ai-chatbots.webp" alt="ai Solutions" />
                      </SwiperSlide>

                      <SwiperSlide>
                        <img class="img-responsive" src="/images/webpages/ai-speech-to-text.webp" alt="ai Solutions" />
                      </SwiperSlide>
                      <SwiperSlide>
                        <img class="img-responsive" src="/images/webpages/ai-text-to-speech.webp" alt="ai Solutions" />
                      </SwiperSlide>
             </Swiper>
            </div>
          </div>
          <div class="col-md-6 text-center text-md-start">
            <div class="mb-3">
              <h2 class="bold py-4 fs-22">Key Features</h2>
            </div>
            <ListItems :items="[
                  { 
                    title: 'AI-Driven Lab Result Analysis', 
                    description: 'AI examines lab reports, detects trends, and provides personalized health recommendations.',  
                  },
                  { 
                    title: 'AI-Powered Conversations', 
                    description: 'Get real-time, secure treatment insights and protocol guidance through AI-driven interactive conversations.',  
                  },
                  { 
                    title: 'Personal Care Hub', 
                    description: 'AI simplifies lab markers and conditions, empowering patients and clinicians with clearer insights.',  
                  },
                  {
                    title: 'Security & Compliance',
                    description: 'HIPAA-compliant data encryption and strict access control ensure patient privacy and protection.',
                  },
                   {
                     title: 'Scalable & Future-Ready',
                     description: 'Seamlessly integrates with wearable devices, telemedicine, and cutting-edge healthcare technologies for expanding personalized care.'
                   }
                ]" />
          </div>
        </div>
      </div>
    </section>


    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
         <div class="col-sm-12 col-lg-10 m-auto">
          <div class="section-title text-center">
          <h2 class="mt-3">AI Agent Builder Platform at Akrahealth</h2>
          <p>Akrahealth is developing a powerful AI Agent Builder Platform designed specifically for healthcare. This platform enables the creation of custom AI solutions for patient engagement, medical billing, appointment scheduling, and administrative automation. Leveraging Crew.AI and cutting-edge technologies, our platform ensures HIPAA-compliant, secure data management with seamless EHR/EMR and billing system integration.</p>
        </div>
         </div>
        </div>

        <div class="row gap-y align-items-center">
             <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="emr-app screen">
                      <img src="/images/app-one.webp" alt="emr-one" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/app-two.webp" alt="online chart" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="bold fs-22">Key Features</h2>
                <ul class="main-list mt-4">
                  <li><span class="bold text-nowrap">Custom AI Development:</span>Build and tailor AI agents for healthcare workflows while ensuring HIPAA compliance.</li>
                  <li><span class="bold text-nowrap">Drag-and-Drop AI Creation</span>User-friendly interface with NLP support for intelligent healthcare conversations.</li>
                  <li><span class="bold text-nowrap">Seamless System Integration</span>Connect effortlessly with EHR/EMR, billing systems, and third-party applications.</li>
                  <li><span class="bold text-nowrap">Enhanced Performance & Usability</span>Optimize AI agents with data analytics, reporting tools, and structured documentation.</li>
                </ul>
              </div>
            </div>
       </div>
    </section>

      <!-- <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
          <div class="col-sm-12">
            <div class="section-title text-center">
          <h2 class="mt-3">
            AI-Driven Telehealth, Remote Monitoring, and Fitness Integration
          </h2>
          <p>Akrahealth’s AI-driven telehealth services revolutionize remote care by providing real-time diagnostics, virtual consultations, and continuous patient monitoring. The use of AI medical assistants enhances workflow efficiency and supports proactive healthcare interventions, all within a unified digital environment.</p>
        </div>
          </div>
        </div>
        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <h2 class="bold">Key Benefits of Telemedicine & Remote Monitoring with AI</h2>
                <ul class="main-list">
                  <li>On-Demand Virtual Consultations – Access healthcare professionals whenever and wherever needed..</li>
                  <li>AI-Enabled Health Monitoring – Keep track of vital signs and receive immediate insights.</li>
                  <li>Tailored AI Care Plans – Customized treatment and recovery plans based on individual patient data.</li>
                  <li>Comprehensive Fitness Integration – Sync with devices such as Apple Watch, Fitbit, Oura, and Whoop to monitor health metrics.</li>
                  <li>Simplified User Dashboard – View all health insights in one convenient location.</li>
                </ul>
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
    </section> -->


   <!-- Akrahealth AI-Powered Solutions have many benefits __Section starts here__ -->
   <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
          <div class="row justify-content-center">
            <div class="col-md-8 col-sm-12">
                <div class="section-title text-center">
                  <h2>Benefits of Akrahealth AI-Powered Solutions</h2>
                </div>
              </div>
          </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                      <div class="mb-2 justify-content-center">
                            <img src="/images/svg/e-prescription-icon.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Efficiency</h4>
                        <p class="description">Automates medical documentation and billing for streamlined workflows.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/better-patient-care.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Better Patient Care</h4>
                        <p class="description">AI-driven diagnostic tools and medical assistants enable real-time, data-driven decisions.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/cost-saving.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Cost-Saving</h4>
                        <p class="description">Optimized revenue cycle management reduces billing costs with AI in healthcare.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/scalability.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Scalability</h4>
                        <p class="description">Easily expand from small practices to large hospitals with AI-driven healthcare applications.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-md-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/proactive-care.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Proactive Care</h4>
                        <p class="description">Predictive AI analytics anticipate patient needs before critical health events.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/data-interoperability.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Data Interoperability</h4>
                        <p class="description">Ensures smooth EMR/EHR integration for seamless data flow.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/integration-of-telemedicine.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">AI-Enabled Telemedicine</h4>
                        <p class="description">Enhances virtual visits and remote patient monitoring with AI-powered telehealth solutions..</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-3 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                            <img src="/images/svg/safe-access-to-data.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Secure Data Access</h4>
                        <p class="description">HIPAA-compliant AI safeguards patient data while ensuring real-time accessibility.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
   <!-- Akrahealth AI-Powered Solutions have many benefits __Section ends here__ -->
    
    <section class="section bg-light">
      <div class="container">
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
            <div class="text-center text-md-start">
              <h2 class="bold fs-24">How It Works: Akrahealth AI Solutions</h2>
              <p class="text-justify"><b>Akrahealth AI </b>empowers healthcare providers with intelligent automation and real-time clinical insights. Our suite of<b> AI-enabled healthcare solutions</b> simplifies and accelerates core processes—from <b>medical documentation </b>and <b>AI-driven medical billing</b> to <b>diagnostics and virtual care.</b></p>
              <ul class="main-list mt-3">
                  <li><b>AI Medical Assistants: </b>Automate charting and clinical notes with smart, voice-enabled support</li>
                  <li><b>AI in Medical Billing:</b> Reduce claim denials and boost revenue cycle efficiency with real-time billing automation.</li>
                  <li><b>AI Diagnostic Tools:</b> Support faster, data-driven clinical decision-making with AI-assisted diagnostics.</li>
                  <li><b>Predictive Analytics in Healthcare:</b> Identify high-risk patients early for proactive treatment and population health management.</li>
                  <li><b>AI Telehealth Solutions:</b> Enable seamless remote consultations through integrated AI-powered telemedicine EMR systems.</li>
                </ul>
            </div>
        </div>

          <div class="col-md-6">
            <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
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
            <img class="img-responsive" src="/images/webpages/ai_solutions_1.webp" alt="ai Solutions" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/webpages/ai_solutions_2.webp" alt="ai Solutions" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/webpages/ai_solutions_3.webp" alt="ai Solutions" />
          </SwiperSlide>
        </Swiper>

          </div>
        </div>
      </div>
    </section>

    <!-- Use Cases & Success Stories __Section starts here -->
    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row">
         <div class="col-sm-12 col-lg-10 m-auto">
          <div class="section-title text-center">
          <h2 class="mt-3">Use Cases & Success Stories</h2>
          <p>The AI-powered Akrahealth solutions have transformed Dental Practice Management by automating everyday tasks, improving patient care, and enhancing practice operations. A key success story includes complete dental management, optimizing patient records management, AI in medical billing, invoicing, treatment planning, and inventory management. Leveraging AI tools in healthcare automates administrative processes, reduces paperwork, minimizes errors, and allows dental professionals to focus more on patient care.</p>
        </div>
         </div>
        </div>

        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <h2 class="bold fs-22">AI Solutions for Smarter Dental Practices</h2>
                <ul class="main-list mt-4">
                  <li><span class="bold">Streamlines administrative tasks:</span>The AI-based medical assistant automates patient records, billing, and treatment planning, saving time and eliminating paperwork.</li>
                  <li><span class="bold">Improved Efficiency:</span> With AI medical assistants, workflows are optimized, giving dental professionals more time to focus on patient care.</li>
                  <li><span class="bold">Enhanced Billing & Invoicing:</span>Revenue cycle management is simplified by ensuring accurate billing and invoicing for faster payments.</li>
                  <li><span class="bold">Immediate Patient Data Access:</span>The system allows patient records and treatment plans to be accessed instantly, improving decision-making and care delivery.</li>
                  <li><span class="bold">Cost Efficiency:</span> Reducing manual errors and operational inefficiencies lowers administrative costs.</li>
                  <li><span class="bold">Quality Patient Care:</span>More efficient workflows enable dental professionals to spend more time with patients, ensuring better care.</li>
                </ul>
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
    <!-- Use Cases & Success Stories __Section ends here -->


  </AppLayout>
</template>
