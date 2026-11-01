<script setup lang="ts">
import type { VisitOptions } from '@inertiajs/core';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import BrandLink from '@/components/BrandLink.vue';
import InputError from '@/components/InputError.vue';
import LimiteLogementsDialog from '@/components/LimiteLogementsDialog.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import SaveBar from '@/components/SaveBar.vue';
import StepNav from '@/components/StepNav.vue';
import { useManualSave } from '@/composables/useManualSave';
import { useI18n } from '@/composables/useI18n';
import {
    formatMoney,
    formatPercent,
    housingMonthly,
    shares,
    toAmount,
} from '@/lib/household';
import type { Household, Housing } from '@/lib/household';
import { newId } from '@/lib/id';

const props = defineProps<{
    household: Household;
    housings: Housing[];
    /** null when unlimited */
    limit: number | null;
}>();

const { t, locale } = useI18n();

const form = useForm<{ housings: Housing[] }>({
    housings: props.housings.map((h) => ({ ...h })),
});

const selectedId = ref(form.housings[0]?.id ?? null);
const selectedIndex = computed(() =>
    form.housings.findIndex((h) => h.id === selectedId.value),
);
const selected = computed(() => form.housings[selectedIndex.value] ?? null);

const showLimit = ref(false);
const atLimit = computed(
    () => props.limit !== null && form.housings.length >= props.limit,
);

const money = (value: number) => formatMoney(value, locale.value);
const fill = (text: string, vars: Record<string, string | number>) =>
    Object.entries(vars).reduce(
        (s, [k, v]) => s.replace(`{${k}}`, String(v)),
        text,
    );

const memberShares = computed(() => shares(props.household.members));

const addHousing = () => {
    if (atLimit.value) {
        showLimit.value = true;

        return;
    }

    const housing: Housing = {
        id: newId(),
        name: '',
        rent: 0,
        parking: 0,
        heating: 'paid',
        heating_amount: 0,
        one_time: 0,
    };
    form.housings.push(housing);
    selectedId.value = housing.id;
};

const removeSelected = () => {
    const index = selectedIndex.value;
    form.housings.splice(index, 1);
    selectedId.value =
        form.housings[Math.min(index, form.housings.length - 1)]?.id ?? null;
};

const payload = (data: { housings: Housing[] }) => ({
    housings: data.housings.map((h) => ({
        ...h,
        rent: toAmount(h.rent),
        parking: toAmount(h.parking),
        heating_amount: h.heating === 'paid' ? toAmount(h.heating_amount) : 0,
        one_time: toAmount(h.one_time),
    })),
});

// Background saves stay on the page; "continue" saves and moves to the verdicts.
const send = (options: VisitOptions, next = false) =>
    form
        .transform((data) => ({ ...payload(data), continue: next }))
        .put('/logements', options);

const { status, savedAt, saveNow } = useManualSave({
    snapshot: () => payload(form.data()),
    ready: () => form.housings.every((h) => h.name.trim()),
    send: (options) => send(options),
});

const submit = () => {
    send({}, true);
};

const input =
    'h-12 w-full rounded-[10px] border border-[#B59A80] bg-white px-4 text-base text-[#2B1D14]';
