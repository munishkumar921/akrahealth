<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive, ref } from 'vue'
import BookDemo from '@/Partials/BookDemo.vue';
import axios from 'axios'
import ContactForm from '@/Partials/ContactForm.vue';

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const successMessage = ref('')
const errors = ref([])
const isSubmitting = ref(false)

const submitForm = async () => {
  errors.value = []
    isSubmitting.value = true
  try {
    const res = await axios.post('/contact-us/store', form)
    successMessage.value = res.data.message
    form.name = form.email = form.phone = form.subject = form.message = ''
  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = Object.values(err.response.data.errors).flat()
    }
  } finally {
    isSubmitting.value = false
  }
}


defineProps({
    policy: String,

});
const recaptchaScript = document.createElement('script')
recaptchaScript.setAttribute('src', 'https://assets.calendly.com/assets/external/widget.js')
document.head.appendChild(recaptchaScript)
</script>
<template>
<AppLayout title="Contact Us" meta-title="Contact Us | eRX | GoodRx - AKRAHEALTH"
    description="Get in touch with us for inquiries and support">

    <Head title="We are at your service. Get in touch with us to answer your queries." />
    <meta name="description" content="Interested in Akrahealth and want to know more about our healthcare services?  
We would be glad to give you a personalized demonstration of the website and solve all your queries. 
Schedule your free appointment with us now!">
    <header class="header section image-background cover overlay overlay-primary alpha-8 text-contrast" style="background-image: url('../../images/contact-us-banner.webp'); padding: 60px !important">
        <div class="container overflow-hidden">
            <div class="row">
                <div class="col-md-10 col-lg-8">
                    <h1 class="display-4 text-contrast bold">
                        Contact us
                        <span class="d-block light"> </span>
                    </h1>
                    <p class="lead">
                        Please do not hesitate to book a free 30-minute appointment to get your queries resolved.
                    </p>
                </div>
            </div>
        </div>
    </header>
    <div class="position-relative">
        <div class="shape-divider shape-divider-bottom shape-divider-fluid-x text-contrast">
            <svg viewBox="0 0 2880 48" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z"></path>
            </svg>
        </div>
    </div>
    <div id="fb-root"></div>
    <section class="section register  d-flex flex-column align-items-center justify-content-center ">

        <div class="align-items-center justify-content-center">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="section-title text-center col-md-10">
                        <h1 class="bold">
                            We are at your service. Get in touch with us to answer your queries.
                        </h1>
                        <p>Interested in Akrahealth and want to know more about our healthcare services?  
                            We would be glad to give you a personalized demonstration of the website and solve all your queries. 
                            Schedule your free appointment with us now!
                        </p>
                         <BookDemo />
                    </div>

                    <div class="contact-top-agileits text-center">

                        <h5>We are delighted to provide you with our Healthcare services and are even pleased to join with
                            our
                            users around the globe. We are here to provide Telemedicine and Telehealth Services at your
                            fingertips!
                        </h5>
                        <div class="clearfix"></div>

                    </div>

                    <div class=" col-md-6">
                        <div class="card">
 
                            <ContactForm />
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</AppLayout>
<Footer />
</template>
