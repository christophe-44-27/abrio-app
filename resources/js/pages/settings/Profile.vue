<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { authInput, authLabel, settingsButton } from '@/lib/authStyles';
import { send } from '@/routes/verification';

defineOptions({ layout: SettingsLayout, inheritAttrs: false });

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head :title="t.settings.profile.metaTitle" />

    <SettingsSection
        :title="t.settings.profile.title"
        :description="t.settings.profile.description"
    >
        <Form
            v-bind="ProfileController.update.form()"
            class="flex flex-col gap-5"
            v-slot="{ errors, processing, recentlySuccessful }"
        >
            <div class="grid gap-2">
                <Label for="name" :class="authLabel">{{
                    t.settings.profile.name
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="nickname"
                    :class="authInput"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" :class="authLabel">{{
                    t.settings.profile.email
                }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="email"
                    :class="authInput"
                />
                <InputError :message="errors.email" />
            </div>

            <label class="flex items-start gap-3 text-sm text-[#43302A]">
                <input
                    type="checkbox"
                    name="marketing"
                    value="1"
                    :checked="!!user.marketing_opt_in_at"
                    class="mt-0.5 size-5 shrink-0 accent-[#2B1D14]"
                />
                <span>{{ t.consent.marketing }}</span>
            </label>

            <div
                v-if="page.props.mustVerifyEmail && !user.email_verified_at"
                class="flex flex-col gap-1 text-sm text-[#6B5647]"
            >
                <p>
                    {{ t.settings.profile.unverified }}
                    <Link
                        :href="send()"
                        as="button"
                        class="cursor-pointer font-semibold text-[#B4540F] hover:text-[#8F3F08]"
                    >
                        {{ t.settings.profile.resend }}
                    </Link>
                </p>
                <p
                    v-if="page.props.status === 'verification-link-sent'"
                    class="font-semibold text-[#2F6B3F]"
                >
                    {{ t.settings.profile.linkSent }}
                </p>
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :class="settingsButton"
                    :disabled="processing"
                    data-test="update-profile-button"
                >
                    {{ t.settings.profile.save }}
                </Button>
                <span
                    v-if="recentlySuccessful"
                    class="text-sm font-semibold text-[#2F6B3F]"
                >
                    {{ t.settings.profile.saved }}
                </span>
            </div>
        </Form>
    </SettingsSection>

    <DeleteUser />
</template>
