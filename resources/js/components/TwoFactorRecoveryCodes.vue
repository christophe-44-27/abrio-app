<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Eye, EyeOff, RefreshCw } from '@lucide/vue';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';
import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/composables/useI18n';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { settingsButtonOutline } from '@/lib/authStyles';
import { regenerateRecoveryCodes } from '@/routes/two-factor';

const { t } = useI18n();
const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <div
        class="flex w-full flex-col gap-4 rounded-xl border border-[#E8DACB] bg-[#FBF5EE] p-5"
    >
        <div class="flex flex-col gap-1">
            <h3 class="font-bold">{{ t.settings.twoFactor.recoveryTitle }}</h3>
            <p class="text-sm leading-normal text-[#6B5647]">
                {{ t.settings.twoFactor.recoveryText }}
            </p>
        </div>

        <div class="flex flex-col gap-3 select-none sm:flex-row">
            <Button
                :class="settingsButtonOutline"
                @click="toggleRecoveryCodesVisibility"
            >
                <component
                    :is="isRecoveryCodesVisible ? EyeOff : Eye"
                    class="size-4"
                />
                {{
                    isRecoveryCodesVisible
                        ? t.settings.twoFactor.hideCodes
                        : t.settings.twoFactor.viewCodes
                }}
            </Button>

            <Form
                v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                v-bind="regenerateRecoveryCodes.form()"
                method="post"
                :options="{ preserveScroll: true }"
                @success="fetchRecoveryCodes"
                #default="{ processing }"
            >
                <Button
                    type="submit"
                    :class="settingsButtonOutline"
                    :disabled="processing"
                >
                    <RefreshCw class="size-4" />
                    {{ t.settings.twoFactor.regenerate }}
                </Button>
            </Form>
        </div>

        <div v-if="isRecoveryCodesVisible">
            <div v-if="errors?.length">
                <AlertError
                    :errors="errors"
                    :title="t.settings.twoFactor.error"
                />
            </div>
            <div v-else class="flex flex-col gap-3">
                <div
                    ref="recoveryCodeSectionRef"
                    class="grid gap-1 rounded-lg border border-[#E8DACB] bg-white p-4 font-mono text-sm"
                >
                    <div v-if="!recoveryCodesList.length" class="space-y-2">
                        <div
                            v-for="n in 8"
                            :key="n"
                            class="h-4 animate-pulse rounded bg-[#E8DACB]"
                        ></div>
                    </div>
                    <div
                        v-else
                        v-for="(code, index) in recoveryCodesList"
                        :key="index"
                    >
                        {{ code }}
                    </div>
                </div>
                <p class="text-[13px] text-[#6B5647] select-none">
                    {{ t.settings.twoFactor.recoveryNote }}
                </p>
            </div>
        </div>
    </div>
</template>
