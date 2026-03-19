 <script setup>
 import AppLayout from '../Layouts/AppLayout.vue';
 import Featured from '../Partials/Featured.vue';
import Services from '../Partials/Services.vue';
import Testimonial from '../Partials/Testimonial.vue';
 import Cta from '@/Partials/Cta.vue';
import BookDemo from '@/Partials/BookDemo.vue';
import HeroSection from '@/Partials/HomeHeroSection.vue';
import DemoRequest from '@/Pages/Modals/DemoRequest.vue';
import WatchDemo from '@/Pages/Modals/WatchDemo.vue';
import LaunchLightweightEMRModal from '@/Pages/Modals/LaunchLightweightEMRModal.vue';
import { ref, onMounted } from 'vue'
import { route } from 'ziggy-js';
 
const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
const openFaq = ref(null)

const toggleFaq = (id) => {
    openFaq.value = openFaq.value === id ? null : id
}

// Load Instagram embed script and process embeds
onMounted(() => {
    // Load Instagram script if not already loaded
    if (!window.instgrm) {
        const script = document.createElement('script');
        script.src = '//www.instagram.com/embed.js';
        script.async = true;
        script.onload = () => {
            // Process Instagram embeds after script loads
            if (window.instgrm && window.instgrm.Embeds) {
                window.instgrm.Embeds.process();
            }
        };
        document.head.appendChild(script);
    } else {
        // If script is already loaded, process embeds
        if (window.instgrm && window.instgrm.Embeds) {
            window.instgrm.Embeds.process();
        }
    }
});
const signup = () => {
    window.location.href = route('signup');
}
</script>
<!-- Instagram -->


