<script setup>
import { ref, computed, onMounted, watch } from "vue";
import Avatar from "../Common/Avatar.vue";
import "./header.css";
import { Link, usePage } from "@inertiajs/vue3";
import { profileOptions as baseProfileOptions } from "./options";
import { router, useForm } from "@inertiajs/vue3";
import ResetPasswordModal from "./ResetPasswordModal.vue";
import Modal from "../Common/Modal.vue";
import axios from "axios";
import { route } from "ziggy-js";


const props = defineProps({
  isMobileView: Boolean,
});

const form = useForm({});
const notifications = ref([]);
const logout = () => {
  router.post(route('logout'), {}, {
    preserveState: false,
    preserveScroll: false,
    replace: true,
  })
}
const showPasswordResetModal = ref(false);
const notifOpen = ref(false);
const profileOpen = ref(false);
const page = usePage();
const authUser = computed(() => page.props.auth.user);
const hasRole = computed(() => {
  return page.props.switched_role || authUser.value?.roles?.[0]?.name;
});

const profileOptions = computed(() => {
  const options = [...baseProfileOptions];
  if (hasRole.value === 'Admin') {
    options.splice(2, 0, {
      label: "My Subscription",
      svg: `
          <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
              <path fill="currentColor" d="M18 4l2 4h-2v2h-2V8H8v2H6V8H4l2-4h12zm2 6v10H4V10h2v8h12v-8h2z"/>
          </svg>
      `,
      path: "", 
      route: "admin.subscription"
    });
  }

  return options;
});

const unreadNotifications = computed(() => {
  return notifications.value.filter(n => !n.is_read);
});

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const { data } = await axios.get(route('getNotifications'))

    notifications.value =
      data?.notifications ??
      data?.data ??
      data ??
      []
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
    notifications.value = []
  }
}


// Mark notification as read
const markAsRead = async (notificationId) => {
  try {
    await axios.post(route('notifications.read', { id: notificationId }));
    // Update local state
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.is_read = true;
      notification.read_at = new Date().toISOString();
    }
  } catch (error) {
    console.error("Failed to mark notification as read:", error);
  }
};

// Handle notification click
const handleNotificationClick = async (notification) => {
  // Mark as read when clicked
  if (!notification.is_read) {
    await markAsRead(notification.id);
  }

  // Handle different notification types
  if (notification.data?.type === 'video_call_invite' && notification.data?.link) {
    // Navigate to live consultation for video call invites
    window.location.href = notification.data.link;
  } else if (notification.data?.type === 'patient_invitation' && notification.data?.action_url) {
    // Navigate to patient details for patient invitation notifications
    window.location.href = notification.data.action_url;
  } else if (notification.data?.order_id) {
    // Navigate to order details for order notifications
    if (hasRole.value === 'Lab') {
      router.get(route('lab.labs.show', { lab: notification.data.order_id }));
    } else if (hasRole.value === 'Doctor') {
      router.get(route('doctor.orders.show', { order: notification.data.order_id }));
    } else {
      router.get(route('orders.show', { order: notification.data.order_id }));
    }
  } else if (notification.data?.appointment_id) {
    // Navigate to calendar/schedule page for appointment notifications
    // The calendar will show the appointment and allow viewing details
    if (hasRole.value === 'Doctor') {
      router.get(route('doctor.schedule.index'));
    } else if (hasRole.value === 'Patient') {
      router.get(route('patient.booking.list'));
    } else if (hasRole.value === 'Admin') {
      router.get(route('admin.allAppointments'));
    } else {
      // Fallback to main schedule
      router.get(route('doctor.schedule.index'));
    }
  }

  // Close notification dropdown
  notifOpen.value = false;
};

const handleAppointmentAction = async (notification, status) => {
  const appointmentId = notification.data.appointment_id;

  try {
    await axios.post(
      route('doctor.appointment.updateStatus', { id: appointmentId }),
      {
        status,
        notification_id: notification.id,
      }
    );

    // Remove notification from list
    notifications.value = notifications.value.filter(
      n => n.id !== notification.id
    );
    notifOpen.value = false;

  } catch (error) {
    console.error("Failed to update appointment status:", error);
  }
};


// Toggle notification dropdown
const toggleDropdown = () => {
  console.log("Toggle dropdown");
  notifOpen.value = !notifOpen.value;
  if (notifOpen.value) profileOpen.value = false;
};

const toggleProfile = () => {
  profileOpen.value = !profileOpen.value;
  if (profileOpen.value) notifOpen.value = false;
};

