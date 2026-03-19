<script setup>
defineProps({
    name: {
        type: String,
        default: "",
    },
    message: {
        type: String,
        default: "",
    },
    photo: {
        type: String,
        default: "",
    },
    time: {
        type: String,
        default: "",
    },
    isActive: {
        type: Boolean,
        default: false,
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

defineEmits(["select"]);
</script>

<template>
    <div
        class="discussion d-flex align-items-center p-3"
        :class="{ 'message-active': isActive }"
        @click="$emit('select')"
    >
        <img v-if="photo" class="photo rounded-circle me-3" :src="photo" alt="Contact avatar" />
        <div v-else class="avatar-fallback me-3">
            {{ (name || "U").charAt(0).toUpperCase() }}
        </div>

        <div class="desc-contact flex-grow-1">
            <p class="name mb-0">{{ name || "Unknown" }}</p>
            <p class="message mb-0">{{ message }}</p>
        </div>

        <div class="contact-meta ms-2 text-end">
            <div class="timer small text-muted">{{ time }}</div>
            <span v-if="unreadCount > 0" class="unread-pill">{{ unreadCount }}</span>
        </div>
    </div>
</template>

<style scoped>
.discussion {
    cursor: pointer;
    min-height: 84px;
    border-bottom: 1px solid #e4edf7;
    background: #f7fbff;
    transition: background-color 0.2s ease;
}

.discussion:hover {
    background: #edf6ff;
}

.message-active {
    background-color: #e4f1ff;
}

.desc-contact .name,
.desc-contact .message {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.name {
    font-weight: 600;
    color: #1d2b39;
}

.message {
    color: #617181;
    font-size: 0.9rem;
}

.photo,
.avatar-fallback {
    width: 45px;
    height: 45px;
}

.avatar-fallback {
    border-radius: 50%;
    background: #3f7fd4;
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 600;
    flex-shrink: 0;
}

.contact-meta {
    min-width: 42px;
}

.timer {
    font-size: 11px;
}

.unread-pill {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    background: #0f5fc6;
    color: #fff;
    font-size: 11px;
    padding: 0 6px;
}
</style>
