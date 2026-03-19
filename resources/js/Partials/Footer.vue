<script setup>
import { Link } from "@inertiajs/vue3";
import MvpModal from '@/Pages/Modals/GetMvp.vue';
 
</script>
<template>
  <div class="footer pt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4 w3agile_footer_grid">
              <Link :href="route('home')" class="logo mr-auto">
                <img class="img-fluid w-40" src="/images/logo_white.webp" alt="" />
                </Link>
              <p class="mt-3">
                We facilitate effortless access to electronic medical records, improving patient care and management while reducing both time and expenses. Our white-label solutions allow healthcare providers to tailor and brand our platform according to their specific requirements.</p>
              <div class="agileits_social_list">
                <li>
                  <a href="https://www.facebook.com/akrahealth" class="brand-facebook">
                    <i class="bx bxl-facebook" aria-hidden="true"></i>
                  </a>
                </li>
                <li>
                  <a href="https://www.linkedin.com/company/akrahealth/" class="brand-linkedin">
                    <i class="bx bxl-linkedin" aria-hidden="true"></i>
                  </a>
                </li>
                <!-- <li>
                  <a href="skype:akra.tech?call" class="brand-skype" id="skype-button">
                    <i class="bx bxl-skype" aria-hidden="true"></i>
                  </a>
                </li> -->
                <li>
                  <a href="https://www.youtube.com/@Akrahealth" class="brand-youtube" id="youtube-button">
                    <i class="bx bxl-youtube" aria-hidden="true"></i>
                  </a>
                </li>
                <li>
                  <a href="https://www.instagram.com/akrahealth/" class="brand-instagram" id="instagram-button">
                    <i class="bx bxl-instagram" aria-hidden="true"></i>
                  </a>
                </li>
                <li>
                  <a href="https://in.pinterest.com/akrahealth/" class="brand-pinterest" id="pinterest-button">
                    <i class="bx bxl-pinterest" aria-hidden="true"></i>
                  </a>
                </li>
              </div>
            </div>
            <div class="col-sm-2 col-md-3 col-xl-2 w3agile_footer_grid w3agile_footer_grid1 mt-5 mt-md-0">
                <h3>Practice</h3>
                <ul>
                  <li>
                    <Link :class="`${route().current('practice-management')?'text-cobalt-blue':''}`"  :href="route('practice-management')">Practice Management</Link>
                  </li>
                  <li>
                    <Link :class="`${route().current('clinical-management')?'text-cobalt-blue':''}`"  :href="route('clinical-management')">Clinical Management</Link>
                  </li>
                  <li>
                    <Link :class="`${route().current('medical-billing')?'text-cobalt-blue':''}`"  :href="route('medical-billing')">Medical Billing</Link>
                  </li>
                  <li>
                    <Link :class="`${route().current('patient-portal')?'text-cobalt-blue':''}`"  :href="route('patient-portal')">Patient Portal</Link>
                  </li>              
                  <li>
                    <Link :class="`${route().current('erx')?'text-cobalt-blue':''}`"  :href="route('erx')">Prescription (eRx)</Link>
                  </li>
                  <li>
                    <a  href="https://practice.akrahealth.com/" rel="noopener noreferrer">Lightweight EMR</a>
                  
                  </li>
                </ul>
              </div>
            <div class="col-sm-2 col-md-3 col-xl-2 w3agile_footer_grid w3agile_footer_grid1">
                <h3>Solutions</h3>
                <ul>
                  <li>
                    <Link :href="route('mental-health')">Mental Health</Link>
                  </li>
                  <li>
                    <Link :href="route('telemedicine')">Telemedicine</Link>
                  </li>
                  <li>
                    <Link :href="route('integration')">Integration</Link>
                  </li>
                  <!-- <li>
                    <Link :href="route('dental')">Dental</Link>
                  </li> -->
                  <li>
                    <Link :href="route('usecases')"> AI</Link>
                  </li>
                </ul>
              </div>
              <div class="col-md-3 col-xl-2 w3agile_footer_grid w3agile_footer_grid1" id="Registration">
                <h3>Get your Apps</h3>
                <ul>
                  <li>
                    <a  :class="route().current('clinic-management') ? 'text-cobalt-blue' : ''"href="https://practice.akrahealth.com/"  rel="noopener noreferrer"> Practice Management</a>
                 
                  </li>
                  <li>
                    <a  :class="route().current('patient-management') ? 'text-cobalt-blue' : ''"href="https://appointment.akrahealth.com/"  rel="noopener noreferrer">Automated Appointments</a>
                  
                  </li>
                  <li>
                    <a  :class="route().current('doctor-on-call') ? 'text-cobalt-blue' : ''"href="https://telehealth.akrahealth.com/" rel="noopener noreferrer">Telehealth Services</a>
                  
                  </li>
                  <li>
                    <a  :class="route().current('mobile-iv') ? 'text-cobalt-blue' : ''"href="https://mobileiv.akrahealth.com/"  rel="noopener noreferrer"> Mobile IV</a>
                 
                  </li>
                  <li>
                    <a  :class="route().current('prescription') ? 'text-cobalt-blue' : ''"href="https://prescription.akrahealth.com"  rel="noopener noreferrer"> Prescription Management</a>
                  
                  </li> 
                  <li>
                    <a  :class="route().current('mar') ? 'text-cobalt-blue' : ''"href="https://mar.akrahealth.com"  rel="noopener noreferrer">  Medication Administration Records</a>                
                  </li> 
                  <li>
                    <a  :class="route().current('wellness') ? 'text-cobalt-blue' : ''"href="https://wellness.akrahealth.com"  rel="noopener noreferrer">Wellness Service</a>                
                  </li>          
                </ul>
              </div>

              <div class="col-md-3 col-xl-2 w3agile_footer_grid w3agile_footer_grid1">
                <h3>Quick Links</h3>
                <ul>
                  <li>
                    <Link :class="`${route().current('contact-us')?'text-cobalt-blue':''}`" :href="route('contact-us')"> Contact Us</Link>
                  </li>
                  <li>
                    <a target="_blank" href="https://blog.akrahealth.com/">Blogs</a>
                  </li>

                  <li>
                    <Link :class="`${route().current('training')?'text-cobalt-blue':''}`" :href="route('training')">Training</Link>
                    
                  </li>
                  <li>
                     <Link :href="route('hire-us')">Hire EMR Integrators</Link>
                  </li>
                  <li>
                    <Link :class="`${route().current('pricing')?'text-cobalt-blue':''}`" :href="route('pricing')">Pricing</Link>
                  </li>
                  <li>
                    <Link :class="`${route().current('outreach-program')?'text-cobalt-blue':''}`" :href="route('outreach-program')">Outreach Program</Link>
                  </li>
                 
                </ul>
              </div>     
          <div class="clearfix"></div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <div class="align-items-center d-flex flex-column flex-md-row gap-0 gap-md-4 justify-content-md-center">
              <div class="align-items-center d-flex text-white">
                <a href="mailto:contact@akrahealth.com">
                <i class="bi bi-envelope-fill mr-1"></i>contact@akrahealth.com
                </a>
              </div>
              <div class="d-flex align-items-center text-white">
                <a href="https://wa.me/918015801639">
                <i class="fa fa-whatsapp mr-1"></i>+91 6381250184
                </a>
              </div>
              <div class="d-flex align-items-center text-white">
                <a  onclick="Calendly.initPopupWidget" href="https://calendly.com/akrahealth/new-meeting">
                  <i class="fa fa-calendar-plus-o mr-1"></i>Schedule a call
                </a>
              </div>
            </div>
        </div>
      </div>
      <div class="row agile_footer_copy">
        <div class="col-md-12">
            <ul class="d-flex gap-3 justify-content-center mb-0 fs-12">
                <li>
                    <Link :class="`${route().current('policy.show')?'text-cobalt-blue':''}`" :href="route('policy.show')">Privacy Policy</Link>
                </li>
                <div class="border border-l-2 border-black-500 h-4"></div>
                <li>
                    <Link :class="`${route().current('terms.show')?'text-cobalt-blue':''}`" :href="route('terms.show')">Terms & Conditions</Link>
                </li>
                <div class="border border-l-2 border-black-500 h-4"></div>
                <li>
                   <Link :class="`${route().current('pricing') ? 'active' : ''}`" :href="route('pricing')">Pricing</Link>
                </li>
                <div class="border border-l-2 border-black-500 h-4"></div>
                <li>
                   <Link :class="`${route().current('cancellation-refund-policy') ? 'active' : ''}`" :href="route('cancellation-refund-policy')">Cancellation & Refund Policy</Link>
                </li>
             </ul>

            <!-- <div class="text-center text-white">
              <div class="text-md-start center-flex">
                <span class="fa fa-envelope text-light" aria-hidden="true"></span>
                <a href="mailto:contact@akrahealth.com " class="text-white me-1 ms-2">contact@akrahealth.com</a>
                <div class="fa fa-phone ms-2 me-2 text-light" aria-hidden="true"></div>
                +91 6381250184
              </div>
            </div> -->
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
            <div class="text-center text-white">
              <p>&copy; 2018 - {{new Date().getFullYear() }} Akra Health. All rights reserved</p>
            </div>
        </div>
      </div>
    </div>
  </div>
<MvpModal />
</template>