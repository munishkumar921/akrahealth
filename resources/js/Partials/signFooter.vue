<script setup>
import { Link } from "@inertiajs/vue3";
import MvpModal from '@/Pages/Modals/GetMvp.vue';
 
</script>
<template>
  <div class="footer pt-5">
    <div class="container">
      
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
