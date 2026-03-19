<script setup>
defineProps({
    text: {
        type: String,
        default: "",
    },
    time: {
        type: String,
        default: "",
    },
    file: {
        type: String,
        default: "",
    },
    isResponse: {
        type: Boolean,
        default: false,
    },
    isRead: {
        type: Boolean,
        default: false,
    },
    canMarkRead: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["mark-read"]);
</script>

<template>
    <div
        class="message d-flex align-items-start mb-3"
        :class="['text-only', { 'justify-content-end': isResponse }]"
    >
        <div class="bubble p-2 rounded" :class="isResponse ? 'bubble-response' : 'bubble-received'">
            <p v-if="text" class="text mb-1">{{ text }}</p>
            <img v-if="file" :src="file" class="attachment" alt="Message attachment" />

            <div class="meta d-flex align-items-center justify-content-end gap-2">
                <small v-if="time" class="time">{{ time }}</small>
                <small v-if="isResponse" class="status">{{ isRead ? "Seen" : "Sent" }}</small>
                <button
                    v-if="canMarkRead"
                    type="button"
                    class="btn btn-link btn-sm p-0"
                    @click="$emit('mark-read')"
                >
                    Mark read
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.message {
    position: relative;
}

.bubble {
    max-width: min(86%, 640px);
    padding: 10px 12px !important;
    border-radius: 14px !important;
    box-shadow: 0 1px 2px rgba(18, 52, 86, 0.09);
}

.bubble-response {
    background: #0f76cf;
    color: #fff;
}

.bubble-received {
    background: #ffffff;
    color: #202427;
    border: 1px solid #e2ebf5;
}

.text {
    white-space: pre-wrap;
    word-break: break-word;
}

.meta {
    min-height: 18px;
}

.time,
.status {
    opacity: 0.8;
    font-size: 11px;
}

.bubble-response .time,
.bubble-response .status {
    color: rgba(255, 255, 255, 0.9);
}

.bubble-received .time,
.bubble-received .status {
    color: #5d6f82;
}

.btn-link {
    font-size: 11px;
    text-decoration: none;
}

.attachment {
    display: block;
    margin-top: 6px;
    max-width: 180px;
    max-height: 180px;
    border-radius: 6px;
    object-fit: cover;
    border: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
