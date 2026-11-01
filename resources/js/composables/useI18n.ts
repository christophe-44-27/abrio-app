import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { messages } from '@/i18n';

export function useI18n() {
    const page = usePage();

    const locale = computed(() => page.props.locale ?? 'fr');
    const t = computed(() => messages[locale.value]);

    return { locale, t };
}
