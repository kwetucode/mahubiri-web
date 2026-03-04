import './bootstrap';
import '../css/app.css';

// Apply theme before render to prevent flash
const savedTheme = localStorage.getItem('mahubiri-theme') || 'light';
if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    title: (title) => title ? `${title} - Mahubiri Admin` : 'Mahubiri Admin',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: false,
});
