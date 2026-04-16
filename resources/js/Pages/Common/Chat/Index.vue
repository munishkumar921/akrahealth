<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import ChatBox from "./ChatBox.vue";
import { ref, watch, computed } from "vue";
import axios from "axios";

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    conversation: {},
    chat_partner_id: String
});

const participants = ref(props.users);
const chatPartnerId = ref(props.chat_partner_id)
const conversationID = ref(props.conversation.id);
const searchQuery = ref("");
const getConversation = (user) => {

    axios.get(route('conversation.id', user.id)).then(response => {
        console.log(response.data);
        conversationID.value = response.data.id;
        chatPartnerId.value = user.id;
    })
}

const filteredParticipants = computed(() => {
    if (!searchQuery.value) return participants.value

    const q = searchQuery.value.toLowerCase()

    return participants.value.filter(user => 
        user.name.toLowerCase().includes(q) ||
        user.email.toLowerCase().includes(q)
    )
})
</script>

<template>
    <AuthLayout title="Chat" description="Messaging and communication" heading="Chat">

        <div class="container">
            <div class="row">
                <div class="col-4">
                    <div>
                        <div class="mb-3">
                            <input v-model="searchQuery" type="text" placeholder="Search users..."
                                class="form-control" />
                        </div>

                        <!-- {{ chat_partner_id }}- {{ user.id }} -->
                        <template v-for="user in filteredParticipants" :key="user.id">
                            <div @click="getConversation(user)"
                                :class="`${chatPartnerId == user.id ? 'bg-color-white-lilac rounded' : ''}`"
                                class="flex items-center gap-2 p-1 border-b hover:bg-color-white-lilac cursor-pointer">
                                <img :src="user.profile_photo_url" class="w-10 mr-2 rounded-circle" />
                                <span>{{ user.name }}</span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="col-8">
                    <ChatBox :conversationId="conversationID" :key="conversationID" />
                </div>
            </div>
        </div>

    </AuthLayout>
</template>
