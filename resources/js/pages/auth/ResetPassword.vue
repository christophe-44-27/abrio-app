<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useI18n } from '@/composables/useI18n';
import AuthCard from '@/layouts/auth/AuthSimpleLayout.vue';
import BlankLayout from '@/layouts/BlankLayout.vue';
import { authButton, authInput, authLabel } from '@/lib/authStyles';
import { update } from '@/routes/password';

defineOptions({ layout: BlankLayout, inheritAttrs: false });

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t.auth.reset.metaTitle" />

    <AuthCard
        :title="t.auth.reset.title"
        :description="t.auth.reset.description"
    >
        <Form
            v-bind="update.form()"
            :transform="
                (data) => ({ ...data, token: props.token, email: props.email })
            "
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email" :class="authLabel">{{
                        t.auth.reset.email
                    }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        :model-value="email"
                        readonly
                        :class="[authInput, 'bg-[#FBF5EE]']"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" :class="authLabel">{{
                        t.auth.reset.password
                    }}</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        autofocus
                        :passwordrules="passwordRules"
                        :class="authInput"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" :class="authLabel">
                        {{ t.auth.reset.passwordConfirm }}
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        :passwordrules="passwordRules"
                        :class="authInput"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    :class="authButton"
                    :disabled="processing"
                    data-test="reset-password-button"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.reset.submit }}
                </Button>
            </div>
        </Form>
    </AuthCard>
</template>
