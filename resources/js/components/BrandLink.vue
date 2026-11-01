<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';
import { landingPath } from '@/i18n';

const { t, locale } = useI18n();
</script>

<template>
    <Link
        :href="landingPath(locale)"
        :aria-label="t.nav.home"
        class="text-xl font-extrabold tracking-tight"
    >
        Abrio
    </Link>
</template>
