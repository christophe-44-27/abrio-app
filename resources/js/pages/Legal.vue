<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
import { useI18n } from '@/composables/useI18n';
import { landingPath } from '@/i18n';
import { legalDocs, legalSlugs, legalUpdatedLabel } from '@/i18n/legal';
import type { LegalSlug } from '@/i18n/legal';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    slug: LegalSlug;
    legal: {
        operator: string;
        address: string | null;
        host: string | null;
        email: string;
    };
}>();

const { t, locale } = useI18n();

const doc = computed(() => legalDocs[locale.value][props.slug]);

const placeholder = computed(() =>
    locale.value === 'fr' ? '[à compléter]' : '[to be completed]',
);

const fill = (text: string): string =>
    text.replace(/\{(operator|email|address|host)\}/g, (_, key) => {
        return props.legal[key as keyof typeof props.legal] || placeholder.value;
    });
</script>

<template>
    <Head :title="doc.title" />

    <div
        class="min-h-svh bg-[#FBF5EE] px-6 py-8 font-['Manrope',system-ui,sans-serif] text-[#2B1D14] md:px-10"
    >
        <div class="mx-auto w-full max-w-[760px]">
            <div class="flex items-center justify-between">
                <Link
                    :href="landingPath(locale)"
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
                <LocaleSwitch />
            </div>

            <nav
                class="mt-8 flex flex-wrap gap-x-5 gap-y-1 text-sm font-semibold"
                :aria-label="t.legal.nav"
            >
                <Link
                    v-for="s in legalSlugs"
                    :key="s"
                    :href="`/legal/${s}`"
                    class="flex h-11 items-center"
                    :class="
                        s === slug
                            ? 'text-[#2B1D14] underline underline-offset-4'
                            : 'text-[#B4540F] hover:text-[#8F3F08]'
                    "
                >
                    {{ t.legal.titles[s] }}
                </Link>
            </nav>

            <main class="mt-4 pb-16">
                <h1
                    class="text-[32px] leading-tight font-extrabold tracking-[-0.03em]"
                >
                    {{ doc.title }}
                </h1>
                <p class="mt-2 text-sm text-[#6B5647]">
                    {{ legalUpdatedLabel[locale] }}
                </p>
                <p v-if="doc.intro" class="mt-6 text-base leading-relaxed">
                    {{ fill(doc.intro) }}
                </p>

                <section
                    v-for="section in doc.sections"
                    :key="section.title"
                    class="mt-8"
                >
                    <h2 class="text-xl font-extrabold">
                        {{ section.title }}
                    </h2>
                    <p
                        v-for="(p, i) in section.paragraphs"
                        :key="i"
                        class="mt-3 text-base leading-relaxed text-[#43302A]"
                    >
                        {{ fill(p) }}
                    </p>
                </section>
            </main>
        </div>
    </div>
</template>
