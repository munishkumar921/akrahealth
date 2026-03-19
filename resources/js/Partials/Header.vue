<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { Link, useForm } from "@inertiajs/vue3"

const form = useForm({})

/* ---------------- LANGUAGE STATE ---------------- */
const availableLanguages = [
  { code: 'en', name: 'English', flag: '🇺🇸' },
  { code: 'es', name: 'Spanish', flag: '🇪🇸' },
  { code: 'fr', name: 'French', flag: '🇫🇷' },
  { code: 'ar', name: 'Arabic', flag: '🇸🇦' },
]

const selectedLanguage = ref(localStorage.getItem('lang') || 'en')
const isLanguageDropdownOpen = ref(false)
const languageDropdownRef = ref(null)
const googleReady = ref(false)

/* ---------------- GOOGLE TRANSLATE INIT ---------------- */
const initGoogleTranslate = () => {
  if (window.google?.translate) {
    googleReady.value = true
    return
  }

  window.googleTranslateElementInit = () => {
    new window.google.translate.TranslateElement(
      {
         includedLanguages: availableLanguages.map(l => l.code).join(','),
        autoDisplay: false,
      },
      'google_translate_element'
    )
    googleReady.value = true

    // Restore previous language
    nextTick(() => triggerTranslate(selectedLanguage.value))
  }

  if (!document.getElementById('google-translate-script')) {
    const script = document.createElement('script')
    script.id = 'google-translate-script'
    script.src =
      'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit'
    document.body.appendChild(script)
  }
}

/* ---------------- TRANSLATE ---------------- */
const triggerTranslate = (lang) => {
  const combo = document.querySelector('.goog-te-combo')
  if (!combo) return

  combo.value = lang
  combo.dispatchEvent(new Event('change'))
}

/* ---------------- UI ACTIONS ---------------- */
const selectLanguage = (lang) => {
  selectedLanguage.value = lang
  localStorage.setItem('lang', lang)
  isLanguageDropdownOpen.value = false

  if (googleReady.value) {
    triggerTranslate(lang)
  }
}

const toggleLanguageDropdown = (e) => {
  e.stopPropagation()
  isLanguageDropdownOpen.value = !isLanguageDropdownOpen.value
}

const handleClickOutside = (e) => {
  if (languageDropdownRef.value && !languageDropdownRef.value.contains(e.target)) {
    isLanguageDropdownOpen.value = false
  }
}

const getCurrentFlag = () =>
  availableLanguages.find(l => l.code === selectedLanguage.value)?.flag || '🇺🇸'

const getCurrentLanguageName = () =>
  availableLanguages.find(l => l.code === selectedLanguage.value)?.name || 'English'
 
