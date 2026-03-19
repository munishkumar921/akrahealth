<script setup>
import Search from "../../Common/Search.vue";

const props = defineProps({
    name: String,
    searchTerm: String,
});
</script>

<template>
    <section class="w-100 d-flex align-items-center gap-4">
        <h3 class="d-flex align-items-center pt-2">{{ name }}</h3>
        <Search searchTerm="searchTerm" />
    </section>
</template>
