<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import ProfileEdit from "../Modals/DoctorProfileEdit.vue";
import { ref } from 'vue';
const props = defineProps({
   DoctorDetail: String,
   doctor: Object,
   specialties: Object,
   language:Object,
   specialties:Object,
   doctorSpecialty:Object,
});
const childComponentRef = ref();
const edit = (doctor) => {
childComponentRef.value.update(doctor);
}
</script>
<template>
   <AppLayout title="List of Pharmacies | AKRAHEALTH"
      description="Check out the reliable pharmacies that deliver medicines faster, safely and with care ">
      <div id="content-page bg-color-white-lilac">
         <div class="container-fluid pt-5 ">
            <div class="row">
               <div class="col-sm-12">
                  <div class="iq-card">
                     <div class="iq-card-body profile-page p-0">
                        <div class="profile-header">
                           <div class="cover-container overlay">
                              <img src="/images/doctor_profile.svg" alt="profile-bg" class="rounded img-fluid w-100">
                              <ul class="header-nav d-flex flex-wrap justify-end p-0 m-0">
                                 <li><a href="javascript:void();"><i class="ri-pencil-line"></i></a></li>
                                 <li><a href="javascript:void();"><i class="ri-settings-4-line"></i></a></li>
                              </ul>
                           </div>
                           <div class="profile-info p-4">
                              <div class="row">
                                 <div class="col-sm-12 col-md-6">
                                    <div class="user-detail pl-5">
                                       <div class="d-flex flex-wrap align-items-center">
                                          <div class="profile-img relative">
                                             <div class="rounded-circle bg-light-gradient" v-if="doctor?.profile_photo_url">
                                                <img :src="doctor?.profile_photo_url"
                                                class="avatar-130 img-fluid" align="left" alt="profile_photo" />
                                             </div>
                                             <div class="bg-light-gradient rounded-circle" v-else>
                                                <img v-if="doctor?.sex === 'Male'" src="/images/doctor_m_avtar.svg"
                                                   alt="profile-img" class="avatar-130 img-fluid" />
                                                <img v-if="doctor?.sex == 'Female'" src="/images/doctor_f_avtar.svg" alt="profile-img"
                                                   class="avatar-130 img-fluid" />
                                                   <img v-if="doctor?.sex === 'Other'" src="/images/doctor_m_avtar.svg"
                                                   alt="profile-img" class="avatar-130 img-fluid" />
                                             </div>
                                             <i @click="edit(doctor)"  v-if="$page.props.auth?.user?.role_id == 3" class="fas fa-pencil-alt text-primary profile-edit-icon bg-white" data-toggle="modal" data-target="#profile-img-edit"></i>
                                          </div>
                                          <div class="profile-detail d-flex text-align-center pl-4">
                                             <h3>{{ doctor?.name }}<p class="font-size-14"> {{ doctor?.sex }}</p></h3>
                                             <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"  data-toggle="modal" data-target="#name-edit"
                                              class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary"> </i> 
                                             </p> 
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-sm-12 col-md-6">
                                    <ul id="pills-tab"
                                       class="profile-feed-items d-flex justify-content-end nav nav-pills">
                                       <li class="nav-item"><a id="pills-profile-tab" data-toggle="tab"
                                             href="#profile-profile" role="tab" aria-controls="pills-contact"
                                             class="nav-link active">profile</a></li>
                                       <li class="nav-item"><a id="pills-activity-tab" data-toggle="tab"
                                             href="#profile-activity" role="tab" aria-controls="pills-profile"
                                             class="nav-link">Activity</a></li>
                                       
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
                                 <h4 class="card-title">Contact</h4>
                              </div>
                              <div class="iq-card-header-toolbar d-flex align-items-center"></div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="m-0 p-0">
                                 <li class="d-flex mb-2">
                                    <div class="news-icon"><i class="ri-mail-fill"></i></div>
                                    <p class="news-detail mb-0">
                                       {{ doctor?.email }}
                                    </p>
                                 </li>
                                 <li class="d-flex">
                                    <div class="news-icon mb-0"><i class="ri-smartphone-fill"></i></div>
                                    <p class="news-detail mb-0">
                                      {{ doctor?.mobile }}
                                      
                                    </p>
                                    <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"  data-toggle="modal" data-target="#edit-mobile"
                                              class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary"> </i> 
                                             </p> 
                                 </li>
                              </ul>
                           </div>
                           <!---->
                        </div>
                        <div class="iq-card">
                           <div class="iq-card-header d-flex justify-content-between">
                              <div class="iq-header-title d-flex">
                                 <h4 class="card-title">Location</h4>
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"   data-toggle="modal" data-target="#edit-address"
                                              class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary"> </i> 
                                 </p> 
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="m-0 p-0">
                                 <li class="d-flex mb-2">
                                    <div class="news-icon"><i class="fas fa-map-marker-alt text-info"></i></div>
                                   {{ doctor?.address?.address_1 }} {{ doctor?.address?.address_2 }} {{ doctor?.address?.city }}{{ doctor?.address?.state }}{{ doctor?.address?.country }}{{ doctor?.address?.zip }}
                                 </li>
                                 <iframe class="w-100"
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902543.2003194243!2d-118.04220880485131!3d36.56083290513502!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80be29b9f4abb783%3A0x4757dc6be1305318!2sInyo%20National%20Forest!5e0!3m2!1sen!2sin!4v1576668158879!5m2!1sen!2sin"
                                    height="200" allowfullscreen=""></iframe>
                              </ul>
                           </div>
                        </div>
                        <div class="iq-card">
                                    <div class="iq-card-header d-flex justify-content-between">
                                       <div class="iq-header-title">
                                          <h4 class="card-title">Appointment</h4>
                                       </div>
                                       <div class="iq-card-header-toolbar d-flex align-items-center">
                                          <p class="m-0">132 pics</p>
                                       </div>
                                    </div>
                                    <div class="iq-card-body p-0">
                                       <ul class="profile-img-gallary d-flex flex-wrap p-0 m-0">     
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
                                       <h4 class="card-title">About Me</h4>
                                        <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"
                                       data-toggle="modal" data-target="#edit-about"
                                       class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                                    </div>
                                 </div>
                                 <div class="iq-card-body ">
                                    <div class="m-0 p-0">
                                       <div class="row">
                                          <div class="user-bio">
                                             <p v-if="doctor?.description">{{ doctor?.description }}</p>
                                             <p v-else>Hard-working and self-motivated virtual doctor with 2+ years of
                                                experience in scheduling appointments for doctor-patient online video
                                                consultations, sending emails to patients for constant communication and
                                                updates, prescribing medications approved by doctors, refilling
                                                medications, record-keeping, and various personal assistance tasks.
                                                Thriving to serve healthcare services on the Akrahealth Platform, Pharmacy
                                                App, and EMR App.</p>
                                          </div>
                                          <div class="col-md-2 mt-2">
                                             <h6>Certificate:</h6>
                                          </div>
                                          <div class="col-10 mt-2">
                                             <p>{{ doctor?.certificate }}</p>
                                          </div>
                                          <div class="col-md-2 mt-2">
                                             <h6>Experience:</h6>
                                          </div>
                                          <div class="col-10 mt-2">
                                             <p>{{ doctor?.experience }}</p>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="iq-card">
                           <div class="iq-card-header d-flex justify-content-between">
                              <div class="iq-header-title">
                                 <h4 class="card-title">OPD Schedule</h4>
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="media-story m-0 p-0">
                                 <li class="col-d-12">
                                    <div class="stories-data ">
                                       <div class="badge font-size-14">{{ doctor?.doctor?.timings }}</div>
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                           </div>
                           <div class="tab-pane fade show" id="profile-activity" role="tabpanel">
                              <div class="iq-card">
                                 <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title">
                                       <h4 class="card-title">Activity timeline</h4>
                                    </div>
                                    <div class="iq-card-header-toolbar d-flex align-items-center">
                                       <div class="dropdown">
                                          <span class="dropdown-toggle text-primary" id="dropdownMenuButton5"
                                             data-toggle="dropdown">
                                             View All
                                          </span>
                                          <div class="dropdown-menu dropdown-menu-right p-0">
                                             <a class="dropdown-item" href="#"><i
                                                   class="ri-user-unfollow-line mr-2"></i>View</a>
                                             <a class="dropdown-item" href="#"><i
                                                   class="ri-close-circle-line mr-2"></i>Delete</a>
                                             <a class="dropdown-item" href="#"><i class="ri-pencil-fill mr-2"></i>Edit</a>
                                             <a class="dropdown-item" href="#"><i
                                                   class="ri-printer-fill mr-2"></i>Print</a>
                                             <a class="dropdown-item" href="#"><i
                                                   class="ri-file-download-fill mr-2"></i>Download</a>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="iq-card-body">
                                    <ul class="iq-timeline">
                                       <li>
                                          <div class="timeline-dots"></div>
                                          <h6 class="float-left mb-1">Client Login</h6>
                                          <small class="float-right mt-1">24 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>Bonbon macaroon jelly beans gummi bears jelly lollipop apple</p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-success"></div>
                                          <h6 class="float-left mb-1">Scheduled Maintenance</h6>
                                          <small class="float-right mt-1">23 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>Bonbon macaroon jelly beans gummi bears jelly lollipop apple</p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-danger"></div>
                                          <h6 class="float-left mb-1">Dev Meetup</h6>
                                          <small class="float-right mt-1">20 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>Bonbon macaroon jelly beans <a href="#">gummi bears</a>gummi bears jelly
                                                lollipop apple</p>
                                             <div class="iq-media-group">
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/05.jpg" alt="">
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/06.jpg" alt="">
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/07.jpg" alt="">
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/08.jpg" alt="">
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/09.jpg" alt="">
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/10.jpg" alt="">
                                                </a>
                                             </div>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-primary"></div>
                                          <h6 class="float-left mb-1">Client Call</h6>
                                          <small class="float-right mt-1">19 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>Bonbon macaroon jelly beans gummi bears jelly lollipop apple</p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-warning"></div>
                                          <h6 class="float-left mb-1">Mega event</h6>
                                          <small class="float-right mt-1">15 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>Bonbon macaroon jelly beans gummi bears jelly lollipop apple</p>
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
                                 <h4 class="card-title">Language</h4>
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"
                                       data-toggle="modal" data-target="#edit-language"
                                       class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="media-story m-0 p-0">
                                 <li class="d-flex mb-4 align-items-center active">
                                    <div class="flex-lg-wrap d-flex">
                                       <div>
                                          <p class="badge m-1 font-size-14" v-for="language in doctor.language">{{ language }}</p>
                                       </div>
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                        <div class="iq-card">
                           <div class="iq-card-header d-flex justify-content-between">
                              <div class="iq-header-title d-flex">
                                 <h4 class="card-title">Specialization</h4>
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == doctor?.name"> <i @click="edit(doctor)" v-if="$page.props.auth?.user?.role_id == 3"
                                       data-toggle="modal" data-target="#edit-specialty"
                                       class=" border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="media-story m-0 p-0">
                                 <li class="col-d-12">
                                    <div class="stories-data ">
                                       <div class="badge font-size-14" v-for="specialty in doctorSpecialty">{{specialty}}</div>
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                        <div class="iq-card">
                           <div class="iq-card-header d-flex justify-content-between">
                              <div class="iq-header-title">
                                 <h4 class="card-title">Connected To</h4>
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <div class="suggestions-lists m-0 p-0">
                                 <div v-if="doctorAssistant?.user?.name" class="d-flex align-items-center">
                                    <div class="user-img img-fluid">
                                       <img v-if="doctorAssistant?.user.profile_photo_path" :src="doctorAssistant?.user.profile_photo_path" alt="story-img"
                                          class="rounded-circle avatar-40" />
                                       <div v-else>
                                          <img v-if="doctorAssistant?.user?.sex === 'Male'" src="/images/doctor_m_avtar.svg" alt="story-img"
                                          class="rounded-circle avatar-40" />
                                          <img v-if="doctorAssistant?.sex ==='Female'" src="/images/doctor_f_avtar.svg" alt="story-img"
                                          class="rounded-circle avatar-40" />
                                       </div>
                                    </div>
                                    <div class="media-support-info ml-3">
                                       <Link :href="route('virtualAssistant.show',doctorAssistant?.user?.slug)">
                                       <h6>{{doctorAssistant?.user?.name }}</h6>
                                       </Link>
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
      </div>
      <ProfileEdit :slug="doctor.slug" :doctor="doctor" :specialties="specialties" :doctorSpecialty="doctorSpecialty"
      :language="language" ref='childComponentRef'/> 
   </AppLayout>
</template>