<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useI18n } from '@/composables/useI18n';
import AuthCard from '@/layouts/auth/AuthSimpleLayout.vue';
import BlankLayout from '@/layouts/BlankLayout.vue';
import { authButton, authInput, authLabel, authLink } from '@/lib/authStyles';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({ layout: BlankLayout, inheritAttrs: false });

defineProps<{
    status?: string;
}>();

const { t } = useI18n();
</script>

<template>
    <Head :title="t.auth.forgot.metaTitle" />

    <AuthCard
        :title="t.auth.forgot.title"
        :description="t.auth.forgot.description"
    >
        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-[#0B5D3B]"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form
                v-bind="email.form()"
                v-slot="{ errors, processing }"
                class="grid gap-6"
            >
                <div class="grid gap-2">
                    <Label for="email" :class="authLabel">{{
                        t.auth.forgot.email
                    }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-focus
                        :class="authInput"
                    />
                    <InputError :message="errors.email" />
                </div>

                <Button
                    :class="authButton"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.forgot.submit }}
                </Button>
            </Form>

            <div class="space-x-1 text-center text-sm text-[#6B5647]">
                <span>{{ t.auth.forgot.back }}</span>
                <TextLink :href="login()" :class="authLink">{{
                    t.auth.forgot.backLink
                }}</TextLink>
            </div>
        </div>
    </AuthCard>
</template>
