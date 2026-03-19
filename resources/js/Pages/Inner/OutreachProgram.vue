<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from '@/Partials/HeroSection2.vue';
import IconboxCard from "@/Partials/IconboxCard.vue";
import IconCard from "@/Partials/IconCard.vue";
import ListItems from '@/Partials/ListItems.vue';
import Cta2 from '@/Partials/Cta2.vue'

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Outreach Program" meta-title="Healthcare Outreach Program for Hospitals & Clinics"
    description="Boost patient engagement and brand credibility with Akrahealth’s AI-powered healthcare outreach, digital marketing, and telehealth integration solutions.">
    <HeroSection2
    title="Akrahealth Healthcare Outreach Program" 
    description="Empowering healthcare firms, hospitals, and clinics with tailored marketing and technology solutions to enhance patient care, streamline operations, and build trust."
    bannerImg="/images/webpages/outreachHeader.svg"
    >
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Why Partner with Akrahealth?</h2>
                <p class="text-center">
                  At Akrahealth, we help healthcare firms, hospitals, and clinics stay ahead with our specialized outreach and digital transformation solutions, including:
              </p>
            </div>
            <IconboxCard :items="[
                { title: 'Strategic Digital Marketing', description: 'SEO, healthcare content marketing, and patient-centric campaigns to improve visibility.', icon: '/images/svg/improve-care-coordination.svg'},
                { title: 'Targeted Patient Engagement', description: 'Data-driven outreach strategies to attract, retain, and enhance patient satisfaction.', icon: '/images/svg/evidence-based-decision-support.svg'},
                { title: 'Reputation Management & Brand Development', description: 'Strengthening credibility through compelling healthcare storytelling, content marketing, and PR.', icon: '/images/svg/real-time-collaboration.svg' }
            ]"
             />
            </div>
        </section>


        <section class="section">
      <div class="container">
        <div class="row">
          <div class="section-title">
            <h2>Our Key Services</h2>
          </div>
        </div>
        <div class="row align-items-center gap-y">
          <div class="col-md-6">
            <ListItems :items="[
                { title: 'Digital Marketing & Patient Engagement', description: 'Boost Healthcare Visibility – Drive awareness with search engine optimization, social media strategies, and targeted healthcare ads.'},
                { title: 'Data-Driven Outreach Solutions', description: 'Utilize AI-powered insights to enhance patient engagement, optimize appointment scheduling, and increase retention rates.'},
                { title: 'Brand Reputation & Trust Building', description: 'Enhance the credibility of your healthcare firm, hospital, or clinic with expert-driven content, patient testimonials, and community-building initiatives.'}
                    ]" />
           </div>
           <div class="col-md-6">
            <div class="text-center">
             <img src="/images/webpages/key-services.svg" alt="" />
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
              <h2 class="bold mb-4 fs-20">Empower Your Healthcare Firm’s Growth</h2>
              <ListItems :items="[
                { title: 'Increase Patient Acquisition', description: 'Personalized marketing campaigns to attract new patients and boost appointment bookings.'},
                { title: 'Enhance Brand Credibility', description: 'Establish your hospital or clinic as a leader in healthcare services.'},
                { title: 'Optimize Patient Communication', description: 'Engage with patients through telehealth, online portals, and automated follow-ups.'}
                    ]" />
            </div>
          </div>
          <div class="col-md-6 position-relative">
            <div class="">
              <img class="img-fluid" src="/images/webpages/empower-healthcare.webpl" alt="emr-online-chart" />
            </div>
          </div>
        </div>
      </div>
    </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Ready to Transform Your Healthcare Outreach?"
       >
       <template v-slot:button>
            <ul class="main-list mt-3">
              <p>Discover Akrahealth’s all-in-one platform for targeted patient engagement, AI-powered marketing, and seamless EMR integration—built to elevate your brand, increase patient acquisition, and streamline operations. Ideal for:</p>
              <li>Multi-Specialty Hospitals & Private Clinics</li>
              <li>Dental, Mental Health & Specialty Care Providers</li>
              <li>Telehealth Startups & Virtual Care Platforms</li>
              <li>Healthcare Marketing & Patient Engagement Teams</li>
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
            <h2 class="bold">Smarter Marketing for Smarter Healthcare</h2>
            <p>Strategic Digital Solutions to Enhance Your Online Presence, Utilize cutting-edge digital marketing, AI-powered engagement tools, and telemedicine integrations to revolutionize patient care.</p>
          </div>
          </div>
          <div class="col-sm-6">

          </div>
          <div class="col-sm-6">
            <h2 class="bold mb-4 fs-20">Comprehensive Solutions for Healthcare Firms, Hospitals & Clinics</h2>
            <ListItems :items="[
                { 
                  title: 'Patient Management & Outreach', 
                  description: 'Strengthen connections with patients, caregivers, and communities through automated outreach programs and personalized care reminders.', 
                },
                { 
                  title: 'AI-Enabled Telehealth Solutions', 
                  description: 'Improve remote patient care with real-time video consultations, AI-driven diagnostics, and virtual health assistants.', 
                },
                { 
                  title: 'Targeted Healthcare Marketing', 
                  description: 'Attract and retain patients with health-focused digital campaigns, search engine strategies, and content-driven engagement.', 
                },
                { 
                  title: 'Patient Analytics & Insights', 
                  description: 'Leverage data to optimize healthcare service delivery, monitor patient engagement, and improve medical outcomes.', 
                }
              ]" />

          </div>
          </div>
          </div>
    </section>


      <!-- Use Cases & Success Stories __Section starts here -->
      <section class="section path-success featured-services">
      <div class="container bring-to-front">
        <div class="row">
         <div class="section-title">
                <h2>Key Benefits of Akrahealth’s Solutions</h2>
            </div>
         </div>
        <div class="row gap-y align-items-center mb-2 justify-content-center">
           <div class="col-sm-4">
            <div class="rounded bg-contrast shadow-box p-3 d-flex align-items-center gap-2">
                <div class="w-20">
                  <img class="img-fluid" src="/images/svg/patient-engagement-communication.svg" alt="Enhanced Patient Engagement"  />
                </div>
                <div class="w-80">
                <h5 class="bold">Enhanced Patient Engagement</h5>
                <p class="mb-0">Personalized outreach to build stronger patient relationships.</p>
                </div>
            </div>
           </div>
           <div class="col-sm-5">
            <div class="rounded shadow-box p-3 d-flex align-items-center gap-2">
                <div class="w-20">
                  <img class="img-fluid" src="/images/svg/ai-suggestions.svg" alt="adfasdfadf"  />
                </div>
                <div class="w-80">
                <h5 class="bold">AI-Powered Tools for Smarter Healthcare</h5>
                <p class="mb-0">Intelligent diagnostics, virtual health assistants, and adaptive treatment plans.</p>
                </div>
            </div>
            </div>
        </div>
        <IconCard :items="[
              { title: 'Increased Patient Appointments & Retention',
                description: 'Data-driven strategies to maximize hospital bookings and telehealth consultations.',
                icon: '/images/svg/scheduling-appointment-management.svg'
              },
              {
                title: 'Stronger Medical Brand Recognition',
                description: 'Establish a trusted, authoritative presence in the healthcare sector.',
                icon: '/images/svg/personalized-patient-care.svg'
              },
              {
                title: 'Real-Time Data Insights',
                description: 'Monitor patient journeys, marketing performance, and operational efficiency with advanced analytics.',
                icon: '/images/svg/real-time-updates-alerts.svg'
              },
            ]" />
      </div>
    </section>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Seamless Integration & Scalable Healthcare Solutions</h2>
            </div>
            <IconCard :items="[
              { title: 'Seamless EMR & Telehealth Integration:',
                description: 'Connect Akrahealth with existing EMRs, patient portals, and healthcare management systems.',
                icon: '/images/svg/customizable-emr-templates.svg'
              },
              {
                title: 'Customizable Patient Experiences',
                description: 'Tailor patient journeys with adaptive scheduling, automated follow-ups, and personalized treatment reminders.',
                icon: '/images/svg/24-x-7-access.svg'
              },
              {
                title: 'Scalable Solutions for Growing Healthcare Firms, Hospitals & Clinics',
                description: 'Whether you\'re a small clinic or a multi-location hospital, Akrahealth grows with your needs.',
                icon: '/images/svg/efficient-communication.svg'
              },
              {
                title: 'Continuous Support & Compliance Guidance',
                description: 'Access HIPAA-compliant solutions, ongoing training, and expert support.',
                icon: '/images/svg/e-prescriptions-icon-erx.svg'
              }
            ]" />
        </div>
    </section>

    <!-- <Cta2 
    title="Struggling with Clinical Operations?" 
    description="Enhance patient care, streamline workflows, and empower your team with AI-driven clinical management. Experience seamless data integration, smart decision support, and boosted efficiency—all with the best EHR and EMR solutions for telemedicine and small practices."/> -->

  </AppLayout>
</template>
