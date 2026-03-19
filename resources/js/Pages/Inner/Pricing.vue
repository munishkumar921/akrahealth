<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DemoRequest from '@/Pages/Modals/DemoRequest.vue'
 
/* ---------------- PROPS ---------------- */
const props = defineProps({
  policy: String,
  plans: {
    type: Array,
    required: true,
  },
})

/* ---------------- STATE ---------------- */
const billingCycle = ref('monthly')

/* ---------------- CUSTOM BILLING OPTIONS ---------------- */
const billingOptions = [
  { value: 'monthly', label: 'Monthly' },
  { value: 'yearly', label: 'Annual' },
  { value: 'custom', label: 'Custom' }
]

const currencySymbol = computed(() => {
  const hasINR = props.plans.some(p => p.currency === 'INR')
  return hasINR ? '₹' : '$'
})
 
 
/* ---------------- PLAN FINDERS ---------------- */
const getPlan = (title, frequency = 'monthly') => {
  return props.plans.find(
    p =>
      p.title?.toLowerCase() === title.toLowerCase() &&
      p.frequency === frequency
  )
}

/* ---------------- PLANS ---------------- */

const trailPlan = computed(() => getPlan('trial', billingCycle.value))
const starterPlan = computed(() =>
  getPlan('starter', billingCycle.value)
)

const growthPlan = computed(() =>
  getPlan('growth', billingCycle.value)
)

const proPlan = computed(() =>
  getPlan('pro', billingCycle.value)
)

/* ---------------- PRICES ---------------- */
const trailPrice = computed(() => trailPlan.value?.price ?? 0)
const starterPrice = computed(() => starterPlan.value?.price ?? 0)
const growthPrice = computed(() => growthPlan.value?.price ?? 0)
const proPrice = computed(() => proPlan.value?.price ?? 0)

/* ---------------- PLAN IDS ---------------- */
const trailPlanId = computed(() => trailPlan.value?.id ?? null)
const starterPlanId = computed(() => starterPlan.value?.id ?? null)
const growthPlanId = computed(() => growthPlan.value?.id ?? null)
const proPlanId = computed(() => proPlan.value?.id ?? null)

/* ---------------- UI TEXT ---------------- */
const billingPeriodText = computed(() =>
  billingCycle.value === 'monthly' ? '/ month' : '/ yearly'
)

/* ---------------- FEATURES PARSER ---------------- */
const parseFeatures = (featuresHtml) => {
  if (!featuresHtml) return []

  const temp = document.createElement('div')
  temp.innerHTML = featuresHtml

  return Array.from(temp.querySelectorAll('li')).map(li =>
    li.textContent.trim()
  )
}

const trailFeatures = computed(() =>
  parseFeatures(trailPlan.value?.features)
)
const starterFeatures = computed(() =>
  parseFeatures(starterPlan.value?.features)
)

const growthFeatures = computed(() =>
  parseFeatures(growthPlan.value?.features)
)

const proFeatures = computed(() =>
  parseFeatures(proPlan.value?.features)
)
const signup = () => {
    window.location.href = route('signup');
}
</script>



