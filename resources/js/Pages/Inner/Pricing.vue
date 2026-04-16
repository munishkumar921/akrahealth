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
const loadingPlanId = ref(null)

/* ---------------- CUSTOM BILLING OPTIONS ---------------- */
const billingOptions = [
  { value: 'monthly', label: 'Monthly' },
  { value: 'yearly', label: 'Annual' },
]

const currencySymbol = computed(() => {
  const hasINR = props.plans.some(p => p.currency === 'INR')
  return hasINR ? '₹' : '$'
})

/* ---------------- PLAN FINDERS ---------------- */
const getPlan = (title, frequency = 'monthly') => {
  return props.plans.find(
    p =>
      p.title?.toLowerCase().includes(title.toLowerCase()) &&
      p.frequency === frequency
  )
}

/* ---------------- PLANS ---------------- */
const trailPlan = computed(() => getPlan('trial', billingCycle.value))
const starterPlan = computed(() => getPlan('starter', billingCycle.value))
const growthPlan = computed(() => getPlan('growth', billingCycle.value))
const proPlan = computed(() => getPlan('pro', billingCycle.value))

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
  return Array.from(temp.querySelectorAll('li')).map(li => li.textContent.trim())
}

const trailFeatures = computed(() => parseFeatures(trailPlan.value?.features))
const starterFeatures = computed(() => parseFeatures(starterPlan.value?.features))
const growthFeatures = computed(() => parseFeatures(growthPlan.value?.features))
const proFeatures = computed(() => parseFeatures(proPlan.value?.features))

/* ---------------- RAZORPAY ---------------- */
const loadRazorpayScript = () => {
  return new Promise((resolve) => {
    if (window.Razorpay) return resolve(true)
    const script = document.createElement('script')
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.onload = () => resolve(true)
    script.onerror = () => resolve(false)
    document.head.appendChild(script)
  })
}

