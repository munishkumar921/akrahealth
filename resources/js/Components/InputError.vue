<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        
        <p class="text-sm text-danger font-italic">
        {{ message }}
</p>
        <!-- <p class="text-sm text-red-600 font-italic Demo-btn">
            {{ message }}
        </p> -->
    </div>
    
</template>