<template>
  <AppLayout title="Pricing" meta-title="Pricing | eRX | GoodRx - AKRAHEALTH"
    description="AKRAHEALTH Patient Portal Pricing Plans to access our EMR/EHR Services at plans starting from a FREE version to Basic and Pro suitable to your budget">
    <section>
      <div class="text home-bg-img_1 bg-size-cover bg-no-repeat overlay text-contrast"
        style="background-image: url('../../images/bg-banner.svg');">
        <div class="container">
          <div class="col-md-12">
            <div class="row gap-y align-items-center">
              <div class="col-md-6 text-contrast">
                <h2 class="bold text-contrast display-4">Affordable, Reliable Healthcare Solutions</h2>
                <p class="lead text-center text-md-start">
                  Quality care you can count on—at a price that works for you.
                </p>
              </div>
              <div class="col-md-6 col-lg-6">
                <div class="aos-init aos-animate d-sm-block" data-aos="fade-in">
                  <img src="images/pricing_banner.webp" class="img-responsive h-328" alt="">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="position-relative">
        <div class="shape-divider shape-divider-bottom shape-divider-fluid-x text-white-lilac">
          <svg viewBox="0 0 2880 48" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z"></path>
          </svg>
        </div>
      </div>
    </section>
    
    <section>
      <div id="content-page" class="bg-color-white-lilac">
        <div class="container-fluid">
          <div class="section-title text-center">
            <h1 class="bold">Smarter EHR. Transparent Pricing. Built for Small Practices.</h1>
            <h5 class="bold">All the power of an advanced EMR — without inflated costs. Start free, scale into advanced
              automation & AI as your practice grows.</h5>
            <button class="btn btn-primary me-2 my-2 my-sm-0" data-toggle="modal" data-target="#demo-request-modal">Book a Demo</button>

           
            <div class="d-flex justify-content-center align-items-center mt-4">
              <div class="btn-group" role="group" aria-label="Billing cycle options">
                <button 
                  v-for="option in billingOptions" 
                  :key="option.value"
                  type="button"
                  class="btn"
                  :class="billingCycle === option.value ? 'btn-primary' : 'btn-outline-primary'"
                  @click="billingCycle = option.value"
                >
                  {{ option.label }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <section class="section pricing" id="starter-pricing">
      <div class="container">
        <div class="row">
          <!-- trail Plan -->
          <div class="col-lg-3 col-md-6 mb-4" v-if="billingCycle !== 'custom' && trailPlanId">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Trail Plan<span class="text-danger">(14-day free trial)</span></h5>
                <p class="card-subtitle text-muted mb-3">For solo providers & new practices</p>
                
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ trailPrice }}</span>
                  <span class="text-muted">14 days</span>
                </div>
                
                <Link v-if="trailPlanId" :href="route('signup', { subscription_plan_id: trailPlanId })" 
                  class="btn btn-primary w-100 mb-3">
                  Start Free Trial
                </Link>
                <div v-else class="text-muted small mb-3">Not available in selected location</div>
                
                <p class="bold mb-2">Includes:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li v-for="(feature, index) in trailFeatures" :key="index" class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">{{ feature }}</span>
                  </li>
                </ul>
                <div class=" mt-auto">
                  <p class="text-muted">Limited Hippa Compliant</p>
                 </div>
              </div>
            </div>
          </div>
          
          <!-- Starter Plan -->
          <div class="col-lg-3 col-md-6 mb-4" v-if="billingCycle !== 'custom' && starterPlanId">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Starter Plan</h5>
                <p class="card-subtitle text-muted mb-3">For solo providers & new practices</p>
                
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ starterPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                
                <Link v-if="starterPlanId" :href="route('signup', { subscription_plan_id: starterPlanId })" 
                  class="btn btn-primary w-100 mb-3">
                  Subscribe Now
                 </Link>
                <div v-else class="text-muted small mb-3">Not available in selected location</div>
                
                <p class="bold mb-2">Includes:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li v-for="(feature, index) in starterFeatures" :key="index" class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">{{ feature }}</span>
                  </li>
                </ul>
                <div class=" mt-auto">
                  <p class="text-muted">Limited Hippa Compliant</p>
                 </div>
              </div>
            </div>
          </div>

          <!-- Growth Plan -->
          <div class="col-lg-3 col-md-6 mb-4" v-if="billingCycle !== 'custom' && growthPlanId">
            <div class="pricing-plan card shadow-box h-100 popular d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Growth</h5>
                <p class="card-subtitle mb-3 text-muted" style="min-height: 42px;">For growing practices (1-10 providers)</p>
                
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ growthPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                
                <Link v-if="growthPlanId" :href="route('signup', { subscription_plan_id: growthPlanId })" 
                  class="btn btn-primary w-100 mb-3">
                  Subscribe Now
                </Link>
                <div v-else class="text-muted small mb-3">Not available in selected location</div>
                
                <p class="bold mb-2">Includes everything in Starter, plus:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li v-for="(feature, index) in growthFeatures" :key="index" class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">{{ feature }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Pro Plan -->
          <div class="col-lg-3 col-md-6 mb-4" v-if="billingCycle !== 'custom' && proPlanId">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Pro</h5>
                <p class="card-subtitle mb-3 text-muted" style="min-height: 42px;">For advanced, automation-driven practices</p>
                
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ proPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                
                <Link v-if="proPlanId" :href="route('signup', { subscription_plan_id: proPlanId })" 
                  class="btn btn-primary w-100 mb-3">
                  Subscribe Now
                </Link>
                <div v-else class="text-muted small mb-3">Not available in selected location</div>
                
                <p class="bold mb-2">Includes everything in Growth, plus:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li v-for="(feature, index) in proFeatures" :key="index" class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">{{ feature }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- White Label Plan -->
           <div class="col-lg-3 col-md-6 mb-4" v-if="billingCycle === 'custom'">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">White Label</h5>
                 
                <div class="price my-3">
                  <span class="fs-4 bold">Custom Pricing</span>
                  <p class="small text-muted mb-0">(Contact Sales)</p>
                </div>
                
                <a href="#" data-toggle="modal" data-target="#demo-request-modal"
                  class="btn btn-primary w-100 mb-3">
                  Contact Sales
                </a>
                
                <p class="bold mb-2">Includes:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">White-label EMR & branding</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Dedicated private hosting</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">FHIR/HL7</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">SSO/SAML & enterprise-grade security</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Dedicated onboarding & SLAs</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Custom workflows & modules</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Information Section -->
        <div class="row mt-5">
          <div class="col-md-4 mb-4">
            <div class="card shadow-box h-100">
              <div class="card-body">
                <h5 class="bold">Add-Ons & Optional Services</h5>
                <ul class="list-unstyled mt-3 feature-list">
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-plus-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Managed RCM services</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-plus-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Dedicated onboarding & migration package</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-plus-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Custom automations & API integrations</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-plus-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Telehealth expansion options</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-plus-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">AI assistant add-ons (if requested)</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-4">
            <div class="card shadow-box h-100">
              <div class="card-body">
                <h5 class="bold">Discounts & Terms</h5>
                <ul class="list-unstyled mt-3 feature-list">
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-tags text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">14-day free trial (no card needed)</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-tags text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">12% annual prepay discount</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-tags text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Volume discounts (5% for 5+, 10% for 10+ providers)</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-tags text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Special pricing for nonprofit clinics</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 mb-4">
            <div class="card shadow-box h-100">
              <div class="card-body">
                <h5 class="bold">FAQ Highlights</h5>
                <ul class="list-unstyled mt-3 feature-list">
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-question-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Per-provider billing with no long-term contract required</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-question-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Optional managed billing services</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-question-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">1–4 week onboarding depending on migration</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
       
      </div>
    </section>
  </AppLayout>
  
  <DemoRequest />
