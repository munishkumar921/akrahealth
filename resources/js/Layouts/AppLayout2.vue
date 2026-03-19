<script setup>
import { watch, onMounted, onUnmounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import Header from '../Partials/signHeader.vue'
import Footer from '../Partials/signFooter.vue'

const props = defineProps({
    title: String,
    description: String,
    success: String,
    error: String,
    flash: Object,
    subscription: Object,
    subscriptionPlan: Object,
})

const page = usePage()

let senderTimeout = null
let senderInitialized = false

const senderAccountId = '1b7b4ea2a06741'
const formId = 'aM8Xrm'

const showSenderForm = () => {
    if (typeof window !== 'undefined' && typeof window.sender === 'function') {
        try {
            window.sender('showForm', formId)
        } catch (e) {
            console.error('Error showing Sender form:', e)
        }
    }
}

const initializeSender = () => {
    if (senderInitialized) return

    if (typeof window.sender === 'function') {
        window.sender(senderAccountId)
        senderInitialized = true

        // Show after 5 minutes
        senderTimeout = setTimeout(() => {
            showSenderForm()
        }, 500000) // 5 minutes
    }
}

const loadSenderScript = () => {
    if (typeof window === 'undefined') return

    const existingScript = document.querySelector('script[data-sender="sender-net"]')

    if (!existingScript) {
        const script = document.createElement('script')
        script.src = 'https://cdn.sender.net/accounts_resources/universal.js'
        script.async = true
        script.setAttribute('data-sender', 'sender-net')

        script.onload = () => {
            initializeSender()
        }

        document.head.appendChild(script)
    } else {
        initializeSender()
    }
}

onMounted(() => {
    loadSenderScript()
})

onUnmounted(() => {
    if (senderTimeout) {
        clearTimeout(senderTimeout)
    }
})

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast(flash.success, 'success', 2000)
        flash.success = null
    }
    if (flash?.error) {
        toast(flash.error, 'error', 2000)
        flash.error = null
    }
    if (flash?.warning) {
        toast(flash.warning, 'warning', 3000)
        flash.warning = null
    }
}, { deep: true })
</script>


<template>
    <div>

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
       
        <!-- Voice Widget -->
        <vapi-widget public-key="665d92ae-06a0-45c1-ba81-b7d8963d43dc"
            assistant-id="74424217-b805-46f5-be7d-934f0db110a0" mode="voice" theme="dark" base-bg-color="#FFFFFF"
            accent-color="#00B2FF" cta-button-color="#000000" cta-button-text-color="#ffffff" border-radius="medium"
            size="compact" position="bottom-right" title="Talk with our AI Assistant" start-button-text="Start"
            end-button-text="End Call" chat-first-message="Hey, How can I help you today?"
            chat-placeholder="Type your message..." voice-show-transcript="true" consent-required="true"
            consent-title="Terms and conditions"
            consent-content="By clicking Agree and each time I interact with this AI agent, I consent to the recording, storage, and sharing of my communications with third-party service providers, and as otherwise described in our Terms of Service."
            consent-storage-key="vapi_widget_consent"></vapi-widget>


        <!-- Chat Widget -->
        <vapi-widget public-key="665d92ae-06a0-45c1-ba81-b7d8963d43dc"
            assistant-id="fae0300f-1587-430f-bb67-057575829769" mode="chat" theme="dark" base-bg-color="#000000"
            accent-color="#00B2FF" cta-button-color="#000000" cta-button-text-color="#ffffff" border-radius="medium"
            size="compact" position="bottom-left" title="Chat with our AI Assistant" start-button-text="Start"
            end-button-text="End Call" chat-first-message="Hey, How can I help you today?"
            chat-placeholder="Type your message..." voice-show-transcript="true" consent-required="true"
            consent-title="Terms and conditions"
            consent-content="By clicking Agree and each time I interact with this AI agent, I consent to the recording, storage, and sharing of my communications with third-party service providers, and as otherwise described in our Terms of Service."
            consent-storage-key="vapi_widget_consent"></vapi-widget>
    </div>
</template>
