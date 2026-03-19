<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from "@inertiajs/vue3";
import { ref } from 'vue';
import { Autoplay, Pagination} from 'swiper';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';
import 'swiper/css/bundle';
defineProps({
    Swiper: String,
});
</script>
<template>
    <section id="testimonials" class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>TESTIMONIALS</h2>
                <p class="fs-15">Unmatched Customer Support: Empowering Healthcare Solutions That Speak Volumes
                    At Akrahealth, our customer support isn't just a service; it's a testament to our dedication. Your success is the reason behind everything we do, driving us to provide exceptional telemedicine and telehealth solutions. "AKRA TECH flawlessly delivered the project, comprehending our needs from the start and providing innovative solutions. The final product exceeded expectations, requiring no adjustments. We’re eager to partner with AKRA TECH again in the future."</p>
            </div>
            <Swiper :slidesPerView="3" :space-between="30" :modules=[Autoplay,Pagination]
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
                slidesPerView: 3,
                spaceBetween: 40
                },
                }"
                class="mySwiper">
                <SwiperSlide>
                    <div class="testimonial-item mb-4">
                        <p>
                            <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                            I worked with AKRA TECH to re-develop a previously started project, they
                            gave me a very
                            professional advice and I enjoyed working with a professional team to get my
                            work as I
                            wanted and in less time than , fast in reply and give you the feeling they
                            very keen to
                            perfect their job and exceed your expectations and requirements.
                            <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="images/docAvatar1.webp" class="testimonial-img" alt="">
                        <h3>DR HISHAM AL DABAH</h3>
                        <h4>Dubai,UAE</h4>
                    </div>
                </SwiperSlide>
                <SwiperSlide>
                    <div class="testimonial-item">
                        <p>
                            <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                            AKRA TECH prepared the project well under the time specified. He immediately
                            understood
                            the purpose of the project and provided relevant solutions in our
                            discussions. The final
                            product required no modifications and met the criteria for the project
                            perfectly. We are
                            very likely to return to AKRA TECH for future work.

                            <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="images/docAvatar2.webp" class="testimonial-img" alt="">
                        <h3>JOSEPH RICE</h3>
                        <h4>Sabaa.org, USA</h4>
                    </div>
                </SwiperSlide>
                <SwiperSlide>
                    <div class="testimonial-item h-auto">
                        <p class="h-250">
                            <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                            Glad to have found this contractor, up till jow splendid job done.This
                            contractor
                            debugged very quickly and correctly the problems which occurred. We will
                            re-contact him
                            if course when other necessities very satisfied with a job well done,
                            correct and
                            quickly solved.
                            <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="images/docAvatar1.webp" class="testimonial-img" alt="">
                        <h3>PAUL D </h3>
                        <h4>Fleuronguide, Belgium</h4>
                    </div>
                </SwiperSlide>
                <SwiperSlide>
                    <div class="testimonial-item ">
                        <p>
                            <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                            Extraordinary professional! It has been an honor to work with some-one of
                            his calibre.
                            He is deliberate with his project management tactics and implements with
                            great attention
                            to every detail. For me, one of his most important skill sets is his ability
                            to self
                            manage and keep our projects moving forward no matter my availability or the
                            lack
                            thereof.
                            <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                       <img src="images/docAvatar2.webp" class="testimonial-img" alt="">
                        <h3>ERIKA MARTINEZ</h3>
                        <h4>Denver Public School, USA</h4>
                    </div>
                </SwiperSlide>
            </Swiper>
        </div>
    </section>
</template>
