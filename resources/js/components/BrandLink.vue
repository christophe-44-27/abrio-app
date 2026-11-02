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
        class="flex items-center gap-2.5 text-xl font-extrabold tracking-tight"
    >
        <img
            src="/assets/images/logo.webp"
            alt=""
            width="36"
            height="36"
            class="size-9 rounded-[22%]"
        />
        Abrio
    </Link>
</template>
