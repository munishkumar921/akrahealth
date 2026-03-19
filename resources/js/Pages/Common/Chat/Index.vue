<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";
import ContactItem from "./Partial/ContactItem.vue";
import MessageItem from "./Partial/MessageItem.vue";
import ChatInput from "./Partial/ChatInput.vue";
import MobileChat from "./MobileChat.vue";

const props = defineProps({
    receivedMessages: {
        type: Array,
        default: () => [],
    },
    sentMessages: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const authUserId = computed(() => page.props?.auth?.user?.id ?? null);

const searchTerm = ref((props.filters?.search || "").trim());
const selectedContact = ref("");
const isNewChatModalOpen = ref(false);

const sendForm = useForm({
    receiver_id: "",
    message: "",
});

const newChatForm = useForm({
    receiver_id: "",
    message: "",
});

const parseTimestamp = (value) => {
    if (!value) return 0;

    const timestamp = new Date(value).getTime();
    return Number.isNaN(timestamp) ? 0 : timestamp;
};

const normalizeMessage = (message, direction) => {
    const otherUser = direction === "received" ? message.sender : message.receiver;

    return {
        id: message.id,
        text: message.message || "",
        file: message.file || "",
        time: message.created_at || message.updated_at || null,
        isRead: Boolean(message.is_read),
        direction,
        otherUser,
    };
};

const allMessages = computed(() => {
    const received = props.receivedMessages.map((message) => normalizeMessage(message, "received"));
    const sent = props.sentMessages.map((message) => normalizeMessage(message, "sent"));

    return [...received, ...sent].sort((a, b) => parseTimestamp(b.time) - parseTimestamp(a.time));
});

const conversations = computed(() => {
    const map = new Map();

    allMessages.value.forEach((message) => {
        if (!message.otherUser?.id) return;

        if (!map.has(message.otherUser.id)) {
            map.set(message.otherUser.id, {
                user: message.otherUser,
                lastMessage: message,
                unread: 0,
            });
        }

        const current = map.get(message.otherUser.id);
        if (message.direction === "received" && !message.isRead) {
            current.unread += 1;
        }

        if (parseTimestamp(message.time) > parseTimestamp(current.lastMessage?.time)) {
            current.lastMessage = message;
        }
    });

    // Keep sidebar based on actual sent/received chats.
    // If user starts a brand-new chat, include that selected user so first message can be sent.
    if (selectedContact.value && !map.has(selectedContact.value)) {
        const selectedUser = props.users.find((user) => user?.id === selectedContact.value);
        if (selectedUser && selectedUser.id !== authUserId.value) {
            map.set(selectedUser.id, {
                user: selectedUser,
                lastMessage: null,
                unread: 0,
            });
        }
    }

    return [...map.values()].sort(
        (a, b) => parseTimestamp(b.lastMessage?.time) - parseTimestamp(a.lastMessage?.time)
    );
});

const filteredConversations = computed(() => {
    const query = searchTerm.value.trim().toLowerCase();
    if (!query) return conversations.value;

    return conversations.value.filter((conversation) => {
        const name = (conversation.user?.name || "").toLowerCase();
        const email = (conversation.user?.email || "").toLowerCase();
        const text = (conversation.lastMessage?.text || "").toLowerCase();

        return name.includes(query) || email.includes(query) || text.includes(query);
    });
});

const unreadCount = computed(() =>
    conversations.value.reduce((count, conversation) => count + conversation.unread, 0)
);

const availableNewChatUsers = computed(() =>
    props.users.filter((user) => user?.id && user.id !== authUserId.value)
);

const activeConversation = computed(() =>
    filteredConversations.value.find((conversation) => conversation.user.id === selectedContact.value) || null
);

const activeMessages = computed(() => {
    if (!selectedContact.value) return [];

    return allMessages.value
        .filter((message) => message.otherUser?.id === selectedContact.value)
        .sort((a, b) => parseTimestamp(a.time) - parseTimestamp(b.time));
});

watch(
    filteredConversations,
    (list) => {
        if (!selectedContact.value && list.length > 0) {
            selectedContact.value = list[0].user.id;
            return;
        }

        if (
            selectedContact.value &&
            !list.some((conversation) => conversation.user.id === selectedContact.value)
        ) {
            selectedContact.value = list[0]?.user?.id || "";
        }
    },
    { immediate: true }
);

watch(selectedContact, (contactId) => {
    sendForm.receiver_id = contactId || "";
});

const selectContact = (contactId) => {
    selectedContact.value = contactId;
};

const openNewChatModal = () => {
    newChatForm.reset();
    newChatForm.clearErrors();
    isNewChatModalOpen.value = true;
};

const closeNewChatModal = () => {
    isNewChatModalOpen.value = false;
};

const sendNewChatMessage = () => {
    const trimmed = (newChatForm.message || "").trim();
    if (!newChatForm.receiver_id || !trimmed || newChatForm.processing) return;

    newChatForm.message = trimmed;

    newChatForm.post(route("chats.store"), {
        preserveScroll: true,
        onSuccess: () => {
            const receiverId = newChatForm.receiver_id;
            searchTerm.value = "";
            selectContact(receiverId);
            closeNewChatModal();
            newChatForm.reset();
        },
    });
};

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

const handleSendMessage = (text) => {
    const trimmed = (text || "").trim();
    if (!trimmed || !sendForm.receiver_id || sendForm.processing) return;

    sendForm.message = trimmed;

    sendForm.post(route("chats.store"), {
        preserveScroll: true,
        onSuccess: () => {
            sendForm.reset("message");
        },
    });
};

const markRead = (messageId) => {
    if (!messageId) return;

    router.patch(
        route("chats.read", messageId),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ["receivedMessages", "sentMessages"],
        }
    );
};

const handleMarkRead = (message) => {
    if (message.direction !== "received" || message.isRead) return;

    markRead(message.id);
};
</script>

<template>
    <AuthLayout title="Chat" description="Messaging and communication" heading="Chat">
        <div class="container-fluid chat-page">
            <div class="chat-topbar d-flex align-items-center justify-content-between px-3 py-2">
                <div>
                    <h6 class="mb-0">Conversations</h6>
                    <small class="text-muted">Direct messages</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary">{{ unreadCount }}</span>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary px-3"
                        @click="openNewChatModal"
                    >
                        New Chat
                    </button>
                </div>
            </div>

            <div class="row d-none d-md-flex chat-shell">
                <section class="col-md-4 p-0 discussions">
                    <div
                        class="discussion search py-3 d-flex justify-content-center align-items-center"
                    >
                        <div class="searchbar d-flex align-items-center">
                            <i class="fa fa-search" aria-hidden="true"></i>
                            <input
                                v-model="searchTerm"
                                type="text"
                                class="form-control border-0"
                                placeholder="Search..."
                            />
                        </div>
                    </div>

                    <div
                        v-for="contact in filteredConversations"
                        :key="contact.user.id"
                    >
                        <ContactItem
                            :name="contact.user.name || contact.user.email"
                            :message="getMessagePreview(contact.lastMessage)"
                            :time="formatTime(contact.lastMessage?.time)"
                            :isActive="contact.user.id === selectedContact"
                            :unreadCount="contact.unread"
                            @select="selectContact(contact.user.id)"
                        />
                    </div>

                    <div
                        v-if="filteredConversations.length === 0"
                        class="text-center text-muted py-4"
                    >
                        No conversations found.
                    </div>
                </section>

                <section class="col-md-8 chat p-0">
                    <div
                        v-if="activeConversation"
                        class="chat-header d-flex align-items-center px-4 py-3 border-bottom"
                    >
                        <div class="avatar-fallback me-2">
                            {{ (activeConversation.user?.name || "U").charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="fw-semibold">
                                {{ activeConversation.user?.name || activeConversation.user?.email }}
                            </div>
                            <small class="text-muted">Direct message</small>
                        </div>
                    </div>

                    <div class="messages-chat p-4">
                        <template v-if="activeConversation">
                            <MessageItem
                                v-for="message in activeMessages"
                                :key="message.id"
                                :text="message.text"
                                :file="message.file"
                                :time="formatTime(message.time)"
                                :isResponse="message.direction === 'sent'"
                                :isRead="message.isRead"
                                :canMarkRead="message.direction === 'received' && !message.isRead"
                                @mark-read="handleMarkRead(message)"
                            />

                            <p
                                v-if="activeMessages.length === 0"
                                class="text-muted text-center pt-5"
                            >
                                No messages yet. Start the conversation.
                            </p>
                        </template>

                        <p v-else class="text-muted text-center pt-5">
                            Select a contact to start chatting.
                        </p>
                    </div>

                    <ChatInput
                        :disabled="!activeConversation || sendForm.processing"
                        @send="handleSendMessage"
                    />
                    <div v-if="sendForm.errors.message" class="text-danger px-3 pb-2 small">
                        {{ sendForm.errors.message }}
                    </div>
                    <div v-if="sendForm.errors.receiver_id" class="text-danger px-3 pb-3 small">
                        {{ sendForm.errors.receiver_id }}
                    </div>
                </section>
            </div>

            <MobileChat
                class="d-md-none"
                :contacts="filteredConversations"
                :messages="activeMessages"
                :selectedContact="selectedContact"
                :searchTerm="searchTerm"
                :isProcessing="sendForm.processing"
                :activeConversation="activeConversation"
                @update:searchTerm="searchTerm = $event"
                @select="selectContact"
                @send="handleSendMessage"
                @mark-read="markRead"
            />

            <Modal
                :isOpen="isNewChatModalOpen"
                title="Start New Chat"
                size="md"
                @close="closeNewChatModal"
            >
                <div class="mb-3">
                    <label class="form-label">Select user</label>
                    <select v-model="newChatForm.receiver_id" class="form-select">
                        <option value="">Choose user</option>
                        <option
                            v-for="user in availableNewChatUsers"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name || user.email }}
                        </option>
                    </select>
                    <div v-if="newChatForm.errors.receiver_id" class="text-danger small mt-1">
                        {{ newChatForm.errors.receiver_id }}
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea
                        v-model="newChatForm.message"
                        rows="4"
                        class="form-control"
                        placeholder="Type your message..."
                    ></textarea>
                    <div v-if="newChatForm.errors.message" class="text-danger small mt-1">
                        {{ newChatForm.errors.message }}
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" @click="closeNewChatModal">
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        :disabled="newChatForm.processing || !newChatForm.receiver_id || !newChatForm.message.trim()"
                        @click="sendNewChatMessage"
                    >
                        Send
                    </button>
                </div>
            </Modal>
        </div>
    </AuthLayout>
</template>

<style scoped>
.chat-page {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-shell {
    border: 1px solid #d8e7f5;
    border-radius: 12px;
    overflow: hidden;
    margin: 0;
    background: #fff;
}

.discussions {
    height: 72vh;
    min-height: 620px;
    overflow-y: auto;
    background-color: #f7fbff;
    border-right: 1px solid #e1edf8;
}

.chat {
    background: #f3f9ff;
    min-height: 620px;
    display: flex;
    flex-direction: column;
}

.chat-topbar {
    background: #fff;
    border: 1px solid #d8e7f5;
    border-radius: 12px;
}

.chat-header {
    background: #ffffff;
    border-color: #dfebf7 !important;
}

.messages-chat {
    flex: 1;
    overflow-y: auto;
    max-height: calc(72vh - 130px);
    min-height: 440px;
    background: linear-gradient(180deg, #f5fbff 0%, #ebf5ff 100%);
}

.searchbar {
    background-color: #fff;
    padding: 0 1rem;
    border-radius: 10px;
    width: 90%;
    border: 1px solid #dce9f7;
}

.avatar-fallback {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #3f7fd4;
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .chat-page {
        gap: 10px;
    }

    .chat-topbar {
        border-radius: 10px;
    }

    .discussions,
    .chat {
        min-height: auto;
        height: auto;
    }

    .messages-chat {
        min-height: 320px;
        max-height: 54vh;
    }
}
</style>
