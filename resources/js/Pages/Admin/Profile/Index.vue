<script setup>
import { h, ref, onMounted, computed, nextTick } from "vue"
import Modal from "@/Components/Common/Modal.vue"
import AuthLayout from "@/Layouts/AuthLayout.vue"
import AddBranch from "@/Pages/Modals/AddBranch.vue"

const props = defineProps({
  hospital: Object,
  branches: Array,
})

const isOpenModal = ref(false)
const modalTitle = ref('Add Branch');
const activeBranchId = ref(null) // ⭐ accordion state - set first branch as default
const childComponentRef = ref(null)

const filteredBranches = computed(() => {
  return props.branches?.filter(branch => branch.main_branch_id !== null) || []
})

const mainBranch = computed(() => {
  // First check if there's a main branch in branches array (main_branch_id === null)
  const branchMain = props.branches?.find(branch => branch.main_branch_id === null);
  if (branchMain) {
    return branchMain;
  }
  // Otherwise return the hospital prop (main branch)
  return props.hospital;
})

const getSchedules = (branch) => {
   // Check if timings (schedules) exist on the branch/hospital
  const timings = branch?.timings || [];
  const daysOrder = { 'monday': 1, 'tuesday': 2, 'wednesday': 3, 'thursday': 4, 'friday': 5, 'saturday': 6, 'sunday': 7 };
  
  return [...timings].sort((a, b) => {
      const dayA = (a.day_of_week || '').toLowerCase();
      const dayB = (b.day_of_week || '').toLowerCase();
      return (daysOrder[dayA] || 0) - (daysOrder[dayB] || 0);
  });
}

onMounted(() => {
  // Set first branch as open by default
  if (filteredBranches.value && filteredBranches.value.length > 0) {
    activeBranchId.value = filteredBranches.value[0].id
  }
})
const openAddModal = () => {
  modalTitle.value = 'Add Branch';
  isOpenModal.value = true
}

const closeModal = () => {
  isOpenModal.value = false
}

const toggleAccordion = (id) => {
  activeBranchId.value = activeBranchId.value === id ? null : id
}


const openEditModal = (branch, tab) => {
  if (tab === 'profile') {
    modalTitle.value = 'Edit Profile';
  } else if (tab === 'contact') {
    modalTitle.value = 'Edit Contact';
  } else if (tab === 'location') {
    modalTitle.value = 'Edit Location';
  } else if (tab === 'schedule') {
    modalTitle.value = 'Edit Schedule';
  } else {
    modalTitle.value = 'Edit Branch';
  }
  isOpenModal.value = true;
  nextTick(() => {
    setTimeout(() => {
      if (childComponentRef.value?.update) {
        childComponentRef.value.update(branch, tab);
      }
    }, 50);
  });

}

const formatAddress = (hospital) =>
    [
    hospital?.street_address1 || hospital?.street_address_1,
    hospital?.street_address2 || hospital?.street_address_2,
    hospital?.city,
    hospital?.state,
    hospital?.country,
    hospital?.zip
    ].filter(Boolean).join(', ');

const mapEmbedUrl = computed(() => {
  const address = formatAddress(mainBranch.value);
  if (!address) {
    return 'https://maps.google.com/maps?q=India&output=embed';
  }

  return `https://maps.google.com/maps?q=${encodeURIComponent(address)}&output=embed`;
});

</script>

