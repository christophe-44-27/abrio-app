<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps<{ current: 0 | 1 | 2 }>();

const { t } = useI18n();
const hrefs = ['/menage', '/logements', '/verdicts'];
</script>

<template>
    <nav class="flex flex-wrap gap-2 text-sm font-semibold print:hidden">
        <Link
            v-for="(label, i) in t.household.steps"
            :key="label"
            :href="hrefs[i]"
            :aria-current="i === current ? 'step' : undefined"
            class="rounded-full px-4 py-2"
            :class="
                i === current
                    ? 'bg-[#2B1D14] text-white'
                    : 'bg-[#F0E4D6] text-[#6B5647] hover:bg-[#E8D6C2]'
            "
        >
            {{ label }}
        </Link>
    </nav>
</template>
