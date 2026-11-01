<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const INTERVAL_MS = 2000;
const GIVE_UP_AFTER_MS = 60000;

const slow = ref(false);
let timer: ReturnType<typeof setInterval> | undefined;

// The Stripe webhook unlocks the account; once it has, this route redirects.
onMounted(() => {
    const startedAt = Date.now();

    timer = setInterval(() => {
        if (Date.now() - startedAt > GIVE_UP_AFTER_MS) {
            slow.value = true;
            clearInterval(timer);

            return;
        }

        router.visit('/unlock/success', {
            replace: true,
            preserveScroll: true,
        });
    }, INTERVAL_MS);
});

onBeforeUnmount(() => clearInterval(timer));

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.limit.pending.metaTitle" />
    <div class="mx-auto flex max-w-[560px] flex-col gap-8 pt-16">
        <div class="flex items-center justify-between gap-4">
            <BrandLink />
            <HeaderTools />
        </div>
        <div
            class="flex flex-col gap-4 rounded-2xl border border-[#E8DACB] bg-white p-8"
            role="status"
        >
            <h1 class="text-[28px] font-extrabold tracking-[-0.02em]">
                {{ t.limit.pending.title }}
            </h1>
            <p class="text-base leading-normal text-[#6B5647]">
                {{ slow ? t.limit.pending.slow : t.limit.pending.text }}
            </p>
        </div>
        <Link
            href="/logements"
            class="text-[15px] font-semibold text-[#B4540F] hover:text-[#8F3F08]"
        >
            {{ t.limit.pending.back }}
        </Link>
    </div>
</template>
