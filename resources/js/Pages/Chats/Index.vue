<script setup>
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Modal from "@/Components/Common/Modal.vue";

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
});

const page = usePage();
const currentUserId = computed(() => page.props?.auth?.user?.id || null);
const contactSearch = ref("");
const selectedUserId = ref("");
const isNewMessageModalOpen = ref(false);
const composerFileInput = ref(null);
const modalFileInput = ref(null);

const form = useForm({
  receiver_id: "",
  message: "",
  file: null,
});

const newMessageForm = useForm({
  receiver_id: "",
  message: "",
  file: null,
});

const filePreviewUrl = ref("");
const newFilePreviewUrl = ref("");

const normalizeMessage = (message, direction) => {
  const otherUser = direction === "received" ? message.sender : message.receiver;

  return {
    ...message,
    direction,
    otherUser,
    createdAt: message.created_at ? new Date(message.created_at) : null,
  };
};

const allMessages = computed(() => {
  const received = props.receivedMessages.map((m) => normalizeMessage(m, "received"));
  const sent = props.sentMessages.map((m) => normalizeMessage(m, "sent"));

  return [...received, ...sent].sort((a, b) => {
    const aTime = a.createdAt ? a.createdAt.getTime() : 0;
    const bTime = b.createdAt ? b.createdAt.getTime() : 0;
    return bTime - aTime;
  });
});

const conversationMap = computed(() => {
  const map = new Map();

  allMessages.value.forEach((msg) => {
    if (!msg.otherUser?.id) return;

    const id = msg.otherUser.id;
    if (!map.has(id)) {
      map.set(id, {
        user: msg.otherUser,
        lastMessage: msg,
        unread: 0,
      });
    }

    const entry = map.get(id);
    if (msg.direction === "received" && !msg.is_read) {
      entry.unread += 1;
    }

    const currentLastTime = entry.lastMessage?.createdAt ? entry.lastMessage.createdAt.getTime() : 0;
    const candidateTime = msg.createdAt ? msg.createdAt.getTime() : 0;
    if (candidateTime > currentLastTime) {
      entry.lastMessage = msg;
    }
  });

  props.users.forEach((user) => {
    if (!user?.id || user.id === currentUserId.value) return;
    if (!map.has(user.id)) {
      map.set(user.id, {
        user,
        lastMessage: null,
        unread: 0,
      });
    }
  });

  return [...map.values()].sort((a, b) => {
    const aTime = a.lastMessage?.createdAt ? a.lastMessage.createdAt.getTime() : 0;
    const bTime = b.lastMessage?.createdAt ? b.lastMessage.createdAt.getTime() : 0;
    return bTime - aTime;
  });
});

const filteredConversations = computed(() => {
  const q = contactSearch.value.trim().toLowerCase();
  if (!q) return conversationMap.value;

  return conversationMap.value.filter((c) => {
    const name = (c.user?.name || "").toLowerCase();
    const email = (c.user?.email || "").toLowerCase();
    const last = (c.lastMessage?.message || "").toLowerCase();
    return name.includes(q) || email.includes(q) || last.includes(q);
  });
});

const activeConversation = computed(() => {
  if (!selectedUserId.value) return null;
  return conversationMap.value.find((c) => c.user.id === selectedUserId.value) || null;
});

const activeMessages = computed(() => {
  if (!selectedUserId.value) return [];

  return allMessages.value
    .filter((m) => m.otherUser?.id === selectedUserId.value)
    .sort((a, b) => {
      const aTime = a.createdAt ? a.createdAt.getTime() : 0;
      const bTime = b.createdAt ? b.createdAt.getTime() : 0;
      return aTime - bTime;
    });
});

const unreadCount = computed(() => props.receivedMessages.filter((m) => !m.is_read).length);

const selectConversation = (userId) => {
  selectedUserId.value = userId;
  form.receiver_id = userId;
};

watch(
  () => filteredConversations.value,
  (list) => {
    if (!selectedUserId.value && list.length > 0) {
      selectConversation(list[0].user.id);
      return;
    }

    if (selectedUserId.value && !list.some((c) => c.user.id === selectedUserId.value)) {
      const fallback = list[0]?.user?.id || "";
      selectedUserId.value = fallback;
      form.receiver_id = fallback;
    }
  },
  { immediate: true }
);

