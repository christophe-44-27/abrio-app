import { createInertiaApp } from '@inertiajs/vue3';
import { createApp } from 'vue';
import CookieBanner from '@/components/CookieBanner.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Abrio';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Landing':
                return null;
            case name.startsWith('onboarding/'):
            case name.startsWith('household/'):
                return OnboardingLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return null;
            default:
                return null;
        }
    },
    withApp: (app) => {
        // The cookie notice lives in its own tiny app so every layout gets it.
        const noticeRoot = document.createElement('div');
        document.body.appendChild(noticeRoot);
        createApp(CookieBanner).mount(noticeRoot);

        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

// This will listen for flash toast data from the server...
initializeFlashToast();
