import { ref, computed, watch } from 'vue';

// Get translations from Inertia (backend) when available
const getInertiaTranslations = () => {
  if (typeof window !== 'undefined' && window.$page?.props?.translations?.common) {
    return window.$page.props.translations.common;
  }
  return null;
};


const translations = {
  en: {
    home: 'Home',
    emr: 'EMR',
    aiReceptionist: 'AI Receptionist',
    integrations: 'Integrations',
    applications: 'Applications',
    security: 'Security',
    mobile: 'Mobile',
    appointment: 'Appointment',
    select_language: 'Select Language',
    selectLanguage: 'Select Language',
    dashboard: 'Dashboard',
    myInformation: 'My Information',
    passwordChange: 'Password Change',
    logout: 'Logout',
    login: 'Login',
    getDemo: 'Get Demo',
    googleTranslate: 'Google Translate',
  },
  es: {
    home: 'Inicio',
    emr: 'EMR',
    aiReceptionist: 'Recepcionista IA',
    integrations: 'Integraciones',
    applications: 'Aplicaciones',
    security: 'Seguridad',
    mobile: 'Móvil',
    appointment: 'Cita',
    select_language: 'Seleccionar idioma',
    selectLanguage: 'Seleccionar idioma',
    dashboard: 'Panel de control',
    myInformation: 'Mi información',
    passwordChange: 'Cambiar contraseña',
    logout: 'Cerrar sesión',
    login: 'Iniciar sesión',
    getDemo: 'Obtener demostración',
    googleTranslate: 'Traductor de Google',
  },
  fr: {
    home: 'Accueil',
    emr: 'EMR',
    aiReceptionist: 'Réceptionniste IA',
    integrations: 'Intégrations',
    applications: 'Applications',
    security: 'Sécurité',
    mobile: 'Mobile',
    appointment: 'Rendez-vous',
    select_language: 'Sélectionner la langue',
    selectLanguage: 'Sélectionner la langue',
    dashboard: 'Tableau de bord',
    myInformation: 'Mes informations',
    passwordChange: 'Changer le mot de passe',
    logout: 'Déconnexion',
    login: 'Connexion',
    getDemo: 'Demander une démo',
    googleTranslate: 'Google Traduction',
  },
  ar: {
    home: 'الرئيسية',
    emr: 'EMR',
    aiReceptionist: 'موظف الاستقبال الذكي',
    integrations: 'التكاملات',
    applications: 'التطبيقات',
    security: 'الأمان',
    mobile: 'الجوال',
    appointment: 'موعد',
    select_language: 'اختر اللغة',
    selectLanguage: 'اختر اللغة',
    dashboard: 'لوحة التحكم',
    myInformation: 'معلوماتي',
    passwordChange: 'تغيير كلمة المرور',
    logout: 'تسجيل الخروج',
    login: 'تسجيل الدخول',
    getDemo: 'احصل على عرض توضيحي',
    googleTranslate: 'ترجمة جوجل',
  },
};

// Initialize language from backend locale if available, otherwise from localStorage
const getInitialLanguage = () => {
  if (typeof window !== 'undefined' && window.$page?.props?.locale) {
    return window.$page.props.locale;
  }
  return localStorage.getItem('selected_language') || 'en';
};

const currentLanguage = ref(getInitialLanguage());

/**
 * Composable hook for managing language and translations
 * @returns {Object} Language utilities and computed properties
 */
export function useLanguage() {
  // Try to get backend translations first, fall back to local translations
  const inertiaTranslations = getInertiaTranslations();
  
  // Sync with backend locale on initialization
  if (typeof window !== 'undefined' && window.$page?.props?.locale) {
    const backendLocale = window.$page.props.locale;
    if (backendLocale !== currentLanguage.value) {
      currentLanguage.value = backendLocale;
      localStorage.setItem('selected_language', backendLocale);
    }
  }
  
  const setLanguage = (lang) => {
    if (translations[lang] || ['en', 'es', 'fr', 'ar'].includes(lang)) {
      currentLanguage.value = lang;
      localStorage.setItem('selected_language', lang);
    }
  };

  const getTranslation = (key, lang = null) => {
    const language = lang || currentLanguage.value;
    
    // Normalize key (support both snake_case and camelCase)
    const normalizedKey = key.replace(/([A-Z])/g, '_$1').toLowerCase();
    
    // First try backend translations (from Laravel __() helper)
    if (inertiaTranslations) {
      // Try both original key and normalized key
      if (inertiaTranslations[key]) {
        return inertiaTranslations[key];
      }
      if (inertiaTranslations[normalizedKey]) {
        return inertiaTranslations[normalizedKey];
      }
    }
    
    // Fall back to local translations
    return translations[language]?.[key] || translations[language]?.[normalizedKey] || translations['en'][key] || translations['en'][normalizedKey] || key;
  };

  const t = (key) => getTranslation(key);

  // Watch for locale changes from Inertia
  if (typeof window !== 'undefined') {
    watch(() => window.$page?.props?.locale, (newLocale) => {
      if (newLocale && newLocale !== currentLanguage.value) {
        currentLanguage.value = newLocale;
        localStorage.setItem('selected_language', newLocale);
      }
    });
  }

  return {
    currentLanguage: computed(() => currentLanguage.value),
    setLanguage,
    t,
    getTranslation,
    translations: computed(() => translations[currentLanguage.value] || translations['en']),
  };
}
