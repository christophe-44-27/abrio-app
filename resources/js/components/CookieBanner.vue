<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const STORAGE_KEY = 'abrio_cookie_notice';

const { t } = useI18n();

const visible = ref(false);

// Abrio only sets essential cookies, so this is an information notice: the
// choice is remembered in the browser to avoid showing it again.
onMounted(() => {
    try {
        visible.value = localStorage.getItem(STORAGE_KEY) !== '1';
    } catch {
        visible.value = true;
    }
});

const dismiss = () => {
    visible.value = false;

    try {
        localStorage.setItem(STORAGE_KEY, '1');
    } catch {
        // Storage unavailable: the notice simply comes back next visit.
    }
};
</script>

<template>
    <div
        v-if="visible"
        role="region"
        :aria-label="t.cookies.title"
        class="fixed inset-x-4 bottom-4 z-50 mx-auto flex max-w-[640px] flex-col gap-4 rounded-2xl border border-[#B59A80] bg-white p-5 font-['Manrope',system-ui,sans-serif] text-[#2B1D14] shadow-lg sm:flex-row sm:items-center"
    >
        <p class="flex-1 text-sm leading-relaxed">
            {{ t.cookies.text }}
            <Link
                href="/legal/privacy"
                class="font-semibold text-[#B4540F] hover:text-[#8F3F08]"
            >
                {{ t.cookies.more }}
            </Link>
        </p>
        <button
            type="button"
            class="h-11 shrink-0 cursor-pointer rounded-xl bg-[#2B1D14] px-5 text-[15px] font-extrabold text-white hover:bg-[#43302A]"
            @click="dismiss"
        >
            {{ t.cookies.ok }}
        </button>
    </div>
</template>