</template>

<style scoped>
/* Ensure consistent card heights and feature list alignment */
.pricing-plan {
  transition: transform 0.3s ease;
}

.pricing-plan:hover {
  transform: translateY(-5px);
}

.pricing-plan .card-body {
  padding: 1.5rem;
}

/* Feature list styling with proper text wrapping */
.feature-list {
  overflow: hidden;
}

.feature-list li {
  display: flex;
  align-items: flex-start;
  line-height: 1.6;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.feature-list li i {
  flex-shrink: 0;
  width: 20px;
  margin-top: 0.25rem;
}

.feature-list li .feature-text {
  flex: 1;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
  max-width: 100%;
}

/* Ensure all subtitle areas have the same height */
.card-subtitle {
  min-height: 42px;
  display: flex;
  align-items: center;
}

/* Popular badge styling */
.pricing-plan.popular {
  border: 2px solid #007bff;
  position: relative;
}

.pricing-plan.popular::before {
  content: "Most Popular";
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: #007bff;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: bold;
  z-index: 1;
}

/* Prevent card overflow */
.pricing-plan .card-body {
  overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 991px) {
  .feature-list li .feature-text {
    font-size: 0.95rem;
  }
}

@media (max-width: 767px) {
  .pricing-plan .card-body {
    padding: 1.25rem;
  }
  
  .feature-list li .feature-text {
    font-size: 0.9rem;
  }
}
</style>