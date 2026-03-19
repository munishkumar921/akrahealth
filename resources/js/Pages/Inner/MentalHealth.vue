<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import "swiper/css";
import "swiper/css/bundle";
import Cta from '@/Partials/Cta.vue';
import HeroSection2 from "@/Partials/HeroSection2.vue";
import ListItems from "@/Partials/ListItems.vue";

defineProps({
  policy: String,
});
</script>

<template>
  <AppLayout title="Mental Health EMR" meta-title="Mental Health EMR | Free AI-Powered EHR Platform"
    description="AkraHealth’s free Mental Health EMR boosts efficiency with AI automation, telehealth integration, and secure patient records for better care delivery.">
   <HeroSection2
    title="Experience Seamless Mental Health EMR Integration—For Free!" 
    description="Get your personalized electronic health record system for mental health for free. Make your workflow optimized through our user-friendly and secure platform."
    >
    <template v-slot:button>
      <a href="#" data-toggle="modal" data-target="#demo-request-modal" class="btn btn-light btn-lg btn-rounded px-5 bw-2 mt-2">Try it free</a> 
    </template>
   </HeroSection2>

    <section id="featured-services" class="featured-services">
        <div class="container aos-init aos-animate">
            <div class="section-title">
                <h2>Akrahealth Personalized EMR Solutions for Mental Health</h2>
                <p>Akrahealth's Mental Health EMR is a feature-rich, user-friendly solution to simplify patient care while making documentation richer and practice management seamless. Our AI-powered automation, secure patient records, integrated telehealth, and HIPAA/ONC compliant streamline the mental health professional's work environment into allowing efficient and high-quality care.
                We understand that each mental health practice is different. That's why we have completely customizable EMR solutions available, including white-labeling, where you can brand your very own platform. You can easily scale your practice without sacrificing professionalism or personal touch with your clients.</p>
            </div>
            <div class="row">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/encounter-management.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Encounter Management</h4>
                        <p class="description">Allow for efficient recording of therapy sessions, treatment plans, and progress notes by therapists and psychiatrists through an EMR designed for psychiatry, which provides a streamlined and organized workflow of mental health care.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/medication-management-e-prescription.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Medication Management & E-Prescription</h4>
                        <p class="description">This enable psychiatrists to e-prescribe and monitor psychiatric medication administrations and refills with considerations on adherence issues to enhance AI medical documentation and AI in health treatments.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/clinical-workflow-automation.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Clinical Workflow Automation</h4>
                        <p class="description">This is facilitated through AI EMR and electronic medical records software to ensure a smooth flow of information between providers, for better care coordination and continuity in mental health treatment.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/comprehensive-patient-history.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Comprehensive Patient History</h4>
                        <p class="description">This maintains extensive psychiatric records regarding the patient's past diagnoses, treatments, and progress that makes well-informed decisions by clinicians with AI medical records and advanced electronic health records software.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/alerts-reminders.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Alerts & Reminders</h4>
                        <p class="description">AI medical assistants help mental health professionals with follow-ups, medication schedules, and even therapy sessions for enhanced patient engagement and adherence to treatment plans.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/secure-patient-portal.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Secure Patient Portal</h4>
                        <p class="description">This offers patients a healthcare AI application for accessing their medical records, scheduling appointments, and communicating with providers, which assists in the facilitation of more patient engagement and successful outcomes in therapy within telemedicine EMR.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/scheduling-telehealth-integration.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Scheduling & Telehealth Integration</h4>
                        <p class="description">This allows easy scheduling of appointments, including AI telehealth and virtual therapy sessions, ensuring accessibility and convenience for both patients and providers in a healthcare EMR system.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
                    <div class="icon-box">
                        <div class="mb-2 justify-content-center">
                          <img src="/images/svg/billing-insurance-management_1.svg" alt="emr-online-chart" />
                        </div>
                        <h4 class="title">Billing & Insurance Management</h4>
                        <p class="description">This streamlines billing, claims processing, and insurance handling through AI in medical billing, optimizing financial workflows in healthcare EMR solutions for mental health providers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

          <!-- Call to action section starts here -->
        <Cta title=" Your Mental Health Practice, Supercharged with AI">
       <template v-slot:button>
         <ul class="main-list mt-3">
            <p>AkraHealth’s Mental Health EMR empowers therapists, psychiatrists, and counselors with AI-driven documentation, secure telehealth, and patient engagement tools — all in a fully customizable, HIPAA/ONC-compliant platform. Streamline sessions, manage medications, and personalize care without sacrificing quality or connection.</p>
            <li>AI-Powered Documentation & Automation</li>
            <li>Secure, HIPAA & ONC-Compliant Patient Portal</li>
            <li>Integrated Telehealth & Virtual Therapy</li>
            <li>Customizable Templates & White-Label Branding</li>
            <li>Seamless Billing & Insurance Management</li>
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
        <div class="row g-4 align-items-center text-center text-lg-start">
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

          <div class="col-md-6 text-center text-md-start">
            <h2 class="bold py-4 fs-20">Benefits of Using Akrahealth's Mental Health EMR Software</h2>
            <ListItems :items="[
              { title: 'Efficiency & Time-Saving', description: 'Reduce documentation time with AI-driven automation and pre-built templates.', icon: 'fa-check' },
              { title: 'Personalized Workflow', description: 'Adapt the system to your unique needs with tailored templates and automation tools.', icon: 'fa-check'},
              { title: 'Enhanced Patient Engagement', description: 'Improve communication and client experience with a modern, interactive portal.', icon: 'fa-check'},
              { title: 'Seamless Integration', description: 'Connect with third-party applications, billing systems, and mental health practice management software without problems.', icon: 'fa-check'},
              { title: 'Free Mental Health EMR Solutions', description: 'Obtain a fully customized EMR for free that makes it accessible to all providers.', icon: 'fa-check'}
            ]" />
          </div>
        </div>
      </div>
    </section>
    <!-- key Features section ends here -->

   

        <!-- <section class="section my-5">
            <div class="container bring-to-front con-box py-0">
              <div class="row justify-content-center">
                <div class="shadow rounded text-center bg-primary p-5 w-70">
                    <div class="text-contrast">
                        <h2 class="mb-3 text-contrast bold">Get Started with Akrahealth’s Mental Health EMR</h2>
                        <p class="font-size-16">Step into the next level of practice with the best EMR software for mental health. Whatever white-labeled system, AI-powered documentation assistant, or seamless telehealth integration, we have a solution for you.</p>
                        <p>Join the future of mental health electronic health records today!</p>
                    </div>
                  <Link  onclick="Calendly.initPopupWidget({url: 'https://calendly.com/akratech-consulting/new-meeting'});return false;" class="btn btn-outline-light btn-rounded mt-2 px-4">Start Your Free EMR Now</Link>
                </div>
              </div>
            </div>
        </section> -->





  </AppLayout>
</template>
