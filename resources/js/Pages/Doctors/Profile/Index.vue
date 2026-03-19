<script setup>
import { ref, nextTick, computed } from "vue";
import DoctorProfileEdit from "../../Modals/DoctorProfileEdit.vue";
import AuthLayout from "../../../Layouts/AuthLayout.vue";
import Notifications from "./Partials/Notifications.vue";
import Modal from "../../../Components/Common/Modal.vue";

const props = defineProps({
    DoctorDetail: String,
    doctor: Object,
    specialties: Object,
    activities: Array,
});

const childComponentRef = ref();
const showProfileEditModal = ref(false);

const closeProfileEditModal = () => {
    showProfileEditModal.value = false;
};

const edit = async (doctor) => {
    showProfileEditModal.value = true;
    await nextTick();
    if (childComponentRef.value) {
        childComponentRef.value.update(doctor);
    }
};
const formatAddress = (address) =>
    [
        address?.address_1,
        address?.address_2,
        address?.city,
        address?.state,
        address?.country,
        address?.zip
    ].filter(Boolean).join(', ');

const formatTime = (time) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
};

const hospitalTimings = computed(() => {
    const timings = props.doctor?.hospital?.timings || props.doctor?.timings;
    return Array.isArray(timings) ? timings : [];
});
</script>