const label = 'text-sm font-semibold text-[#6B5647]';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.housings.metaTitle" />
    <form
        class="mx-auto flex max-w-[1120px] flex-col gap-8"
        @submit.prevent="submit"
    >
        <div class="flex items-center justify-between gap-4 print:hidden">
            <BrandLink />
            <HeaderTools />
        </div>
        <StepNav :current="1" />

        <div class="flex flex-col gap-2">
            <h1 class="text-[40px] font-extrabold tracking-[-0.03em]">
                {{ t.housings.title }}
            </h1>
            <p class="text-[17px] text-[#6B5647]">{{ t.housings.text }}</p>
        </div>

        <div class="flex flex-wrap items-start gap-6">
            <!-- List -->
            <div class="flex min-w-0 flex-[1_1_300px] flex-col gap-3">
                <button
                    v-for="housing in form.housings"
                    :key="housing.id"
                    type="button"
                    :aria-pressed="housing.id === selectedId"
                    class="flex min-h-16 cursor-pointer items-center justify-between gap-3 rounded-xl px-4 py-3 text-left"
                    :class="
                        housing.id === selectedId
                            ? 'border-2 border-[#B4540F] bg-[#FDEBD9]'
                            : 'border border-[#E8DACB] bg-white'
                    "
                    @click="selectedId = housing.id"
                >
                    <span class="flex min-w-0 flex-col">
                        <span class="truncate text-base font-bold">{{
                            housing.name || t.housings.newName
                        }}</span>
                        <span class="text-[13px] text-[#6B5647]">
                            {{
                                fill(t.housings.rentLine, {
                                    amount: money(toAmount(housing.rent)),
                                })
                            }}
                        </span>
                    </span>
                    <span class="text-base font-bold">{{
                        money(housingMonthly(housing))
                    }}</span>
                </button>

                <p v-if="!form.housings.length" class="text-sm text-[#6B5647]">
                    {{ t.housings.empty }}
                </p>

                <button
                    type="button"
                    class="h-12 cursor-pointer rounded-xl border border-dashed border-[#B59A80] text-[15px] font-semibold"
                    :class="atLimit ? 'text-[#6B5647]' : 'text-[#B4540F]'"
                    @click="addHousing"
                >
                    {{
                        atLimit
                            ? fill(t.housings.addNth, {
                                  n: form.housings.length + 1,
                              })
                            : t.housings.add
                    }}
                </button>
            </div>

            <!-- Editor -->
            <div
                v-if="selected"
                :key="selected.id"
                class="flex min-w-0 flex-[999_1_560px] flex-col gap-7 rounded-2xl border border-[#E8DACB] bg-white p-8"
            >
                <div class="flex flex-col gap-1.5">
                    <label for="nom" :class="label">{{
                        t.housings.name
                    }}</label>
                    <input
                        id="nom"
                        v-model="selected.name"
                        type="text"
                        required
                        maxlength="80"
                        :class="input"
                    />
                    <InputError
                        :message="form.errors[`housings.${selectedIndex}.name`]"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label for="loyer" :class="label">{{
                            t.housings.rent
                        }}</label>
                        <input
                            id="loyer"
                            v-model.number="selected.rent"
                            type="number"
                            inputmode="decimal"
                            min="0"
                            step="0.01"
                            :class="input"
                        />
                        <InputError
                            :message="
                                form.errors[`housings.${selectedIndex}.rent`]
                            "
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="stat" :class="label">{{
                            t.housings.parking
                        }}</label>
                        <input
                            id="stat"
                            v-model.number="selected.parking"
                            type="number"
                            inputmode="decimal"
                            min="0"
                            step="0.01"
                            :class="input"
                        />
                    </div>
                </div>

                <fieldset class="flex flex-col gap-3">
                    <legend :class="[label, 'mb-1']">
                        {{ t.housings.heating }}
                    </legend>
                    <div class="flex flex-wrap items-center gap-4">
                        <label
                            class="flex min-h-11 items-center gap-2 text-[15px]"
                        >
                            <input
                                v-model="selected.heating"
                                type="radio"
                                value="included"
                                class="accent-[#B4540F]"
                            />
                            {{ t.housings.heatingIncluded }}
                        </label>
                        <label
                            class="flex min-h-11 items-center gap-2 text-[15px]"
                        >
                            <input
                                v-model="selected.heating"
                                type="radio"
                                value="paid"
                                class="accent-[#B4540F]"
                            />
                            {{ t.housings.heatingPaid }}
                        </label>
                        <template v-if="selected.heating === 'paid'">
                            <label for="chaufmt" class="sr-only">{{
                                t.housings.heatingAmount
                            }}</label>
                            <input
                                id="chaufmt"
                                v-model.number="selected.heating_amount"
                                type="number"
                                inputmode="decimal"
                                min="0"
                                step="0.01"
                                :class="[input, 'w-36']"
                            />
                        </template>
                    </div>
                    <span class="text-[13px] text-[#6B5647]">{{
                        t.housings.heatingHint
                    }}</span>
                </fieldset>

                <div class="flex flex-col gap-1.5">
                    <label for="ponct" :class="label">{{
                        t.housings.oneTime
                    }}</label>
                    <input
                        id="ponct"
                        v-model.number="selected.one_time"
                        type="number"
                        inputmode="decimal"
                        min="0"
                        step="0.01"
                        :class="[input, 'w-52']"
                    />
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-[#2B1D14] px-6 py-5 text-white"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-sm font-semibold text-[#E0CDB9]">{{
                            t.housings.monthlyCost
                        }}</span>
                        <span
                            class="text-[32px] font-extrabold tracking-[-0.02em]"
                            >{{ money(housingMonthly(selected)) }}</span
                        >
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="m in household.members"
                            :key="m.id"
                            class="rounded-[10px] bg-[#43302A] px-4 py-3"
                        >
                            <div class="text-[13px] text-[#E0CDB9]">
                                {{ m.name }} ({{
                                    formatPercent(memberShares[m.id], locale)
                                }})
                            </div>
                            <div class="text-xl font-extrabold">
                                {{
                                    money(
                                        housingMonthly(selected) *
                                            memberShares[m.id],
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                        type="button"
                        class="h-11 cursor-pointer text-sm font-semibold text-[#8F1B13]"
                        @click="removeSelected"
                    >
                        {{ t.housings.remove }}
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <Link
                href="/menage"
                class="flex h-12 items-center px-2 text-[15px] font-semibold text-[#B4540F] hover:text-[#8F3F08]"
            >
                {{ t.housings.back }}
            </Link>
            <span v-if="!form.housings.length" class="text-sm text-[#6B5647]">{{
                t.housings.needOne
            }}</span>
        </div>

        <SaveBar
            :status="status"
            :saved-at="savedAt"
            :busy="!form.housings.length"
            :continue-label="t.housings.next"
            @save="saveNow"
        />

        <LimiteLogementsDialog
            :open="showLimit"
            reason="housings"
            @dismiss="showLimit = false"
        />
    </form>
</template>
