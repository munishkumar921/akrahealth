<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref } from "vue";
import Swal from "sweetalert2";

// ===========================
// TABLE COLUMNS
// ===========================
const columns = [
    { label: "Type", key: "type" },
    { label: "Created On", key: "created_on" },
    { label: "Subject", key: "subject" },
    { label: "User Email", key: "email" },
    { label: "Country", key: "country" },
    { label: "Read On", key: "read_on" },
   
];

// ===========================
// SAMPLE DATA
// ===========================
const sample = ref([
    {
        id: 1,
        type: "Email",
        created_on: "2025-01-01",
        subject: "Welcome to System",
        email: "user1@example.com",
        country: "India",
        read_on: "2025-01-02",
    },
    {
        id: 2,
        type: "SMS",
        created_on: "2025-01-05",
        subject: "New Login Detected",
        email: "user2@example.com",
        country: "USA",
        read_on: null,
    },
    {
        id: 3,
        type: "Push",
        created_on: "2025-01-10",
        subject: "Subscription Expiring",
        email: "user3@example.com",
        country: "UK",
        read_on: null,
    }
]);

// TABLE FORMAT (required by your Table.vue)
const tableData = ref({
    data: sample.value,
    links: [],
});

// ===========================
// VIEW POPUP
// ===========================
const openViewPopup = (row) => {
    row.read_on = new Date().toISOString().split("T")[0];

    Swal.fire({
        title: "Notification Details",
        html: `
            <b>Type:</b> ${row.type}<br>
            <b>Created:</b> ${row.created_on}<br>
            <b>Subject:</b> ${row.subject}<br>
            <b>Email:</b> ${row.email}<br>
            <b>Country:</b> ${row.country}<br>
            <b>Read On:</b> ${row.read_on}<br>
        `,
        icon: "info",
        confirmButtonText: "Close",
        width: "450px"
    });
};

// ===========================
// DELETE SINGLE
// ===========================
const deleteRow = (row) => {
    Swal.fire({
        title: "Delete Notification?",
        text: "This cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Delete",
    }).then((res) => {
        if (res.isConfirmed) {
            sample.value = sample.value.filter(n => n.id !== row.id);
            tableData.value.data = sample.value;

            Swal.fire("Deleted!", "Notification removed.", "success");
        }
    });
};

// ===========================
// MARK ALL AS READ
// ===========================
const markAll = () => {
    const today = new Date().toISOString().split("T")[0];
    sample.value.forEach(n => n.read_on = today);

    Swal.fire("Updated", "All notifications marked as read.", "success");
};

// ===========================
// DELETE ALL
// ===========================
const deleteAll = () => {
    Swal.fire({
        title: "Delete All Notifications?",
        text: "This action cannot be undone",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete All",
    }).then((res) => {
        if (res.isConfirmed) {
            sample.value = [];
            tableData.value.data = [];
            Swal.fire("Deleted!", "All notifications removed.", "success");
        }
    });
};
</script>

<template>
    <AuthLayout>
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="page-title mb-0">System Notifications</h4>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary" @click="markAll">Mark All As Read</button>
                <button class="btn btn-danger" @click="deleteAll">Delete All Notifications</button>
            </div>
        </div>

        <!-- TABLE -->
        <Table :columns="columns" :data="tableData" :searchShow="true">

            <!-- ACTIONS SLOT -->
            <template #actions="{ row }">
                <div class="d-flex gap-2 justify-content-center">

                    <!-- VIEW ICON -->
                    <button 
                        class="btn btn-sm btn-light border"
                        @click.prevent="openViewPopup(row)"
                        title="View"
                    >
                        <i class="bi bi-eye-fill text-primary"></i>
                    </button>

                    <!-- DELETE ICON -->
                    <button 
                        class="btn btn-sm btn-light border"
                        @click.prevent="deleteRow(row)"
                        title="Delete"
                    >
                        <i class="bi bi-trash-fill text-danger"></i>
                    </button>

                </div>
            </template>

        </Table>
    </AuthLayout>
</template>

<style scoped>
.btn-sm {
    padding: 4px 8px;
}
</style>