/* ---------------- LIFECYCLE ---------------- */
onMounted(() => {
  initGoogleTranslate()
   document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

/* ---------------- OTHER ---------------- */
const logout = () => form.post(route('logout'))

 


const announcementText='A Lightweight EMR Built for Modern Clinics — Designed for solo providers and clinics';
</script>


<template>
  <div id="topbar" class="d-flex align-items-center" style="padding: 0px 0;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-4">
          <div
            class="align-items-center d-flex flex-column flex-md-row gap-0 gap-md-4 justify-content-lg-start justify-content-md-center">
            <div class="align-items-center d-flex gap-4">
              <a class="text-white" href="mailto:support@akrahealth.com">
                <i class="bi bi-envelope-fill mr-1 font-md fa-2xl "></i>support@akrahealth.com
              </a>
              <a href="https://wa.me/916381250184" class="text-white d-flex">
                <img class="w-30px mr-1" src="/images/techStack/WhatsApp.svg" alt="WhatsApp" /><span class="mt-1">+91
                  6381250184</span>
              </a>
              
            </div>
          </div>
        </div>
        <!-- Desktop: marquee -->
        <div class="col-lg-8">
          <div class="announcement-wrap">
              <!-- Google Translate Widget -->
           
            <div class="marquee-container" title="Click to pause/resume" @click="toggleMarquee">
              <span
                class="marquee-text pointer"
                :style="{ animationPlayState: isPaused ? 'paused' : 'running' }"
              >
                <img
                  src="/images/speaker_13668445.webp"
                  alt="announcement"
                  class="me-2 announcement-icon"
                />
                <span class="announcement-text">{{ announcementText }}</span>
                <img
                  src="/images/speaker_13668445.webp"
                  alt="announcement"
                  class="me-2 announcement-icon"
                />
              </span>
            </div>
             <li class="nav-item ms-2 d-flex align-items-center" ref="languageDropdownRef">
              <div class="language-selector" @click.stop>
                <button 
                  class="language-dropdown d-flex align-items-center gap-2"
                  @click="toggleLanguageDropdown($event)"
                >
                  <span class="current-flag">{{ getCurrentFlag() }}</span>
                  <span class="current-lang">{{ getCurrentLanguageName() }}</span>
                  <i class="ri-arrow-down-s-line" :class="{ 'rotate-180': isLanguageDropdownOpen }"></i>
                </button>
                
                <div 
                  v-show="isLanguageDropdownOpen"
                  class="language-options"
                >
                  <div 
                    v-for="lang in availableLanguages" 
                    :key="lang.code"
                    class="language-option d-flex align-items-center gap-2"
                    :class="{ 'active': selectedLanguage === lang.code }"
                    @click="selectLanguage(lang.code)"
                  >
                    <span class="option-flag">{{ lang.flag }}</span>
                    <span class="option-name">{{ lang.name }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Hidden Google Translate element container -->
              <div 
                id="google_translate_element" 
                style="position: absolute; left: -9999px; top: -9999px; visibility: hidden;"
              ></div>
            </li>

          </div>
        </div>
      </div>
    </div> 
  </div> 

  <header id="header" class="fixed-top">
    <div class="container ">
      <nav class="navbar navbar-expand-lg" id="navbar">
        <Link :href="route('home')" class="logo mr-auto">
        <img src="/images/akrahealth.webp" alt="" />
        </Link>
        <a class="navbar-toggler" data-toggle="collapse" data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="ri-menu-fill font-size-28"></span>
        </a>
        <div class="collapse navbar-collapse flex-row-reverse" id="navbarSupportedContent">
          <ul>
            <li>
              <Link class="nav-link me-2" :class="`${route().current('home') ? 'active' : ''}`" :href="route('home')"
                :active="route().current('home')">
                {{  'Home'}}</Link>
            </li>
            <li>
              <Link class="nav-link me-2" :class="`${route().current('emr') ? 'active' : ''}`" :href="route('emr')"
                :active="route().current('emr')">{{ 'EMR' }}
              </Link>
            </li>
            <!-- <li>
              <Link class="nav-link me-2" :class="`${route().current('ai-receptionist') ? 'active' : ''}`" :href="route('ai-receptionist')"
                :active="route().current('ai-receptionist')">{{ 'AI Receptionist' }}
              </Link>
            </li> -->
                        <li class="nav-item dropdown">
               <a href="#" class="nav-link me-2 dropdown-toggle mt-1" role="button" data-toggle="dropdown"
                aria-expanded="false"
                :class="`${route().current('ai-receptionist') ? 'active' : ''}` || `${route().current('ai-receptionist') ? 'active' : ''}`
                  || `${route().current('ai-receptionist') ? 'active' : ''}` || `${route().current('ai-receptionist') ? 'active' : ''}`">
                <span>{{ 'AI Solutions' }}</span> <i class="ri-arrow-drop-down-line ri-xl "></i>
              </a>
           
              <div class="dropdown-menu">
                  <Link class="nav-link me-2 dropdown-item" :class="`${route().current('ai-receptionist') ? 'active' : ''}`" :href="route('ai-receptionist')"
                    :active="route().current('ai-receptionist')">{{ 'AI Receptionist' }}
                  </Link>
                <div class="dropdown-divider"></div>
                  <Link class="nav-link me-2 dropdown-item" :class="`${route().current('ai-patient-intake') ? 'active' : ''}`" :href="route('ai-patient-intake')"
                    :active="route().current('ai-patient-intake')">{{ 'AI Patient Intake' }}
                  </Link>
                <div class="dropdown-divider"></div>
                  <Link class="nav-link me-2 dropdown-item" :class="`${route().current('ai-medical-scribe') ? 'active' : ''}`" :href="route('ai-medical-scribe')"
                    :active="route().current('ai-medical-scribe')">{{ 'AI Medical Scribe' }}
                  </Link>
               
              </div>
              </li> 
            <!---- <li>
              <Link class="nav-link me-2" :class="`${route().current('dental') ? 'active' : ''}`"
                :href="route('dental')" :active="route().current('dental')">Dental
              </Link>
            </li> -->

            <li class="nav-item dropdown">
              <a href="#" class="nav-link me-2 dropdown-toggle mt-1" role="button" data-toggle="dropdown"
                aria-expanded="false"
                :class="`${route().current('emr-integration') ? 'active' : ''}` || `${route().current('api-integration') ? 'active' : ''}`
                  || `${route().current('standard-integrations') ? 'active' : ''}` || `${route().current('billing-integrations') ? 'active' : ''}`">
                <span>{{ 'Integrations' }}</span> <i class="ri-arrow-drop-down-line ri-xl "></i>
              </a>

              <div class="dropdown-menu">
                <Link class="nav-link me-2 dropdown-item"
                  :class="`${route().current('emr-integration') ? 'active' : ''}`" :href="route('emr-integration')">EMR
                Integration
                </Link>
                <div class="dropdown-divider"></div>
                <Link class="nav-link me-2 dropdown-item"
                  :class="`${route().current('api-integration') ? 'active' : ''}`" :href="route('api-integration')">API
                Integration
                </Link>
                <div class="dropdown-divider"></div>
                <Link class="nav-link me-2 dropdown-item"
                  :class="`${route().current('standard-integrations') ? 'active' : ''}`"
                  :href="route('standard-integrations')">Standard Integrations</Link>
                <div class="dropdown-divider"></div>
                <Link class="nav-link me-2 dropdown-item"
                  :class="`${route().current('billing-integrations') ? 'active' : ''}`"
                  :href="route('billing-integrations')">Billing Integrations</Link>
              </div>
            </li>

            <li class="nav-item dropdown">
              <a href="#" class="nav-link me-2 dropdown-toggle mt-1" role="button" data-toggle="dropdown"
                aria-expanded="false"
                :class="`${route().current('clinic-integration') ? 'active' : ''}` || `${route().current('doctor-on-call') ? 'active' : ''}`
                  || `${route().current('patient-integrations') ? 'active' : ''}` || `${route().current('mobile-iv') ? 'active' : ''}`|| `${route().current('prescription') ? 'active' : ''}`|| `${route().current('mar') ? 'active' : ''}`|| `${route().current('wellness') ? 'active' : ''}`"
                >
                <span>{{ 'Products' }}</span> <i class="ri-arrow-drop-down-line ri-xl "></i>
              </a>

              <div class="dropdown-menu">
                <!-- External links use <a> -->
                  <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('clinic-management') ? 'active' : ''"
                  href="https://practice.akrahealth.com/"
                  rel="noopener noreferrer"
                >
                  Practice Management
                </a>            
                <div class="dropdown-divider"></div>
                <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('patient-management') ? 'active' : ''"
                  href="https://appointment.akrahealth.com/"
                  rel="noopener noreferrer"
                >
                Automated Appointments
              </a>

                <div class="dropdown-divider"></div>

                <!-- Internal Inertia route -->
                <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('doctor-on-call') ? 'active' : ''"
                  href="https://telehealth.akrahealth.com/"
                  rel="noopener noreferrer"
                >
                  Telehealth Services
                </a>               
                <div class="dropdown-divider"></div>
                <a
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('mobile-iv') ? 'active' : ''"
                  href="https://mobileiv.akrahealth.com/"
                  rel="noopener noreferrer"
       >
                  Mobile IV
              </a>
               

                <div class="dropdown-divider"></div>

                <!-- Internal route -->
                <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('prescription') ? 'active' : ''"
                  href="https://prescription.akrahealth.com"
                  rel="noopener noreferrer"
                >
                  Prescription Management
                </a>
                
                <div class="dropdown-divider"></div>

                <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('mar') ? 'active' : ''"
                  href="https://mar.akrahealth.com"
                  rel="noopener noreferrer"
                >
                  Medication Administration Records
                </a>

                <div class="dropdown-divider"></div>

                <a 
                  class="nav-link me-2 dropdown-item"
                  :class="route().current('wellness') ? 'active' : ''"
                  href="https://wellness.akrahealth.com"
                  rel="noopener noreferrer"
                >
                  Wellness Service
                </a>
                </div>
          </li>

            <li>
              <Link class="nav-link me-2" :class="`${route().current('security') ? 'active' : ''}`"
                :href="route('security')">
              {{ 'Security'}}</Link>
            </li>

            <li class="nav-item dropdown">
              <a href="#" class="nav-link me-2 dropdown-toggle mt-1" role="button" data-toggle="dropdown"
                aria-expanded="false"
                :class="`${route().current('pharmacy-app') ? 'active' : ''}` || `${route().current('emr-app') ? 'active' : ''}`">
                <span>{{ 'Apps' }}</span> <i class="ri-arrow-drop-down-line ri-xl "></i>
              </a>
              <div class="dropdown-menu">
                <Link class="nav-link me-2 dropdown-item" :class="`${route().current('emr-app') ? 'active' : ''}`"
                  :href="route('emr-app')">EMR App</Link>
                <div class="dropdown-divider"></div>
                <Link class="nav-link me-2 dropdown-item" :class="`${route().current('pharmacy-app') ? 'active' : ''}`"
                  :href="route('pharmacy-app')">Pharmacy App</Link>

              </div>
            </li>

            <li v-if="$page.props.auth.user?.name">
              <Link class="nav-link me-2" :class="`${route().current('appointments.index') ? 'active' : ''}`"
                :href="route('appointments.index')">Appointment</Link>
            </li>

            <li class="dropdown" v-if="$page.props.auth?.user">
              <a href="#" class="nav-link me-2  dropdown-toggle" role="button" data-toggle="dropdown"
                aria-expanded="false">
                <span>{{ $page.props.auth.user?.name }}</span><i class="ri-arrow-drop-down-line ri-xl "></i>
              </a>
              <div class="dropdown-menu">

                <Link class="nav-link me-2 dropdown-item" :href="route('dashboard')">
                  <i class="fa fa-btn fa-dashboard mr-3"></i>Dashboard</Link>
                <div class="dropdown-divider"></div>
                <Link class="nav-link me-2 dropdown-item my-2" :href="route('user.profile')"><i
                  class="ri-account-circle-fill mr-3"></i>
                  My Information
                </Link>

                <Link class="nav-link me-2 dropdown-item my-2" :href="route('change.password')">
                <i class="ri-lock-password-fill mr-3"></i>Password Change</Link>

                <a class="nav-link me-2 dropdown-item my-2" @click="logout()"><i
                    class="fa fa-btn fa-sign-out mr-3"></i>Logout</a>
              </div>
            </li>
            <div v-else class="d-lg-inline-flex ms-3 ">
              <!-- <li>
              <a class="btn btn-primary me-2 my-2 my-sm-0" href="https://app.akrahealth.com/login">Login</a>
          </li> -->
              <li>
                 <a :href="route('login')" class="btn btn-success me-2 my-2 my-sm-0">Login</a>
              </li>
              <button class="btn btn-primary me-2 my-2 my-sm-0" data-toggle="modal" data-target="#demo-request-modal">Get Demo</button>
               <!-- <li>
                 <a :href="route('signup')" class="btn btn-primary me-2 my-2 my-sm-0">  Sign Up</a>
              </li> -->
            </div>
          </ul>
        </div>
      </nav>
    </div>
  </header>
 </template>

<style scoped>
.announcement-wrap {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.announcement-static {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.announcement-icon {
  width: 5%;
  height: 5%;
  flex: 0 0 auto;
  margin-top: 2px;
}

.announcement-text {
  color: #ffffff;
  font-weight: 600;
  letter-spacing: 0.2px;
  line-height: 1.25;
}

.animated-button {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.animated-button:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.marquee-container {
  overflow: hidden;
  white-space: nowrap;
  width: 100%;
  padding: 6px 10px;
  border-radius: 10px;
  cursor: pointer;
}

.marquee-text {
  display: inline-block;
  will-change: transform;
  animation: marquee 18s linear infinite;
}

@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

/* Dropdown Styling */
.language-selector {
  position: relative;
  z-index: 1000;
  display: flex;
  align-items: center;
}
.language-dropdown:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.language-dropdown option {
  color: #000;
  background: #fff;
  padding: 8px;
}
 

.language-dropdown:focus:not(:disabled) {
  outline: none;
  border-color: #fff;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.4);
}

/* Custom Language Selector */
.language-selector {
  position: relative;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

.language-dropdown {
  background: #fff;
  color: #000;
  border: 2px solid rgba(255, 255, 255, 0.3);
   border-radius: 8px;
   cursor: pointer;
  text-align: left;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.language-dropdown:focus {
  outline: none;
  border-color: #fff;
  box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.4);
}

.current-flag,
.option-flag {
  font-size: 18px;
  line-height: 1;
}

.current-lang,
.option-name {
  flex: 1;
  font-weight: 500;
}

.language-options {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 4px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  overflow: hidden;
   z-index: 10000;
}

.language-option {
  padding: 12px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #333;
  border-bottom: 1px solid #f0f0f0;
}

.language-option:last-child {
  border-bottom: none;
}

.language-option.active {
  background-color: #09ACFF;
  color: #fff;
  font-weight: 600;
}

.rotate-180 {
  transform: rotate(180deg);
}

.language-dropdown i {
  margin-left: auto;
  font-size: 14px;
  transition: transform 0.3s ease;
}
</style>

<style>
/* Global styles to handle Google Translate elements - hidden widget */
.goog-te-banner-frame {
  display: none !important;
  visibility: hidden !important;
  height: 0 !important;
  overflow: hidden !important;
}

body {
  top: 0 !important;
  position: static !important;
}

.goog-logo-link,
.goog-te-gadget span,
.goog-te-gadget-simple .goog-te-menu-value {
  display: none !important;
}

.goog-te-gadget {
  font-size: 0 !important;
}

.goog-te-combo {
  margin: 0 !important;
  padding: 4px !important;
  border: 1px solid #ccc !important;
  border-radius: 4px !important;
}

/* Hide the iframe banner */
iframe.goog-te-banner-frame {
  display: none !important;
  visibility: hidden !important;
  height: 0 !important;
  width: 0 !important;
  border: none !important;
}

/* Hide skiptranslate links */
a.skiptranslate {
  display: none !important;
  visibility: hidden !important;
}

.VIpgJd-ZVi9od-ORHb-OEVmcd {
  display: none !important;
  visibility: hidden !important;
}

/* Remove Google icon from language select */
.goog-te-gadget .goog-te-combo {
  background-image: none !important;
}

.goog-te-gadget-simple {
  background-image: none !important;
}

/* Hide the Google translate logo image */
.goog-te-gadget img,
.goog-logo-link img,
.VIpgJd-ZVi9od-lKx7e-uxVf3f,
.VIpgJd-ZVi9od-ORHb-OEVmcd img {
  display: none !important;
  visibility: hidden !important;
  width: 0 !important;
  height: 0 !important;
    pointer-events: none !important;

}

/* Hide the translate box Google branding */
.goog-te-gadget > a > img,
.goog-te-gadget > a {
  display: none !important;
}

/* Hide any remaining Google icons/logos in the widget */
.goog-te-combo,
.goog-te-combo:focus,
.goog-te-combo:active {
  background-image: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
  text-indent: 1px !important;
  text-overflow: '' !important;
}

/* Hide Google Translate element container */
#google_translate_element {
  position: absolute !important;
  left: -9999px !important;
  top: -9999px !important;
  visibility: hidden !important;
}
 .VIpgJd-ZVi9od-ORHb-OEVmcd,
.VIpgJd-ZVi9od-ORHb-OEVmcd * {
  display: none !important;
  visibility: hidden !important;
  pointer-events: none !important;
}
.goog-te-balloon-frame,
.goog-te-balloon-frame * {
  display: none !important;
  visibility: hidden !important;
  pointer-events: none !important;
}

</style>
