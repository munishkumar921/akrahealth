<script setup>
import { ref } from "vue";

defineProps({
    title:String ,
    subtitle:String ,
    description: String,
    description2: String,
    description3: String,
    bannerImage: {
        default: "images/webpages/appss_1.webp",
    },
    bannerImg: Boolean
})
</script>

<template>
    <section class="header section">
      <div class="position-relative">
        <div class="shape-divider shape-divider-bottom shape-divider-fluid-x text-contrast">
          <svg viewBox="0 0 2880 48" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z"></path>
          </svg>
        </div>
      </div>
      <header class="header app-landing-2-header alter2-header">
        <div class="shapes-container">
          <div class="shape shape-animated aos-init aos-animate" data-aos="fade-down-right" data-aos-duration="1500"
            data-aos-delay="100"></div>
          <div class="shape shape-animated aos-init aos-animate" data-aos="fade-down" data-aos-duration="1000"
            data-aos-delay="100"></div>
          <div class="shape shape-animated aos-init aos-animate" data-aos="fade-up-left" data-aos-duration="500"
            data-aos-delay="200"></div>
          <div class="shape shape-animated aos-init" data-aos="fade-up" data-aos-duration="500" data-aos-delay="200">
          </div>

          <div class="animation-shape shape-triangle">
            <div class="animation--rotating"></div>
          </div>
          <div class="animation-shape shape-cross">
            <div class="animation--rotating"></div>
          </div>

          <div class="static-shape shape-ring shape-ring-1">
            <div class="animation animation--rotating"></div>
          </div>
          <div class="static-shape shape-ring shape-ring-2">
            <div class="animation animation--rotating-clockwise"></div>
          </div>

          <div class="static-shape pattern-dots-1"></div>
          <div class="static-shape pattern-dots-2"></div>

          <div class="static-shape background-shape-main"></div>
        </div>

        <div class="container">
          <div class="row align-items-center gap-y">
            <div class="col-md-6 pr-md-4">
              <h1 class="display-lg-4 mt-3 text-center text-md-start" style="line-height: 1.1;">
                <span class="bold">{{ title }}</span><br>
                <span class="bold fs-38">{{ subtitle }}</span>
              </h1>
              <p class="lead text-center text-md-start" v-html="description"></p>
              <p v-if="description2" class="lead text-center text-md-start" v-html="description2"></p>
              <p v-if="description3" class="lead text-center text-md-start" v-html="description3"></p>
              <div class="text-center text-md-start">
                <slot name="button"></slot>
              </div>
            </div>
            <div class="col-lg-6 col-md-9 mx-md-auto mx-lg-0 pe-lg-0 position-relative my-3">
              <img v-if="bannerImg" :src="bannerImg" alt="..." class="img-fluid shadow" style="border-radius: 15px;">
                <div v-if="!bannerImg" class="device-twin free-width align-items-center mt-4 mt-lg-0" >
                    <div class="browser shadow aos-init aos-animate" data-aos="fade-left" style="overflow: hidden;">
                        <img :src=bannerImage alt="..." class="img-fluid">
                    </div>
                </div>
           </div>
        </div>
      </div>
    </header>
  </section>
</template>


