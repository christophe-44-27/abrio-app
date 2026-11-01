<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
import { useI18n } from '@/composables/useI18n';
import { landingPath } from '@/i18n';

defineProps<{
    title?: string;
    description?: string;
}>();

const { locale } = useI18n();

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>
    <div
        class="flex min-h-svh flex-col items-center bg-[#FBF5EE] px-6 py-8 font-['Manrope',system-ui,sans-serif] text-[#2B1D14] md:px-10"
    >
        <div class="w-full max-w-[480px]">
            <div class="flex items-center justify-between">
                <Link
                    :href="landingPath(locale)"
                    class="text-xl font-extrabold tracking-tight"
                >
                    Abrio
                </Link>
                <LocaleSwitch />
            </div>
            <div class="mt-10 flex flex-col gap-8">
                <div class="flex flex-col gap-3">
                    <h1
                        class="text-[32px] leading-tight font-extrabold tracking-[-0.03em]"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-base leading-normal text-[#6B5647]"
                    >
                        {{ description }}
                    </p>
                </div>
                <div class="rounded-2xl border border-[#E8DACB] bg-white p-7">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
