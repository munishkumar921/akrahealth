<script setup>
const props = defineProps({
    hideTitle: Boolean,
});
</script>

<template>

<div class="iq-light-logo">
    <img  src="/images/logo.webp"
            alt="Akrahealth Logo"
            draggable="false"
            style="height: 45px"
        />
    </div>
 <span class="text-capitalize">Akrahealth</span>


    <!-- <div class="d-flex justify-content-around">
        
        <span
            class="font-bold text-xl ml-2 text-dark"
            v-if="hideTitle"
            style="font-size: 25px"
        >
            
        </span>
    </div> -->
</template>
