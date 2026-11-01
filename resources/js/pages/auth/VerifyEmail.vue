<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({ layout: BlankLayout, inheritAttrs: false });

defineProps<{
    status?: string;
}>();

const { t } = useI18n();
const page = usePage();
const description = computed(() =>
    t.value.auth.verify.description.replace(
        '{email}',
        page.props.auth.user?.email ?? '',
    ),
);
</script>

<template>
    <Head :title="t.auth.verify.metaTitle" />

    <AuthCard :title="t.auth.verify.title" :description="description">
        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-[#0B5D3B]"
        >
            {{ t.auth.verify.resent }}
        </div>

        <div class="grid gap-6">
            <Form
                action="/email/verify-code"
                method="post"
                :reset-on-success="['code']"
                v-slot="{ errors, processing }"
                class="grid gap-5"
            >
                <div class="grid gap-2">
                    <Label for="code" :class="authLabel">{{
                        t.auth.verify.code
                    }}</Label>
                    <Input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        required
                        v-focus
                        :class="[
                            authInput,
                            'text-center text-2xl tracking-[0.5em]',
                        ]"
                    />
                    <InputError :message="errors.code" />
                </div>

                <Button
                    type="submit"
                    :class="authButton"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.verify.submit }}
                </Button>
            </Form>

            <Form
                v-bind="send.form()"
                class="text-center"
                v-slot="{ processing }"
            >
                <Button
                    type="submit"
                    variant="ghost"
                    :disabled="processing"
                    :class="authLink"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.verify.resend }}
                </Button>
            </Form>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm text-[#6B5647]"
            >
                {{ t.auth.verify.logout }}
            </TextLink>
        </div>
    </AuthCard>
</template>