const sendMessage = () => {
  if (!form.receiver_id || (!form.message.trim() && !form.file)) return;

  form.post(route("chats.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset("message", "file");
      if (filePreviewUrl.value) {
        URL.revokeObjectURL(filePreviewUrl.value);
        filePreviewUrl.value = "";
      }
      if (composerFileInput.value) composerFileInput.value.value = "";
    },
  });
};

const openNewMessageModal = () => {
  newMessageForm.reset();
  newMessageForm.clearErrors();
  if (newFilePreviewUrl.value) {
    URL.revokeObjectURL(newFilePreviewUrl.value);
    newFilePreviewUrl.value = "";
  }
  if (modalFileInput.value) modalFileInput.value.value = "";
  isNewMessageModalOpen.value = true;
};

const closeNewMessageModal = () => {
  isNewMessageModalOpen.value = false;
};

const sendFromModal = () => {
  if (!newMessageForm.receiver_id || (!newMessageForm.message.trim() && !newMessageForm.file)) return;

  newMessageForm.post(route("chats.store"), {
    preserveScroll: true,
    onSuccess: () => {
      const selectedReceiver = newMessageForm.receiver_id;
      closeNewMessageModal();
      if (selectedReceiver) {
        selectConversation(selectedReceiver);
      }
      newMessageForm.reset("message", "file");
      if (newFilePreviewUrl.value) {
        URL.revokeObjectURL(newFilePreviewUrl.value);
        newFilePreviewUrl.value = "";
      }
      if (modalFileInput.value) modalFileInput.value.value = "";
    },
  });
};

const onFileChange = (event) => {
  const file = event.target.files?.[0] || null;
  form.file = file;

  if (filePreviewUrl.value) {
    URL.revokeObjectURL(filePreviewUrl.value);
    filePreviewUrl.value = "";
  }

  if (file && file.type.startsWith("image/")) {
    filePreviewUrl.value = URL.createObjectURL(file);
  }
};

const onNewFileChange = (event) => {
  const file = event.target.files?.[0] || null;
  newMessageForm.file = file;

  if (newFilePreviewUrl.value) {
    URL.revokeObjectURL(newFilePreviewUrl.value);
    newFilePreviewUrl.value = "";
  }

  if (file && file.type.startsWith("image/")) {
    newFilePreviewUrl.value = URL.createObjectURL(file);
  }
};

const triggerComposerFilePicker = () => {
  composerFileInput.value?.click();
};

const triggerModalFilePicker = () => {
  modalFileInput.value?.click();
};

const clearComposerFile = () => {
  form.file = null;
  if (filePreviewUrl.value) {
    URL.revokeObjectURL(filePreviewUrl.value);
    filePreviewUrl.value = "";
  }
  if (composerFileInput.value) composerFileInput.value.value = "";
};

const clearModalFile = () => {
  newMessageForm.file = null;
  if (newFilePreviewUrl.value) {
    URL.revokeObjectURL(newFilePreviewUrl.value);
    newFilePreviewUrl.value = "";
  }
  if (modalFileInput.value) modalFileInput.value.value = "";
};

const markRead = (id) => {
  form.patch(route("chats.read", id), {
    preserveScroll: true,
    preserveState: true,
    only: ["receivedMessages", "sentMessages"],
  });
};

const formatTime = (value) => {
  if (!value) return "";
  return new Date(value).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
};

const sentStatusIcon = (isRead) => (isRead ? "bi bi-check2-all" : "bi bi-check2");

onBeforeUnmount(() => {
  if (filePreviewUrl.value) URL.revokeObjectURL(filePreviewUrl.value);
  if (newFilePreviewUrl.value) URL.revokeObjectURL(newFilePreviewUrl.value);
});
</script>