<template>
  <AuthLayout title="Hospital Profile" description="Manage your hospital profile and branches" heading="Hospital Profile">
    <div id="content-page">
      <div class="row">
        <div class="col-sm-12">
          <div class="iq-card">
            <div class="iq-card-body profile-page p-0">
              <div class="profile-header">
                <div class="cover-container overlay"></div>
                <ul class="header-nav d-flex flex-wrap justify-end p-0 m-0">
               
            </ul>
 
                <div class="profile-info p-4">
                  <div class="row">
                    <div class="col-sm-12 col-md-6">
                      <div class="user-detail pl-5">
                         <div class="d-flex flex-wrap align-items-center">
                          <div class="profile-img relative">
                            <img :src="mainBranch?.user?.profile_photo_url || hospital?.user?.profile_photo_url"
                                class="avatar-130 img-fluid rounded-circle"
                                alt="profile photo" />
                                 <div class="profile-img-edit cursor-pointer" @click="openEditModal(mainBranch, 'profile')"><i
                            class="ri-pencil-line"></i>
                    </div>
                          </div>
                          <div class="profile-detail d-flex text-align-center pl-4">
                            <h3>{{ mainBranch?.name || hospital?.name || 'Hospital Name' }}</h3>
                           </div>
                           
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-12">
          <div class="row">
            <!-- Left Column: Contact & Location -->
            <div class="col-lg-4 profile-left">
              <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                  <div class="iq-header-title row align-items-center ">
                    <h4 class="card-title d-flex align-items-center"><i class="ri-contacts-book-2-fill text-primary mr-2"></i>Contact</h4>
                   
                  </div>
                   <div class="profile cursor-pointer" @click="openEditModal(mainBranch ,'contact')"><i
                            class="ri-pencil-line"></i>
                    </div>
                </div>
                <div class="iq-card-body">
                  <ul class="m-0 p-0">
                    <li class="d-flex mb-2">
                      <p class="news-detail mb-0 text-md-nowrap text-wrap">
                        <i class="ri-mail-fill mr-2"></i>
                        {{ mainBranch?.email || mainBranch?.user?.email || hospital?.email || hospital?.user?.email || 'No email' }}
                      </p>
                    </li>
                    <li class="d-flex">
                      <p class="mb-0 text-md-nowrap text-wrap">
                        <i class="ri-smartphone-fill mr-2"></i>
                        {{ mainBranch?.phone || mainBranch?.user?.phone || hospital?.phone || hospital?.user?.phone || 'No mobile' }}
                      </p>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                  <div class="iq-header-title d-flex">
                    <h4 class="card-title d-flex align-items-center"><i class="ri-map-pin-user-fill text-primary mr-2"></i>Location</h4>
                  </div>
                  <div class="profile cursor-pointer" @click="openEditModal(mainBranch ,'location')"><i
                            class="ri-pencil-line"></i>
                    </div>
                </div>
                <div class="iq-card-body">
                  <ul class="m-0 p-0">
                    <li class="d-flex mb-2">
                      <div class="news-icon">
                        <i class="fas fa-map-marker-alt text-info mr-2"></i>
                      </div>
                      {{ formatAddress(mainBranch) || 'No address provided' }}
                    </li>
                     <iframe class="w-100"
                                            :src="mapEmbedUrl"
                                            height="200" allowfullscreen=""></iframe>
                  </ul>
                </div>
              </div>

              <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                  <div class="iq-header-title">
                    <h4 class="card-title d-flex align-items-center"><i class="ri-time-fill text-primary mr-2"></i>Schedule</h4>
                  </div>
                   <div class="profile cursor-pointer" @click="openEditModal(mainBranch ,'schedule')"><i
                            class="ri-pencil-line"></i>
                    </div>
                </div>
                <div class="iq-card-body">
                  <ul class="list-unstyled m-0 p-0" v-if="getSchedules(mainBranch).length">
                    <li v-for="schedule in getSchedules(mainBranch)" :key="schedule.id" class="d-flex justify-content-between mb-2 border-bottom pb-2">
                      <span class="text-capitalize">{{ schedule.day_of_week }}</span>
                      
                      <span class="badge badge-light text-primary" v-if="schedule.open_time">
                        {{ schedule.open_time }} - {{ schedule.close_time }}
                      </span>
                      <span class="badge badge-light text-danger" v-else>Closed</span>
                    </li>
                  </ul>
                  <div v-else>
                    <p class="text-muted">No schedule available.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Center Column: About & Branches -->
            <div class="col-lg-8 profile-center">
              <!-- Branches Section -->
              <div class="iq-card">
                <div class="iq-card-header d-flex justify-content-between">
                  <div class="iq-header-title">
                    <h4 class="card-title d-flex align-items-center"><i class="ri-building-fill text-primary mr-2"></i>Branches</h4>
                  </div>
                  <div class="iq-card-header-toolbar d-flex align-items-center">
                    <button class="btn btn-primary" @click="openAddModal">Add Branch</button>
                  </div>
                </div>
                <div class="iq-card-body">
                  <div v-if="filteredBranches && filteredBranches.length > 0" class="branch-list">
                    <div v-for="branch in filteredBranches" :key="branch.id" class="border rounded mb-3">
                      <!-- Branch Header -->
                      <div class="d-flex justify-content-between align-items-center p-3 bg-primary pointer"
                        @click="toggleAccordion(branch.id)" style="border-radius: 5px;">
                        <div class="d-flex align-items-center">
                          <img v-if="!branch.main_branch_id && branch.practice_logo_url"
                            :src="branch.practice_logo_url" alt="Branch Logo" class="me-3 rounded"
                            style="width:40px; height:40px; object-fit:cover;" />
                          <h6 class="mb-0 text-white">{{branch.city}}, {{ branch.name }} <span class="text-white small">({{ branch.main_branch_id !== null ? 'Sub' : 'Main' }})</span></h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <button class="btn btn-outline-light" @click.stop="openEditModal(branch)">Edit</button>
                          <i class="ri-arrow-down-s-line fs-5 transition" :class="{ 'rotate-180': activeBranchId === branch.id }"></i>
                        </div>
                      </div>

                      <!-- Branch Body -->
                      <transition name="accordion">
                        <div v-show="activeBranchId === branch.id" class="p-3 border-top bg-white">
                          <div class="row g-3">
                            <div class="col-md-6"><strong>Address:</strong> {{ branch.street_address1 || '-' }}</div>
                            <div class="col-md-6"><strong>City/State:</strong> {{ branch.city }}, {{ branch.state }}</div>
                            <div class="col-md-6"><strong>Phone:</strong> {{ branch.phone || '-' }}</div>
                            <div class="col-md-6"><strong>Email:</strong> {{ branch.email || '-' }}</div>
                            <div class="col-md-6"><strong>Status:</strong>
                              <span :class="'badge badge-' + (branch.is_active == 1 ? 'success' : 'danger')">
                                {{ branch.is_active == 1 ? 'Active' : 'Inactive' }}
                              </span>
                            </div>
                            <div class="col-12" v-if="getSchedules(branch).length">
                                <hr>
                                <h6 class="mt-2">Schedule</h6>
                                <ul class="list-unstyled m-0 p-0">
                                    <li v-for="schedule in getSchedules(branch)" :key="schedule.id" class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-capitalize">{{ schedule.day_of_week }}</span>
                                    <span class="badge badge-light text-primary" v-if="schedule.open_time">
                                        {{ schedule.open_time }} - {{ schedule.close_time }}
                                    </span>
                                    <span class="badge badge-light text-danger" v-else>Closed</span>
                                    </li>
                                </ul>
                            </div>
                          </div>
                        </div>
                      </transition>
                    </div>
                  </div>
                  <div v-else class="text-center p-3">
                    <p class="text-muted">No branches found.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal :isOpen="isOpenModal" :title="modalTitle" @close="closeModal" size="xl">
      <AddBranch ref="childComponentRef" :hospital="props.hospital" @close="closeModal" />
    </Modal>
  </AuthLayout>
</template>

<style scoped>
.profile-img {
    position: relative;
}
.profile-img-edit {
    position: absolute;
    right: 5px;
    bottom: 5px;
    background: rgba(255, 255, 255, 0.9);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.profile-img-edit:hover {
    background: #fff;
    transform: scale(1.1);
}
.profile{
    align-items: center;
    justify-content: center;
    text-decoration: none;
    display: flex;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9)!important;

}
.profile:hover{
    background: rgb(247, 247, 249, 0.9)!important;
}

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