// Profile actions
const doProfileAction = (icon) => {
  profileOpen.value = false;

  if (icon === "Sign out") {    logout();
    return;
  }
  if (icon === "Change Password") {
    showPasswordResetModal.value = true;
    return;
  }

  const actionRoutes = {
    Profile: {
      Doctor: () => router.get(route("doctor.profile", authUser.id)),
      Patient: () => router.get(route("patient.profile")),
      Admin: () => router.get(route("admin.profile")),
    },
    Messages: {
      Doctor: () => router.get(route("doctor.messages.index")),
      Patient: () => router.get(route("patient.messages")),
      Admin: () => router.get(route("admin.messages")),
    },
    Branches: {
      Admin: () => router.get(route("admin.branches")),

    },
    "My Subscription": {
      Admin: () => router.get(route("admin.subscription")),
    }
  };

  if (actionRoutes[icon] && actionRoutes[icon][hasRole.value]) {
    actionRoutes[icon][hasRole.value]();
  }
};

// Close dropdown when clicking outside
const closeDropdowns = (event) => {
  if (!event.target.closest('.notification-container') && !event.target.closest('.profile-container')) {
    notifOpen.value = false;
    profileOpen.value = false;
  }
};

onMounted(() => {
  fetchNotifications();
  document.addEventListener('click', closeDropdowns);

  // Poll for new notifications every 30 seconds
  const pollInterval = setInterval(fetchNotifications, 30000);

  // Cleanup
  return () => {
    document.removeEventListener('click', closeDropdowns);
    clearInterval(pollInterval);
  };
});

// Watch for new notifications (if using Laravel Echo or similar)
watch(() => page.props.flash.notification, (newNotification) => {
  if (newNotification) {
    fetchNotifications(); // Refresh notifications
  }
});
// Get switched doctor data when in doctor mode
const switchedDoctor = computed(() => {
  return page.props.switched_role === 'Doctor' ? page.props.switched_doctor : null;
});

// Get the profile photo URL to display
const profilePhotoUrl = computed(() => {
  // If in Doctor mode and switched_doctor has a profile photo, use it
  if (switchedDoctor.value?.profile_photo_url) {
    return switchedDoctor.value.profile_photo_url;
  }
  // Fall back to user's profile photo
  return authUser.value?.profile_photo_url;
});

// Get sex for avatar fallback
const userSex = computed(() => {
  // If in Doctor mode, use switched doctor's sex
  if (switchedDoctor.value?.sex) {
    return switchedDoctor.value.sex;
  }
  // Fall back to user's sex
  return authUser.value?.sex;
});

// Determine if we should show doctor default avatars
const showDoctorAvatar = computed(() => {
  return !profilePhotoUrl.value && 
         (authUser.value?.role_id === 4 || authUser.value?.role_id === 5);
});

</script>

