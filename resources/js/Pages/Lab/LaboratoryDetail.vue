<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
const props = defineProps({
    LabList: String,
    lab: Object,
    tests: Object,

});
</script>
<template>
    <AppLayout title="List of labs/Healthcare Providers AKRAHEALTH"
        description="Checkout Top labs/Healthcare Providers on AKRAHEALTH at rates affordable to you, from a range of specialties available to you 24*7">
        <div id="content-page" class="bg-color-white-lilac">
            <div class="container-fluid pt-5">
                <div class="row">
                    <div class="col-md-12">
                        <div class="iq-card "><!---->
                            <div class="iq-card-body">
                                <div class="row align-items-center justify-content-center">
                                    <div class="col-md-5 iq-item-product-left mt-2 ">
                                        <div class="h-56 d-flex align-items-center justify-content-center mb-4">
                                            <img v-if="lab?.profile_photo_path" :src="lab?.profile_photo_path"
                                                class="grid-view-img px-4 w-200 h-56" align="left" alt="profile_photo_path">
                                            <img v-else src="/images/Lab.svg" class="grid-view-img px-4 w-200 h-56"
                                                align="left" alt="Lab">
                                        </div>
                                    </div>
                                    <div class="col-md-7 iq-item-product-right">
                                        <div class="product-additional-details">
                                            <h4 class="productpage_title">{{ lab?.name }}
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
                                            <div class="product-descriptio">
                                                <p>Enjoy smart access to videos, games and apps with this Amazon Fire TV
                                                    stick.
                                                    Its Alexa voice remote lets you deliver hands-free commands when you
                                                    want to
                                                    watch television or engage with other applications. With a quad-core
                                                    processor, 1GB internal memory and 8GB of storage, this portable Amazon
                                                    Fire
                                                    TV stick works fast for buffer-free streaming.</p>
                                            </div>
                                            <div class="product-price">
                                                <div class="regular-price"><b></b> <span class="offer-price ml-2">
                                                    </span></div>
                                            </div>
                                            <div class="additional-product-action d-flex align-items-center">
                                                <div class="product-action">
                                                    <div class="wishlist"><button type="button"
                                                            class="btn btn-primary ml-2"><i class="far fa-heart"></i>
                                                            Wishlist</button></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 product-description-details mt-4">
                                        <div class="description-tabs">
                                            <div class="tabs" id="__BVID__479"><!---->
                                                <div class="">
                                                    <ul role="tablist" class="nav nav-tabs justify-content-center"
                                                        id="__BVID__479__BV_tab_controls_"><!---->
                                                        <li role="tab" class="nav-item"><a role="tab" href="#description"
                                                                class="nav-link active" id="description-tab"
                                                                data-toggle="tab" aria-controls="description">Tests</a></li>

                                                        <li role="tab" class="nav-item"><a role="tab" href="#specifications"
                                                                class="nav-link" id="specifications-tab" data-toggle="tab"
                                                                aria-controls="specifications">Specifications</a></li>

                                                    </ul>
                                                </div>
                                                <div class="tab-content mt-3">
                                                    <div role="tabpanel" class="tab-pane fade active show" id="description"
                                                        aria-labelledby="description-tab">
                                                        <table
                                                            class="table table-bordered table-responsive-md table-striped text-center">
                                                            <thead>
                                                                <tr>
                                                                    <th>Test</th>
                                                                    <th>description</th>
                                                                    <th>Price</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr v-for="test in tests" :key="tests.id">
                                                                    <td contenteditable="true"> {{ test.name }}</td>
                                                                    <td contenteditable="true"> {{ test.description }}</td>
                                                                    <td contenteditable="true">{{ test.price }} </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div role="tabpanel" class="tab-pane fade " id="specifications"
                                                        aria-labelledby="specifications-tab">
                                                        <p>Lorem dolor sit amet, consectetur adipiscing elit. In
                                                            pharetra
                                                            nibh et urna tempus, id feugiat sapien iaculis. Morbi aliquam et
                                                            nisl ut varius. Vivamus fringilla, nisl ac lacinia fermentum,
                                                            nisl
                                                            felis facilisis lectus, id condimentum turpis quam ac ipsum.
                                                            Vivamus
                                                            purus nisi, condimentum eget felis vel, finibus interdum odio.
                                                            Pellentesque lacinia aliquam ipsum. Donec euismod ex in sodales
                                                            faucibus. Proin eget lacus vitae eros vestibulum vehicula.
                                                            Mauris
                                                            tortor massa, fringilla ut metus quis, malesuada semper diam.
                                                            Phasellus et iaculis ex, vitae maximus odio.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!---->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>