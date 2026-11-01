<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { authInput, authLabel, settingsButton } from '@/lib/authStyles';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({ layout: SettingsLayout, inheritAttrs: false });

const { t } = useI18n();
</script>

<template>
    <Head :title="t.settings.password.metaTitle" />

    <SettingsSection
        :title="t.settings.password.title"
        :description="t.settings.password.description"
    >
        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="flex flex-col gap-5"
            v-slot="{ errors, processing, recentlySuccessful }"
        >
            <div class="grid gap-2">
                <Label for="current_password" :class="authLabel">{{
                    t.settings.password.current
                }}</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    :class="authInput"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password" :class="authLabel">{{
                    t.settings.password.next
                }}</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                    :class="authInput"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" :class="authLabel">{{
                    t.settings.password.confirm
                }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                    :class="authInput"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :class="settingsButton"
                    :disabled="processing"
                    data-test="update-password-button"
                >
                    {{ t.settings.password.save }}
                </Button>
                <span
                    v-if="recentlySuccessful"
                    class="text-sm font-semibold text-[#2F6B3F]"
                >
                    {{ t.settings.password.saved }}
                </span>
            </div>
        </Form>
    </SettingsSection>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />
</template>