const subscribeWithRazorpay = async (planId, planTitle) => {
  if (!planId) return

  // If not logged in, redirect to signup with plan pre-selected
  // (We'll detect this from the 401 response below)

  loadingPlanId.value = planId

  const loaded = await loadRazorpayScript()
  if (!loaded) {
    alert('Failed to load payment gateway. Please try again.')
    loadingPlanId.value = null
    return
  }

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    // Step 1: Create Razorpay order via backend
    // Controller validates 'payment_method_id' as required, so we pass a placeholder
    // (actual payment method is chosen in Razorpay modal)
    const response = await fetch(`/subscriptions/subscribe/${planId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        payment_method_id: 'razorpay', // required by controller validation
      }),
    })

    // Not logged in → redirect to signup with plan pre-selected
    if (response.status === 401 || response.status === 403) {
      window.location.href = route('signup', { subscription_plan_id: planId })
      return
    }

    const data = await response.json()

    if (!data.success) {
      alert(data.message ?? 'Failed to create payment order. Please try again.')
      loadingPlanId.value = null
      return
    }

    // Step 2: Open Razorpay Checkout
    // Response shape from controller:
    // data.razorpayKey, data.razorpayOrder.id, data.razorpayOrder.amount,
    // data.razorpayOrder.currency, data.subscription.id
    const options = {
      key: data.razorpayKey,
      amount: data.razorpayOrder.amount,       // already in paise from Razorpay
      currency: data.razorpayOrder.currency,
      name: 'AKRAHEALTH',
      description: `${planTitle} Plan Subscription`,
      order_id: data.razorpayOrder.id,
      handler: async (paymentResponse) => {
        // Step 3: Verify payment
        await verifyPayment(data.subscription.id, paymentResponse, planId)
      },
      theme: { color: '#007bff' },
      modal: {
        ondismiss: () => {
          loadingPlanId.value = null
        },
      },
    }

    const rzp = new window.Razorpay(options)
    rzp.on('payment.failed', (response) => {
      alert(`Payment failed: ${response.error.description}`)
      loadingPlanId.value = null
    })
    rzp.open()

  } catch (error) {
    console.error('Payment error:', error)
    alert('Something went wrong. Please try again.')
    loadingPlanId.value = null
  }
}

const verifyPayment = async (subscriptionId, paymentResponse, planId) => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    const response = await fetch(`/subscriptions/verify-payment/${subscriptionId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        razorpay_payment_id: paymentResponse.razorpay_payment_id,
        razorpay_order_id: paymentResponse.razorpay_order_id,
        razorpay_signature: paymentResponse.razorpay_signature,
      }),
    })

    const result = await response.json()

    if (result.success) {
      // Controller returns: { success: true, message: '...', subscription: {...} }
      // Redirect to dashboard after successful payment
      window.location.href = '/dashboard'
    } else {
      alert(result.message ?? 'Payment verification failed. Please contact support.')
    }

  } catch (error) {
    console.error('Verification error:', error)
    alert('Payment verification failed. Please contact support.')
  } finally {
    loadingPlanId.value = null
  }
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
            <button class="btn btn-primary me-2 my-2 my-sm-0" data-toggle="modal" data-target="#demo-request-modal">Book
              a Demo</button>

            <div class="d-flex justify-content-center align-items-center mt-4">
              <div class="btn-group" role="group" aria-label="Billing cycle options">
                <button v-for="option in billingOptions" :key="option.value" type="button" class="btn"
                  :class="billingCycle === option.value ? 'btn-primary' : 'btn-outline-primary'"
                  @click="billingCycle = option.value">
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
        <div class="row justify-content-center">

          <!-- Starter Plan -->
          <div class="col-lg-4 col-md-6 mb-4" v-if="starterPlanId">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Starter Plan <span class="text-danger fs-15">(14-day free trial)</span></h5>
                <p class="card-subtitle text-muted mb-3">For solo providers & new practices</p>
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ starterPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                <span class="mb-2 d-block" v-if="billingCycle != 'monthly'">Pay for 11 months, get 12 months access..</span>
                <button class="btn btn-primary w-100 mb-3" :disabled="loadingPlanId === starterPlanId"
                  @click="subscribeWithRazorpay(starterPlanId, 'Starter')">
                  <span v-if="loadingPlanId === starterPlanId">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Processing...
                  </span>
                  <span v-else>Subscribe Now</span>
                </button>
                <p class="bold mb-2">Includes:</p>
                <ul class="list-unstyled flex-grow-1 feature-list">
                  <li v-for="(feature, index) in starterFeatures" :key="index" class="mb-2 d-flex align-items-start">
                    <i class="fas fa-check-circle text-success me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">{{ feature }}</span>

                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Coordination of Care</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Lab Results</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Clinical Orders</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Insurance Payor</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Medical Billing</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Lab Integration</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Pharmacy Integration</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Telehealth</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Resource management</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Financial management</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Order management</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Inventory</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Insurance and Claims</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Schedule Exceptions</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Referrals</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">CCDA,CCR</span>
                  </li>
                  <li class="mb-2 d-flex align-items-start">
                    <i class="fas fa-circle-xmark text-danger me-2 mt-1 flex-shrink-0"></i>
                    <span class="feature-text">Charts</span>
                  </li>
                </ul>
                <div class="mt-auto">
                  <p class="text-muted">Limited Hippa Compliant</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Growth Plan -->
          <div class="col-lg-4 col-md-6 mb-4" v-if="growthPlanId">
            <div class="pricing-plan card shadow-box h-100 popular d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Growth</h5>
                <p class="card-subtitle mb-3 text-muted" style="min-height: 42px;">For growing practices (1-10
                  providers)</p>
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ growthPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                <span class="mb-2 d-block" v-if="billingCycle != 'monthly'">Pay for 11 months, get 12 months access..</span>
                <button class="btn btn-primary w-100 mb-3" :disabled="loadingPlanId === growthPlanId"
                  @click="subscribeWithRazorpay(growthPlanId, 'Growth')">
                  <span v-if="loadingPlanId === growthPlanId">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Processing...
                  </span>
                  <span v-else>Subscribe Now</span>
                </button>
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
          <div class="col-lg-3 col-md-6 mb-4" v-if="proPlanId">
            <div class="pricing-plan card shadow-box h-100 d-flex flex-column">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title bold">Pro</h5>
                <p class="card-subtitle mb-3 text-muted" style="min-height: 42px;">For advanced, automation-driven
                  practices</p>
                <div class="price my-3">
                  <span class="fs-2 bold">{{ currencySymbol }}{{ proPrice }}</span>
                  <span class="text-muted">{{ billingPeriodText }}</span>
                </div>
                <button class="btn btn-primary w-100 mb-3" :disabled="loadingPlanId === proPlanId"
                  @click="subscribeWithRazorpay(proPlanId, 'Pro')">
                  <span v-if="loadingPlanId === proPlanId">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Processing...
                  </span>
                  <span v-else>Subscribe Now</span>
                </button>
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
        <section class="section">
          <div class="container bring-to-front con-box py-0 mb-3">
            <div class="row justify-content-center">
              <div class="col-md-10">
                <div class="shadow rounded text-center bg-primary p-5">
                  <div class="text-contrast">
                    <h2 class="mb-3 text-contrast bold fs-20">Build Your Own EMR Brand Without the Tech</h2>
                    <p class="font-size-16"> Launch a fully branded EMR with your logo, domain, and identity - powered
                      by AkraHealth’s secure and scalable platform.</p>
                  </div>
                  <!-- <button  data-toggle="modal" data-target="#demo-request-modal" class="btn btn-outline-light btn-rounded mt-2 px-4"> Launch Your Branded EMR</button> -->
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </section>
  </AppLayout>

  <DemoRequest />
</template>

<style scoped>
.pricing-plan {
  transition: transform 0.3s ease;
}

.pricing-plan:hover {
  transform: translateY(-5px);
}

.pricing-plan .card-body {
  padding: 1.5rem;
  overflow: hidden;
}

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

.card-subtitle {
  min-height: 42px;
  display: flex;
  align-items: center;
}

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