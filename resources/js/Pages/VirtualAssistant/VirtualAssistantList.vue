<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Filter from '../../Partials/Filter.vue';
import SearchBar from '../../Partials/SearchBar.vue';
import Paginate from '@/Components/Paginate.vue';

const props = defineProps({
    VirtualAssistantList: String,
    assistants: Object,

});
</script>
<template>
    <AppLayout title="List of assistant/Healthcare Providers AKRAHEALTH"
        description="Checkout Top assistant/Healthcare Providers on AKRAHEALTH at rates affordable to you, from a range of specialties available to you 24*7">
        <div class="mt-8 bg-color-white-lilac">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="ais-InstantSearch">
                            <div class="row">
                                <Filter />
                                <div class="col-md-9">
                                    <div class="right-panel">
                                        <div class="row">
                                            <SearchBar :type="'assistant'" />
                                            <div class="col-md-12">
                                                <div id="hits" class="iq-product-layout-grid">
                                                    <div class="ais-Hits iq-product">
                                                        <ul class="ais-Hits-list iq-product-list">
                                                            <li key="0" v-for="assistant in assistants.data" :key="assistant.id"
                                                                class="ais-Hits-item iq-product-item iq-card h-25">
                                                                <div class="text-center">
                                                                    <div class="iq-product-free-shipping"></div>
                                                                    <Link
                                                                        :href="route('virtualAssistant.show', assistant?.slug)">
                                                                    <div
                                                                        class="align-items-center bg-white d-flex m-2  iq-border-radius-15 justify-content-center">
                                                                        <div class="d-flex flex-wrap align-items-center">
                                                                            <div class="profile-img  mt-0">
                                                                                <img v-if="assistant?.profile_photo_path"
                                                                                    :src="assistant?.profile_photo_path"
                                                                                    class="grid-view-img avatar-130"
                                                                                    align="left" alt="profile_photo_path">
                                                                                <div v-else>
                                                                                    <img v-if="assistant?.sex === 'Male'"
                                                                                        src="images/assistant_m_avtar.svg"
                                                                                        class="avatar-130 grid-view-img pt-2 "
                                                                                        align="left"
                                                                                        alt="assistant_m_avtar.svg">
                                                                                    <img v-if="assistant?.sex === 'Female'"
                                                                                        src="images/assistant_f_avtar.svg"
                                                                                        class="avatar-130 grid-view-img pt-2 "
                                                                                        align="left"
                                                                                        alt="assistant_f_avtar">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    </Link>
                                                                    <div class="card-body">
                                                                        <div class="text-center">
                                                                            <Link
                                                                                :href="route('virtualAssistant.show', assistant?.slug)">
                                                                            {{ assistant?.name }}
                                                                            </Link>
                                                                            <p class="tags text-break">

                                                                                 
                                                                            </p>
                                                                            <div class="ratting center-flex">
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
                                                                            <button type="button"
                                                                                class="btn custom_wish_btn iq-waves-effect text-uppercase btn-sm addToWish"
                                                                                id="5477500">
                                                                                <i class="ri-heart-fill mr-0"></i>
                                                                            </button>
                                                                            <Link
                                                                                :href="route('virtualAssistant.show',assistant?.slug)"
                                                                                class="font-size-16 font-weight-bold float-right btn btn-primary mt-sm-2 mb-sm-2 mt-md-0">
                                                                            More Details
                                                                            <i class="fa fa-arrow-right ms-2"></i>
                                                                            </Link>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="$page.props.assistants.data.length == 0">
                                                <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12 m-b-10 center-flex">
                                                        <div class="alert alert-info  alert-dismissible"><strong>Oops!</strong> There no any
                                                          Virtual Assistants available.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        <div class="col-md-12">
                                            <div class="mt-5 mb-5">
                                                <Paginate :links="assistants.links" />
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
</AppLayout></template>