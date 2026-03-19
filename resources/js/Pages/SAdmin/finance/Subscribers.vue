<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import ActionButtons from "@/Components/Table/Partials/ActionButtons.vue";
import { ref, computed } from "vue";
import Swal from "sweetalert2/dist/sweetalert2.js";
import { router } from "@inertiajs/vue3";

// Define props to receive data from backend
const props = defineProps({
    subscribers: {
        type: Array,
        default: () => []
    },
    keyword: String
});

// Use real data from props, fallback to empty array if not provided
const subscribersData = ref(props.subscribers);

/*
|--------------------------------------------------------------------------
| TABLE COLUMNS
|--------------------------------------------------------------------------
*/
const columns = [
    { label: "User", key: "user" },
    { label: "Status", key: "status" },
    { label: "Plan Name", key: "plan_name" },
    { label: "Subscribed On", key: "subscribed_on" },
    { label: "Subscription ID", key: "subscription_id" },
    { label: "Pricing Plan", key: "pricing_plan" },
    { label: "Next Payment", key: "next_payment" },
];

/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/
const deleteSubscription = (row) => {
    Swal.fire({
        title: "Cancel Subscription?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, Cancel",
    }).then((r) => {
        if (r.isConfirmed) {
            const index = subscribersData.value.findIndex((s) => s.id === row.id);
            if (index !== -1) {
                subscribersData.value.splice(index, 1);
                Swal.fire("Cancelled", "Subscription cancelled successfully.", "success");
            }
        }
    });
};

/*
|--------------------------------------------------------------------------
| TABLE DATA (computed)
|--------------------------------------------------------------------------
*/
const tableDataProp = computed(() => {
    return { data: subscribersData.value, links: [] };
});

/*
|--------------------------------------------------------------------------
| ACTION BUTTONS (Top Right)
|--------------------------------------------------------------------------
*/

</script>

<template>
    <AuthLayout title="Subscribers" description="Subscribers" heading="Subscribers">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="text-xl mb-0">Subscribers</h3>
        </div>

        <!-- TABLE -->
        <Table :columns="columns" :data="tableDataProp" table="subscribers" :search="keyword">
            <template #actions="{ row }">
                <button class="btn btn-danger icon-btn" @click.prevent="deleteSubscription(row)">
                    <i class="bi bi-trash"></i>
                </button>
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
    background: #ef4444;
    cursor: pointer;
}

.icon-btn i {
    font-size: 14px;
}
</style>
