<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import { onUnmounted, ref } from 'vue';
import SettingsSection from '@/components/SettingsSection.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Button } from '@/components/ui/button';
import { useI18n } from '@/composables/useI18n';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { settingsButton, settingsButtonDanger } from '@/lib/authStyles';
import { disable, enable } from '@/routes/two-factor';

export type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    canManageTwoFactor: false,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { t } = useI18n();
const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => clearTwoFactorAuthData());
</script>

<template>
    <SettingsSection
        v-if="canManageTwoFactor"
        :title="t.settings.twoFactor.title"
        :description="t.settings.twoFactor.description"
    >
        <div v-if="!twoFactorEnabled" class="flex flex-col items-start gap-4">
            <p class="text-sm leading-normal text-[#6B5647]">
                {{ t.settings.twoFactor.offText }}
            </p>

            <Button
                v-if="hasSetupData"
                :class="settingsButton"
                @click="showSetupModal = true"
            >
                <ShieldCheck />{{ t.settings.twoFactor.continueSetup }}
            </Button>
            <Form
                v-else
                v-bind="enable.form()"
                @success="showSetupModal = true"
                #default="{ processing }"
            >
                <Button
                    type="submit"
                    :class="settingsButton"
                    :disabled="processing"
                >
                    {{ t.settings.twoFactor.enable }}
                </Button>
            </Form>
        </div>

        <div v-else class="flex flex-col items-start gap-4">
            <p class="text-sm leading-normal text-[#6B5647]">
                {{ t.settings.twoFactor.onText }}
            </p>

            <Form v-bind="disable.form()" #default="{ processing }">
                <Button
                    type="submit"
                    :class="settingsButtonDanger"
                    :disabled="processing"
                >
                    {{ t.settings.twoFactor.disable }}
                </Button>
            </Form>

            <TwoFactorRecoveryCodes />
        </div>

        <TwoFactorSetupModal
            v-model:isOpen="showSetupModal"
            :requiresConfirmation="requiresConfirmation"
            :twoFactorEnabled="twoFactorEnabled"
        />
    </SettingsSection>
</template>
