<script setup>
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch, nextTick } from 'vue'
import 'emoji-picker-element'

const axios = window.axios;
const props = defineProps({
    conversationId: String
})

const userID = usePage().props?.auth?.user?.id;

defineOptions({
    compilerOptions: {
        isCustomElement: (tag) => tag === 'emoji-picker'
    }
})

const chatContainer = ref(null)
const messages = ref([])
const newMessage = ref('')
const showEmoji = ref(false)
const addEmoji = (event) => {
    newMessage.value += event.detail.unicode;
    showEmoji.value = false;
}

const fetchMessages = async () => {
    try {
        const res = await axios.get(`/chat/messages/${props.conversationId}`)
        messages.value = res.data.data.reverse()
    } catch (error) {
        console.error('Fetch messages failed:', error)
    }
}

const scrollToBottom = () => {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
}

function getFormattedDateTime() {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

const sendMessage = async () => {
    if (!newMessage.value) return
    await axios.post(`/chat/messages/${props.conversationId}`, {
        message: newMessage.value,
        local_time: getFormattedDateTime(),
    })

    newMessage.value = '';
    await nextTick()
    scrollToBottom()

    document.getElementById("chat-message").focus();
}

function getCurrentFormattedTime() {
    const date = new Date()

    const day = String(date.getDate()).padStart(2, '0')
    const month = date.toLocaleString('en-IN', { month: 'short' })
    const year = date.getFullYear()

    let hours = date.getHours()
    const minutes = String(date.getMinutes()).padStart(2, '0')
    const ampm = hours >= 12 ? 'PM' : 'AM'

    hours = hours % 12 || 12
    hours = String(hours).padStart(2, '0')

    return `${day} ${month}, ${year} ${hours}:${minutes} ${ampm}`
}

onMounted(async () => {
    try {
        await axios.get('/sanctum/csrf-cookie')
        await fetchMessages()
    } catch (error) {
        console.error('Chat init error:', error)
    }

    Echo.private(`chat.${props.conversationId}`)
        .listen('MessageSent', async (e) => {
            e.sender_id = e.sender.id
            e.send_at = getCurrentFormattedTime();
            messages.value.push(e);
            await nextTick()
            scrollToBottom();
        })
})

watch(messages, async () => {
    await nextTick()
    scrollToBottom()
})
</script>

<template>
    <div class="flex flex-col h-full">

        <div ref="chatContainer"
            class="flex-1 overflow-y-auto p-4 border bg-color-white-lilac  overflow-scroll hide-scroll-x"
            style="height:520px;">
            <template v-for="msg in messages" :key="msg.id">

                <div class="mb-2 bg-contrast p-2 rounded w-70 text-left"
                    :class="`${msg?.sender_id == $page.props?.auth?.user?.id ? 'float-end' : 'float-start'}`">
                    <p>{{ msg.message }}</p>
                    <small>{{ msg.send_at }}</small>
                </div>
                <div style="clear:both"></div>
            </template>
        </div>

        <!-- Emoji Picker -->
        <div v-if="showEmoji" class="absolute bottom-full mb-2  left-0 ml-4 mt-2d z-100">
            <component is="emoji-picker" @emoji-click="addEmoji"></component>
        </div>

        <div class="pt-2 flex justify-content-center">

            <!-- Emoji Button -->
            <button @click="showEmoji = !showEmoji" class="btn btn-light">
                😊
            </button>

            <input v-model="newMessage" id="chat-message" class="border p-2 form-control flex-1"
                :placeholder="`${showEmoji ? '' : 'Type...'}`" />
            <button @click="sendMessage" class="btn btn-primary ml-2">Send</button>

        </div>

    </div>
</template>
<style>
/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    /* IE & Edge */
    scrollbar-width: none;
    /* Firefox */
}

/* Thin scrollbar */
::-webkit-scrollbar {
    width: 1px;
}

::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 1px;
}

/* Hide horizontal scrollbar */
.hide-scroll-x {
    overflow-x: hidden !important;
}
</style>