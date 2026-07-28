import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route as routeFn } from 'ziggy-js';

createInertiaApp({
    title: (title) => `ServiceKU - ${title}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        const vm = createApp({ render: () => h(App, props) })
            .use(plugin);

        // Buat route() global di Vue dari global Ziggy (dicatat oleh @routes)
        vm.config.globalProperties.route = (name, params, absolute) => {
            return routeFn(name, params, absolute);
        };

        return vm.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
