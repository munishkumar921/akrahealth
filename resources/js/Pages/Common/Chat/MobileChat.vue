<script setup>
import { ref, watch } from "vue";
import ContactItem from "./Partial/ContactItem.vue";
import MessageItem from "./Partial/MessageItem.vue";
import ChatInput from "./Partial/ChatInput.vue";

const props = defineProps({
    contacts: {
        type: Array,
        default: () => [],
    },
    messages: {
        type: Array,
        default: () => [],
    },
    selectedContact: {
        type: String,
        default: "",
    },
    searchTerm: {
        type: String,
        default: "",
    },
    isProcessing: {
        type: Boolean,
        default: false,
    },
    activeConversation: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:searchTerm", "select", "send", "mark-read"]);
const showThread = ref(false);

watch(
    () => props.selectedContact,
    (value) => {
        showThread.value = Boolean(value);
    },
    { immediate: true }
);

const formatTime = (value) => {
    if (!value) return "";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";

    return date.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getMessagePreview = (message) => {
    if (!message) return "Start conversation";
    if (message.text) return message.text;
    if (message.file) return "Attachment";

    return "Start conversation";
};

const selectMobileContact = (contactId) => {
    emit("select", contactId);
    showThread.value = true;
};

const goBackToList = () => {
    showThread.value = false;
};
</script>

<template>
    <section class="mobile-chat rounded mt-2">
        <section v-if="!showThread" class="mobile-list">
            <div class="discussion search py-3 d-flex justify-content-center align-items-center">
                <div class="searchbar d-flex align-items-center">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        :value="searchTerm"
                        @input="emit('update:searchTerm', $event.target.value)"
                        type="text"
                        class="form-control border-0"
                        placeholder="Search..."
                    />
                </div>
            </div>

            <div v-for="contact in contacts" :key="contact.user.id">
                <ContactItem
                    :name="contact.user.name || contact.user.email"
                    :message="getMessagePreview(contact.lastMessage)"
                    :time="formatTime(contact.lastMessage?.time)"
                    :isActive="contact.user.id === selectedContact"
                    :unreadCount="contact.unread"
                    @select="selectMobileContact(contact.user.id)"
                />
            </div>

            <div v-if="contacts.length === 0" class="text-center text-muted py-4">
                No conversations found.
            </div>
        </section>

        <section v-else class="mobile-thread">
            <div class="chat-header d-flex align-items-center px-3 py-2 border-bottom">
                <button type="button" class="btn btn-sm btn-light me-2" @click="goBackToList">
                    Back
                </button>
                <div class="avatar-fallback me-2">
                    {{ (activeConversation?.user?.name || "U").charAt(0).toUpperCase() }}
                </div>
                <div class="fw-semibold text-truncate">
                    {{ activeConversation?.user?.name || activeConversation?.user?.email }}
                </div>
            </div>

            <div class="messages-chat p-3">
                <MessageItem
                    v-for="message in messages"
                    :key="message.id"
                    :text="message.text"
                    :file="message.file"
                    :time="formatTime(message.time)"
                    :isResponse="message.direction === 'sent'"
                    :isRead="message.isRead"
                    :canMarkRead="message.direction === 'received' && !message.isRead"
                    @mark-read="emit('mark-read', message.id)"
                />

                <p v-if="messages.length === 0" class="text-muted text-center py-4 mb-0">
                    No messages yet.
                </p>
            </div>

            <ChatInput
                :disabled="!activeConversation || isProcessing"
                @send="emit('send', $event)"
            />
        </section>
    </section>
</template>

<style scoped>
.mobile-chat {
    background: #f7fbff;
    border: 1px solid #d8e7f5;
    overflow: hidden;
}

.searchbar {
    background-color: #fff;
    padding: 0 1rem;
    border-radius: 10px;
    width: 92%;
    border: 1px solid #dce9f7;
}

.mobile-thread {
    background: #f3f9ff;
}

.messages-chat {
    max-height: 55vh;
    overflow-y: auto;
    background: linear-gradient(180deg, #f5fbff 0%, #ebf5ff 100%);
}

.chat-header {
    background: #ffffff;
}

.avatar-fallback {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #3f7fd4;
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 600;
    flex-shrink: 0;
}
</style>