<template>
<AuthLayout title="Doctor Profile" description="View and manage your doctor profile" heading="Doctor Profile">
        <div id="content-page bg-color-white-lilac">
            <div class="row">
                <div class="col-sm-12">
                    <div class="iq-card">
                        <div class="iq-card-body profile-page p-0">
                            <div class="profile-header">
                                <div class="cover-container overlay">
                                    
                                </div>
                                <ul class="header-nav d-flex flex-wrap justify-end p-0 m-0">
                                    <li>
                                        <div class="profile-edit cursor-pointer" @click="edit(props.doctor)"><i
                                                class="ri-pencil-line"></i>
                                        </div>
                                    </li>
                                </ul>
                                <div class="profile-info p-4">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="user-detail pl-5">
                                                <div class="d-flex flex-wrap align-items-center">
                                                    <div class="profile-img relative">
                                                        <div v-if="doctor?.profile_photo_url">
                                                            <img :src="doctor.profile_photo_url"
                                                                class="avatar-130 img-fluid rounded-circle" align="left"
                                                                alt="profile photo" />
                                                        </div>
                                                        <div v-else class="bg-light-gradient rounded-circle">
                                                            <!-- Fixed conditions - use === for strict comparison -->
                                                            <img v-if="doctor?.sex === 'Male'"
                                                                src="/images/doctor_m_avtar.svg"
                                                                alt="male doctor avatar" class="avatar-130 img-fluid" />
                                                            <img v-else-if="doctor?.sex === 'Female'"
                                                                src="/images/doctor_f_avtar.svg"
                                                                alt="female doctor avatar"
                                                                class="avatar-130 img-fluid" />
                                                            <img v-else src="/images/doctor_m_avtar.svg"
                                                                alt="default doctor avatar"
                                                                class="avatar-130 img-fluid" />
                                                        </div>
                                                    </div>
                                                    <div class="profile-detail d-flex text-align-center pl-4">
                                                        <h3>
                                                            {{ doctor?.first_name || '' }} {{ doctor?.last_name || '' }}
                                                            
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <ul id="pills-tab"
                                                class="profile-feed-items d-flex justify-content-end nav nav-pills">
                                                <li class="nav-item">
                                                    <a id="pills-profile-tab" data-toggle="tab" href="#profile-profile"
                                                        role="tab" aria-controls="pills-contact"
                                                        class="nav-link active">
                                                        Profile
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a id="pills-activity-tab" data-toggle="tab"
                                                        href="#profile-activity" role="tab"
                                                        aria-controls="pills-profile" class="nav-link">
                                                        Activity
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-lg-3 profile-left">
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                        <h4 class="card-title d-flex align-items-center"><i class="ri-contacts-book-2-fill text-primary mr-2"></i>Contact</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center"></div>
                                </div>
                                <div class="iq-card-body">
                                    <ul class="m-0 p-0">
                                        <li class="d-flex mb-2">
                                            <p class="news-detail mb-0 text-md-nowrap text-wrap">
                                                <i class="ri-mail-fill"></i>
                                                {{ doctor?.user?.email }}
                                            </p>
                                        </li>
                                        <li class="d-flex">
                                            <p class="mb-0 text-md-nowrap text-wrap">
                                                <i class="ri-smartphone-fill"></i>
                                                {{ doctor?.user?.mobile }}
                                            </p>

                                        </li>
                                    </ul>
                                </div>
                                <!---->
                            </div>
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title d-flex">
                                        <h4 class="card-title d-flex align-items-center"><i class="ri-map-pin-user-fill text-primary mr-2"></i>Location</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <ul class="m-0 p-0">
                                        <li class="d-flex mb-2">
                                            <div class="news-icon">
                                                <i class="fas fa-map-marker-alt text-info"></i>
                                            </div>
                                            {{ formatAddress(doctor?.user?.address) }}
                                        </li>
                                        <iframe class="w-100"
                                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902543.2003194243!2d-118.04220880485131!3d36.56083290513502!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80be29b9f4abb783%3A0x4757dc6be1305318!2sInyo%20National%20Forest!5e0!3m2!1sen!2sin!4v1576668158879!5m2!1sen!2sin"
                                            height="200" allowfullscreen=""></iframe>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="col-lg-6 profile-center">
                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="profile-profile" role="tabpanel">
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title d-flex">
                                                <h4 class="card-title d-flex align-items-center"><i class="ri-user-3-fill text-primary mr-2"></i>About Me</h4>

                                            </div>
                                        </div>
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="m-0 p-0 w-100">
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <h6>Gender: <span class="text-muted font-weight-normal">{{ doctor?.sex || doctor?.user?.sex || '-' }}</span></h6>
                                                    </div>
                                                    <div class="col-6">
                                                        <h6>Experience: <span class="text-muted font-weight-normal">{{ doctor?.experience ? doctor.experience + ' Years' : '-' }}</span></h6>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                     <div class="user-bio col-12">
                                                        <h6>About</h6>
                                                        <p v-if="doctor?.about">
                                                          <span class="text-muted font-weight-normal">{{ doctor?.about }}</span>
                                                        </p>
                                                        <p v-else>
                                                            No description
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title">
                                                <h4 class="card-title d-flex align-items-center"><i class="ri-calendar-event-fill text-primary mr-2"></i>Schedule</h4>
                                            </div>
                                        </div>
                                        <div class="iq-card-body">
                                            <ul class="list-unstyled m-0 p-0">
                                                 <template v-if="hospitalTimings.length > 0">
                                                     <li v-for="timing in hospitalTimings" :key="timing?.id" class="d-flex justify-content-between mb-2 border-bottom pb-1">
                                                         <span class="text-capitalize font-weight-bold">{{ timing?.day_of_week }}</span>
                                                        <span v-if="!timing?.is_closed && timing?.open_time" class="text-primary">
                                                            {{timing?.open_time }} - {{timing?.close_time}}
                                                        </span>
                                                        <span v-else class="text-danger">Closed</span>
                                                     </li>
                                                </template>
                                                <li v-else class="text-center text-muted">
                                                    No schedule available
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade show" id="profile-activity" role="tabpanel">
                                    <div class="iq-card">
                                        <div class="iq-card-header d-flex justify-content-between">
                                            <div class="iq-header-title">
                                                <h4 class="card-title d-flex align-items-center"><i class="ri-time-line text-primary mr-2"></i>Activity timeline</h4>
                                            </div>
                                            <div class="iq-card-header-toolbar d-flex align-items-center">
                                                <!-- Dropdown can be implemented later if needed -->
                                            </div>
                                        </div>
                                        <div class="iq-card-body">
                                            <ul class="iq-timeline">
                                                <li v-if="!activities || activities.length === 0">
                                                    <div class="timeline-dots"></div>
                                                    <div class="d-inline-block w-100">
                                                        <p>No activities to display.</p>
                                                    </div>
                                                </li>
                                                <li v-for="activity in activities"
                                                    :key="activity.id + '-' + activity.type">
                                                    <div :class="`timeline-dots border-${activity.color}`"></div>
                                                    <h6 class="float-left mb-1">{{ activity.title }}</h6>
                                                    <small class="float-right mt-1">{{
                                                        activity.date
                                                    }}</small>
                                                    <div class="d-inline-block w-100">
                                                        <p>{{ activity.description }}</p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 profile-right">
                            <div class="iq-card">
                                <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title d-flex">
                                        <h4 class="card-title d-flex align-items-center"><i class="ri-award-fill text-primary mr-2"></i>Speciality</h4>
                                    </div>
                                </div>
                                <div class="iq-card-body">
                                    <ul class="media-story m-0 p-0">
                                        <li class="col-d-12">
                                            <div class="stories-data">
                                                <div class="badge font-size-14 m-1"
                                                    v-for="speciality in doctor?.specialities" :key="specialty">
                                                    {{ speciality?.name }}
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- <Notifications /> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal :isOpen="showProfileEditModal" title="Edit Profile" @close="closeProfileEditModal" size="xl">
            <DoctorProfileEdit :slug="doctor.slug" :doctor="doctor" :specialties="specialties"
                @close="closeProfileEditModal" :doctorSpecialty="doctorSpecialty" :language="language"
                ref="childComponentRef" />
        </Modal>
    </AuthLayout>
</template>
<style scoped>
.header-nav {
    position: absolute;
    top: 20px;
    right: 20px;
}

.news-icon {
    font-size: 20px;
    margin-right: 5px !important;
}

.header-nav li {
    list-style: none;
    margin-left: 10px;
}

.header-nav div {
    background: rgba(255, 255, 255, 0.9);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
}

.header-nav div:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.iq-card .iq-card-header{
    padding: 1rem 0.5rem!important;
}
.cover-container {
    background: #09acff2b;
    height: 250px;
}
.overlay:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background:#09acff2b;
}
</style>