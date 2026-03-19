<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Filter from '../../Partials/Filter.vue';
import SearchBar from '../../Partials/SearchBar.vue';
import Paginate from '@/Components/Paginate.vue';

const props = defineProps({
    DoctorList: String,
    doctors: Object,
    specialties: String,
    keyword:String,
    doctorSpecialty:Object,
});
</script>
<template>
    <AppLayout title="List of Doctors/Healthcare Providers AKRAHEALTH"
        description="Checkout Top Doctors/Healthcare Providers on AKRAHEALTH at rates affordable to you, from a range of specialties available to you 24*7">
        <div class="mt-8 bg-color-white-lilac" id="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="ais-InstantSearch">
                            <div class="row">
                                <Filter :specialties="specialties" />
                                <div class="col-md-9">
                                    <div class="right-panel">
                                        <div class="row">
                                            <SearchBar :type="'doctor'" :keyword="$props.keyword" />
                                            <div class="col-md-12">
                                                <div  id="hits" class="iq-product-layout-grid">

                                                    <div class="ais-Hits iq-product">
                                                        <ul class="ais-Hits-list iq-product-list">
                                                            <li v-for="doctor in doctors.data" :key="doctor.id" key="0"
                                                                class="ais-Hits-item iq-product-item iq-card">
                                                                <div class="text-center">
                                                                    <Link :href="route('doctors.show', doctor.slug)">
                                                                    <div class="align-items-center bg-white d-flex m-2 iq-border-radius-15 justify-content-center">
                                                                        <div class="d-flex flex-wrap align-items-center">
                                                                            <div class="profile-img  mt-0">
                                                                                <img v-if="doctor?.profile_photo_url"
                                                                                    :src="doctor?.profile_photo_url"
                                                                                    class="grid-view-img avatar-130"
                                                                                    align="left" alt="profile_photo">
                                                                                <div v-else>
                                                                                    <img v-if="doctor?.sex == 'Male'"
                                                                                        src="images/doctor_m_avtar.svg"
                                                                                        class="grid-view-img pt-2 avatar-130"
                                                                                        align="left" alt="Doctor_avtar">
                                                                                    <img v-if="doctor?.sex == 'Female'"
                                                                                        src="images/doctor_f_avtar.svg"
                                                                                        class="grid-view-img pt-2 avatar-130 "
                                                                                        align="left" alt="Doctor_avtar">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    </Link>
                                                                    <div class="card-body">
                                                                        <div class="text-center">
                                                                            <Link
                                                                                :href="route('doctors.show', doctor.slug)">
                                                                            <h4 class="text-primary">{{ doctor?.name }}</h4>
                                                                            </Link>
                                                                            <p class="tags text-break">
                                                                                Senior Orthopedic
                                                                            </p>
                                                                        
                                                                            <p class="text-break" v-for="specialty in doctorSpecialty">
                                                                             
                                                                            </p>
                                                                            <div class="ratting  center-flex">
                                                                                <ul class="ratting-item d-flex p-0 m-0">
                                                                                    <li class="full"><i
                                                                                            class="ri-star-fill"></i></li>
                                                                                    <li class="full"><i
                                                                                            class="ri-star-fill"></i></li>
                                                                                    <li class="full"><i
                                                                                            class="ri-star-fill"></i></li>
                                                                                    <li class="full"><i
                                                                                            class="ri-star-fill"></i></li>
                                                                                    <li class="full"><i
                                                                                            class="ri-star-line"></i></li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                        <div class="iq-product-action my-2">
                                                                            <button type="button" class="btn custom_wish_btn iq-waves-effect text-uppercase btn-sm addToWish mt-sm-2"
                                                                                id="5477500">
                                                                                <i class="ri-heart-fill mr-0"></i>
                                                                            </button>
                                                                            <Link
                                                                                :href="route('doctors.show', doctor.slug)"
                                                                                class="font-size-16 font-weight-bold float-right btn btn-primary mt-sm-2">
                                                                            More Details <i
                                                                                class="fa fa-arrow-right ms-2 ms-sm-0"></i>
                                                                            </Link>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="$page.props.doctors.data.length == 0">
                                                <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12 m-b-10 center-flex">
                                                        <div class="alert alert-info  alert-dismissible"><strong>Oops!</strong> There no any
                                                          Doctors available.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mt-5 mb-5">
                                                    <Paginate :links="doctors.links" />
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
    </AppLayout>
</template>