<template>
  <ul :class="isMobileView ? 'navbar-nav' : ''" class="ml-auto navbar-list d-flex align-items-center">
    <!-- 🔔 Notification Bell -->
    <li class="nav-item position-relative mr-2 d-flex notification-container" >
      <a  href="#" class="pointer search-toggle iq-waves-effect d-flex align-items-center rounded" @click.prevent="toggleDropdown">
        <i class="fa fa-bell-o fs-20" aria-hidden="true" style="color: #ff5dcd;" ></i>
        <span v-if="unreadNotifications.length > 0" class="count-mail">
          {{ unreadNotifications.length }}
        </span>
      </a>
     
      <!-- Messages Link -->
      <Link :href="route('chats.index')" class="search-toggle iq-waves-effect d-flex align-items-center ml-2" @click="notifOpen = false">
        <i class="fa fa-comments-o fs-20" aria-hidden="true"></i>
      </Link>

      <!-- Notifications Dropdown -->
      <div class="iq-sub-dropdown iq-notification-dropdown" v-show="notifOpen" :class="{ 'show': notifOpen }">
        <div class="iq-card shadow-none m-0">
          <div class="iq-card-body">
            <div class="bg-primary p-3 d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-white">All Notifications</h5>
              <small v-if="unreadNotifications.length > 0" class="badge badge-light text-dark">
                {{ unreadNotifications.length }} unread
              </small>
            </div>

            <div v-if="notifications.length === 0" class="text-center p-3">
              <p class="mb-0 text-muted">No new notifications</p>
            </div>

            <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
              <a v-for="notification in notifications" :key="notification.id" href="#" class="iq-sub-card"
                :class="{ 'notification-unread': !notification.is_read }"
                @click.prevent="handleNotificationClick(notification)">
                <div class="media align-items-center">
                  <div class="media-body">
                    <div class="d-flex justify-content-between align-items-end fs-14 p-1">
                      <h6 class="mb-0 text-wrap fs-14" :class="{'font-weight-bold': !notification.is_read, 'font-weight-normal': notification.is_read}">{{ typeof notification?.data?.message === 'string' ? notification?.data?.message : 'New Notification' }}</h6>
                      <small class="ml-2 font-size-12 text-nowrap text-muted">{{ notification?.created_at_human }}</small>
                    </div>
                    <!-- <p v-if="notification.data?.patient_name" class="mb-0 text-muted">
                      Patient: {{ notification.data?.patient_name }}
                    </p> -->

                    <!-- Action buttons for appointment notifications -->
                    <div
                      v-if="notification.type?.includes('AppointmentCreated') && !notification.is_read && hasRole === 'Doctor'"
                      class="mt-2">
                      <button @click.stop="handleAppointmentAction(notification, 'confirmed')"
                        class="btn btn-sm btn-success mr-2">
                        Approve
                      </button>
                      <button @click.stop="handleAppointmentAction(notification, 'rejected')"
                        class="btn btn-sm btn-danger">
                        Reject
                      </button>
                    </div>
                  </div>
                </div>
              </a>
            </div>

            <div v-if="notifications.length > 0" class="border-top p-2 text-center">
              <Link :href="route('notifications.index')" class="text-primary small" @click="notifOpen = false">
                View All Notifications
              </Link>
            </div>
          </div>
        </div>
      </div>
    </li>

    <!-- 👤 Profile Avatar -->
    <li class="nav-item pointer profile-container">
   
      <a href="#" class="search-toggle iq-waves-effect d-flex align-items-center rounded" @click.prevent="toggleProfile">
        
         <Avatar
                    :image="profilePhotoUrl"
                    alt="user"
                    v-if="profilePhotoUrl"
                 />
                <div v-else>
                    <div v-if="showDoctorAvatar">
                        <Avatar
                            image="/images/doctor_m_avtar.svg"
                            alt="user"
                            v-if="userSex === 'Male'"
                         />
                        <Avatar
                            image="/images/doctor_f_avtar.svg"
                            alt="user"
                            v-if="userSex === 'Female'"
                         />
                    </div>
                </div>
      </a>

      <div class="iq-sub-dropdown iq-user-dropdown" v-show="profileOpen" :class="{ 'show': profileOpen }">
        <div class="iq-card shadow-none m-0">
          <div class="iq-card-body p-0">
            <div class="bg-primary p-3">
              <h5 class="mb-0 text-white line-height">
                {{ hasRole === 'Admin' ? authUser?.name : authUser?.doctor?.name || authUser?.patient?.name || authUser?.name ||authUser?.hospital?.name }}
              </h5>
              <small class="text-white-50">{{ hasRole }}</small>
            </div>
            <div v-for="option in profileOptions" :key="option.id"
              class="iq-sub-card iq-bg-primary-hover cursor-pointer" :style="{ animationDelay: option.delay + 'ms' }"
              @click="doProfileAction(option.label)">
              <div class="media align-items-center">
                <div class="rounded iq-card-icon bg-light">
                  <i v-html="option.svg"></i>
                </div>
                <div class="media-body ml-3">
                  <h6 class="mb-0">{{ option.label }}</h6>
                </div>
              </div>
            </div>
            
            <div class="d-inline-block w-100 text-center p-2">
              <button class="btn btn-primary dark-btn-primary" @click="logout()" role="button">
                Sign out
                <i class="ri-login-box-line ml-2"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </li>
  </ul>

  <!-- 🔐 Change Password Modal -->
  <Modal :isOpen="showPasswordResetModal" title="Change Password" @close="() => (showPasswordResetModal = false)" size="lg">
    <ResetPasswordModal @close="() => (showPasswordResetModal = false)" />
  </Modal>
</template>

<style scoped>
.count-mail {
  position: absolute;
  top: -5px;
  right: -5px;
  border-radius: 50%;
  font-size: 10px;
  padding: 2px 5px;
  background: #ff4444;
  color: white;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.iq-sub-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 360px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  z-index: 1050;
  margin-top: 10px;
}

.iq-sub-card {
  display: block;
  padding: 12px;
  border-bottom: 1px solid #f1f1f1;
  text-decoration: none;
  color: inherit;
  transition: background-color 0.2s ease;
}

.notification-unread {
  background-color: #f8f9fa;
  /* A light grey, similar to bg-light */
  font-weight: bold;
  color: #212529;
}

.iq-sub-card:hover {
  background-color: #f8f9fa;
}

.iq-sub-card:last-child {
  border-bottom: none;
}

.notification-list {
  scrollbar-width: thin;
}

.notification-list::-webkit-scrollbar {
  width: 4px;
}

.notification-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notification-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.navbar-list li a:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.doctor-name {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  white-space: nowrap;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  .iq-sub-dropdown {
    position: fixed;
    top: 70px;
    right: 10px;
    left: 10px;
  }
}
</style>