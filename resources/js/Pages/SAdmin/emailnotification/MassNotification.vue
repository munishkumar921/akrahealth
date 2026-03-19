<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";
import Swal from "sweetalert2/dist/sweetalert2.js";

// ===============================
// SAMPLE MASS NOTIFICATION DATA
// ===============================
const sampleNotifications = ref([
    { 
        id: 1, 
        notification_type: "Email",
        created_on: "2025-11-20",
        user_action: "Login Alert",
        subject: "Your login was detected",
    },
    { 
        id: 2, 
        notification_type: "SMS",
        created_on: "2025-11-18",
        user_action: "Password Reset",
        subject: "Reset your password securely",
    },
    { 
        id: 3, 
        notification_type: "Push",
        created_on: "2025-11-15",
        user_action: "System Update",
        subject: "Maintenance scheduled",
    },
]);

// ===============================
// TABLE COLUMNS
// ===============================
const columns = [
    { label: "Type", key: "notification_type" },
    { label: "Created Date", key: "created_on" },
    { label: "User Action", key: "user_action" },
    { label: "Subject", key: "subject" },
];

// ===============================
// VIEW POPUP
// ===============================
const viewNotification = (row) => {
    Swal.fire({
        title: `<strong>Notification Details</strong>`,
        html: `
            <div style="text-align:left; line-height:1.6;">
                <b>Type:</b> ${row.notification_type}<br>
                <b>Created Date:</b> ${row.created_on}<br>
                <b>User Action:</b> ${row.user_action}<br>
                <b>Subject:</b> ${row.subject}<br>
            </div>
        `,
        icon: "info",
        confirmButtonText: "Close",
    });
};

// ===============================
// DELETE NOTIFICATION
// ===============================
const deleteNotification = (row) => {
    Swal.fire({
        title: "Confirm Notification Deletion",
        text: "It will permanently delete this notification",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            const idx = sampleNotifications.value.findIndex(n => n.id === row.id);
            if (idx !== -1) sampleNotifications.value.splice(idx, 1);

            Swal.fire("Deleted!", "Notification has been successfully deleted", "success");
        }
    });
};

// ===============================
// "New Notification" button
// ===============================
const createNotification = () => {
    Swal.fire("New Notification", "This would open the Create Notification form (demo).", "info");
};

const buttons = [
    {
        label: "New Notification",
        icon: "bi bi-plus-circle",
        function: createNotification,
    }
];

// ===============================
// TABLE DATA FORMAT FOR <Table />
// ===============================
const tableDataProp = computed(() => {
    return { data: sampleNotifications.value, links: [] };
});
</script>

<template>
    <AuthLayout title="Mass Notifications" heading="Mass Notifications">

        <!-- PAGE HEADER -->
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="d-flex align-items-center text-xl mb-0">Mass Notifications</h3>

            <ActionButtons :actionButtons="buttons" />
        </div>

        <!-- TABLE -->
        <Table 
            :columns="columns"
            :data="tableDataProp"
            table="notifications"
        >
            <!-- ACTIONS SLOT -->
            <template #actions="{ row }">
                <div class="d-flex gap-2">

                    <!-- VIEW -->
                    <button 
                        class="icon-btn btn btn-primary" 
                        @click.prevent="viewNotification(row)"
                        title="View"
                    >
                        <i class="bi bi-eye"></i>
                    </button>

                    <!-- DELETE -->
                    <button 
                        class="icon-btn btn btn-danger" 
                        @click.prevent="deleteNotification(row)"
                        title="Delete"
                    >
                        <i class="bi bi-trash"></i>
                    </button>

                </div>
            </template>
        </Table>

    </AuthLayout>
</template>

<style scoped>
.icon-btn {
    padding: 9px 8px 6px 8px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    transition: transform .07s ease-in-out, opacity .15s ease-in-out;
}

.icon-btn:active {
    transform: scale(0.97);
}
</style>
