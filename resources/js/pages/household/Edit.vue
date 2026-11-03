<script setup lang="ts">
import type { VisitOptions } from '@inertiajs/core';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Plus } from '@lucide/vue';
import BrandLink from '@/components/BrandLink.vue';
import InputError from '@/components/InputError.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import ConflictNotice from '@/components/ConflictNotice.vue';
import SaveBar from '@/components/SaveBar.vue';
import StepNav from '@/components/StepNav.vue';
import { useManualSave } from '@/composables/useManualSave';
import { useI18n } from '@/composables/useI18n';
import { fill } from '@/lib/utils';
import {
    commonPart,
    commonSubtotal,
    commonTotal,
    formatMoney,
    formatPercent,
    personalSubtotal,
    personalTotal,
    remaining,
    shares,
    toAmount,
    totalIncome,
} from '@/lib/household';
import type { Household } from '@/lib/household';
import { newId } from '@/lib/id';

const props = defineProps<{
    household: Household;
    /** Bumped by every save, so a stale page cannot overwrite a teammate. */
    revision: number;
}>();

const { t, locale } = useI18n();

const MAX_MEMBERS = 8;

const form = useForm<Household>({
    members: props.household.members.map((m) => ({ ...m })),
    common_expenses: props.household.common_expenses.map((e) => ({ ...e })),
    personal_expenses: props.household.personal_expenses.map((e) => ({
        ...e,
        amounts: { ...e.amounts },
    })),
});

const money = (value: number) => formatMoney(value, locale.value);

const memberShares = computed(() => shares(form.members));
const commonSum = computed(() => commonTotal(form.common_expenses));
const personalSum = computed(() => personalTotal(form.personal_expenses));
const incomeSum = computed(() => totalIncome(form.members));
const left = computed(
    () => incomeSum.value - commonSum.value - personalSum.value,
);

const n = computed(() => form.members.length);
const commonCols = computed(
    () => `minmax(0,1.6fr) 110px repeat(${n.value}, 80px) 44px`,
);
const personalCols = computed(
    () => `minmax(0,1.6fr) repeat(${n.value}, 110px) 44px`,
);

const addMember = () => {
    const id = newId();
    form.members.push({ id, name: '', income: 0 });
    form.personal_expenses.forEach((e) => (e.amounts[id] = 0));
};

const removeMember = (id: string) => {
    form.members = form.members.filter((m) => m.id !== id);
    form.personal_expenses.forEach((e) => delete e.amounts[id]);
};

const addCommon = () =>
    form.common_expenses.push({ id: newId(), name: '', amount: 0 });
const removeCommon = (id: string) =>
    (form.common_expenses = form.common_expenses.filter((e) => e.id !== id));

const addPersonal = () =>
    form.personal_expenses.push({
        id: newId(),
        name: '',
        amounts: Object.fromEntries(form.members.map((m) => [m.id, 0])),
    });
const removePersonal = (id: string) =>
    (form.personal_expenses = form.personal_expenses.filter(
        (e) => e.id !== id,
    ));

const payload = (data: Household) => ({
    members: data.members.map((m) => ({
        ...m,
        income: toAmount(m.income),
    })),
    common_expenses: data.common_expenses.map((e) => ({
        ...e,
        amount: toAmount(e.amount),
    })),
    personal_expenses: data.personal_expenses.map((e) => ({
        ...e,
        amounts: Object.fromEntries(
            data.members.map((m) => [m.id, toAmount(e.amounts[m.id])]),
        ),
    })),
});

// Background saves stay on the page; "continue" saves and moves to the next step.
// The revision this page last saw: it only moves on after a successful save,
// so a refused save (a teammate saved first) keeps being refused until a reload.
const revision = ref(props.revision);

const send = (options: VisitOptions, next = false) =>
    form
        .transform((data) => ({
            ...payload(data),
            revision: revision.value,
            continue: next,
        }))
        .put('/menage', {
            ...options,
            onSuccess: (page) => {
                revision.value = props.revision;

                return options.onSuccess?.(page);
            },
        });

const complete = () =>
    form.members.every((m) => m.name.trim()) &&
    form.common_expenses.every((e) => e.name.trim()) &&
    form.personal_expenses.every((e) => e.name.trim());

const { status, savedAt, saveNow } = useManualSave({
    snapshot: () => payload(form.data()),
    ready: complete,
    send: (options) => send(options),
});

const submit = () => {
    send({}, true);
};

const field =
    'h-11 w-full rounded-[10px] border border-[#B59A80] bg-white px-3 text-[15px] text-[#2B1D14]';
