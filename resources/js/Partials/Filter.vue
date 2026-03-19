<script setup>
import { Head, Link } from '@inertiajs/vue3';
const props = defineProps({
    specialties: Object,
    skiils:Object,
});

</script>
<template>
    <div class="col-md-3">
        <div class="left-panel">
            <div class="iq-filter-border iq-card p-3">
                <div id="clear-refinements" class="float-right">
                    <button disabled="" id="clear-result" class="btn btn-primary border-0 btn-sm mr-2"><i
                            class="ri-refresh-line mr-0"></i></button>
                </div>
                <h4 class="mb-0">Filter </h4>
            </div>
            <div class="iq-filter-border iq-card p-3">
                <h5 class="card-title mb-3">Location</h5>
                <div id="numeric-menu">
                    <div class="ais-NumericMenu">
                        <ul class="ais-NumericMenu-list">
                            <li class="ais-NumericMenu-item ais-NumericMenu-item--selected">
                                <label class="ais-NumericMenu-label"> 
                                    <input type="radio" class="ais-NumericMenu-radio" name="price" checked=""> 
                                    <span class="ais-NumericMenu-labelText ml-2">All</span>
                                </label>
                            </li>
                            <li class="ais-NumericMenu-item">
                                <label class="ais-NumericMenu-label"> 
                                    <input type="radio" class="ais-NumericMenu-radio" name="price"> 
                                    <span class="ais-NumericMenu-labelText ml-2">Less than 2 KM</span> 
                                </label>
                            </li>
                            <li class="ais-NumericMenu-item">
                                <label class="ais-NumericMenu-label"> 
                                   <input type="radio" class="ais-NumericMenu-radio" name="price"> 
                                   <span class="ais-NumericMenu-labelText ml-2">Between 2 KM - 4 KM</span> 
                                </label>
                            </li>
                            <li class="ais-NumericMenu-item">
                                <label class="ais-NumericMenu-label"> 
                                    <input type="radio" class="ais-NumericMenu-radio" name="price"> 
                                    <span class="ais-NumericMenu-labelText ml-2">More than 8 KM</span> 
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="iq-filter-border iq-card p-3">
                <h5 class="card-title mb-3">Specialist</h5>
                <div class="ais-RefinementList">
                 <div id="brand-list">
                    <ul class="list-group iq-list-style-1">
                        <li class="mb-2 mr-0" v-for="row in specialties" :key="row.id">
                                <div class="checkbox d-inline-block mr-2">
                                    <input type="checkbox" class="checkbox-input" id="specialties" data-value="Insignia" value="true" >
                                    <label for="specialties">{{ row.specialty}}</label>
                                </div>
                        </li>
                    </ul>
                 </div>
                </div>
            </div>
            <div class="iq-filter-border iq-card p-3">
                <h5 class="card-title mb-3">Rating</h5>
                <div id="rating-menu">
                    <div class="ais-RatingMenu"><svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <symbol id="ais-RatingMenu-starSymbol" viewBox="0 0 24 24">
                                <path
                                    d="M12 .288l2.833 8.718h9.167l-7.417 5.389 2.833 8.718-7.416-5.388-7.417 5.388 2.833-8.718-7.416-5.389h9.167z">
                                </path>
                            </symbol>
                            <symbol id="ais-RatingMenu-starEmptySymbol" viewBox="0 0 24 24">
                                <path
                                    d="M12 6.76l1.379 4.246h4.465l-3.612 2.625 1.379 4.246-3.611-2.625-3.612 2.625 1.379-4.246-3.612-2.625h4.465l1.38-4.246zm0-6.472l-2.833 8.718h-9.167l7.416 5.389-2.833 8.718 7.417-5.388 7.416 5.388-2.833-8.718 7.417-5.389h-9.167l-2.833-8.718z">
                                </path>
                            </symbol>
                        </svg>
                        <ul class="ais-RatingMenu-list">
                            <li class="ais-RatingMenu-item">
                                <div>
                                    <a href="#" class="d-flex justify-content-between ais-RatingMenu-link"
                                        aria-label="4 &amp; up">
                                        <div class="ratting" >
                                            <i class="ri-star-fill mr-2"></i><i
                                                class="ri-star-fill mr-2"></i><i
                                                class="ri-star-fill mr-2"></i><i
                                                class="ri-star-fill mr-2"></i><i
                                                class="ri-star-line mr-2"></i>
                                            <span class="ais-RatingMenu-label ">&amp;
                                                Up</span>
                                        </div>
                                        <span class="badge">16075</span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>