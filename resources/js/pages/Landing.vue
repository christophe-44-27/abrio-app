<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Minus, Plus } from '@lucide/vue';
import { computed } from 'vue';
import { landingPath, messages } from '@/i18n';
import type { Locale } from '@/i18n';

const props = defineProps<{
    locale: Locale;
    urls: Record<Locale, string>;
}>();

const t = computed(() => messages[props.locale]);
const otherLocale = computed<Locale>(() =>
    props.locale === 'fr' ? 'en' : 'fr',
);
// Signed-in visitors would be bounced off /register, so send them to the app.
const signedIn = computed(() => Boolean(usePage().props.auth?.user));
const startHref = computed(() => (signedIn.value ? '/dashboard' : '/register'));

const contactForm = useForm({
    name: '',
    email: '',
    message: '',
    website: '',
});
const sendContact = () =>
    contactForm.post('/contact', {
        preserveScroll: true,
        onSuccess: () => contactForm.reset(),
    });

const featureIcons = [
    'M9 8a3 3 0 1 0 0 .01M17 9a2.5 2.5 0 1 0 0 .01M3 19c0-3 3-5 6-5s6 2 6 5M15 19c0-2 2-4 5-4',
    'M4 11l8-7 8 7v9H4zM10 20v-6h4v6',
    'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM8 12l3 3 5-6',
    'M7 3h7l5 5v13H7zM14 3v5h5M12 11v6M9.5 14.5L12 17l2.5-2.5',
];

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.meta.title">
        <meta
            head-key="description"
            name="description"
            :content="t.meta.description"
        />
        <meta head-key="og:title" property="og:title" :content="t.meta.title" />
        <meta
            head-key="og:description"
            property="og:description"
            :content="t.meta.description"
        />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta
            head-key="og:image"
            property="og:image"
            :content="`${urls.fr.replace(/\/$/, '')}/assets/images/logo.webp`"
        />
        <meta
            head-key="og:locale"
            property="og:locale"
            :content="locale === 'fr' ? 'fr_CA' : 'en_CA'"
        />
        <link head-key="canonical" rel="canonical" :href="urls[locale]" />
        <link head-key="alt-fr" rel="alternate" hreflang="fr" :href="urls.fr" />
        <link head-key="alt-en" rel="alternate" hreflang="en" :href="urls.en" />
        <link
            head-key="alt-default"
            rel="alternate"
            hreflang="x-default"
            :href="urls.fr"
        />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        :lang="locale"
        class="min-h-screen scroll-smooth bg-[#FBF5EE] font-['Manrope',system-ui,sans-serif] text-[#2B1D14]"
    >
        <!-- Header -->
        <header class="px-10 py-6">
            <div
                class="mx-auto flex max-w-[1120px] flex-wrap items-center justify-between gap-4"
            >
                <Link
                    :href="landingPath(locale)"
                    class="flex items-center gap-2.5 text-[22px] font-extrabold tracking-tight"
                >
                    <img
                        src="/assets/images/logo.webp"
                        alt=""
                        width="40"
                        height="40"
                        class="size-10 rounded-[22%]"
                    />
                    Abrio
                </Link>
                <nav
                    class="flex flex-wrap items-center gap-x-6 gap-y-1 text-[15px] font-semibold"
                    aria-label="Principal"
                >
                    <a href="#how" class="flex h-11 items-center">{{
                        t.nav.how
                    }}</a>
                    <a href="#pricing" class="flex h-11 items-center">{{
                        t.nav.pricing
                    }}</a>
                    <a href="#faq" class="flex h-11 items-center">{{
                        t.nav.faq
                    }}</a>
                    <a href="#contact" class="flex h-11 items-center">{{
                        t.nav.contact
                    }}</a>
                    <Link
                        :href="landingPath(otherLocale)"
                        :hreflang="otherLocale"
                        :aria-label="t.nav.switchLabel"
                        class="flex h-11 items-center rounded-full border border-[#B59A80] px-3.5"
                    >
                        {{ t.nav.switchText }}
                    </Link>
                    <Link
                        :href="startHref"
                        class="flex h-11 items-center rounded-xl bg-[#2B1D14] px-5.5 text-white hover:bg-[#43302A]"
                    >
                        {{ signedIn ? t.nav.app : t.nav.start }}
                    </Link>
                    <Link
                        v-if="!signedIn"
                        href="/login"
                        class="flex h-11 items-center rounded-xl border border-[#2B1D14] px-5.5 hover:bg-white"
                    >
                        {{ t.nav.login }}
                    </Link>
                </nav>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section class="px-10 pt-14 pb-24">
                <div
                    class="mx-auto flex max-w-[1120px] flex-wrap items-center gap-14"
                >
                    <div class="flex flex-[1_1_460px] flex-col gap-6">
                        <h1
                            class="text-5xl leading-[1.04] font-extrabold tracking-[-0.035em] lg:text-6xl"
                        >
                            {{ t.hero.title }}
                        </h1>
                        <p class="text-xl leading-normal text-[#6B5647]">
                            {{ t.hero.text }}
                        </p>
                        <div class="flex flex-wrap items-center gap-4">
                            <Link
                                :href="startHref"
                                class="flex h-14 items-center justify-center rounded-[14px] bg-[#2B1D14] px-8 text-[17px] font-extrabold text-white hover:bg-[#43302A]"
                            >
                                {{ t.hero.cta }}
                            </Link>
                            <a
                                href="#how"
                                class="flex h-14 items-center px-2 text-base font-bold text-[#B4540F] hover:text-[#8F3F08]"
                            >
                                {{ t.hero.secondary }}
                            </a>
                        </div>
                        <span class="text-sm text-[#6B5647]">{{
                            t.hero.note
                        }}</span>
                    </div>

                    <div
                        class="flex flex-[1_1_380px] flex-col gap-3.5"
                        aria-hidden="true"
                    >
                        <div
                            class="flex flex-col gap-3 rounded-2xl border-2 border-[#1F8A5B] bg-white p-5"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <span class="text-lg font-extrabold">{{
                                    t.hero.examples.good.name
                                }}</span>
                                <span
                                    class="rounded-full bg-[#DFF3E8] px-3 py-1 text-[13px] font-extrabold text-[#0B5D3B]"
                                >
                                    {{ t.hero.goBadge }}
                                </span>
                            </div>
                            <div
                                class="relative h-2.5 rounded-full bg-[#F0E4D6]"
                            >
                                <div
                                    class="h-2.5 w-[33%] rounded-full bg-[#1F8A5B]"
                                />
                                <div
                                    class="absolute -top-1 left-[75%] h-[18px] w-[3px] bg-[#2B1D14]"
                                />
                            </div>
                            <div
                                class="flex justify-between text-sm text-[#6B5647]"
                            >
                                <span>{{
                                    t.hero.shareOfIncome.replace(
                                        '{percent}',
                                        t.hero.examples.good.percent,
                                    )
                                }}</span>
                                <span>{{ t.hero.limit }}</span>
                            </div>
                        </div>
                        <div
                            class="flex flex-col gap-3 rounded-2xl border border-[#E8DACB] bg-white p-5"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <span class="text-lg font-extrabold">{{
                                    t.hero.examples.bad.name
                                }}</span>
                                <span
                                    class="rounded-full bg-[#FBE0DE] px-3 py-1 text-[13px] font-extrabold text-[#8F1B13]"
                                >
                                    {{ t.hero.noBadge }}
                                </span>
                            </div>
                            <div
                                class="relative h-2.5 rounded-full bg-[#F0E4D6]"
                            >
                                <div
                                    class="h-2.5 w-[77%] rounded-full bg-[#B3261E]"
                                />
                                <div
                                    class="absolute -top-1 left-[75%] h-[18px] w-[3px] bg-[#2B1D14]"
                                />
                            </div>
                            <div
                                class="flex justify-between text-sm text-[#6B5647]"
                            >
                                <span>{{
                                    t.hero.shareOfIncome.replace(
                                        '{percent}',
                                        t.hero.examples.bad.percent,
                                    )
                                }}</span>
                                <span>{{ t.hero.limit }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="bg-white px-10 py-22">
                <div class="mx-auto flex max-w-[1120px] flex-col gap-12">
                    <div class="flex max-w-[720px] flex-col gap-3">
                        <h2
                            class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >
                            {{ t.features.title }}
                        </h2>
                        <p class="text-lg leading-normal text-[#6B5647]">
                            {{ t.features.text }}
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-5"
                    >
                        <article
                            v-for="(item, i) in t.features.items"
                            :key="item.title"
                            class="flex flex-col gap-3.5 rounded-2xl bg-[#FBF5EE] p-7"
                        >
                            <span
                                class="flex size-11 items-center justify-center rounded-full bg-[#FDEBD9]"
                            >
                                <svg
                                    width="22"
                                    height="22"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="#B4540F"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path :d="featureIcons[i]" />
                                </svg>
                            </span>
                            <h3 class="text-xl font-extrabold">
                                {{ item.title }}
                            </h3>
                            <p class="text-base leading-normal text-[#6B5647]">
                                {{ item.text }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- How it works -->
            <section id="how" class="px-10 py-22">
                <div class="mx-auto flex max-w-[1120px] flex-col gap-12">
                    <h2
                        class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                    >
                        {{ t.how.title }}
                    </h2>
                    <ol
                        class="grid grid-cols-[repeat(auto-fit,minmax(260px,1fr))] gap-5"
                    >
                        <li
                            v-for="(step, i) in t.how.steps"
                            :key="step.title"
                            class="flex flex-col gap-3 rounded-2xl border border-[#E8DACB] bg-white p-7"
                        >
                            <span
                                class="flex size-10 items-center justify-center rounded-full bg-[#F5A25D] text-[17px] font-extrabold"
                            >
                                {{ i + 1 }}
                            </span>
                            <h3 class="text-xl font-extrabold">
                                {{ step.title }}
                            </h3>
                            <p class="text-base leading-normal text-[#6B5647]">
                                {{ step.text }}
                            </p>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- Guide -->
            <section id="guide" class="bg-white px-10 py-22">
                <div class="mx-auto flex max-w-[1120px] flex-wrap gap-14">
                    <div class="flex flex-[1_1_320px] flex-col gap-4">
                        <h2
                            class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >
                            {{ t.guide.title }}
                        </h2>
                        <p class="text-lg leading-normal text-[#6B5647]">
                            {{ t.guide.text }}
                        </p>
                    </div>
                    <div class="flex flex-[2_1_480px] flex-col gap-8">
                        <article
                            v-for="item in t.guide.items"
                            :key="item.title"
                            class="flex flex-col gap-2"
                        >
                            <h3 class="text-[22px] font-extrabold">
                                {{ item.title }}
                            </h3>
                            <p class="text-[17px] leading-[1.6] text-[#6B5647]">
                                {{ item.text }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Pricing -->
            <section id="pricing" class="px-10 py-22">
                <div class="mx-auto flex max-w-[1120px] flex-col gap-12">
                    <div class="flex max-w-[720px] flex-col gap-3">
                        <h2
                            class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >
                            {{ t.pricing.title }}
                        </h2>
                        <p class="text-lg leading-normal text-[#6B5647]">
                            {{ t.pricing.text }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <div
                            class="flex flex-[1_1_320px] flex-col gap-6 rounded-[20px] border border-[#E8DACB] bg-white p-9"
                        >
                            <div class="flex flex-col gap-2">
                                <span class="text-lg font-extrabold">{{
                                    t.pricing.free.name
                                }}</span>
                                <span
                                    class="text-5xl font-extrabold tracking-[-0.03em]"
                                    >{{ t.pricing.free.price }}</span
                                >
                            </div>
                            <ul class="flex flex-col gap-3 text-base">
                                <li
                                    v-for="perk in t.pricing.free.perks"
                                    :key="perk"
                                    class="flex items-center gap-3"
                                >
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#B4540F"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12l5 5 9-10" />
                                    </svg>
                                    {{ perk }}
                                </li>
                            </ul>
                            <Link
                                :href="startHref"
                                class="flex h-[52px] items-center justify-center rounded-[14px] border-2 border-[#2B1D14] text-base font-extrabold"
                            >
                                {{ t.pricing.free.cta }}
                            </Link>
                        </div>

                        <div
                            class="flex flex-[1_1_320px] flex-col gap-6 rounded-[20px] bg-[#2B1D14] p-9 text-white"
                        >
                            <div class="flex flex-col gap-2">
                                <span class="text-lg font-extrabold">{{
                                    t.pricing.unlimited.name
                                }}</span>
                                <div class="flex items-baseline gap-2.5">
                                    <span
                                        class="text-5xl font-extrabold tracking-[-0.03em]"
                                        >{{ t.pricing.unlimited.price }}</span
                                    >
                                    <span class="text-[15px] text-[#E0CDB9]">{{
                                        t.pricing.unlimited.priceNote
                                    }}</span>
                                </div>
                            </div>
                            <ul class="flex flex-col gap-3 text-base">
                                <li
                                    v-for="perk in t.pricing.unlimited.perks"
                                    :key="perk"
                                    class="flex items-center gap-3"
                                >
                                    <svg
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#F5A25D"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12l5 5 9-10" />
                                    </svg>
                                    {{ perk }}
                                </li>
                            </ul>
                            <Link
                                :href="startHref"
                                class="flex h-[52px] items-center justify-center rounded-[14px] bg-[#F5A25D] text-base font-extrabold text-[#2B1D14] hover:bg-[#F29443]"
                            >
                                {{ t.pricing.unlimited.cta }}
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Founder -->
            <section id="founder" class="bg-white px-10 py-22">
                <div
                    class="mx-auto flex max-w-[1120px] flex-wrap items-center gap-12"
                >
                    <img
                        src="/assets/images/christophe.jpg"
                        :alt="t.founder.name"
                        width="160"
                        height="160"
                        loading="lazy"
                        class="size-40 flex-none rounded-full bg-[#F5A25D] object-cover"
                    />
                    <div class="flex flex-[1_1_420px] flex-col gap-3.5">
                        <span
                            class="text-sm font-extrabold tracking-[0.04em] text-[#B4540F] uppercase"
                        >
                            {{ t.founder.label }}
                        </span>
                        <h2
                            class="text-4xl leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >
                            {{ t.founder.name }}
                        </h2>
                        <p
                            v-for="p in t.founder.paragraphs"
                            :key="p"
                            class="text-lg leading-[1.6] text-[#6B5647]"
                        >
                            {{ p }}
                        </p>
                        <a
                            href="https://www.linkedin.com/in/christophe-lablancherie/"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="t.founder.linkedin"
                            :title="t.founder.linkedin"
                            class="mt-1 self-start text-[#B4540F] hover:text-[#8F3F08]"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="size-7"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section id="faq" class="px-10 py-22">
                <div class="mx-auto flex max-w-[800px] flex-col gap-8">
                    <h2
                        class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                    >
                        {{ t.faq.title }}
                    </h2>
                    <div class="flex flex-col border-b border-[#E8DACB]">
                        <details
                            v-for="item in t.faq.items"
                            :key="item.q"
                            class="group border-t border-[#E8DACB] py-5"
                        >
                            <summary
                                class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 text-lg font-bold [&::-webkit-details-marker]:hidden"
                            >
                                {{ item.q }}
                                <Plus
                                    class="size-6 flex-none text-[#B4540F] group-open:hidden"
                                    aria-hidden="true"
                                />
                                <Minus
                                    class="hidden size-6 flex-none text-[#B4540F] group-open:block"
                                    aria-hidden="true"
                                />
                            </summary>
                            <p
                                class="mt-3 text-base leading-[1.6] text-[#6B5647]"
                            >
                                {{ item.a }}
                            </p>
                        </details>
                    </div>
                </div>
            </section>

            <!-- Contact -->
            <section id="contact" class="bg-white px-10 py-22">
                <div class="mx-auto flex max-w-[1120px] flex-wrap gap-14">
                    <div class="flex flex-[1_1_320px] flex-col gap-4">
                        <h2
                            class="text-[40px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >
                            {{ t.contact.title }}
                        </h2>
                        <p class="text-lg leading-normal text-[#6B5647]">
                            {{ t.contact.text }}
                        </p>
                    </div>
                    <form
                        class="flex flex-[1_1_420px] flex-col gap-4"
                        @submit.prevent="sendContact"
                    >
                        <input
                            v-model="contactForm.website"
                            type="text"
                            name="website"
                            tabindex="-1"
                            autocomplete="off"
                            aria-hidden="true"
                            class="hidden"
                        />
                        <div class="flex flex-col gap-1.5">
                            <label
                                for="c-name"
                                class="text-sm font-semibold text-[#6B5647]"
                                >{{ t.contact.name }}</label
                            >
                            <input
                                id="c-name"
                                type="text"
                                v-model="contactForm.name"
                                name="name"
                                autocomplete="name"
                                required
                                class="h-12 rounded-[10px] border border-[#B59A80] bg-white px-4 text-base"
                            />
                            <p
                                v-if="contactForm.errors.name"
                                class="text-sm text-[#8F1B13]"
                            >
                                {{ contactForm.errors.name }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label
                                for="c-email"
                                class="text-sm font-semibold text-[#6B5647]"
                                >{{ t.contact.email }}</label
                            >
                            <input
                                id="c-email"
                                type="email"
                                v-model="contactForm.email"
                                name="email"
                                autocomplete="email"
                                required
                                class="h-12 rounded-[10px] border border-[#B59A80] bg-white px-4 text-base"
                            />
                            <p
                                v-if="contactForm.errors.email"
                                class="text-sm text-[#8F1B13]"
                            >
                                {{ contactForm.errors.email }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label
                                for="c-message"
                                class="text-sm font-semibold text-[#6B5647]"
                                >{{ t.contact.message }}</label
                            >
                            <textarea
                                id="c-message"
                                v-model="contactForm.message"
                                name="message"
                                rows="5"
                                required
                                class="resize-y rounded-[10px] border border-[#B59A80] bg-white px-4 py-3 text-base"
                            />
                            <p
                                v-if="contactForm.errors.message"
                                class="text-sm text-[#8F1B13]"
                            >
                                {{ contactForm.errors.message }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <button
                                type="submit"
                                :disabled="contactForm.processing"
                                class="h-[52px] cursor-pointer rounded-[14px] bg-[#2B1D14] px-8 text-base font-extrabold text-white hover:bg-[#43302A] disabled:cursor-wait disabled:opacity-60"
                            >
                                {{
                                    contactForm.processing
                                        ? t.contact.sending
                                        : t.contact.send
                                }}
                            </button>
                            <p
                                v-if="contactForm.recentlySuccessful"
                                role="status"
                                class="text-[15px] font-semibold text-[#0B5D3B]"
                            >
                                {{ t.contact.sent }}
                            </p>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <footer class="bg-[#2B1D14] px-10 py-12 text-[#E0CDB9]">
            <div
                class="mx-auto flex max-w-[1120px] flex-wrap items-center justify-between gap-4 text-sm"
            >
                <div class="flex flex-wrap items-center gap-4">
                    <span
                        class="flex items-center gap-2.5 text-xl font-extrabold text-white"
                    >
                        <img
                            src="/assets/images/logo.webp"
                            alt=""
                            width="32"
                            height="32"
                            class="size-8 rounded-[22%]"
                        />
                        Abrio
                    </span>
                    <span>© 2026</span>
                </div>
                <nav class="flex flex-wrap items-center gap-x-5 gap-y-1">
                    <a href="#pricing" class="flex h-11 items-center">{{
                        t.nav.pricing
                    }}</a>
                    <a href="#faq" class="flex h-11 items-center">{{
                        t.nav.faq
                    }}</a>
                    <a href="#contact" class="flex h-11 items-center">{{
                        t.nav.contact
                    }}</a>
                    <Link href="/legal/terms" class="flex h-11 items-center">{{
                        t.legal.titles.terms
                    }}</Link>
                    <Link href="/legal/privacy" class="flex h-11 items-center">{{
                        t.legal.titles.privacy
                    }}</Link>
                    <Link
                        href="/legal/legal-notice"
                        class="flex h-11 items-center"
                        >{{ t.legal.titles['legal-notice'] }}</Link
                    >
                    <Link
                        :href="landingPath(otherLocale)"
                        :hreflang="otherLocale"
                        class="flex h-11 items-center"
                    >
                        {{ t.footer.switchText }}
                    </Link>
                </nav>
            </div>
        </footer>
    </div>
</template>
