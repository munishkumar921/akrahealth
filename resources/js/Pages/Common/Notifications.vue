<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import Table from "@/Components/Table/Table.vue";
import { ref, computed, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  notifications: Object, // Laravel paginated object
  filters: Object,
});

const columns = [
    { label: 'Notification', key: 'data.message', type: 'slot', slot: 'message' },
    { label: 'Patient', key: 'data.patient_name' },
    { label: 'Date', key: 'created_at_human' },
];

const page = usePage();
const authUser = computed(() => page.props.auth.user);
const role = computed(() => authUser.value?.roles?.[0]?.name);

const filterStatus = ref(props.filters.read_status || 'all');

// Use a local ref to make notifications mutable for deletion
const localNotifications = ref([...(props.notifications?.data || [])]);

watch(() => props.notifications.data, (newData) => {
  localNotifications.value = [...newData];
});

const tableData = computed(() => ({
    ...props.notifications,
    data: localNotifications.value
}));

const fetchNotifications = (status) => {
  filterStatus.value = status;
  router.get(route('notifications.index'), { read_status: status }, {
    preserveState: true,
    replace: true,
  });
};

const markAsRead = async (notification) => {
  if (notification.is_read) return;
  try {
    await axios.post(route('notifications.read', { id: notification.id }));
    notification.is_read = true;
  } catch (error) {
    console.error("Failed to mark notification as read:", error);
  }
};

const handleNotificationClick = async (notification) => {
  await markAsRead(notification);

  if (notification.data?.appointment_id) {
    // Navigate to calendar/schedule page for appointment notifications
    // The calendar will show the appointment and allow viewing details
    if (role.value === 'Doctor') {
      router.get(route('doctor.schedule.index'));
    } else if (role.value === 'Patient') {
      router.get(route('patient.booking.list'));
    } else if (role.value === 'Admin') {
      router.get(route('admin.allAppointments'));
    } else {
      // Fallback to main schedule
      router.get(route('doctor.schedule.index'));
    }
  } else if (notification.data?.order_id) {
    // Navigate to order details for order notifications
    if (role.value === 'Lab') {
      router.get(route('lab.labs.show', { lab: notification.data.order_id }));
    } else if (role.value === 'Doctor') {
      router.get(route('doctor.orders.show', { order: notification.data.order_id }));
    } else {
      router.get(route('orders.show', { order: notification.data.order_id }));
    }
  }
};

const deleteNotification = async (notificationId) => {
  Swal.fire(confirmSettings("Are you sure you want to delete this medication?")).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(route('notifications.destroy', { id: notificationId }));
                localNotifications.value = localNotifications.value.filter(n => n.id !== notificationId);
                Swal.fire('Deleted!','Your notification has been deleted.','success');
            } catch (error) {
                console.error("Failed to delete notification:", error);
                Swal.fire('Error!','Could not delete the notification.','error');
            }
        }
    });
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.read-all'));
        localNotifications.value.forEach(n => n.is_read = true);
        // Optionally refetch to be sure
        fetchNotifications(filterStatus.value);
    } catch (error) {
        console.error("Failed to mark all as read:", error);
    }
};

</script>
<template>
  <AuthLayout title="All Notifications">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between align-items-center">
                        <div class="iq-header-title">
                            <h4 class="card-title">All Notifications</h4>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn" :class="filterStatus === 'all' ? 'btn-primary' : 'btn-outline-primary'" @click="fetchNotifications('all')">All</button>
                                <button type="button" class="btn" :class="filterStatus === 'unread' ? 'btn-primary' : 'btn-outline-primary'" @click="fetchNotifications('unread')">Unread</button>
                                <button type="button" class="btn" :class="filterStatus === 'read' ? 'btn-primary' : 'btn-outline-primary'" @click="fetchNotifications('read')">Read</button>
                            </div>
                            <button class="btn btn-sm btn-info ml-3" @click="markAllAsRead">Mark all as read</button>
                        </div>
                    </div>
                    <div class="iq-card-body">
                        <Table 
                            :columns="columns" 
                            :data="tableData" 
                            :searchShow="false"
                            @cell-click="({ row }) => handleNotificationClick(row)"
                        >
                            <template #message="{ row }">
                                <div :class="{ 'font-weight-bold': !row.is_read }">
                                    {{ row.data?.message || 'Notification' }}
                                    <span v-if="!row.is_read" class="badge badge-primary ml-2">New</span>
                                </div>
                            </template>
                            <template #actions="{ row }">
                                <button class="btn btn-icon btn-danger" @click.stop="deleteNotification(row.id)" title="Delete Notification">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </AuthLayout>
</template>

<style scoped>
.btn-icon {
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
    line-height: 1.5;
}
</style>