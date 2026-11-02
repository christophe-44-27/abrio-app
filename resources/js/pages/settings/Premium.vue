<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { useI18n } from '@/composables/useI18n';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { settingsButton, settingsButtonOutline } from '@/lib/authStyles';

defineOptions({ layout: SettingsLayout, inheritAttrs: false });

const props = defineProps<{
    unlocked: boolean;
    unlockedAt: string | null;
    hasInvoice: boolean;
    housingLimit: number | null;
}>();

const { t, locale } = useI18n();

const since = computed(() =>
    props.unlockedAt
        ? new Intl.DateTimeFormat(locale.value === 'en' ? 'en-CA' : 'fr-CA', {
              dateStyle: 'long',
          }).format(new Date(props.unlockedAt))
        : null,
);
</script>

<template>
    <Head :title="t.settings.premium.metaTitle" />

    <SettingsSection
        :title="t.settings.premium.title"
        :description="
            unlocked
                ? t.settings.premium.descriptionUnlocked
                : t.settings.premium.description
        "
    >
        <div class="flex flex-col gap-5">
            <div class="flex items-baseline gap-2.5">
                <span class="text-4xl font-extrabold tracking-[-0.03em]">
                    {{ t.limit.price }}
                </span>
                <span class="text-[15px] text-[#6B5647]">
                    {{ t.limit.priceNote }}
                </span>
            </div>

            <ul class="flex flex-col gap-3 text-base">
                <li
                    v-for="perk in t.limit.perks"
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

            <p
                v-if="unlocked"
                class="text-sm font-semibold text-[#2F6B3F]"
                role="status"
            >
                {{ t.settings.premium.active }}
                <template v-if="since">
                    {{ t.settings.premium.since.replace('{date}', since) }}
                </template>
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Plain links: the payment and invoice pages live on stripe.com -->
                <a
                    v-if="!unlocked"
                    href="/unlock"
                    :class="[
                        settingsButton,
                        'inline-flex items-center justify-center',
                    ]"
                    data-test="premium-subscribe"
                >
                    {{ t.limit.unlock }}
                </a>
                <a
                    v-if="hasInvoice"
                    href="/settings/premium/invoice"
                    target="_blank"
                    rel="noopener"
                    :class="[
                        settingsButtonOutline,
                        'inline-flex items-center justify-center',
                    ]"
                    data-test="premium-invoice"
                >
                    {{ t.settings.premium.invoice }}
                </a>
            </div>

            <p v-if="!unlocked" class="text-sm text-[#6B5647]">
                {{ t.settings.premium.free.replace('{n}', String(housingLimit)) }}
            </p>
        </div>
    </SettingsSection>
</template>
