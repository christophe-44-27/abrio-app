<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';

defineProps<{ visible: boolean }>();

const { t } = useI18n();

// A full reload: it drops the local edits that would otherwise clash again.
const reload = () => window.location.reload();
</script>

<template>
    <div
        v-if="visible"
        role="alert"
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-[#FBE0DE] px-5 py-4 text-[15px] font-semibold text-[#8F1B13]"
    >
        <span>{{ t.conflict.text }}</span>
        <button
            type="button"
            class="h-10 cursor-pointer rounded-[10px] bg-[#8F1B13] px-4 text-sm font-extrabold text-white hover:bg-[#6F140E]"
            @click="reload"
        >
            {{ t.conflict.reload }}
        </button>
    </div>
</template>
