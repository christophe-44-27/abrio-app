<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps<{
    people: { id: string; name: string }[];
}>();

const { t } = useI18n();

const MAX_PEOPLE = 8;

const form = useForm<{ people: { id: string | null; name: string }[] }>({
    people: props.people.length
        ? props.people.map((p) => ({ ...p }))
        : t.value.onboarding.people.defaults.map((name) => ({
              id: null,
              name,
          })),
});

const addPerson = () => form.people.push({ id: null, name: '' });
const removePerson = (index: number) => form.people.splice(index, 1);
const fill = (text: string, vars: Record<string, string | number>) =>
    Object.entries(vars).reduce(
        (s, [k, v]) => s.replace(`{${k}}`, String(v)),
        text,
    );

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.onboarding.people.metaTitle" />
    <form
        class="mx-auto flex max-w-[640px] flex-col gap-10"
        @submit.prevent="form.post('/onboarding/menage')"
    >
        <div class="flex items-center justify-between gap-4">
            <BrandLink />
            <div class="flex items-center gap-4">
                <Link
                    href="/onboarding"
                    class="flex h-11 items-center gap-2 text-[15px] font-semibold text-[#B4540F] hover:text-[#8F3F08]"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" />
                    {{ t.onboarding.people.back }}
                </Link>
                <HeaderTools />
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <h1 class="text-[40px] font-extrabold tracking-[-0.03em]">
                {{ t.onboarding.people.title }}
            </h1>
            <p class="text-[17px] leading-normal text-[#6B5647]">
                {{ t.onboarding.people.text }}
            </p>
        </div>

        <div
            class="flex flex-col gap-4 rounded-2xl border border-[#E8DACB] bg-white p-7"
        >
            <div
                v-for="(person, i) in form.people"
                :key="i"
                class="flex items-end gap-3"
            >
                <div class="flex flex-1 flex-col gap-1.5">
                    <label
                        :for="`p${i + 1}`"
                        class="text-[13px] font-semibold text-[#6B5647]"
                    >
                        {{ fill(t.onboarding.people.label, { n: i + 1 }) }}
                    </label>
                    <input
                        :id="`p${i + 1}`"
                        v-model="person.name"
                        type="text"
                        required
                        maxlength="50"
                        class="h-12 rounded-[10px] border border-[#B59A80] bg-white px-4 text-base text-[#2B1D14]"
                    />
                    <InputError :message="form.errors[`people.${i}.name`]" />
                </div>
                <button
                    v-if="form.people.length > 1"
                    type="button"
                    :aria-label="
                        fill(t.onboarding.people.removeLabel, { n: i + 1 })
                    "
                    class="h-12 w-24 cursor-pointer text-sm font-semibold text-[#8F1B13]"
                    @click="removePerson(i)"
                >
                    {{ t.onboarding.people.remove }}
                </button>
                <div v-else class="w-24" />
            </div>

            <div v-if="form.people.length < MAX_PEOPLE">
                <button
                    type="button"
                    class="flex h-11 cursor-pointer items-center gap-2 rounded-[10px] border border-dashed border-[#B59A80] px-4 text-sm font-semibold text-[#B4540F]"
                    @click="addPerson"
                >
                    <Plus class="size-4" aria-hidden="true" />
                    {{ t.onboarding.people.add }}
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="flex h-14 cursor-pointer items-center justify-center rounded-[14px] bg-[#2B1D14] text-[17px] font-extrabold text-white hover:bg-[#43302A] disabled:opacity-60"
            >
                {{ t.onboarding.people.cta }}
            </button>
            <span class="text-center text-sm text-[#6B5647]">
                {{ t.onboarding.people.note }}
            </span>
        </div>
    </form>
</template>
