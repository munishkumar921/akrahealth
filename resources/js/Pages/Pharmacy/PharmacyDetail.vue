<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import Paginate from '@/Components/Paginate.vue';
const props = defineProps({
    PharmacyDetail: String,
    pharmacy: Object,
    drugs: Object,
});
</script>
<template>
    <AppLayout title="Pharmacy Details AKRAHEALTH"
        description="Checkout Top labs/Healthcare Providers on AKRAHEALTH at rates affordable to you, from a range of specialties available to you 24*7">
        <div id="content-page" class="bg-color-white-lilac">
            <div class="container-fluid pt-5">
                <div class="row">
                    <div class="col-md-12">
                        <div class="iq-card">
                            <div class="iq-card-body">
                                <div class="row align-items-center justify-content-center mt-2">
                                    <div class="col-md-5 iq-item-product-left">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <img v-if="pharmacy?.profile_photo_path" :src="pharmacy?.profile_photo_path"
                                                class="grid-view-img px-4 w-200 h-56" align="left" alt="profile_photo_path">
                                            <img src="/images/Pharmacy.svg" class="grid-view-img px-4 w-200 h-56"
                                                align="left" alt="avtar.jpg">
                                        </div>
                                    </div>
                                    <div class="col-md-7 iq-item-product-right">
                                        <div class="product-additional-details">
                                            <h4 class="productpage_title">{{ pharmacy?.name }}
                                            </h4>
                                            <p> <b></b></p>
                                            <div class="ratting">
                                                <ul class="ratting-item d-flex p-0 m-0">
                                                    <li class="full"><i class="ri-star-fill"></i></li>
                                                    <li class="full"><i class="ri-star-fill"></i></li>
                                                    <li class="full"><i class="ri-star-fill"></i></li>
                                                    <li class="full"><i class="ri-star-fill"></i></li>
                                                    <li class="full"><i class="ri-star-line"></i></li>
                                                </ul>
                                            </div>
                                            <div class="product-descriptio" v-if="pharmacy?.description">
                                                <p>{{ pharmacy?.description }}</p>
                                            </div>
                                            <div class="product-descriptio" v-else>
                                                <p>Enjoy smart access to videos, games and apps with this Amazon Fire TV
                                                    stick.
                                                    Its Alexa voice remote lets you deliver hands-free commands when you
                                                    want to
                                                    watch television or engage with other applications. With a quad-core
                                                    processor, 1GB internal memory and 8GB of storage, this portable Amazon
                                                    Fire
                                                    TV stick works fast for buffer-free streaming.</p>
                                            </div>
                                            <div class="additional-product-action d-flex align-items-center">
                                                <div class="product-action">
                                                    <!-- <div class="add-to-cart"><button type="button"
                                                            class="btn btn-primary"><i class="fas fa-cart-plus"></i> Add to
                                                            Cart </button></div> -->
                                                    <div class="wishlist"><button type="button"
                                                            class="btn btn-primary ml-2"><i class="far fa-heart"></i>
                                                            Wishlist</button></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="product-description-details">
                                <div class="description-tabs">
                                    <div class="tabs">
                                        <ul role="tablist" class="nav nav-tabs justify-content-center">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="product-tab" data-toggle="tab"
                                                    href="#product" role="tab" aria-controls="home"
                                                    aria-selected="true">Product</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="review-tab" data-toggle="tab" href="#review"
                                                    role="tab" aria-controls="review" aria-selected="false">Review</a>
                                            </li>
                                        </ul>
                                        <div class="iq-card-body">
                                            <div class="tab-content" id="myTabContent-2">
                                                <div class="tab-pane fade active show" id="product" role="tabpanel"
                                                    aria-labelledby="product-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">    
                                                            <div id="hits" class="iq-product-layout-grid">
                                                                <div class="ais-Hits iq-product">
                                                                    <div class="ais-Hits-list iq-product-list">
                                                                        <li v-for="drug in drugs.data"
                                                                            class="ais-Hits-item iq-product-item iq-card border-eeeeee">
                                                                            <Link  :href="route('dugsDetail', drug?.id)" :title="drug.labeler_name">
                                                                                <div class="h-56 d-flex align-items-center justify-content-center bg-white iq-border-radius-15">
                                                                                    <a href=""><img
                                                                                            src="/images/Pharmacy.svg"
                                                                                            alt=" "
                                                                                            class="grid-view-img px-4 pt-4  h-56 w-130"></a>
                                                                                </div>
                                                                            </Link>
                                                                            <div class="card-body">
                                                                                <div class=" text-center">
                                                                                    <Link  :href="route('dugsDetail', drug?.id)" :title="drug.labeler_name">{{ drug.labeler_name }}</Link>
                                                                                    <p class="font-size-12 mb-0">Control
                                                                                        media playba...</p>
                                                                                    <div class="ratting">
                                                                                        <ul
                                                                                            class="ratting-item d-flex p-0 m-0 center-flex">
                                                                                            <li class="full"><i
                                                                                                    class="ri-star-fill"></i>
                                                                                            </li>
                                                                                            <li class="full"><i
                                                                                                    class="ri-star-fill"></i>
                                                                                            </li>
                                                                                            <li class="full"><i
                                                                                                    class="ri-star-fill"></i>
                                                                                            </li>
                                                                                            <li class="full"><i
                                                                                                    class="ri-star-fill"></i>
                                                                                            </li>
                                                                                            <li class="full"><i
                                                                                                    class="ri-star-line"></i>
                                                                                            </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="iq-product-action my-2"><button
                                                                                        type="button"
                                                                                        class="btn btn-primary iq-waves-effect text-uppercase btn-sm"><i
                                                                                            class="fas fa-cart-plus mr-0"></i></button><button
                                                                                        type="button"
                                                                                        class="btn btn-primary ml-2 btn-sm"><i
                                                                                            class="far fa-heart mr-0"></i></button>
                                                                                    <p
                                                                                        class="font-size-16 font-weight-bold float-right">
                                                                                        $29.99
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                        </li>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="mt-5 mb-5">
                                                                <Paginate :links="drugs.links" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="review" role="tabpanel"
                                                    aria-labelledby="review-tab">
                                                    <p>Lorem Ipsum is simply dummy text of the printing and
                                                        typesetting industry. Lorem Ipsum has been the industry's
                                                        standard dummy text ever since the 1500s, when an unknown
                                                        printer took a galley of type and scrambled it to make a
                                                        type specimen book.</p>
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