<template>
  <AuthLayout title="Chats" description="Chat" heading="Chats">
    <div class="chat-shell">
      <aside class="chat-sidebar">
        <div class="chat-sidebar-header">
          <h6 class="mb-0">Chats</h6>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger">{{ unreadCount }}</span>
            <button type="button" class="btn btn-sm btn-primary new-chat-btn" @click="openNewMessageModal">
              New
            </button>
          </div>
        </div>

        <div class="chat-sidebar-search">
          <input v-model="contactSearch" type="search" class="form-control" placeholder="Search or start new chat" />
        </div>

        <div class="chat-list">
          <button v-for="chat in filteredConversations" :key="chat.user.id" type="button" class="chat-list-item"
            :class="{ active: selectedUserId === chat.user.id }" @click="selectConversation(chat.user.id)">
            <div class="chat-avatar">{{ (chat.user.name || "U").charAt(0).toUpperCase() }}</div>
            <div class="chat-meta">
              <div class="chat-row">
                <strong class="chat-name">{{ chat.user.name || chat.user.email || "User" }}</strong>
                <small class="chat-time">{{ formatTime(chat.lastMessage?.created_at) }}</small>
              </div>
              <div class="chat-row">
                <span class="chat-last">{{ chat.lastMessage?.message || "Start conversation" }}</span>
                <span v-if="chat.unread > 0" class="chat-unread">{{ chat.unread }}</span>
              </div>
            </div>
          </button>

          <div v-if="filteredConversations.length === 0" class="chat-empty-list">No users found.</div>
        </div>
      </aside>

      <section class="chat-main">
        <header class="chat-main-header" v-if="activeConversation">
          <div class="chat-avatar">
            {{ (activeConversation.user?.name || "U").charAt(0).toUpperCase() }}
          </div>
          <div>
            <strong>{{ activeConversation.user?.name || activeConversation.user?.email || "User" }}</strong>
            <div class="text-muted small">Direct messages</div>
          </div>
        </header>

        <div v-if="activeConversation" class="chat-messages">
          <div v-for="message in activeMessages" :key="message.id" class="bubble-wrap"
            :class="message.direction === 'sent' ? 'sent' : 'received'">
            <div class="chat-bubble" :class="message.direction === 'sent' ? 'bubble-sent' : 'bubble-received'">
              <div class="bubble-text">{{ message.message }}</div>
              <div class="bubble-meta">
                <small>{{ formatTime(message.created_at) }}</small>
                <i v-if="message.direction === 'sent'" class="ms-2 status-check"
                  :class="[sentStatusIcon(message.is_read), message.is_read ? 'read' : 'sent']"
                  :title="message.is_read ? 'Seen' : 'Sent'"></i>
                <button v-if="message.direction === 'received' && !message.is_read" type="button"
                  class="btn btn-link btn-sm p-0 ms-2" @click="markRead(message.id)">
                  Mark read
                </button>
              </div>
            </div>
          </div>

          <div v-if="activeMessages.length === 0" class="chat-empty-thread">
            No messages yet. Send the first one.
          </div>
        </div>

        <div v-else class="chat-empty-thread">Select a conversation to start chatting.</div>

        <footer class="chat-composer" v-if="activeConversation">
          <div class="composer-inputs">
            <textarea v-model="form.message" class="form-control" rows="1" placeholder="Type a message"
              @keydown.enter.prevent="sendMessage"></textarea>

          </div>
          <button class="btn btn-primary" :disabled="form.processing || (!form.message.trim() && !form.file)"
            @click="sendMessage">
            Send
          </button>
        </footer>
      </section>
    </div>

    <Modal :isOpen="isNewMessageModalOpen" title="New Message" size="lg" @close="closeNewMessageModal">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">To</label>
          <select v-model="newMessageForm.receiver_id" class="form-select">
            <option value="">Select user</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name || user.email }}
            </option>
          </select>
          <div v-if="newMessageForm.errors.receiver_id" class="text-danger mt-1">
            {{ newMessageForm.errors.receiver_id }}
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Message</label>
          <textarea v-model="newMessageForm.message" rows="5" class="form-control" placeholder="Type your message"
            @keydown.enter.exact.prevent="sendFromModal"></textarea>
          <div v-if="newMessageForm.errors.message" class="text-danger mt-1">
            {{ newMessageForm.errors.message }}
          </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-light" @click="closeNewMessageModal">Cancel</button>
          <button type="button" class="btn btn-success"
            :disabled="newMessageForm.processing || !newMessageForm.receiver_id || (!newMessageForm.message.trim() && !newMessageForm.file)"
            @click="sendFromModal">
            Send
          </button>
        </div>
      </div>
    </Modal>
  </AuthLayout>
</template>

<style scoped>
.chat-shell {
  display: grid;
  grid-template-columns: 360px 1fr;
  min-height: 72vh;
  border: 1px solid #d8ecfb;
  border-radius: 14px;
  overflow: hidden;
  background: #eaf6ff;
}

