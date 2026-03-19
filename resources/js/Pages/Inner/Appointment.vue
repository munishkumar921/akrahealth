<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Calender from '@/Components/EventCalender.vue';
defineProps({
   policy: String,
   patient: Object,
   doctor: Object,
   schedules: Object,
});
</script>

<template>
   <AppLayout title="Appointment">
      <div id="content-page" class="mt-5 bg-color-white-lilac">
         <div class="container-fluid">
            <div class="row row-eq-height">
               <div class="col-md-3">

                  <div class="iq-card">
                     <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                           <h4 class="card-title ">Classification</h4>
                        </div>

                        <div class="iq-card-header-toolbar d-flex align-items-center">
                           <a href="#"><i class="fa fa-plus  mr-0" aria-hidden="true"></i></a>
                        </div>
                     </div>
                     <div class="iq-card-body">
                        <ul class="m-0 p-0 job-classification">
                           <li class=""><i class="ri-check-line bg-danger"></i>Meeting</li>
                           <li class=""><i class="ri-check-line bg-success"></i>Business travel</li>
                           <li class=""><i class="ri-check-line bg-warning"></i>Personal Work</li>
                           <li class=""><i class="ri-check-line bg-info"></i>Team Project</li>
                        </ul>
                     </div>
                  </div>
                  <div class="iq-card">
                     <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                           <h4 class="card-title">Today's Schedule</h4>
                        </div>
                     </div>
                     <div class="iq-card-body">
                        <ul class="m-0 p-0 today-schedule">
                           <li class="d-flex">
                              <div class="schedule-icon"><i class="ri-checkbox-blank-circle-fill text-primary"></i></div>
                              <div class="schedule-text"> <span>Web Design</span>
                                 <span>09:00 to 12:00</span>
                              </div>
                           </li>
                           <li class="d-flex">
                              <div class="schedule-icon"><i class="ri-checkbox-blank-circle-fill text-success"></i></div>
                              <div class="schedule-text"> <span>Participate in Design</span>
                                 <span>09:00 to 12:00</span>
                              </div>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
               
               <div class="col-md-9">
                  <div class="iq-card">
                     <div class="iq-card-body">
                           <Calender :patient="patient" :doctor="doctor" :schedules="schedules"/>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </AppLayout>
</template>