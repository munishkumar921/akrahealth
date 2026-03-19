<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref } from "vue";
import Swal from "sweetalert2";

// ===========================
// TABLE COLUMNS (Blade → Vue)
// ===========================
const columns = [
    { label: "Type", key: "type" },
    { label: "Sent Date", key: "sent_date" },
    { label: "User Action", key: "action" },
    { label: "Email Template", key: "email_template" },
    { label: "Subject", key: "subject" },
];

// ===========================
// SAMPLE DATA (ServerSide Mock)
// ===========================
const notifications = ref([
    {
        id: 1,
        type: "Email",
        sent_date: "2025-01-05",
        action: "User Signup",
        email_template: "Welcome Email",
        subject: "Welcome to Our Platform",
    },
    {
        id: 2,
        type: "Email",
        sent_date: "2025-01-10",
        action: "Password Reset",
        email_template: "Reset Password",
        subject: "Reset Your Password",
    },
    {
        id: 3,
        type: "Email",
        sent_date: "2025-01-15",
        action: "Subscription",
        email_template: "Subscription Expiry",
        subject: "Your Subscription is Expiring",
    },
]);

// Required by Table.vue
const tableData = ref({
    data: notifications.value,
    links: [],
});

// ===========================
// VIEW NOTIFICATION
// ===========================
const viewNotification = (row) => {
    Swal.fire({
        title: "Email Notification",
        html: `
            <b>Type:</b> ${row.type}<br>
            <b>Sent Date:</b> ${row.sent_date}<br>
            <b>User Action:</b> ${row.action}<br>
            <b>Email Template:</b> ${row.email_template}<br>
            <b>Subject:</b> ${row.subject}
        `,
        icon: "info",
        confirmButtonText: "Close",
        width: 450,
    });
};

// ===========================
// DELETE SINGLE NOTIFICATION
// ===========================
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
            notifications.value = notifications.value.filter(
                (n) => n.id !== row.id
            );
            tableData.value.data = notifications.value;

            Swal.fire(
                "Email Mass Notification Deleted",
                "Email Mass Notification has been successfully deleted",
                "success"
            );
        }
    });
};
</script>

<template>
    <AuthLayout>
        <!-- PAGE HEADER -->
        <div class="page-header mt-5-7 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title mb-0">Mass Email Notifications</h4>
            </div>

            <div>
                <a href="#" class="btn btn-primary mt-1">
                    New Email Notification
                </a>
            </div>
        </div>

        <!-- CARD -->
        <div class="card overflow-hidden border-0">

            <div class="card-body pt-2">
                <Table
                    :columns="columns"
                    :data="tableData"
                    :searchShow="true"
                >
                    <!-- ACTION COLUMN -->
                    <template #actions="{ row }">
                        <div class="d-flex gap-2 justify-content-center">
                            <!-- VIEW -->
                            <button
                                class="btn btn-sm btn-light border"
                                title="View"
                                @click.prevent="viewNotification(row)"
                            >
                                <i class="bi bi-eye-fill text-primary"></i>
                            </button>

                            <!-- DELETE -->
                            <button
                                class="btn btn-sm btn-light border"
                                title="Delete"
                                @click.prevent="deleteNotification(row)"
                            >
                                <i class="bi bi-trash-fill text-danger"></i>
                            </button>
                        </div>
                    </template>
                </Table>
            </div>
        </div>
    </AuthLayout>
</template>