const card = 'rounded-2xl border border-[#E8DACB] bg-white p-7';
const dashed =
    'flex h-11 cursor-pointer items-center gap-2 rounded-[10px] border border-dashed border-[#B59A80] px-4 text-sm font-semibold text-[#B4540F]';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.household.metaTitle" />
    <form
        class="mx-auto flex max-w-[1120px] flex-col gap-8"
        @submit.prevent="submit"
    >
        <div class="flex items-center justify-between gap-4 print:hidden">
            <BrandLink />
            <HeaderTools />
        </div>
        <StepNav :current="0" />

        <div class="flex flex-col gap-2">
            <h1 class="text-[40px] font-extrabold tracking-[-0.03em]">
                {{ t.household.title }}
            </h1>
            <p class="text-[17px] text-[#6B5647]">{{ t.household.text }}</p>
        </div>

        <div class="flex flex-wrap items-start gap-6">
            <div class="flex min-w-0 flex-[999_1_600px] flex-col gap-6">
                <!-- Incomes -->
                <section :class="[card, 'flex flex-col gap-4']">
                    <h2 class="text-lg font-bold">
                        {{ t.household.income.title }}
                    </h2>
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="(member, i) in form.members"
                            :key="member.id"
                            class="flex flex-wrap items-end gap-3"
                        >
                            <div class="flex flex-[1_1_160px] flex-col gap-1.5">
                                <label
                                    :for="`nom-${member.id}`"
                                    class="text-[13px] font-semibold text-[#6B5647]"
                                >
                                    {{ t.household.income.name }}
                                </label>
                                <input
                                    :id="`nom-${member.id}`"
                                    v-model="member.name"
                                    type="text"
                                    required
                                    maxlength="50"
                                    :class="[field, 'h-12']"
                                />
                                <InputError
                                    :message="form.errors[`members.${i}.name`]"
                                />
                            </div>
                            <div class="flex flex-[1_1_160px] flex-col gap-1.5">
                                <label
                                    :for="`rev-${member.id}`"
                                    class="text-[13px] font-semibold text-[#6B5647]"
                                >
                                    {{ t.household.income.amount }}
                                </label>
                                <input
                                    :id="`rev-${member.id}`"
                                    v-model.number="member.income"
                                    type="number"
                                    inputmode="decimal"
                                    min="0"
                                    step="0.01"
                                    required
                                    :class="[field, 'h-12']"
                                />
                                <InputError
                                    :message="
                                        form.errors[`members.${i}.income`]
                                    "
                                />
                            </div>
                            <div class="flex w-[110px] flex-col gap-1.5">
                                <span
                                    class="text-[13px] font-semibold text-[#6B5647]"
                                    >{{ t.household.income.share }}</span
                                >
                                <div
                                    class="flex h-12 items-center text-lg font-extrabold text-[#B4540F]"
                                >
                                    {{
                                        formatPercent(
                                            memberShares[member.id],
                                            locale,
                                        )
                                    }}
                                </div>
                            </div>
                            <button
                                v-if="form.members.length > 1"
                                type="button"
                                :aria-label="
                                    fill(t.household.income.removeLabel, {
                                        name: member.name,
                                    })
                                "
                                class="h-12 w-24 cursor-pointer text-sm font-semibold text-[#8F1B13]"
                                @click="removeMember(member.id)"
                            >
                                {{ t.household.income.remove }}
                            </button>
                            <div v-else class="w-24" />
                        </div>
                    </div>
                    <div v-if="form.members.length < MAX_MEMBERS">
                        <button
                            type="button"
                            :class="dashed"
                            @click="addMember"
                        >
                            <Plus class="size-4" aria-hidden="true" />
                            {{ t.household.income.add }}
                        </button>
                    </div>
                </section>

                <!-- Shared expenses -->
                <section :class="[card, 'flex flex-col gap-2']">
                    <h2 class="text-lg font-bold">
                        {{ t.household.common.title }}
                    </h2>
                    <p class="mb-2 text-sm text-[#6B5647]">
                        {{ t.household.common.text }}
                    </p>

                    <div class="overflow-x-auto">
                        <div
                            class="flex flex-col"
                            :style="{ minWidth: `${300 + n * 92}px` }"
                        >
                            <div
                                class="grid gap-3 border-b-2 border-[#2B1D14] py-2 text-[13px] font-bold text-[#6B5647]"
                                :style="{ gridTemplateColumns: commonCols }"
                            >
                                <span>{{ t.household.common.charge }}</span>
                                <span class="text-right">{{
                                    t.household.common.total
                                }}</span>
                                <span
                                    v-for="m in form.members"
                                    :key="m.id"
                                    class="truncate text-right"
                                    >{{ m.name }}</span
                                >
                                <span />
                            </div>

                            <div
                                v-for="(expense, i) in form.common_expenses"
                                :key="expense.id"
                                class="grid items-center gap-3 border-b border-[#F1E7DB] py-1.5"
                                :style="{ gridTemplateColumns: commonCols }"
                            >
                                <div>
                                    <input
                                        v-model="expense.name"
                                        type="text"
                                        required
                                        maxlength="80"
                                        :aria-label="
                                            t.household.common.nameLabel
                                        "
                                        :class="field"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `common_expenses.${i}.name`
                                            ]
                                        "
                                    />
                                </div>
                                <input
                                    v-model.number="expense.amount"
                                    type="number"
                                    inputmode="decimal"
                                    min="0"
                                    step="0.01"
                                    required
                                    :aria-label="
                                        fill(t.household.common.amountLabel, {
                                            name: expense.name,
                                        })
                                    "
                                    :class="[field, 'text-right']"
                                />
                                <span
                                    v-for="m in form.members"
                                    :key="m.id"
                                    class="text-right text-[15px]"
                                >
                                    {{
                                        money(
                                            commonPart(
                                                expense,
                                                m,
                                                form.members,
                                            ),
                                        )
                                    }}
                                </span>
                                <button
                                    type="button"
                                    :aria-label="
                                        fill(t.household.common.deleteLabel, {
                                            name: expense.name,
                                        })
                                    "
                                    class="flex size-11 cursor-pointer items-center justify-center text-[#6B5647]"
                                    @click="removeCommon(expense.id)"
                                >
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M6 6l12 12M18 6L6 18" />
                                    </svg>
                                </button>
                            </div>

                            <p
                                v-if="!form.common_expenses.length"
                                class="py-3 text-sm text-[#6B5647]"
                            >
                                {{ t.household.common.empty }}
                            </p>

                            <div
                                class="grid gap-3 pt-3 pb-1 text-[15px] font-extrabold"
                                :style="{ gridTemplateColumns: commonCols }"
                            >
                                <span>{{ t.household.common.subtotal }}</span>
                                <span class="text-right">{{
                                    money(commonSum)
                                }}</span>
                                <span
                                    v-for="m in form.members"
                                    :key="m.id"
                                    class="text-right"
                                >
                                    {{
                                        money(
                                            commonSubtotal(
                                                form.common_expenses,
                                                m,
                                                form.members,
                                            ),
                                        )
                                    }}
                                </span>
                                <span />
                            </div>

                            <div class="pt-3">
                                <button
                                    type="button"
                                    :class="dashed"
                                    @click="addCommon"
                                >
                                    <Plus class="size-4" aria-hidden="true" />
                                    {{ t.household.common.add }}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Personal expenses -->
                <section :class="[card, 'flex flex-col gap-2']">
                    <h2 class="text-lg font-bold">
                        {{ t.household.personal.title }}
                    </h2>
                    <p class="mb-2 text-sm text-[#6B5647]">
                        {{ t.household.personal.text }}
                    </p>

                    <div class="overflow-x-auto">
                        <div
                            class="flex flex-col"
                            :style="{ minWidth: `${260 + n * 122}px` }"
                        >
                            <div
                                class="grid gap-3 border-b-2 border-[#2B1D14] py-2 text-[13px] font-bold text-[#6B5647]"
                                :style="{ gridTemplateColumns: personalCols }"
                            >
                                <span>{{ t.household.personal.charge }}</span>
                                <span
                                    v-for="m in form.members"
                                    :key="m.id"
                                    class="truncate text-right"
                                    >{{ m.name }}</span
                                >
                                <span />
                            </div>

                            <div
                                v-for="(expense, i) in form.personal_expenses"
                                :key="expense.id"
                                class="grid items-center gap-3 border-b border-[#F1E7DB] py-1.5"
                                :style="{ gridTemplateColumns: personalCols }"
                            >
                                <div>
                                    <input
                                        v-model="expense.name"
                                        type="text"
                                        required
                                        maxlength="80"
                                        :aria-label="
                                            t.household.common.nameLabel
                                        "
                                        :class="field"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `personal_expenses.${i}.name`
                                            ]
                                        "
                                    />
                                </div>
                                <input
                                    v-for="m in form.members"
                                    :key="m.id"
                                    v-model.number="expense.amounts[m.id]"
                                    type="number"
                                    inputmode="decimal"
                                    min="0"
                                    step="0.01"
                                    :aria-label="
                                        fill(t.household.personal.amountLabel, {
                                            name: expense.name,
                                            person: m.name,
                                        })
                                    "
                                    :class="[field, 'text-right']"
                                />
                                <button
                                    type="button"
                                    :aria-label="
                                        fill(t.household.common.deleteLabel, {
                                            name: expense.name,
                                        })
                                    "
                                    class="flex size-11 cursor-pointer items-center justify-center text-[#6B5647]"
                                    @click="removePersonal(expense.id)"
                                >
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M6 6l12 12M18 6L6 18" />
                                    </svg>
                                </button>
                            </div>

                            <p
                                v-if="!form.personal_expenses.length"
                                class="py-3 text-sm text-[#6B5647]"
                            >
                                {{ t.household.personal.empty }}
                            </p>

                            <div
                                class="grid gap-3 pt-3 pb-1 text-[15px] font-extrabold"
                                :style="{ gridTemplateColumns: personalCols }"
                            >
                                <span>{{ t.household.personal.subtotal }}</span>
                                <span
                                    v-for="m in form.members"
                                    :key="m.id"
                                    class="text-right"
                                >
                                    {{
                                        money(
                                            personalSubtotal(
                                                form.personal_expenses,
                                                m,
                                            ),
                                        )
                                    }}
                                </span>
                                <span />
                            </div>

                            <div class="pt-3">
                                <button
                                    type="button"
                                    :class="dashed"
                                    @click="addPersonal"
                                >
                                    <Plus class="size-4" aria-hidden="true" />
                                    {{ t.household.personal.add }}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Total excluding housing -->
                <div
                    class="min-w-0 overflow-x-auto rounded-2xl border border-[#E8DACB] bg-white px-7 py-5"
                >
                    <div
                        class="grid gap-3 text-base font-extrabold"
                        :style="{
                            gridTemplateColumns: `minmax(0,1.6fr) repeat(${n}, 110px)`,
                            minWidth: `${180 + n * 122}px`,
                        }"
                    >
                        <span>{{ t.household.totalOutside }}</span>
                        <span
                            v-for="m in form.members"
                            :key="m.id"
                            class="text-right"
                        >
                            {{
                                money(
                                    commonSubtotal(
                                        form.common_expenses,
                                        m,
                                        form.members,
                                    ) +
                                        personalSubtotal(
                                            form.personal_expenses,
                                            m,
                                        ),
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <aside
                class="flex min-w-0 flex-[1_1_340px] flex-col gap-6 rounded-2xl bg-[#2B1D14] p-8 text-white lg:sticky lg:top-6"
            >
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-semibold text-[#E0CDB9]">{{
                        t.household.side.remaining
                    }}</span>
                    <span
                        class="text-[52px] leading-[1.1] font-extrabold tracking-[-0.03em]"
                        >{{ money(left) }}</span
                    >
                    <span class="text-sm text-[#E0CDB9]">{{
                        t.household.side.perMonth
                    }}</span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div
                        v-for="m in form.members"
                        :key="m.id"
                        class="min-w-[120px] flex-[1_1_120px] rounded-xl bg-[#43302A] px-4 py-3.5"
                    >
                        <div class="truncate text-[13px] text-[#E0CDB9]">
                            {{ m.name }}
                        </div>
                        <div class="text-[22px] font-extrabold">
                            {{ money(remaining(m, form)) }}
                        </div>
                    </div>
                </div>
                <div class="h-px bg-[#5A4638]" />
                <div class="flex flex-col gap-3 text-[15px]">
                    <div class="flex justify-between">
                        <span class="text-[#E0CDB9]">{{
                            t.household.side.income
                        }}</span>
                        <span class="font-bold">{{ money(incomeSum) }}</span>
                    </div>
                    <div
                        v-for="m in form.members"
                        :key="m.id"
                        class="flex justify-between pl-4 text-sm"
                    >
                        <span class="text-[#E0CDB9]"
                            >{{ m.name }} ({{
                                formatPercent(memberShares[m.id], locale)
                            }})</span
                        >
                        <span class="font-bold">{{
                            money(toAmount(m.income))
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#E0CDB9]">{{
                            t.household.side.common
                        }}</span>
                        <span class="font-bold">− {{ money(commonSum) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#E0CDB9]">{{
                            t.household.side.personal
                        }}</span>
                        <span class="font-bold"
                            >− {{ money(personalSum) }}</span
                        >
                    </div>
                </div>
            </aside>
        </div>

        <ConflictNotice :visible="'conflict' in form.errors" />

        <SaveBar
            :status="status"
            :saved-at="savedAt"
            :continue-label="t.household.side.cta"
            @save="saveNow"
        />
    </form>
</template>