<template>
    <AppLayout title="Home" 
    description="Explore top-rated EMR software, EHR systems, and healthcare apps tailored for urgent care, small practices, telemedicine, and virtual health services.">
      <HeroSection
          description="Manage appointments, medical records, billing & telehealth in one secure platform.HIPAA Compliant. Global Support. Start your free 14-day trial today."
      >
        <template v-slot:title>
        <h1 class="display-lg-4 mt-3 text-center text-md-start">
          <span class="bold">
            EMR Software <div class="display-lg-5">for Small Clinics & Growing Hospitals</div>
          </span>
        </h1>
      </template>
     <template v-slot:images>
        <div class="device-twin free-width align-items-center mt-4 mt-lg-0">
            <div class="browser shadow aos-init aos-animate shadow" style="border-radius: 15px;" >
                <img src="images/webpages/appss_1.webp" alt="..." class="img-fluid shadow" style="border-radius: 15px;">
            </div>

            <div class="front iphone-x absolute d-none d-lg-block aos-init aos-animate mt-6 ms-xxl-n5" data-aos="fade-right">
                <div class="screen">
                    <img src="images/webpages/mobiless_2.webp" alt="...">
                </div>
                <div class="notch"></div>
            </div>
        </div>
     </template>
     <template v-slot:button >
        <button  @click="signup()" class="btn btn-primary btn-lg btn-rounded px-4 mt-3 mr-2">
           Start Free Trial 
        </button>
        <button class="btn btn-light btn-lg btn-rounded px-4 mt-3 btn-watch-demo" data-toggle="modal" data-target="#watch-demo-modal">
            Watch Demo
        </button>
    </template>
      </HeroSection>

    <main id="main">
      <section id="about" class="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 section-title">
                    <h2 class="bold text-center">AkraHealth: The Best EMR for Telehealth and Virtual Care</h2>
                    <p class="text-center fs-16">AkraHealth offers a powerful and intuitive <b>telemedicine EMR software</b>that seamlessly integrates  <b>electronic medical records (EMR) </b> and  <b>electronic health records (EHR)</b> to support modern <b> virtual healthcare solutions </b>. Our platform is optimized for telehealth integration, enabling providers to deliver secure, compliant, and efficient <b>remote patient care.</b></p>
                   
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
                                <img src="/images/EMR-18.webp" alt="emr-eighteen" />
                            </div>
                            <span class="button"></span>
                        </div>
                        <div class="iphone-x front me-0">
                            <div class="screen shadow-box">
                                <img src="/images/EMR-9.webp" alt="emr-nine" />
                            </div>
                            <div class="notch"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 text-center text-md-start">
                    <p class="text-justify fs-15">Our platform supports <b>primary care, pediatrics, psychotherapy, IV infusion, occupational therapy, functional medicine, mental health, weight loss clinics, and home visits.</b> AkraHealth works just as well for a provider managing 5 patients as it does for multi-provider practices planning to scale - no forced upgrades, no bloated features.</p>
                    <ul class="main-list mt-3 fs-15">
                    <li>Solo practitioners & new medical startups</li>
                    <li>Small clinics & part-time practices</li>
                    <li>Telemedicine & virtual care providers</li>
                    <li>HIPAA-compliant virtual consultation tools</li>
                    <li>IV infusion & mobile care teams</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <Featured />
    <Cta title="Starting Fresh or Customizing Your EMR?">
          <template v-slot:button>
            <p class="home-cta fs-16">AkraHealth is an affordable, cloud-based EMR designed to support new setups, smooth migrations, and custom integrations-without heavy systems or long-term commitments. Get a solution tailored to your workflow, not the other way around.</p>
            <ul class="main-list mt-3 fs-15">
                    <li>Small clinics & solo medical practices</li>
                    <li>Telemedicine & virtual care startups</li>
                    <li>Multi-location practices needing instant setup</li>
                    <li>IV infusion, mobile care & house-visit services</li>
                    <li>Functional, integrative & primary care practices</li>
                    </ul>
        </template>
    </Cta>
    <Services />
     <Testimonial />
    <!-- Faq section starts here -->
    <section id="faq-calendly" class="faq section-bg py-5">
        <div class="container">
            <div class="row g-4 align-items-start">

                <!-- ================= LEFT : FAQ ================= -->
                <div class="col-lg-6">
                    <div class="section-title">
                        <h2>Frequently Asked Questions</h2>
                        <p>
                            Not sure how this platform can help you?
                            Here are answers to the most common questions.
                        </p>
                    </div>

                <ul class="faq-list">
                    <li>
                        <div class="question" @click="toggleFaq(1)">
                            Is Akrahealth compatible with existing EMR systems?
                            <i class="bi"
                            :class="openFaq === 1 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 1" class="faq-answer">
                            <p>
                                Yes, Akrahealth can easily integrate with multiple EMR systems,
                                cloud-based EMR, SaaS EMR, and telemedicine EMR to ensure smooth
                                data flow and interoperability.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(2)">
                            Can Akrahealth be customized for specific specialties?
                            <i class="bi"
                            :class="openFaq === 2 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 2" class="faq-answer">
                            <p>
                                Absolutely! Akrahealth customizes for most specialties, including
                                small practices and telemedicine providers.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(3)">
                            Is Akrahealth cloud-based?
                            <i class="bi"
                            :class="openFaq === 3 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 3" class="faq-answer">
                            <p>
                                Yes, Akrahealth is a fully cloud-based EMR that can be accessed
                                securely from anywhere.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(4)">
                            Do you offer any e-prescription functionality?
                            <i class="bi"
                            :class="openFaq === 4 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 4" class="faq-answer">
                            <p>
                                Yes, Akrahealth includes built-in e-prescription functionality
                                for seamless pharmacy integration.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(5)">
                            What security protocols are adapted to protect patient data?
                            <i class="bi"
                            :class="openFaq === 5 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 5" class="faq-answer">
                            <p>
                                We use HIPAA compliance, encryption, and secure cloud hosting
                                to protect patient data.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(6)">
                            Does Akrahealth support telemedicine platforms?
                            <i class="bi"
                            :class="openFaq === 6 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 6" class="faq-answer">
                            <p>
                                Yes, Akrahealth integrates seamlessly with telemedicine platforms
                                to support virtual care.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(7)">
                            Do you provide training on how to use Akrahealth?
                            <i class="bi"
                            :class="openFaq === 7 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 7" class="faq-answer">
                            <p>
                                Yes, we provide comprehensive training including tutorials and
                                personal assistance.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(8)">
                            Can I view the progress of patients in Akrahealth?
                            <i class="bi"
                            :class="openFaq === 8 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 8" class="faq-answer">
                            <p>
                                Yes, providers can easily track patient progress and treatment
                                history inside the EMR.
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="question" @click="toggleFaq(9)">
                            Does Akrahealth have insurance billing capabilities?
                            <i class="bi"
                            :class="openFaq === 9 ? 'bi-chevron-up icon-close' : 'bi-chevron-down icon-show'">
                            </i>
                        </div>
                        <div v-show="openFaq === 9" class="faq-answer">
                            <p>
                                Yes, Akrahealth includes insurance billing and claims processing
                                to streamline revenue cycles.
                            </p>
                        </div>
                    </li>
                </ul>

                </div>

                <!-- ================= RIGHT : CALENDLY ================= -->
                <div class="col-lg-6">
                    <div class="bg-white rounded shadow-sm p-4">
                        <div class="section-title text-start pb-2">
                            <h2>Start Your Journey With AkraHealth</h2>
                            <p class="text-center">
                                Schedule a 45-minute consultation or book a free demo.
                            </p>
                        </div>

                        <div class="col-sm-12">
                        <BookDemo />
                    </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
  <section>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="section-title">
                    <p>Social Media</p>
                    <h2>Explore our Social Media Channels.</h2>
                </div>
             </div>
            </div>
            <div class="row justify-content-md-center">
              <div class="row g-4 social-row">
                    <div class="col-12 col-md-4">
                        <div class="social-card">
                        <!-- Instagram -->
                        <blockquote 
                            class="instagram-media" 
                            data-instgrm-permalink="https://www.instagram.com/reel/DQ_NxhniiMH/"
                            data-instgrm-version="14">
                        </blockquote>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="social-card facebook-card">
                        <!-- Facebook -->
                        <iframe
                            src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/reel/1659372081703209/&show_text=false"
                            loading="lazy"
                            allowfullscreen>
                        </iframe>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="social-card ">
                        <!-- LinkedIn -->
                        <iframe
                            src="https://www.linkedin.com/embed/feed/update/urn:li:activity:7399045017949941760"
                            loading="lazy"
                            allowfullscreen>
                        </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
        </main>
         <DemoRequest />
    <WatchDemo />
    <LaunchLightweightEMRModal />
    </AppLayout>
   
</template>
<style scoped>
.social-row {
    align-items: stretch;
}

.social-card {
    height: 520px;              /* SAME HEIGHT */
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    overflow: hidden;           /* 🔥 KILLS INTERNAL SCROLL */
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Force embeds to fill card */
.social-card iframe,
.social-card blockquote {
    width: 100% !important;
    height: 100% !important;
    border: none;
}

/* LinkedIn internal scroll killer */
.social-card iframe {
    overflow: hidden !important;
}

@media (max-width: 768px) {

    .facebook-card {
        height: 360px;   /* 👈 MOBILE HEIGHT */
    }

    .facebook-card iframe {
        height: 360px;
    }
}
</style>
