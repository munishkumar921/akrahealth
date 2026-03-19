<script setup>
import { defineProps } from "vue";
import ContactItem from "./Partial/ContactItem.vue";
import MessageItem from "./Partial/MessageItem.vue";
import ChatInput from "./Partial/ChatInput.vue";

const props = defineProps({
    contacts: Array,
    messages: Array,
    selectedContact: Number,
    searchTerm: String,
    handleSendMessage: Function,
    selectContact: Function,
});
const emit = defineEmits(["update:searchTerm"]);
</script>

<template>
    <div class="row">
        <section class="col-md-4 p-0 discussions rounded">
            <div
                class="discussion search py-3 d-flex justify-content-center align-items-center"
            >
                <div class="searchbar d-flex align-items-center">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        :value="searchTerm"
                        @input="
                            (event) =>
                                emit('update:searchTerm', event.target.value)
                        "
                        type="text"
                        class="form-control border-0"
                        placeholder="Search..."
                    />
                </div>
            </div>
            <div v-for="(contact, index) in contacts" :key="index">
                <ContactItem
                    :name="contact.name"
                    :message="contact.message"
                    :photo="contact.photo"
                    :time="contact.time"
                    :isActive="index === selectedContact"
                    @select="selectContact(index)"
                />
            </div>
        </section>

        <section class="col-md-8 chat p-0">
            <div class="messages-chat p-4">
                <MessageItem
                    v-for="(message, index) in messages"
                    :key="index"
                    :text="message.text"
                    :time="message.time"
                    :photo="message.photo"
                    :isResponse="message.response"
                />
            </div>
            <ChatInput @send="handleSendMessage" />
        </section>
    </div>
</template>
