<script setup>
import { computed } from 'vue';
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { useForm } from '@inertiajs/vue3';



const props = defineProps({
    releases: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    keyword: '',
});

const normalizedKeyword = computed(() => form.keyword?.toLowerCase()?.trim() || '');

const filteredReleases = computed(() => {
    if (!normalizedKeyword.value) return props.releases;
    return props.releases.filter(item =>
        (item?.text || '').toLowerCase().includes(normalizedKeyword.value) ||
        (item?.date || '').toLowerCase().includes(normalizedKeyword.value)
    );
});

const Search = () => {
    // Client-side filter is reactive via v-model; keep this
    // hook to align with other pages using Search()
    if (!normalizedKeyword.value) {
        toast('Please enter some keyword for search.');
    }
};
</script>
<template>
    <AuthLayout title="Coordination of Care" description="Manage patient care coordination and records"
        heading="Coordination of Care">

        <div class="text-center py-10 font-medium font-md">
            Coming soon...
        </div>
    </AuthLayout>
</template>
