import { ref, onMounted } from 'vue';

/**
 * Composable for Google Translate widget integration
 * @returns {Object} Google Translate utilities
 */
export function useGoogleTranslate() {
  const isGoogleTranslateLoaded = ref(false);
  const isGoogleTranslateActive = ref(false);

  /**
   * Initialize Google Translate widget
   */
  const initGoogleTranslate = () => {
    if (typeof window === 'undefined') return;

    // Check if Google Translate is already loaded
    if (window.google && window.google.translate) {
      isGoogleTranslateLoaded.value = true;
      return;
    }

    // Check if script is already being loaded
    if (document.querySelector('script[src*="translate.google.com"]')) {
      return;
    }

    // Create the script element
    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    script.async = true;
    script.defer = true;

    // Define the callback function
    window.googleTranslateElementInit = () => {
      if (window.google && window.google.translate) {
        new window.google.translate.TranslateElement(
          {
            pageLanguage: 'en',
            includedLanguages: 'en,es,fr,ar,de,zh-CN,ja,ko,pt,ru,hi',
            layout: window.google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false,
            multilanguagePage: true,
          },
          'google_translate_element'
        );
        isGoogleTranslateLoaded.value = true;

        // Show the widget by default
        const element = document.getElementById('google_translate_element');
        if (element) {
          element.style.display = 'block';
          element.classList.add('show');
          isGoogleTranslateActive.value = true;
          setTimeout(removeGoogleTranslateBanner, 500);
        }
      }
    };

    // Add script to document
    document.head.appendChild(script);
  };

  /**
   * Toggle Google Translate widget visibility
   */
  const toggleGoogleTranslate = () => {
    const element = document.getElementById('google_translate_element');
    if (!element) {
      console.warn('Google Translate element not found');
      return;
    }

    // Check if already visible
    const isCurrentlyVisible = element.style.display === 'block' || element.classList.contains('show');

    if (!isGoogleTranslateLoaded.value) {
      // Initialize Google Translate
      initGoogleTranslate();
      isGoogleTranslateActive.value = true;
      
      // Wait for the script to load and widget to initialize
      const checkForWidget = setInterval(() => {
      const select = document.querySelector('.goog-te-combo');
        if (select) {
          clearInterval(checkForWidget);
          element.style.display = 'block';
          element.classList.add('show');
          select.style.display = 'block';
          select.style.visibility = 'visible';
          isGoogleTranslateActive.value = true;
          // Remove banner after showing
          setTimeout(removeGoogleTranslateBanner, 500);
        }
      }, 100);
      
      // Stop checking after 5 seconds
      setTimeout(() => clearInterval(checkForWidget), 5000);
    } else {
      // Toggle visibility
      const select = document.querySelector('.goog-te-combo');
      if (select) {
        if (isCurrentlyVisible) {
          element.style.display = 'none';
          element.classList.remove('show');
          select.style.display = 'none';
          isGoogleTranslateActive.value = false;
        } else {
          element.style.display = 'block';
          element.classList.add('show');
          select.style.display = 'block';
          select.style.visibility = 'visible';
          isGoogleTranslateActive.value = true;
          setTimeout(removeGoogleTranslateBanner, 500);
        }
      } else {
        // Widget might not be initialized yet, try to show it
        element.style.display = 'block';
        element.classList.add('show');
        isGoogleTranslateActive.value = true;
      }
    }
  };

  /**
   * Get current Google Translate language
   */
  const getCurrentGoogleTranslateLanguage = () => {
    if (typeof window === 'undefined') return null;
    const cookie = document.cookie.match(/googtrans=([^;]+)/);
    if (cookie) {
      const parts = cookie[1].split('/');
      return parts[parts.length - 1] || null;
    }
    return null;
  };

  /**
   * Set Google Translate language programmatically
   */
  const setGoogleTranslateLanguage = (langCode) => {
    if (typeof window === 'undefined' || !window.google?.translate) return;
    
    const select = document.querySelector('.goog-te-combo');
    if (select) {
      select.value = langCode;
      select.dispatchEvent(new Event('change'));
    }
  };

  /**
   * Remove Google Translate styling that might interfere with the page
   */
  const removeGoogleTranslateBanner = () => {
    // Remove the top banner that Google Translate adds
    const banner = document.querySelector('.goog-te-banner-frame');
    if (banner) {
      banner.style.display = 'none';
    }
    
    // Remove the skip link
    const skipLink = document.querySelector('.goog-te-menu-value');
    if (skipLink && skipLink.parentElement) {
      skipLink.parentElement.style.display = 'none';
    }
  };

  onMounted(() => {
    // Initialize on mount
    initGoogleTranslate();
    
    // Watch for Google Translate changes and remove banner
    const observer = new MutationObserver(() => {
      removeGoogleTranslateBanner();
    });
    
    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    // Also try to remove banner immediately
    setTimeout(removeGoogleTranslateBanner, 1000);
  });

  return {
    isGoogleTranslateLoaded,
    isGoogleTranslateActive,
    initGoogleTranslate,
    toggleGoogleTranslate,
    getCurrentGoogleTranslateLanguage,
    setGoogleTranslateLanguage,
    removeGoogleTranslateBanner,
  };
}

