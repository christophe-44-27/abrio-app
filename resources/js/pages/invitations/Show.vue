<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BrandLink from '@/components/BrandLink.vue';
import HeaderTools from '@/components/HeaderTools.vue';
import { useI18n } from '@/composables/useI18n';
import { settingsButton } from '@/lib/authStyles';

const props = defineProps<{
    state: 'invalid' | 'confirm' | 'wrong_account' | 'unavailable';
    inviter?: string;
    email?: string;
    token?: string;
}>();

const { t } = useI18n();

const fill = (text: string, vars: Record<string, string | undefined>) =>
    Object.entries(vars).reduce(
        (s, [k, v]) => s.replace(`{${k}}`, v ?? ''),
        text,
    );

const accept = () => router.post(`/invitations/${props.token}`);

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head :title="t.invitation.metaTitle" />
    <div class="mx-auto flex max-w-[560px] flex-col gap-8 pt-16">
        <div class="flex items-center justify-between gap-4">
            <BrandLink />
            <HeaderTools />
        </div>

        <div
            class="flex flex-col gap-4 rounded-2xl border border-[#E8DACB] bg-white p-8"
        >
            <template v-if="state === 'confirm'">
                <h1 class="text-[28px] font-extrabold tracking-[-0.02em]">
                    {{ fill(t.invitation.title, { inviter }) }}
                </h1>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ t.invitation.text }}
                </p>
                <div>
                    <button
                        type="button"
                        :class="settingsButton"
                        data-test="invitation-accept"
                        @click="accept"
                    >
                        {{ t.invitation.accept }}
                    </button>
                </div>
            </template>
            <template v-else-if="state === 'wrong_account'">
                <h1 class="text-[28px] font-extrabold tracking-[-0.02em]">
                    {{ t.invitation.wrongTitle }}
                </h1>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ fill(t.invitation.wrongText, { email }) }}
                </p>
            </template>
            <template v-else-if="state === 'unavailable'">
                <h1 class="text-[28px] font-extrabold tracking-[-0.02em]">
                    {{ t.invitation.unavailableTitle }}
                </h1>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ t.invitation.unavailableText }}
                </p>
            </template>
            <template v-else>
                <h1 class="text-[28px] font-extrabold tracking-[-0.02em]">
                    {{ t.invitation.invalidTitle }}
                </h1>
                <p class="text-base leading-normal text-[#6B5647]">
                    {{ t.invitation.invalidText }}
                </p>
            </template>
        </div>

        <Link
            href="/dashboard"
            class="text-[15px] font-semibold text-[#B4540F] hover:text-[#8F3F08]"
        >
            {{ t.invitation.home }}
        </Link>
    </div>
</template>
