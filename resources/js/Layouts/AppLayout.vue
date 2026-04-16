<script setup>
import { watch, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Header from '../Partials/Header.vue';
import Footer from '../Partials/Footer.vue';

const props = defineProps({
    title: String,
    description: String,
    success: String,
    error: String,
});

const page = usePage();

const formId = 'aM8Xrm';

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast(flash.success, 'success', 2000);
        flash.success = null;
    }
    if (flash?.error) {
        toast(flash.error, 'error', 2000);
        flash.error = null;
    }
    if (flash?.warning) {
        toast(flash.warning, 'warning', 3000);
        flash.warning = null;
    }
}, { deep: true });

onMounted(() => {
    // Show the Sender form immediately on page load
    // sender() queue is available instantly via the inline snippet
    if (typeof window.sender === 'function') {
        window.sender('showForm', formId);
    }
});
</script>

<script>
// Official Sender.net initialization snippet — queues calls before script loads
(function (s, e, n, d, er) {
    s['Sender'] = er;
    s[er] = s[er] || function () {
        (s[er].q = s[er].q || []).push(arguments)
    }, s[er].l = 1 * new Date();
    var a = e.createElement(n),
        m = e.getElementsByTagName(n)[0];
    a.async = 1;
    a.src = d;
    m.parentNode.insertBefore(a, m)
})(window, document, 'script', 'https://cdn.sender.net/accounts_resources/universal.js', 'sender');

sender('1b7b4ea2a06741');

// Google Analytics
window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }
gtag('js', new Date());
gtag('config', 'G-YMJ62PV7BJ');
</script>

<template>
    <div>
        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description" />
        </Head>

        <Header />

        <div class="min-h-screen">

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="bg-white">
                <slot />
            </main>
        </div>

        <Footer />
    </div>
</template>