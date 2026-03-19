<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Cta from '@/Partials/Cta.vue';
import Cta2 from '@/Partials/Cta2.vue'
import HeroSection from '@/Partials/HeroSection.vue';
import { Autoplay, Pagination, Navigation, Virtual } from "swiper";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/bundle";
import ListItems from '@/Partials/ListItems.vue';

defineProps({
  policy: String,
  SwiperSlide: String,
  Swiper: String,
});
</script>

<template>
  <AppLayout title="Pharmacy App" meta-title="Pharmacy App with EMR | E-Prescriptions & Tracking"
     description="AkraHealth’s Pharmacy App offers HIPAA-compliant e-prescriptions, real-time tracking, and Rx API integration for faster, safer medication management.">
    <HeroSection
      title="Effortless e-Prescriptions & Medication Management"
      description="Streamline pharmacy operations with Akrahealth’s secure, HIPAA-compliant platform. Send e-prescriptions instantly and ensure fast, hassle-free medication fulfillment."
      >
     <template v-slot:button>
      <h4 class="text-primary">Sign up for FREE & simplify patient care!</h4>
     </template>
     <template v-slot:images>
      <div class="device-twin m-auto align-items-center">
          <div class="mockup absolute aos-init aos-animate" data-aos="fade-left">
            <div class="screen">
              <img src="/images/app-one.webp" alt="app-one" />
            </div>
            <span class="button"></span>
          </div>

          <div class="iphone-x light front me-0">
            <div class="screen shadow-box">
              <img src="/images/app-two.webp" alt="app-two" />
            </div>
            <div class="notch"></div>
          </div>
        </div>
     </template>
      </HeroSection>

    <section class="quick-start-your-business mb-lg-n3 ">
      <div class="container con-box mb--4">
        <div class="row justify-content-center">
        <div class="col-sm-12 col-lg-10 order-md-2 ms-4 mb-3">
          <p class="text-center">
            Apart from our EMR Platform, we provide users with our Pharmacy App
            where they can easily place an order for their medicines by
            uploading prescriptions wherein each medication is smartly detected
            and accurately conveyed to the pharmacies through our Rx APIs. Users
            can place orders and track their status live through our Geolocation
            feature. Pharmacies and Labs can join Akrahealth and register.
            Access and get listed on our list of pharmacies for users to connect with
            you and expand your reach.
          </p>
        </div>
      </div>
      </div>
    </section>
    <section class="section screenshots coverflow">
      <div class="container">
        <Swiper class="swiper-container swiper-container-initialized swiper-container-horizontal" :showItems="1"
          :centeredSlides="true" :spaceBetween="10" :swiperInstance="{ object, Object }" :pagination="{
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
        }"
        :modules="[Autoplay,Navigation,Virtual]" navigation :loop="true" :autoplay="{ delay: 2500 }">
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-two.webp" style="width: 250.6px" alt="app-two.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-three.webp" style="width: 250.6px" alt="app-three.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-four.webp" style="width: 250.6px" alt="app-four.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-five.webp" style="width: 250.6px" alt="app-five.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-six.webp" style="width: 250.6px" alt="app-six.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-one.webp" style="width: 250.6px" alt="app-one" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-two.webp" style="width: 250.6px" alt="app-two.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-three.webp" style="width: 250.6px" alt="app-three.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-four.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-four.webp" />
          </SwiperSlide>

          <SwiperSlide>
            <img class="img-responsive" src="/images/app-five.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-five.webp" />
          </SwiperSlide>
          <SwiperSlide>
            <img class="img-responsive" src="/images/app-six.webp" style="width: 250.6px; margin-right: 20px"
              alt="app-six.webp" />
          </SwiperSlide>
        </Swiper>
      </div>
    </section>
    <section class="section">
      <div class="container con-box">
        <div class="row justify-content-center">
              <div class="col-sm-10">
                <div class="section-title">
                    <h2> Upload Prescriptions. Order Medicines from trusted Pharmacies. Live Track your medicines</h2>
                </div>
             </div>
            </div>

        <ul class="nav nav-tabs nav-outlined nav-rounded justify-content-around justify-content-md-center mb-5 custom_tabs" role="tablist">
            <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#upload-prescription">Upload Prescription</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#categorize-medicines">Categorize Medicines</a>
            </li>
           <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#share-medicines">Share Medicines</a>
           </li>
           <li class="nav-item">
             <a class="nav-link" data-toggle="tab" href="#accurate-address-capturing">Accurate Address Capturing</a>
           </li>
        </ul>

        <div class="tab-content pt-4">
          <div class="tab-pane active" id="upload-prescription">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 me-lg-auto position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/app-one.webp" alt="app-one.webp" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/pharmacy-upload.webp" alt="Upload Prescription" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="bold fs-20">Upload Prescription</h2>
                <p>
                  In our Pharmacy app, users can upload medical prescriptions
                  and also access them at their own convenience. Details like
                  contact number, date, and prescription details can be added by
                  the user. We have also provided an option for uploading images
                  of the prescription and this is found to be very beneficial
                  for both the users and the healthcare providers. This feature
                  helps in the proper clinical management of each patient.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="categorize-medicines">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 order-md-2 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/app-one.webp" alt="app-one.webp" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/pharmacy-filter.webp" alt="pharmacy-fitter" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <h2 class="bold">Categorize Medicines</h2>
                <p>
                  We have added filters and sorting options so as to filter
                  medicines based on various parameters. Users can search for
                  medicines & drugs and add them to the telehealth cart. The
                  search filters have been designed in such a way as to enhance
                  the user experience & user engagement that is supported by our
                  Rx APIs. We have added this feature as a part of our
                  pharmaceutical services consultancies.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="share-medicines">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 col-lg-6 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>
                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/app-one.webp" alt="app-one.webp" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/pharmacy-favorit.webp" alt="pharmacy-favorit" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <h2 class="bold">Share Medicines</h2>
                <p>
                  Medicines can be added to the cart according to the user’s
                  preference. Users can also mark medicines as their favorite.
                  Another useful feature of this module is that users can share
                  medicine details. This share option has benefited several
                  users and healthcare providers.
                </p>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="accurate-address-capturing">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 order-md-2 position-relative">
                <div class="center-xy op-1">
                  <div class="shape shape-background rounded-circle shadow-lg bg-info aos-init"
                    style="width: 600px; height: 600px" data-aos="zoom-in"></div>
                </div>

                <div class="device-twin mx-auto align-items-center">
                  <div class="mockup absolute aos-init aos-animate" data-aos=" fade-left ">
                    <div class="screen">
                      <img src="/images/app-one.webp" alt="app-one.webp" />
                    </div>
                    <span class="button"></span>
                  </div>

                  <div class="iphone-x front me-0">
                    <div class="screen shadow-box">
                      <img src="/images/app-six.webp" alt="Geolocation" />
                    </div>
                    <div class="notch"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h2 class="bold">Accurate Address Capturing</h2>
                <p>
                  Addresses can be easily tracked and located using our latest
                  geolocation feature. We have also taken care of the accuracy
                  verification of this Geolocation module & made sure that is it
                  error-free. This feature also enhances medical device
                  connectivity and reaches out to patients who live in remote
                  areas.
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
                  <img src="/images/place-an-order.webp" alt="place-an-order" />
                </div>
                <span class="button"></span>
              </div>

              <div class="iphone-x front ms-0">
                <div class="screen shadow-box">
                  <img src="/images/place-an-order-front.webp" alt="place-an-order-front" />
                </div>
                <div class="notch"></div>
              </div>
            </div>
          </div>

          <div class="col-md-6 text-center text-md-start">
            <ul class="list-unstyled">
              <li class="d-flex flex-column flex-md-row align-items-start">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-activity stroke-primary">
                    <path
                      d="M15.05 5A5 5 0 0 1 19 8.95M15.05 1A9 9 0 0 1 23 8.94m-1 7.98v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                    </path>
                  </svg>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Call to place an Order</h5>
                  <p class="m-0">
                    Users can call and place orders for medicines using the
                    “Call to order” feature in our platform. Instant medicine
                    delivery options are also an integral part of this feature.
                    Users can receive medicine resources by just making a phone
                    call which saves time.
                  </p>
                </div>
              </li>
              <li class="d-flex flex-column flex-md-row align-items-start mt-5">
                <div class="bg-light p-3 rounded d-flex align-items-center justify-content-center mx-auto me-md-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-activity stroke-primary">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                </div>

                <div class="media-body mt-3 mt-md-0">
                  <h5 class="bold mt-0 mb-1">Order Tracking</h5>
                  <p class="m-0">
                    Users awaiting the delivery of medicines can easily track
                    the location of their medicine package. Different diagnosis
                    codes are used to help in the quick tracking of these
                    medicines.
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
                  <h1 class="mt-2"></h1>
                  <p>
                    Try out our Pharmacy App features by simply booking a
                    <span class="bold">
                      <a class="bold underline" style="cursor: pointer">
                        FREE DEMO
                      </a>
                    </span>
                    with us.
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
       title="Deliver Medications Faster, Smarter & More Securely">
       <template v-slot:button>
            <ul class="main-list mt-3">
              <p>From e-prescriptions to doorstep delivery, AkraHealth’s Pharmacy App streamlines the entire medication process — improving accuracy, reducing delays, and enhancing patient satisfaction.</p>
              <li>HIPAA-Compliant E-Prescriptions</li>
              <li>Real-Time Order Tracking & Geo-Location</li>
              <li>Automated Refill Reminders</li>
              <li>Doctor–Pharmacist Secure Collaboration</li>
              <li>Integrated Rx APIs for Accurate Medicine Detection</li>
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
            <h2 class="bold fs-20">
                How our Telehealth Services are beneficial to users
              </h2>
              <p class="mb-2">Our Pharmacy app ensures that all the necessary features are
                detailed in the app for users to have an engaging & time-saving
                experience. The ways through which our pharmacy/telehealth
                services have helped users are:
              </p>
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
                      Separate accounts for Patients & Doctors to experience
                      features related to their roles
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
                      Either manually search for medicines prescribed or upload
                      a medical prescription that is precisely scanned to view
                      the right medicines
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
                      Various options to filter & sort the medicines from
                      selecting categories to medicine costs, all required
                      options to get the best list of medicines
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
                      Integration of Rx APIs to support search functionality for
                      better patient engagement & display the most preferred
                      medicines
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
                    <p class="my-0">Real-time data on medicines is generated</p>
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
                      Share medicines & also add them to favorites for later use
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
                      Order medicines that are cost-effective & securely located
                      through Geo-location
                    </p>
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <div class="col-md-6 position-relative">
            <div class="iphone-x">
              <div class="screen shadow-box">
                <img src="/images/app-six.webp" alt="app-six.webp" />
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

          <div class="col-md-6 text-md-start">
            <h2 class="bold py-4 text-center text-md-start fs-20">Key Features</h2>
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

    <section class="section path-success">
      <div class="container bring-to-front">
        <div class="row gap-y align-items-center">
              <div class="col-md-6">
                <h2 class="bold fs-20">Why Choose Our Pharmacy App?</h2>
                <ul class="main-list">
                  <li>Secure, HIPAA-compliant e-prescription processing</li>
                  <li>Enhanced medication adherence with automated reminders</li>
                  <li>Real-time tracking of prescriptions and refills</li>
                  <li>Seamless communication between doctors and pharmacists</li>
                  <li>Simplified pharmacy selection for patients</li>
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

  <!-- Faq section starts here -->
  <section id="faq" class="faq section-bg">
        <div class="container">
            <div class="section-title">
                <h2>Frequently Asked Questions</h2>
                <p>Not sure how this platform can help you? Wonder why you need to use our services?<br>
                    Here are the
                    answers to some of the most common questions we hear from our appreciated customers.
                </p>
            </div>
            <ul class="faq-list">
                <li>
                    <div data-toggle="collapse" class="collapsed question" href="#faq1">Can doctors send e-prescriptions directly to pharmacies?
                        <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i>
                    </div>
                    <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                        <p>
                          Yes, our platform allows doctors to issue electronic prescriptions, which are securely sent to pharmacies for seamless medication fulfillment.
                        </p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq2" class="collapsed question">Can Akrahealth be customized for specific specialties?
                        <i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                        <p>
                            Absolutely! Akrahealth customizes for most specialties, like EMR for small practices, best EMR for telemedicine, and best EMR for small practices, making sure your practice gets exactly what it needs. Whether you want free EMR software, cloud-based EMR solutions, or telehealth EMR integration, Akrahealth can deliver tailored electronic medical records software solutions for your unique requirements.
                        </p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq3" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">How does the Pharmacy App improve patient medication adherence?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq3" class="collapse">
                        <p>Doctors can track prescription fulfillment, receive alerts for missed refills, and collaborate with pharmacists to ensure patients follow their treatment plans.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq4" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Can doctors recommend specific pharmacies to patients?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq4" class="collapse">
                        <p>Yes, doctors can suggest nearby or preferred pharmacies, ensuring convenient access to prescribed medications.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq5" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Is the platform compliant with healthcare regulations?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq5" class="collapse">
                        <p>Absolutely! Our Pharmacy App complies with HIPAA and other healthcare standards, ensuring secure and compliant prescription handling.</p>
                    </div>
                </li>
                <li>
                    <div data-toggle="collapse" href="#faq6" class="collapsed question" role="button"
                        aria-expanded="false" aria-controls="collapseExample">Can doctors communicate with pharmacists through the app?<i class="bi bi-chevron-down icon-show"></i><i
                            class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq6" class="collapse">
                        <p>Yes, our integrated chat feature enables direct communication between doctors and pharmacists for medication clarifications and patient care coordination.</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>


    <!-- <Cta2 
    title="Experience our pharmacy management solutions with a Complimentary Consultation."  /> -->

  </AppLayout>
</template>
