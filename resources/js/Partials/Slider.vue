<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from "@inertiajs/vue3";
import { Autoplay, Pagination, Navigation, Parallax } from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/bundle';

</script>
<template>
    <section class=" ">
        <swiper :pagination="{
         clickable: true,
        }" :navigation="{
         clickable: true,
        }" :loop="true" :autoplay="{ delay: 6000, }" :modules=[Pagination,Parallax,Navigation,Autoplay] class="mySwiper  bg-size-cover bg-no-repeat home-bg-img_1 overlay ">
            <SwiperSlide>
                <div class="text">
                    <div class="container">
                        <div class=" col-md-12 pt-3">
                            <div class="row gap-y align-items-center mt-4 ms-md-5">
                                <div class="col-md-6 text-contrast">
                                    <h1 class="bold text-contrast">Sign Up for Free 
                                    </h1>
                                    <p class="bold pb-2 font-size-16">
                                        Access the Best EMR Software for Telehealth and Seamless Healthcare Management
                                    </p>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="aos-init aos-animate d-sm-block" data-aos="fade-end">
                                        <img src="images/home_slider_1.svg" class="img-responsive h-350"
                                            alt="our ultimate saas based emr system.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </SwiperSlide>
            <SwiperSlide>
                <div class="text">
                    <div class="container">
                        <div class="col-md-12">
                            <div class="row gap-y align-items-center mt-4 ms-md-5">
                                <div class="col-md-6 text-contrast">
                                    <h1 class="text-contrast bold">AkraHealth’s White-Label Solution</h1>
                                    <p class=" text-contrast bold font-size-16 pb-3">
                                        Brand Your EMR System for Your Practice
                                    </p>
                                </div>
                                <div class="col-md-6 col-lg-6">     
                                    <div class=" aos-init aos-animate d-sm-block " data-aos="fade-end">
                                        <img src="images/home_slider_2.svg" class="img-responsive h-350"
                                            alt="our ultimate saas based emr system">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </SwiperSlide>
            <SwiperSlide>
                <div class="title">
                    <div class="container">
                        <div class="col-md-12">
                            <div class="row gap-y align-items-center mt-4 ms-md-5">
                                <div class="col-md-6 text-contrast">   
                                    <h1 class="bold text-contrast">Tailored EMR Solutions</h1>
                                        <p class=" text-contrast bold font-size-16 pb-3">
                                            Customize EMR features to fit your unique healthcare needs and provide a personalized experience for your patients.
                                        </p>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <div class="aos-init aos-animate d-sm-block" data-aos="fade-end">
                                        <img src="images/home_slider_3.svg" class="img-responsive h-350"
                                            alt="our ultimate saas based emr system">
                                    </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </SwiperSlide>
        </swiper>
    </section>
</template>
