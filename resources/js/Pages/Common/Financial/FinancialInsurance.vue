<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import Table from "@/Components/Table/Table.vue";
import AuthLayout2 from "@/Layouts/AuthLayout2.vue";
import { Label } from "konva/lib/shapes/Label";
// ✅ Define props safely (can be array or paginated object)
const props = defineProps({
     keyword: String,  
     insurance:Object,   
});
const columns=[
    
    {label:'Insurance',key:"insurance"},
    {label:'Patient Seen',key:"patients_seen"}

]
</script>


<template>
    <AuthLayout2>
         <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Financial Insurance</h3>
        </div>
        <Table :columns="columns" :data="{data:insurance}" :search="keyword" />

    </AuthLayout2>
</template>