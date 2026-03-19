<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["send"]);

const message = ref("");

const canSend = computed(() => !props.disabled && Boolean(message.value.trim()));

const sendMessage = () => {
    const value = message.value.trim();
    if (!value || props.disabled) return;

    emit("send", value);
    message.value = "";
};
</script>

<template>
    <div class="footer-chat d-flex align-items-center p-3 border-top bg-white">
        <i class="icon fa fa-smile-o clickable me-3" aria-hidden="true"></i>
        <input
            v-model="message"
            type="text"
            class="form-control write-message me-3"
            placeholder="Type your message here"
            :disabled="disabled"
            @keydown.enter="sendMessage"
        />
        <button
            type="button"
            class="icon send fa fa-paper-plane-o clickable text-white bg-primary p-2 rounded-circle border-0"
            :disabled="!canSend"
            @click="sendMessage"
            aria-label="Send message"
        ></button>
    </div>
</template>

<style scoped>
.footer-chat {
    border-color: #dfeaf6 !important;
}

.footer-chat .write-message {
    border: 1px solid #d7e5f5;
    border-radius: 10px;
    padding: 10px 12px;
    background: #fdfefe;
}

.footer-chat .write-message:focus {
    border-color: #8db5e0;
    box-shadow: none;
}

.clickable {
    cursor: pointer;
}

.send {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    padding: 0 !important;
}

.send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
