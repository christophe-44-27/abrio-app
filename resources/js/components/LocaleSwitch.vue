<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const { t, locale } = useI18n();

const other = computed(() => (locale.value === 'fr' ? 'en' : 'fr'));
</script>

<template>
    <Link
        :href="`/locale/${other}`"
        method="post"
        as="button"
        preserve-scroll
        :lang="other"
        :hreflang="other"
        :aria-label="t.nav.switchLabel"
        class="flex h-11 cursor-pointer items-center rounded-full border border-[#B59A80] px-4 text-sm font-semibold text-[#2B1D14] hover:bg-[#F0E4D6]"
    >
        {{ t.nav.switchText }}
    </Link>
</template>
