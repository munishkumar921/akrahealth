<script setup>
import { nextTick, onMounted } from 'vue';


defineProps({
    title:String ,
    description: String,
})

</script>

<template>
    <section class="section mb-5">
            <div class="container bring-to-front con-box py-0">
              <div class="row justify-content-center">
                <div class="col-sm-12 col-md-10">
                <div class="shadow rounded text-center bg-primary p-5">
                    <div class="text-contrast">
                        <h2 class="mb-3 text-contrast bold fs-20">{{title}}</h2>
                        <p class="font-size-16">{{description}}</p>
                        <slot name="button"></slot>
                    </div>
                   <button href="https://akrahealth.com/signup" data-toggle="modal" data-target="#demo-request-modal"class="btn btn-light btn-lg btn-rounded px-4 mt-3">Start Free Trial</button>
                </div>
              </div>
              </div>
            </div>
      </section>
</template>
