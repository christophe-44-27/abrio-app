<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';
import type { SaveStatus } from '@/composables/useManualSave';

const props = defineProps<{
    status: SaveStatus;
    savedAt: Date | null;
    /** Disables the continue button (e.g. while the final save runs). */
    busy?: boolean;
    continueLabel: string;
}>();

const emit = defineEmits<{ save: [] }>();

const { t, locale } = useI18n();

const time = computed(() =>
    props.savedAt
        ? props.savedAt.toLocaleTimeString(
              locale.value === 'fr' ? 'fr-CA' : 'en-CA',
              {
                  hour: '2-digit',
                  minute: '2-digit',
              },
          )
        : null,
);

const tone = computed(
    () =>
        ({
            saved: 'text-[#0B5D3B]',
            saving: 'text-[#6B5647]',
            dirty: 'text-[#8F3F08]',
            incomplete: 'text-[#8F3F08]',
            error: 'text-[#8F1B13]',
        })[props.status],
);

const label = computed(() => {
    const s = t.value.save;

    if (props.status === 'saved') {
        return time.value ? `${s.saved} · ${time.value}` : s.upToDate;
    }

    return s[props.status];
});
</script>

<template>
    <div
        class="sticky bottom-0 z-10 -mx-10 mt-2 border-t border-[#E8DACB] bg-[#FBF5EE]/95 px-10 py-4 backdrop-blur print:hidden"
    >
        <div
            class="mx-auto flex max-w-[1120px] flex-wrap items-center justify-between gap-4"
        >
            <p
                class="flex items-center gap-2 text-sm font-semibold"
                :class="tone"
                role="status"
                aria-live="polite"
            >
                <svg
                    v-if="status === 'saved'"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 12l5 5 9-10" />
                </svg>
                <span
                    v-else-if="status === 'saving'"
                    class="size-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"
                    aria-hidden="true"
                />
                <span
                    v-else
                    class="size-2.5 rounded-full bg-current"
                    aria-hidden="true"
                />
                {{ label }}
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    class="h-12 cursor-pointer rounded-xl border-2 border-[#2B1D14] bg-white px-5 text-[15px] font-extrabold disabled:cursor-default disabled:opacity-50"
                    :disabled="status === 'saved' || status === 'saving'"
                    @click="emit('save')"
                >
                    {{ t.save.now }}
                </button>
                <button
                    type="submit"
                    class="h-12 cursor-pointer rounded-xl bg-[#2B1D14] px-6 text-[15px] font-extrabold text-white hover:bg-[#43302A] disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="busy"
                >
                    {{ continueLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
