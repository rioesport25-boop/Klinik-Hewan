import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Import AOS
import AOS from 'aos';
import 'aos/dist/aos.css';

// Import SweetAlert2
import SweetAlert from './Plugins/sweetalert';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Make SweetAlert available globally
        app.config.globalProperties.$swal = SweetAlert;

        // Initialize AOS
        app.mixin({
            mounted() {
                AOS.init({
                    duration: 800,
                    easing: 'ease-in-out',
                    once: true,
                    mirror: false,
                });
            },
        });

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
