<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.onboarding.welcome.metaTitle" />
    <div class="mx-auto flex max-w-[960px] flex-col gap-16">
        <div class="flex items-center justify-between gap-4">
            <BrandLink />
            <HeaderTools />
        </div>

        <div class="flex max-w-[720px] flex-col gap-5">
            <h1
                class="text-5xl leading-[1.05] font-extrabold tracking-[-0.035em] sm:text-[56px]"
            >
                {{ t.onboarding.welcome.title }}
            </h1>
            <p class="text-[19px] leading-normal text-[#6B5647]">
                {{ t.onboarding.welcome.text }}
            </p>
        </div>

        <div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4">
            <div
                v-for="(step, i) in t.onboarding.welcome.steps"
                :key="step.title"
                class="flex flex-col gap-3 rounded-2xl border border-[#E8DACB] bg-white p-6"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-full bg-[#F5A25D] text-base font-extrabold"
                >
                    {{ i + 1 }}
                </span>
                <span class="text-lg font-extrabold">{{ step.title }}</span>
                <span class="text-[15px] leading-normal text-[#6B5647]">
                    {{ step.text }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-5">
            <Link
                href="/onboarding/menage"
                class="flex h-14 items-center justify-center rounded-[14px] bg-[#2B1D14] px-9 text-[17px] font-extrabold text-white hover:bg-[#43302A]"
            >
                {{ t.onboarding.welcome.cta }}
            </Link>
            <span class="text-sm text-[#6B5647]">
                {{ t.onboarding.welcome.note }}
            </span>
        </div>
    </div>
</template>
