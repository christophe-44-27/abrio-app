<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useI18n } from '@/composables/useI18n';
import AuthCard from '@/layouts/auth/AuthSimpleLayout.vue';
import BlankLayout from '@/layouts/BlankLayout.vue';
import { authButton, authInput, authLabel, authLink } from '@/lib/authStyles';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import PasskeyVerify from '@/components/PasskeyVerify.vue';

defineOptions({ layout: BlankLayout, inheritAttrs: false });

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t.auth.login.metaTitle" />

    <AuthCard
        :title="t.auth.login.title"
        :description="t.auth.login.description"
    >
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-[#0B5D3B]"
        >
            {{ status }}
        </div>

        <PasskeyVerify />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email" :class="authLabel">{{
                        t.auth.login.email
                    }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        v-focus
                        :tabindex="1"
                        autocomplete="email"
                        :class="authInput"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" :class="authLabel">{{
                            t.auth.login.password
                        }}</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            :class="['text-sm', authLink]"
                            :tabindex="5"
                        >
                            {{ t.auth.login.forgot }}
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        :class="authInput"
                    />
                    <InputError :message="errors.password" />
                </div>

                <Label
                    for="remember"
                    class="flex items-center space-x-3 text-sm"
                >
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>{{ t.auth.login.remember }}</span>
                </Label>

                <Button
                    type="submit"
                    :class="authButton"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.login.submit }}
                </Button>
            </div>

            <div class="text-center text-sm text-[#6B5647]">
                {{ t.auth.login.noAccount }}
                <TextLink :href="register()" :class="authLink" :tabindex="5">
                    {{ t.auth.login.signUp }}
                </TextLink>
            </div>
        </Form>
    </AuthCard>
</template>
