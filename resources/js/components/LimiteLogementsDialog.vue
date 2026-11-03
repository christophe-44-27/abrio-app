<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';

defineProps<{ open: boolean }>();
const emit = defineEmits<{ dismiss: [] }>();

const { t } = useI18n();
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-[#2B1D14]/60 p-6 font-['Manrope',system-ui,sans-serif] text-[#2B1D14]"
    >
        <div
            role="dialog"
            aria-modal="true"
            :aria-label="t.limit.title"
            class="flex w-full max-w-[480px] flex-col gap-6 rounded-[20px] bg-white p-9"
        >
            <div class="flex flex-col gap-3">
                <span
                    class="self-start rounded-full bg-[#FDEBD9] px-3 py-1.5 text-[13px] font-extrabold text-[#8F3F08]"
                >
                    {{ t.limit.badge }}
                </span>
                <h2 class="text-[28px] font-extrabold tracking-[-0.02em]">
                    {{ t.limit.title }}
                </h2>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ t.limit.text }}
                </p>
            </div>

            <div class="flex items-baseline gap-2.5">
                <span class="text-5xl font-extrabold tracking-[-0.03em]">
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

            <div class="flex flex-col gap-2">
                <!-- Plain link: the payment page lives on stripe.com -->
                <a
                    href="/unlock"
                    class="flex h-14 items-center justify-center rounded-[14px] bg-[#F5A25D] text-[17px] font-extrabold text-[#2B1D14] hover:bg-[#F29443]"
                >
                    {{ t.limit.unlock }}
                </a>
                <button
                    type="button"
                    class="h-12 cursor-pointer text-[15px] font-semibold text-[#6B5647]"
                    @click="emit('dismiss')"
                >
                    {{ t.limit.later }}
                </button>
            </div>
        </div>
    </div>
</template>
