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
  <AppLayout title="Training" 
   meta-title="AI-Powered Healthcare Training & Medical Exam Prep | SCORM & LMS Medical Learning Solutions"
    description="We create structured healthcare training courses, medical quizzes, and question banks from your videos, PDFs, and docs. Our team builds SCORM and LMS-ready content to help medical students and healthcare professionals prepare for exams and certifications.">
    <HeroSection2
    title="Healthcare Training & Medical Exam Prep Services" 
    description="Empowering healthcare firms, hospitals, and clinics with tailored marketing and technology solutions to enhance patient care, streamline operations, and build trust."
    bannerImg="/images/webpages/training.webp"
    >
   </HeroSection2>  

    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Create Engaging Healthcare Training Courses from Your Medical Content</h2>
                <p class="text-center">
                    You provide the raw materials—lectures, medical case studies, or PDFs—and our team does the work. We’ll structure them into engaging, LMS-ready courses with narration, chapters, and interactivity tailored for healthcare training.
              </p>
            </div>
            <IconboxCard :items="[
                { title: 'Convert PDFs, Docs, and Videos into Structured Medical eLearning', description: 'We transform your medical lectures, surgical training materials, and case studies into interactive, learner-friendly courses.', icon: '/images/svg/content-management-system.webp'},
                { title: 'Add Narration, Chapters, and Interactive Elements', description: 'Our experts enrich your healthcare courses with professional narration, case-based modules, and clinical engagement tools.', icon: '/images/svg/narration.webp'},
            ]"
             />
            </div>
        </section>


    <section class="section">
      <div class="container">
        <div class="row">
          <div class="section-title">
            <h2>AI-Generated Quizzes and Medical Question Banks for Smarter Learning</h2>
            <p class="text-center">
                We don’t just create courses—we also build AI-powered quizzes and medical question banks from your training resources.
              </p>
          </div>
        </div>
        <div class="row align-items-center gap-y">
          <div class="col-md-6">
            <ListItems :items="[
                { title: 'Automatically Create Assessments from Your Healthcare Content', description: 'Our AI analyzes your medical materials and generates multiple-choice, case-based, and short-answer clinical questions.'},
                { title: 'Build Reusable Question Banks in Our LMS', description: 'We upload and organize these into your LMS Question Bank so you can reuse them across multiple healthcare courses and programs.'},
                    ]" />
           </div>
           <div class="col-md-6">
            <div class="text-center">
             <img src="/images/webpages/create-assessment.png" alt="" />
            </div>
           </div> 
        

        </div>
      </div>
    </section>

     <section class="section">
      <div class="container">
        <div class="row">
          <div class="section-title">
            <h2>SCORM & LMS Integration for Seamless Healthcare Training</h2>
            <p class="text-center">
                We handle the technical side—publishing SCORM packages and integrating with our LMS for healthcare institutions.
              </p>
          </div>
        </div>
        <div class="row align-items-center gap-y">
          <div class="col-md-6 order-md-last">
            <div class="mb-3">
              <ListItems :items="[
                { title: 'Export SCORM 1.2/2004 for Any LMS', description: 'Our team ensures compatibility with industry-standard SCORM formats, so student and professional progress is always tracked.'},
                { title: 'Leverage Interactive Videos in Our LMS', description: 'We enrich your healthcare training videos with interactive overlays, hotspots, and quizzes—ideal for clinical scenarios.'},
                    ]" />
            </div>
          </div>
          <div class="col-md-6 position-relative">
            <div class="">
              <img class="img-fluid" src="/images/webpages/empower-healthcare.webp" alt="emr-online-chart" />
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section edge bottom-right emr-plateform">
            <div class="container bring-to-front emr-plateform">
              <div class="section-title">
                <h2>Exam Preparation & Certification Support for Medical Students and Professionals</h2>
                <p class="text-center">
                    We specialize in helping medical students and healthcare professionals prepare for key exams and certifications.
              </p>
            </div>
            <IconboxCard :items="[
                { title: 'Generate Mock Tests and Adaptive Quizzes', description: 'We build AI-driven practice tests and clinical simulations to reinforce key medical concepts and exam-style preparation.', icon: '/images/svg/adapt-quizz.jpg'},
                { title: 'Track Student Progress & Certification Readiness', description: 'Our LMS tracks performance, completion, and readiness so institutions know when students are prepared for licensing and certification exams.', icon: '/images/svg/progress-tracking.jpg'},
            ]"
             />
            </div>
        </section>

       <!-- Call to action section starts here -->
       <Cta 
       title="Why Choose Our Services for Healthcare Training?"
       >
       <template v-slot:button>
            <ul class="main-list mt-3">
              <p>We do all the heavy lifting: content structuring, quiz generation, SCORM publishing, LMS integration, and healthcare exam-prep setup. You focus on teaching and clinical expertise—we handle technology and AI.</p>
              <li>Medical Colleges & Nursing Schools</li>
              <li>Pharma & MedTech Companies</li>
              <li>Government & Public Health Agencies</li>
              <li>Continuing Medical Education (CME) Providers</li>
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
            <h2 class="bold">How Our Healthcare Training Service Works</h2>
          </div>
          </div>
          <div class="col-sm-6 justify-content-center">
            <div class="text-center">
             <img style="height:400px;width:450px;" src="/images/webpages/healthcare-training.png" alt="" />
            </div>
          </div>
          <div class="col-sm-6">
            <ListItems :items="[
                { 
                  title: 'Share Your Medical Training Materials', 
                  description: 'Send us your lectures, case studies, surgical demos, or medical documents.', 
                },
                { 
                  title: 'AI Processing & Quiz Generation', 
                  description: 'We process the content with AI to generate structured outlines, quizzes, and clinical scenario-based questions.', 
                },
                { 
                  title: ' Course Assembly & Packaging', 
                  description: 'Our team assembles everything into an LMS-ready healthcare course.', 
                },
                { 
                  title: 'Deployment to Our LMS', 
                  description: 'We upload the package into our LMS platform for you.', 
                },
                { 
                  title: ' Medical Exam Practice & Certification Prep', 
                  description: 'We provide AI-powered question banks and mock tests to prepare your students for healthcare exams and certifications.', 
                }
              ]" />

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
        <div class="row g-4 justify-content-center">
          <div class="col-sm-12 col-md-8">
          <div class="section-title">
            <h2 class="bold">Key Features of Our Healthcare Training Services</h2>
          </div>
          </div>
         
          <div class="col-sm-6 justify-content-center">
            <ul class="main-list mt-4">
              <li>Done-for-you healthcare course creation from lectures, surgical demos, and PDFs.</li>
              <li>AI-generated medical quizzes, clinical question banks, and assessments.</li>
              <li>SCORM 1.2/2004 export and LMS compatibility ensured by our team.</li>
              <li>Interactive video setup for clinical training and patient case studies.</li>
              <li>Centralized Medical Question Bank creation and organization.</li>
              <li>Student exam prep and certification readiness tools built-in.</li>
            </ul>

          </div>
          <div class="col-md-6">
            <div class="text-center">
             <img style="height:280px;width:450px;" src="/images/webpages/key-feature-healthcare.png" alt="" />
            </div>
           </div> 
          </div>
          </div>
    </section>
  
    <!-- <Cta2 
    title="Struggling with Clinical Operations?" 
    description="Enhance patient care, streamline workflows, and empower your team with AI-driven clinical management. Experience seamless data integration, smart decision support, and boosted efficiency—all with the best EHR and EMR solutions for telemedicine and small practices."/> -->

  </AppLayout>
</template>
