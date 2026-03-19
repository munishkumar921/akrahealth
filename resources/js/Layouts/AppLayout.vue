<script setup>
import { ref, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Header from '../Partials/Header.vue';
import Footer from '../Partials/Footer.vue';


const props = defineProps({
    title: String,
    description: String,
    success: String,
    error: String,
});

const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash.success) {
        toast(flash.success, 'success', 2000);
    }
    if (flash.error) {
        toast(flash.error, 'error', 2000);
    }
    if (flash.warning) {
        toast(flash.warning, 'warning', 3000);
    }
}, { deep: true });

// Demo notification on page load
onMounted(() => {
    // Uncomment the line below to enable demo notification
    // toast('Welcome to AkraHealth!', 'info', 3000);

    // Client-only: load Sender.net widget and initialize with account ID
    if (typeof window !== 'undefined') {
        const senderAccountId = '1b7b4ea2a06741';
        const alreadyLoaded = !!window.sender || !!document.querySelector('script[data-sender="sender-net"]');
        if (!alreadyLoaded) {
            const s = document.createElement('script');
            s.async = true;
            s.src = 'https://cdn.sender.net/accounts_resources/universal.js';
            s.setAttribute('data-sender', 'sender-net');
            s.onload = () => {
                try {
                    if (typeof window.sender === 'function') {
                        window.sender(senderAccountId);
                    }
                } catch (e) {
                    // ignore initialization errors
                }
            };
            document.head.appendChild(s);
        } else {
            try {
                if (typeof window.sender === 'function') window.sender('1b7b4ea2a06741');
            } catch (e) { }
        }
    }
});

</script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }
gtag('js', new Date());
gtag('config', 'G-YMJ62PV7BJ');
</script>

<template>
    <div>
        <signHeader />

        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description" />
         </Head>

        <Header />

        <div class="min-h-screen ">

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
