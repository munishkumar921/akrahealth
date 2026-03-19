<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive, ref } from 'vue';
import ContactForm from '@/Partials/ContactForm.vue';
 import axios from 'axios'
defineProps({
    policy: String,

});
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
 
const recaptchaScript = document.createElement('script')
recaptchaScript.setAttribute('src', 'https://assets.calendly.com/assets/external/widget.js')
document.head.appendChild(recaptchaScript)
</script>
<template>
<AppLayout title="Enquire Now" description="Get in touch with us for pricing and personalized demo">

    <Head title="We are at your service. Get in touch with us to answer your queries." />
    <meta name="description" content="Interested in Akrahealth and want to know more about our healthcare services?  
We would be glad to give you a personalized demonstration of the website and solve all your queries. 
Schedule your free appointment with us now!">
    <header class="header section image-background cover overlay overlay-primary alpha-8 text-contrast" style="background-image: url('../../images/bg-banner.svg'); padding: 60px !important">
        <div class="container overflow-hidden">
            <div class="row">
                <div class="col-md-10 col-lg-8">
                    <h1 class="display-4 text-contrast bold">
                        Got questions on pricing?
                        <span class="d-block light"> </span>
                    </h1>
                    <p class="lead">
                        Get tailored pricing and answers to your questions.
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

        <div class=" align-items-center justify-content-center">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="section-title text-center col-md-10">
                        <h1 class="bold">
                            We’ll prepare a personalized plan for your practice.
                        </h1>
                        <p>Thinking about AkraHealth but want to know more about how it works and what it costs? We’d be happy to give you a one-on-one walkthrough, discuss pricing options, and show how we can help your practice run smoothly.</p>
                         <p class="font-bold">Tell us your needs — we’ll get back promptly.</p>
                    </div>

                    <div class="m-4 col-md-8">
                    <ContactForm />
                    </div>
                </div>
            </div>
        </div>
    </section>
</AppLayout>
<Footer />
</template>
