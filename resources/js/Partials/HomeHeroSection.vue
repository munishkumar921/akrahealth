<script setup>
import { ref } from "vue";

defineProps({
    title:String ,
    description: String,
    bannerImage: String
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
                <!-- ✅ LAUNCH OFFER BANNER -->
                <div class="launch-offer-wrapper text-center text-md-start mb-3">
                    <button
                        class="launch-offer-banner"
                        data-toggle="modal"
                        data-target="#lighthweight-emr-request-modal"
                    >                     
                          <span class="offer-text">
                            

                            <span class="offer-inline">
                              <span class="offer-tag">New</span>
                              <span class="offer-sub"><strong>LAUNCH OFFER!</strong><br>Lightweight EMR for Growing Practices ✨</span>
                              <span class="offer-cta">Click here</span>
                            </span>
                          </span>
                    </button>
                </div>
                <slot name="title">
                <h1 class="display-lg-4 mt-3 text-center text-md-start">
                  <span class="bold">{{ title }}</span>
                </h1>
              </slot>
              <p class="lead text-center text-md-start mt-4">{{ description }}</p>
              <div class="text-center text-md-start">
                <slot name="button"></slot>
              </div>
            </div>
            <div class="col-md-6">
              <div class="text-center">
                <img class="img-fluid" style="max-width: 600px;" v-if="bannerImage" :src=bannerImage alt="...">
                <slot name="images" />
              </div>
            </div>
          </div>
        </div>
      </header>
    </section>
</template>
<style scoped>
   /* Wrapper */
.launch-offer-wrapper {
  display: flex;
  justify-content: center;
}

@media (min-width: 768px) {
  .launch-offer-wrapper {
    justify-content: flex-start;
  }
}

/* Button Base */
.launch-offer-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 22px;
  border-radius: 44px;
  background: linear-gradient(180deg, #ffffff 0%, #eaf7ff 100%);
  border: 2px solid #06c270;
  cursor: pointer;
  position: relative;
  max-width: 100%;
  transition: all 0.35s ease;
  box-shadow:
    0 0 0 3px rgba(110, 193, 255, 0.25),
    0 12px 28px rgba(0, 136, 255, 0.22);
  overflow: hidden;
  width: 556px;
}

/* Shine overlay */
.launch-offer-banner::before {
  content: "";
  position: absolute;
  top: -50%;
  left: -60%;
  width: 40%;
  height: 200%;
  background: linear-gradient(
    120deg,
    transparent,
    rgba(255, 255, 255, 0.7),
    transparent
  );
  transform: rotate(25deg);
  transition: left 0.6s ease;
}

/* Hover Shine Movement */
.launch-offer-banner:hover::before {
  left: 120%;
}

/* Hover Effects */
.launch-offer-banner:hover {
  transform: translateY(-4px) scale(1.02);
  background: linear-gradient(180deg, #f6fbff 0%, #dff2ff 100%);
  border-color: #06c270;
  box-shadow:
    0 0 0 4px rgba(63, 255, 79, 0.35),
    0 20px 45px rgba(0, 255, 34, 0.35);
}

/* Active (Click) */
.launch-offer-banner:active {
  transform: translateY(-1px) scale(0.99);
  box-shadow:
    0 0 0 2px rgba(63, 255, 79, 0.25),
    0 10px 20px rgba(0, 255, 55, 0.25);
}



/* Icon animation on hover */
.launch-offer-banner:hover .offer-icon {
  transform: rotate(-10deg) scale(1.1);
}

/* Text */
.offer-text {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
  margin-left: 10px;
}

.offer-text strong {
  font-size: 18px;
  font-weight: 800;
  color: #000;
}

.offer-sub {
  font-size: 16px;
  font-weight: 600;
  color: #0a6ebd;
  margin-left: 20px;
}

/* Inline row: NEW + text + CTA */
.offer-inline {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 4px;
  flex-wrap: nowrap;
}

/* Small "New" tag */
.offer-tag {
  background: #ffcc33;
  color: #000;
  font-size: 14px;
  font-weight: 800;
  padding: 7px 15px;
  border-radius: 14px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.25);
}

/* CTA button */
.offer-cta {
  padding: 8px 15px;
  background: linear-gradient(135deg, #0ec927, #06c270);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  border-radius: 16px;
  white-space: nowrap;
  box-shadow: 0 4px 10px rgba(57, 230, 94, 0.35);
  transition: all 0.25s ease;
}

/* Hover sync */
.launch-offer-banner:hover .offer-cta {
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(79, 199, 75, 0.45);
}


/* Mobile */
@media (max-width: 576px) {
  .launch-offer-banner {
    width: 100%;
    justify-content: center;
  }

  .offer-text {
    text-align: center;
    align-items: center;
  }
   .offer-row {
    flex-wrap: wrap;
    justify-content: center;
  }
   .offer-inline {
    flex-wrap: wrap;
    justify-content: center;
  }
}

</style>

