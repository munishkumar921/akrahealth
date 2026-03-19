<script setup>
import AppLayout from "../../Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import HirUs from "../Modals/HirUs.vue";
import ProfileEdit from "../Modals/AssistantProfileEdit.vue";
import { ref } from 'vue';
const props = defineProps({
   VirtualAssistantDetail: String,
   assistant: Object,
   skills: String,
   slug: String,
   language: Object,
   AssistantSkills: String,
   doctorAssistant: Object,
});

const childComponentRef = ref();
const edit = (assistant) => {

   childComponentRef.value.update(assistant);
}
</script>
<template>
   <AppLayout title="Virtual Assistant Details"
      description="is a Virtual Assistant at Akrahealth and supports related Doctors/Healthcare. Providers. Extends assistance in Scheduling Appointment, E-Prescriptions, Refilling Medications, constant Doctor-Patient communication. ">
      <div id="content-page bg-color-white-lilac">
         <div class="container-fluid pt-5">
            <div class="row">
               <div class="col-sm-12">
                  <div class="iq-card">
                     <div class="iq-card-body profile-page p-0">
                        <div class="profile-header">
                           <div class="cover-container overlay">
                              <img src="/images/assistant_profile.svg" alt="profile-bg" class="rounded img-fluid w-100" />
                              <!-- <ul class="header-nav d-flex flex-wrap justify-end p-0 m-0">
                                 <li>
                                    <a href="javascript:void();"><i class="ri-pencil-line"></i></a>
                                 </li>
                                 <li>
                                    <a href="javascript:void();"><i class="ri-settings-4-line"></i></a>
                                 </li>
                              </ul> -->
                           </div>
                           <div class="profile-info p-4">
                              <div class="row">
                                 <div class="col-sm-12 col-md-6">
                                    <div class="user-detail pl-5">
                                       <div class="d-flex flex-wrap align-items-center">
                                          <div class="profile-img relative">
                                             <img v-if="assistant?.profile_photo_path" :src="assistant?.profile_photo_path"
                                                class="avatar-130 img-fluid" align="left" alt="profile_photo_path" />
                                             <div class="bg-light-gradient rounded-circle" v-else>
                                                <img v-if="assistant?.sex === 'Male'" src="/images/assistant_m_avtar.svg"
                                                   alt="profile-img" class="avatar-130 img-fluid" />
                                                <img v-else src="/images/assistant_f_avtar.svg" alt="profile-img"
                                                   class="avatar-130 img-fluid" />
                                                   
                                             </div>
                                             <i @click="edit(assistant)"  v-if="$page.props.auth?.user?.role_id == 5" class="fas fa-pencil-alt text-primary profile-edit-icon bg-white" data-toggle="modal" data-target="#profile-img-edit"></i>
                                          </div>
                                          <div class="profile-detail d-flex text-align-center">
                                             <h3>{{ assistant?.name }}<p class="font-size-14"> {{ assistant?.sex }}</p>
                                             </h3>
                                             <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)"
                                                   v-if="$page.props.auth?.user?.role_id == 5" data-toggle="modal"
                                                   data-target="#name-edit"
                                                   class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                                </i>
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-sm-12 col-md-6">
                                    <ul id="pills-tab" class="profile-feed-items d-flex justify-content-end nav nav-pills">
                                       <li class="nav-item">
                                          <a id="pills-profile-tab" data-toggle="tab" href="#profile-profile" role="tab"
                                             aria-controls="pills-contact" class="nav-link active">profile</a>
                                       </li>
                                       <li class="nav-item">
                                          <a id="pills-activity-tab" data-toggle="tab" href="#profile-activity" role="tab"
                                             aria-controls="pills-profile" class="nav-link">Activity</a>
                                       </li>
                                       <!-- <li class="nav-item">
                                          <a id="pills-friend-tab" data-toggle="tab" href="#profile-friends" role="tab"
                                             aria-controls="pills-contact" class="nav-link">Friends</a>
                                       </li> -->

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
                                       {{ assistant?.email }}
                                    </p>
                                 </li>
                                 <li class="d-flex">
                                    <div class="news-icon mb-0"><i class="ri-smartphone-fill"></i></div>
                                    <p class="news-detail mb-0">
                                       {{ assistant?.mobile }}

                                    </p>
                                    <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)"
                                          v-if="$page.props.auth?.user?.role_id == 5" data-toggle="modal"
                                          data-target="#edit-mobile"
                                          class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                       </i>
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
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)" v-if="$page.props.auth?.user?.role_id == 5"
                                       data-toggle="modal" data-target="#edit-address"
                                       class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                              </div>

                           </div>
                           <div class="iq-card-body">
                              <ul class="m-0 p-0">
                                 <li class="d-flex mb-2">
                                    <div class="news-icon">
                                       <i class="fas fa-map-marker-alt text-info"></i>
                                    </div>
                                    {{ assistant?.address?.address_1 }}
                                    {{ assistant?.address?.address_2 }} {{ assistant?.address?.city }}
                                    {{ assistant?.address?.state }}{{ assistant?.address?.country }}
                                    {{ assistant?.address?.zip }}
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
                                 <h4 class="card-title">Message</h4>
                              </div>
                              <div class="iq-card-header-toolbar d-flex align-items-center"></div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="m-0 p-0">
                                 <li class="d-flex mb-2">
                                    <div class="news-icon"><i class="ri-chat-4-fill"></i></div>
                                    <p class="news-detail mb-0">
                                       there is a meetup in your city on friday at 19:00.<a href="#">see details</a>
                                    </p>
                                 </li>
                                 <li class="d-flex">
                                    <div class="news-icon mb-0"><i class="ri-chat-4-fill"></i></div>
                                    <p class="news-detail mb-0">
                                       20% off coupon on selected items at pharmaprix
                                    </p>
                                 </li>
                              </ul>
                           </div>

                        </div>
                     </div>
                     <div class="col-lg-6 profile-center">
                        <div class="tab-content">
                           <div class="tab-pane fade active show" id="profile-profile" role="tabpanel">
                              <div class="iq-card">
                                 <div class="iq-card-header d-flex justify-content-between">
                                    <h4 class="card-title d-flex">About Me <p class="pl-3 font-size-14" v-if="$page.props.auth?.user?.name == assistant?.name"> <i
                                             @click="edit(assistant)" v-if="$page.props.auth?.user?.role_id == 5"
                                             data-toggle="modal" data-target="#edit-about"
                                             class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                          </i>
                                       </p>
                                    </h4>

                                    <div class=" ">
                                       <a v-if="$page.props?.auth?.user?.role_id == 3" href="#"
                                          class="btn btn-primary  text-white" data-toggle="modal" data-target="#hir_us">
                                          Hire Me</a>

                                       <HirUs :slug="slug" :assistant="assistant" :AssistantSkills="AssistantSkills" />
                                    </div>
                                 </div>

                                 <div class="iq-card-body">
                                    <div class="m-0 p-0">
                                       <div class="row">
                                          <div class="user-bio">
                                             <p v-if="assistant?.description">
                                                {{ assistant?.description }}
                                             </p>
                                             <p v-else>
                                                Hard-working and self-motivated virtual assistant with 2+
                                                years of experience in scheduling appointments for
                                                doctor-patient online video consultations, sending emails
                                                to patients for constant communication and updates,
                                                prescribing medications approved by doctors, refilling
                                                medications, record-keeping, and various personal
                                                assistance tasks. Thriving to serve healthcare services on
                                                the Akrahealth Platform, Pharmacy App, and EMR App.
                                             </p>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="iq-card">
                                 <div class="iq-card-header d-flex justify-content-between">
                                    <div class="iq-header-title d-flex">
                                       <h4 class="card-title">Resume</h4>
                                       <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)"
                                             v-if="$page.props.auth?.user?.role_id == 5" data-toggle="modal"
                                             data-target="#edit-resume"
                                             class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                          </i>
                                       </p>
                                    </div>
                                 </div>
                                 <div class="iq-card-body">
                                    <div class="m-0 p-0">
                                       <div class="row">
                                          <div class="user-bio"></div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="tab-pane fade " id="profile-activity" role="tabpanel">
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
                                             <p>
                                                Bonbon macaroon jelly beans gummi bears jelly lollipop
                                                apple
                                             </p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-success"></div>
                                          <h6 class="float-left mb-1">Scheduled Maintenance</h6>
                                          <small class="float-right mt-1">23 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>
                                                Bonbon macaroon jelly beans gummi bears jelly lollipop
                                                apple
                                             </p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-danger"></div>
                                          <h6 class="float-left mb-1">Dev Meetup</h6>
                                          <small class="float-right mt-1">20 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>
                                                Bonbon macaroon jelly beans
                                                <a href="#">gummi bears</a>gummi bears jelly lollipop
                                                apple
                                             </p>
                                             <div class="iq-media-group">
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/05.jpg" alt="" />
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/06.jpg" alt="" />
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/07.jpg" alt="" />
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/08.jpg" alt="" />
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/09.jpg" alt="" />
                                                </a>
                                                <a href="#" class="iq-media">
                                                   <img class="img-fluid avatar-40 rounded-circle"
                                                      src="/vite-images/user/10.jpg" alt="" />
                                                </a>
                                             </div>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-primary"></div>
                                          <h6 class="float-left mb-1">Client Call</h6>
                                          <small class="float-right mt-1">19 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>
                                                Bonbon macaroon jelly beans gummi bears jelly lollipop
                                                apple
                                             </p>
                                          </div>
                                       </li>
                                       <li>
                                          <div class="timeline-dots border-warning"></div>
                                          <h6 class="float-left mb-1">Mega event</h6>
                                          <small class="float-right mt-1">15 November 2019</small>
                                          <div class="d-inline-block w-100">
                                             <p>
                                                Bonbon macaroon jelly beans gummi bears jelly lollipop
                                                apple
                                             </p>
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
                                 <h4 class="card-title">Skills</h4>
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)" v-if="$page.props.auth?.user?.role_id == 5"
                                       data-toggle="modal" data-target="#edit-skills"
                                       class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                              </div>

                           </div>

                           <div class="iq-card-body">
                              <ul class="media-story m-0 p-0">
                                 <li class="d-flex mb-4 align-items-center active">
                                    <div class="flex-lg-wrap d-flex">
                                       <div v-for="skill in AssistantSkills">
                                          <p class="badge m-1">{{ skill }}</p>
                                       </div>
                                    </div>
                                 </li>
                              </ul>
                           </div>
                        </div>
                        <div class="iq-card">
                           <div class="iq-card-header d-flex justify-content-between">
                              <div class="iq-header-title d-flex">
                                 <h4 class="card-title">Language</h4>
                                 <p class="pl-3" v-if="$page.props.auth?.user?.name == assistant?.name"> <i @click="edit(assistant)" v-if="$page.props.auth?.user?.role_id == 5"
                                       data-toggle="modal" data-target="#edit-language"
                                       class="border border-primary border-2 p-1 rounded-circle   pointer fas fa-pencil-alt text-primary">
                                    </i>
                                 </p>
                              </div>
                           </div>
                           <div class="iq-card-body">
                              <ul class="media-story m-0 p-0">
                                 <li class="d-flex mb-4 align-items-center active">
                                    <div class="flex-lg-wrap d-flex">
                                       <div>
                                          <p class="badge m-1" v-for="language in assistant.language">{{ language }}</p>
                                       </div>
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
                                       <img v-if="doctorAssistant?.user.profile_photo_path"
                                          :src="doctorAssistant?.user.profile_photo_path" alt="story-img"
                                          class="rounded-circle avatar-40" />
                                       <div v-else>
                                          <img v-if="doctorAssistant?.user?.sex === 'Male'"
                                             src="/images/doctor_m_avtar.svg" alt="story-img"
                                             class="rounded-circle avatar-40" />
                                          <img v-if="doctorAssistant?.sex === 'Female'" src="/images/doctor_f_avtar.svg"
                                             alt="story-img" class="rounded-circle avatar-40" />
                                       </div>
                                    </div>
                                    <div class="media-support-info ml-3">
                                       <Link :href="route('doctors.show',doctorAssistant?.user?.slug)">
                                    <h6>{{doctorAssistant?.user?.name }}</h6>
                                    </Link>
                                 </div>
                              </div>
                              <div v-else>
                                 <p>No Doctor has connected with this assistant </p>
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
   <ProfileEdit :slug="slug" :assistant="assistant" :skills="skills" :AssistantSkills="AssistantSkills"
      :language="language" ref='childComponentRef' />
</AppLayout></template>
