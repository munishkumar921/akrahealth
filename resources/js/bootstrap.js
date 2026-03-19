/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

/**
 * Next we will register the CSRF token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Global axios interceptor to handle session expiry (401) and CSRF token mismatch (419)
 * Redirects to login page with appropriate message
 */
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            // Handle 401 (Unauthorized) - Session expired
            if (error.response.status === 401) {
                // Always use a friendly, generic message (avoid "Unauthenticated.")
                const message = 'Your session has expired. Please log in again.';

                // Store message in sessionStorage as fallback
                sessionStorage.setItem('session_expired_message', message);

                // Redirect to login with message via query parameter
                const encodedMessage = encodeURIComponent(message);
                window.location.href = `/login?session_expired=${encodedMessage}`;
                return Promise.reject(error);
            }
            
            // Handle 419 (CSRF Token Mismatch) - Usually indicates session expiry
            if (error.response.status === 419) {
                const message = 'Your session has expired or you are not logged in. Please log in again.';
                sessionStorage.setItem('session_expired_message', message);
                
                // Redirect to login with message via query parameter
                const encodedMessage = encodeURIComponent(message);
                window.location.href = `/login?session_expired=${encodedMessage}`;
                return Promise.reject(error);
            }
        }
        
        return Promise.reject(error);
    }
);
