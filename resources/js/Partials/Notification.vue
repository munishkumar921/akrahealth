<script setup>
import { Link } from '@inertiajs/vue3';
</script>
<template>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto navbar-list">
            <li class="nav-item">
                <a href="#" class="search-toggle iq-waves-effect">
                    <div id="lottie-beil">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 192" width="192" height="192" preserveAspectRatio="xMidYMid meet" style="width: 100%; height: 100%; transform: translate3d(0px, 0px, 0px);"><defs><clipPath id="__lottie_element_2"><rect width="192" height="192" x="0" y="0"></rect></clipPath></defs><g clip-path="url(#__lottie_element_2)"><g transform="matrix(5.833330154418945,0,0,5.833330154418945,26.000038146972656,32.98597717285156)" opacity="1" style="display: block;"><g opacity="1" transform="matrix(1,0,0,1,12,21.639999389648438)"></g><g opacity="1" transform="matrix(0.9996416568756104,-0.0267680324614048,0.0267680324614048,0.9996416568756104,12,8.654000282287598)"><path stroke-linecap="round" stroke-linejoin="round" fill-opacity="0" stroke="rgb(0,0,0)" stroke-opacity="1" stroke-width="2" d=" M6,-0.6539999842643738 C6,-5.052999973297119 1.2649999856948853,-8.345999717712402 -3.38700008392334,-5.715000152587891 C-5.052000045776367,-4.77400016784668 -6.0329999923706055,-2.9560000896453857 -6.002999782562256,-1.0440000295639038 C-5.888999938964844,6.271999835968018 -9,8.345999717712402 -9,8.345999717712402 C-9,8.345999717712402 9,8.345999717712402 9,8.345999717712402 C9,8.345999717712402 6,6.3460001945495605 6,-0.6539999842643738z"></path></g><g opacity="1" transform="matrix(0.997535228729248,-0.07016736268997192,0.07016736268997192,0.997535228729248,15.853620529174805,21.405719757080078)"><path stroke-linecap="round" stroke-linejoin="round" fill-opacity="0" stroke="rgb(0,0,0)" stroke-opacity="1" stroke-width="2" d=" M1.7300000190734863,-0.6399999856948853 C1.1749999523162842,0.3149999976158142 -0.04800000041723251,0.6399999856948853 -1.003999948501587,0.08699999749660492 C-1.3049999475479126,-0.08799999952316284 -1.555999994277954,-0.33799999952316284 -1.7300000190734863,-0.6399999856948853"></path></g></g></g></svg>
                    </div>
                    <span class="bg-danger dots"></span>
                </a>
                <div class="iq-sub-dropdown">
                    <div class="iq-card shadow-none m-0">
                        <div class="iq-card-body p-0 ">
                            <div class="bg-primary p-3">
                                <h5 class="mb-0 text-white">All Notifications<small class="badge badge-light float-right pt-1">4</small></h5>
                            </div>

                            <Link href="#" class="iq-sub-card">
                                <div class="media align-items-center">
                                    <div class="">
                                        <img class="avatar-40 rounded" src="/images/avatar.webp" alt="">
                                    </div>
                                    <div class="media-body ml-3">
                                        <h6 class="mb-0 ">Emma Watson Nik</h6>
                                        <small class="float-right font-size-12">Just Now</small>
                                        <p class="mb-0">95 MB</p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
