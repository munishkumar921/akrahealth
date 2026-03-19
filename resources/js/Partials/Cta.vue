<script setup>
import { nextTick, onMounted } from 'vue';
import ContactForm from '@/Partials/ContactForm.vue';


defineProps({
    title:String ,
    description: String,
})

onMounted(() => {
  nextTick(() => {
    if (typeof senderForms !== 'undefined') {
      senderForms.render();
    } else {
      window.addEventListener('SenderLoaded', () => {
        if (typeof senderForms !== 'undefined') {
          senderForms.render();
        } else {
          console.error('senderForms is still not defined');
        }
      });
    }
  });
});
</script>

<template>
    <section id="cta" class="cta p-0 gradient gradient-primary-dark">
        <div class="container">
            <div class="row">
            <div class="col-sm-6 order-1 order-md-0">
              <ContactForm />
            </div>
            <div class="col-sm-6">
                <div class="d-flex flex-column h-100 justify-content-center">
                    <h3 class="text-white bold text-center text-md-start">{{ title }}</h3>
                    <p class="font-l text-center text-md-start">{{ description }}</p>
                    <div class="w-100 text-center text-md-start">
                      <slot name="button"></slot>
                    </div>
                </div>
               </div>
            </div>
        </div>
    </section>
</template>
