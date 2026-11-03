<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from '@lucide/vue';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
import StepNav from '@/components/StepNav.vue';
import { useI18n } from '@/composables/useI18n';
import { fill } from '@/lib/utils';
import {
    HOUSING_LIMIT,
    formatMoney,
    formatPercent,
    rankedVerdicts,
} from '@/lib/household';
import type { Household, Housing } from '@/lib/household';

const props = defineProps<{
    household: Household;
    housings: Housing[];
    /** Public read-only view, reached through the owner's secret link. */
    shared?: boolean;
}>();

const { t, locale } = useI18n();

const verdicts = computed(() =>
    rankedVerdicts(props.housings, props.household),
);

const money = (value: number) => formatMoney(value, locale.value);
const pct = (value: number) =>
    Number.isFinite(value) ? formatPercent(value, locale.value) : '—';
/** The bar spans 0 to 40% of income, so the 30% limit sits at 75%. */
const SCALE = 0.4;
const barWidth = (ratio: number) =>
    `${Math.min(100, ((Number.isFinite(ratio) ? ratio : 1) / SCALE) * 100)}%`;
const limitLeft = `${(HOUSING_LIMIT / SCALE) * 100}%`;

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.verdicts.metaTitle">
        <meta v-if="shared" name="robots" content="noindex, nofollow" />
    </Head>
    <div class="mx-auto flex max-w-[1120px] flex-col gap-8">
        <div class="flex items-center justify-between gap-4 print:hidden">
            <BrandLink />
            <HeaderTools v-if="!shared" />
            <LocaleSwitch v-else />
        </div>
        <StepNav v-if="!shared" :current="2" />

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-2">
                <h1 class="text-[40px] font-extrabold tracking-[-0.03em]">
                    {{ t.verdicts.title }}
                </h1>
                <p class="text-[17px] text-[#6B5647]">
                    {{
                        verdicts.length === 1
                            ? t.verdicts.textOne
                            : fill(t.verdicts.text, { count: verdicts.length })
                    }}
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-[#6B5647]">
                <span class="inline-block h-[18px] w-[3px] bg-[#2B1D14]" />
                {{ t.verdicts.limitLegend }}
            </div>
        </div>

        <ol class="flex flex-col gap-4">
            <li
                v-for="(v, i) in verdicts"
                :key="v.housing.id"
                class="flex break-inside-avoid flex-col gap-4 rounded-2xl bg-white p-6"
                :class="
                    i === 0 && v.pass
                        ? 'border-2 border-[#1F8A5B]'
                        : 'border border-[#E8DACB]'
                "
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="flex size-9 items-center justify-center rounded-full text-base font-extrabold"
                            :class="
                                i === 0
                                    ? 'bg-[#2B1D14] text-white'
                                    : 'bg-[#F0E4D6]'
                            "
                        >
                            {{ i + 1 }}
                        </span>
                        <div class="flex flex-col">
                            <span class="text-xl font-extrabold">{{
                                v.housing.name
                            }}</span>
                            <span class="text-[13px] text-[#6B5647]">
                                {{
                                    fill(t.verdicts.monthlyLine, {
                                        amount: money(v.monthly),
                                    })
                                }}
                            </span>
                        </div>
                        <span
                            class="rounded-full px-3 py-1.5 text-sm font-extrabold"
                            :class="
                                v.pass
                                    ? 'bg-[#DFF3E8] text-[#0B5D3B]'
                                    : 'bg-[#FBE0DE] text-[#8F1B13]'
                            "
                        >
                            {{ v.pass ? t.verdicts.go : t.verdicts.no }}
                        </span>
                        <span
                            v-if="i === 0 && v.pass"
                            class="text-[13px] font-bold text-[#0B5D3B]"
                        >
                            {{ t.verdicts.best }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-3">
                        <span class="text-[28px] font-extrabold">{{
                            pct(v.ratio)
                        }}</span>
                        <span class="text-sm text-[#6B5647]">{{
                            t.verdicts.ofIncome
                        }}</span>
                    </div>
                </div>

                <div
                    class="relative h-2.5 rounded-full bg-[#F0E4D6]"
                    aria-hidden="true"
                >
                    <div
                        class="h-2.5 rounded-full"
                        :class="v.pass ? 'bg-[#1F8A5B]' : 'bg-[#B3261E]'"
                        :style="{ width: barWidth(v.ratio) }"
                    />
                    <div
                        class="absolute -top-1 h-[18px] w-[3px] bg-[#2B1D14]"
                        :style="{ left: limitLeft }"
                    />
                </div>

                <div class="flex flex-wrap gap-3">
                    <div
                        v-for="p in v.parts"
                        :key="p.member.id"
                        class="flex-[1_1_260px] rounded-[10px] bg-[#FBF5EE] px-4 py-3 text-sm"
                    >
                        <div class="mb-0.5 font-bold">
                            {{ p.member.name }} · {{ pct(p.share) }}
                        </div>
                        {{
                            fill(t.verdicts.partLine, {
                                part: money(p.part),
                                pct: pct(p.ownRatio),
                                left: money(p.left),
                            })
                        }}
                    </div>
                </div>

                <div class="flex flex-wrap justify-between gap-3 text-[15px]">
                    <span class="flex-[1_1_360px]">
                        {{
                            fill(t.verdicts.tiers[v.tier], {
                                amount: money(Math.abs(v.margin)),
                            })
                        }}
                    </span>
                    <span
                        class="font-bold"
                        :class="v.pass ? 'text-[#0B5D3B]' : 'text-[#8F1B13]'"
                    >
                        {{
                            fill(
                                v.pass
                                    ? t.verdicts.underLimit
                                    : t.verdicts.overLimit,
                                {
                                    amount: money(Math.abs(v.margin)),
                                },
                            )
                        }}
                    </span>
                </div>
            </li>
        </ol>

        <Link
            v-if="!shared"
            href="/logements"
            class="flex h-12 items-center gap-2 self-start px-2 text-[15px] font-semibold text-[#B4540F] hover:text-[#8F3F08] print:hidden"
        >
            <ArrowLeft class="size-4" aria-hidden="true" />
            {{ t.verdicts.edit }}
        </Link>
    </div>
</template>