.chat-sidebar {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-right: 1px solid #d8ecfb;
}

.chat-sidebar-header {
  padding: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e6f2fb;
}

.new-chat-btn {
  min-width: 64px;
  font-weight: 600;
}

.chat-sidebar-search {
  padding: 12px;
  border-bottom: 1px solid #e6f2fb;
}

.chat-list {
  overflow-y: auto;
}

.chat-list-item {
  width: 100%;
  display: flex;
  gap: 10px;
  padding: 12px;
  border: 0;
  background: #fff;
  text-align: left;
  border-bottom: 1px solid #eaf4fc;
}

.chat-list-item:hover {
  background: #f2f9ff;
}

.chat-list-item.active {
  background: #e3f4ff;
}

.chat-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #09ACFF;
  color: #fff;
  display: grid;
  place-items: center;
  font-weight: 600;
  flex-shrink: 0;
}

.chat-meta {
  min-width: 0;
  width: 100%;
}

.chat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.chat-name,
.chat-last {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chat-last {
  color: #66726a;
  font-size: 13px;
  max-width: 220px;
}

.chat-time {
  color: #7e8f84;
  font-size: 11px;
  flex-shrink: 0;
}

.chat-unread {
  background: #09acff;
  color: #fff;
  border-radius: 999px;
  min-width: 20px;
  height: 20px;
  display: grid;
  place-items: center;
  font-size: 11px;
  padding: 0 6px;
}

.chat-main {
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #f3faff 0%, #eaf5ff 100%);
}

.chat-main-header {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid #d8ecfb;
  background: #ffffffd9;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 18px 16px;
  background-color: #e5f3ff;
  background-image:
    radial-gradient(circle at 12px 12px, rgba(255, 255, 255, 0.32) 1.2px, transparent 1.2px);
  background-size: 22px 22px;
}

.bubble-wrap {
  display: flex;
  margin-bottom: 8px;
}

.bubble-wrap.sent {
  justify-content: flex-end;
}

.bubble-wrap.received {
  justify-content: flex-start;
}

.chat-bubble {
  max-width: min(78%, 680px);
  padding: 12px 14px 10px;
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
  word-break: break-word;
  line-height: 1.3;
  font-size: 14px;
}

.bubble-sent {
  background: #d9f1ff;
}

.bubble-received {
  background: #ffffff;
}

.bubble-text {
  display: inline;
  font-size: 17px;
  color: #111;
}

.bubble-meta {
  float: right;
  display: flex;
  align-items: center;
  margin-left: 10px;
  margin-top: 6px;
  color: #6f6f6f;
  gap: 3px;
}

.bubble-meta small {
  font-size: 12px;
  letter-spacing: 0.1px;
}

.status-check {
  font-size: 14px;
  line-height: 1;
}

.status-check.sent {
  color: #748494;
}

.status-check.read {
  color: #09acff;
}

.chat-composer {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 10px;
  padding: 12px;
  border-top: 1px solid #d8ecfb;
  background: #f2f9ff;
}

.composer-inputs {
  min-width: 0;
}

.chat-composer textarea {
  resize: none;
  min-height: 42px;
  max-height: 120px;
}

.upload-preview {
  max-width: 180px;
  max-height: 120px;
  border-radius: 8px;
  border: 1px solid #d8ecfb;
  object-fit: cover;
}

.file-inline-field {
  display: flex;
  align-items: center;
  gap: 8px;
}

.file-inline-display {
  flex: 1;
  min-height: 38px;
  border: 1px solid #cfe6f7;
  border-radius: 8px;
  background: #fff;
  padding: 6px 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.inline-thumb {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  object-fit: cover;
  border: 1px solid #d8ecfb;
  flex-shrink: 0;
}

.file-inline-text {
  display: block;
  color: #536376;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-empty-list,
.chat-empty-thread {
  color: #7b8a80;
  padding: 18px;
  text-align: center;
}

@media (max-width: 992px) {
  .chat-shell {
    grid-template-columns: 1fr;
    min-height: auto;
  }

  .chat-sidebar {
    border-right: 0;
    border-bottom: 1px solid #dde8df;
    max-height: 320px;
  }

  .chat-bubble {
    max-width: 86%;
  }

  .new-chat-btn {
    min-width: 56px;
    padding: 0.2rem 0.5rem;
  }
}
</style>
