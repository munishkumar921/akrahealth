<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import DemoRequest from '../Pages/Modals/DemoRequest.vue';
const form = useForm({});
 
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
        
        <!-- <div class="collapse navbar-collapse flex-row-reverse" id="navbarSupportedContent">
          <ul>
            <li>
              <Link class="nav-link me-2" :class="`${route().current('home') ? 'active' : ''}`" :href="route('home')"
                :active="route().current('home')">
              Home</Link>
            </li>
             
          </ul>
        </div> -->
      </nav>
    </div>
  </header>
  <DemoRequest />
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
