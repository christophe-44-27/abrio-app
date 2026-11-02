<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import { Toaster } from '@/components/ui/sonner';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useI18n } from '@/composables/useI18n';
import { edit as editPremium } from '@/routes/premium';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

defineOptions({ inheritAttrs: false });

const { t } = useI18n();
const { isCurrentOrParentUrl } = useCurrentUrl();

const items = [
    { key: 'profile', href: editProfile() },
    { key: 'premium', href: editPremium() },
    { key: 'team', href: '/settings/team' },
    { key: 'security', href: editSecurity() },
] as const;
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>
    <Toaster position="top-center" />
    <div
        class="min-h-screen bg-[#FBF5EE] px-6 pt-8 pb-16 font-['Manrope',system-ui,sans-serif] text-[#2B1D14] md:px-10"
    >
        <div class="mx-auto flex max-w-[640px] flex-col gap-8">
            <div class="flex items-center justify-between gap-4">
                <BrandLink />
                <HeaderTools />
            </div>

            <div class="flex flex-col gap-2">
                <h1 class="text-[32px] font-extrabold tracking-[-0.03em]">
                    {{ t.settings.title }}
                </h1>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ t.settings.subtitle }}
                </p>
            </div>

            <nav
                :aria-label="t.settings.nav.label"
                class="flex gap-2 border-b border-[#E8DACB]"
            >
                <Link
                    v-for="item in items"
                    :key="item.key"
                    :href="item.href"
                    :class="[
                        '-mb-px border-b-2 px-3 pb-3 text-[15px] font-semibold',
                        isCurrentOrParentUrl(item.href)
                            ? 'border-[#B4540F] text-[#B4540F]'
                            : 'border-transparent text-[#6B5647] hover:text-[#2B1D14]',
                    ]"
                >
                    {{ t.settings.nav[item.key] }}
                </Link>
            </nav>

            <div class="flex flex-col gap-6">
                <slot />
            </div>
        </div>
    </div>
</template>
