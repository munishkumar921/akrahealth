import './bootstrap';
// import '../css/app.css';
// import '../sass/styles.scss';

import { createApp, h } from 'vue';
import { createInertiaApp, useForm} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/src/sweetalert2.scss';
import VueKonva from "vue-konva";

/* Sweet alert */
window.toast = function (message, type = 'success', timer = 2000) {
    Swal.fire({
        title: '',
        text: message,
        icon: type,
        toast: true,
        position: 'top-end',
        timer: timer,
        timerProgressBar: true,
        showConfirmButton: false,
        showCloseButton: true,
    });
}
window.confirm = (url) => {

    const form = useForm({});

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        backdrop:true,
        allowOutsideClick: false,
        allowEscapeKey: false,
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(url);
        }
    });
},

 /* sweet alert confirmation popup's settings */
window.confirmSettings = function (
    title = 'Are you sure?',
    text = 'You won\'t be able to revert this!',
    confirmButtonText = 'Yes, do it!',
    cancelButtonText = 'Cancel!',
    icon = 'question') {
    return {
        title: `<strong>${title}</strong>`,
        icon: icon,
        html: `<b>${text}</b></br/>`,
        showCloseButton: false,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: confirmButtonText,
        confirmButtonAriaLabel: "Thumbs up, great!",
        cancelButtonText: cancelButtonText,
        cancelButtonAriaLabel: "Thumbs down",
        toast: true,
        position: 'top-end'
    }
}


/*
Example usage of sweet alert confirmation popup:

Swal.fire(confirmSettings('Are you sure to delete this data?', 'You want be get it back')).then((result) => {
    if (result.isConfirmed) {
        toast('Data deleted successfully');
    }
});
*/
const appName = import.meta.env.VITE_APP_NAME || 'AkraHealth';

createInertiaApp({
    title: (title) => `${title ? title + ' ' : ''}${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {

        window.supportedLanguages = props.initialPage.props.supported_languages;
        window.default_language = props.initialPage.props.default_language;

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueKonva)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.app-dark'
                    }
                }
            })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

/* data for the multi-language support form's field */
window.multiLang = function (data) {

    let result = {};

    try {
        const field = JSON.parse(data);

        window.supportedLanguages.forEach((lang) => {
            result[lang] = field?.[lang] || '';
        });
    } catch (e) {
        window.supportedLanguages.forEach((lang) => {
            result[lang] = '';
        });
    }

    return result;
}
 
