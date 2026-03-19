<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    keyword: String,
    route: Array,
    messages: {
        type: Object,
        default: () => ({
            data: [],
            links: {},
            meta: {}
        })
    },
    unreadCount: {
        type: Number,
        default: 0
    }
});

const columns = [
    { label: "Date", key: "date" },
    { label: "From", key: "from" },
    { label: "Subject", key: "subject" },
    { label: "Message", key: "message", type: "slot", slot: "message" },
    { label: "Status", key: "read_status", type: "slot", slot: "read_status" },
];
 
const viewMessage = (msg) => {
    router.get(route('patient.messages.show', msg.id));
};

const getReadStatus = (message) => {
    return message.read ? 'Read' : 'Unread';
};

const getReadStatusClass = (message) => {
    return message.read ? 'badge-success' : 'badge-warning';
};
</script>

<template>
    <AuthLayout title="Messages" description="View your messages" heading="Messages">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="d-flex align-items-center">
                 Messages
            </h3>
            <div v-if="unreadCount > 0" class="badge badge-warning">
                {{ unreadCount }} unread message{{ unreadCount > 1 ? 's' : '' }}
            </div>
        </div>
         <Table :columns="columns" :data="messages" :search="keyword">
            <template #date="{ row }">
                {{ row.date }}
            </template>
            <template #from="{ row }">
                {{ row.from || row.doctor?.name ||row.doctor?.user?.name || 'Doctor' }}
            </template>
            <template #subject="{ row }">
                <strong v-if="!row.read">{{ row.subject }}</strong>
                <span v-else>{{ row.subject }}</span>
            </template>
            <template #message="{ row }">
                <span :title="row.message">
                    {{ row.message.length > 80 ? row.message.substring(0, 80) + '...' : row.message }}
                </span>
            </template>
            <template #read_status="{ row }">
                <span :class="['badge', getReadStatusClass(row)]" :title="getReadStatusClass(row)">
                    {{ getReadStatus(row) }}
                </span>
            </template>
            <template #actions="{ row }">
                <div class="d-flex gap-1 justify-content-center">
                    <button
                        class="btn btn-info"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="View"
                        @click="viewMessage(row)"
                    >
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </template>
        </Table>
    </AuthLayout>
</template>

<style scoped>
.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}
</style>
