<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
import { store } from '@/routes/register';

defineOptions({ layout: BlankLayout, inheritAttrs: false });

const props = defineProps<{
    passwordRules: string;
    geo: {
        countries: string[];
        defaultCountry: string;
        regions: Record<string, Record<string, { fr: string; en: string }>>;
    };
}>();

const { t, locale } = useI18n();

const country = ref(props.geo.defaultCountry);

const countryOptions = computed(() => {
    const names = new Intl.DisplayNames([locale.value], { type: 'region' });

    return props.geo.countries
        .map((code) => ({ code, name: names.of(code) ?? code }))
        .sort((a, b) => a.name.localeCompare(b.name, locale.value));
});

const regionOptions = computed(() => {
    const regions = props.geo.regions[country.value];

    if (!regions) {
        return null;
    }

    return Object.entries(regions)
        .map(([code, name]) => ({ code, name: name[locale.value] }))
        .sort((a, b) => a.name.localeCompare(b.name, locale.value));
});

const selectClass = `${authInput} w-full border bg-white`;
</script>

<template>
    <Head :title="t.auth.register.metaTitle" />

    <AuthCard
        :title="t.auth.register.title"
        :description="t.auth.register.description"
    >
        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="name" :class="authLabel">{{
                        t.auth.register.name
                    }}</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        v-focus
                        :tabindex="1"
                        autocomplete="nickname"
                        name="name"
                        :class="authInput"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" :class="authLabel">{{
                        t.auth.register.email
                    }}</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        :class="authInput"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="country" :class="authLabel">{{
                        t.auth.register.country
                    }}</Label>
                    <select
                        id="country"
                        v-model="country"
                        name="country"
                        required
                        :tabindex="3"
                        autocomplete="country"
                        :class="selectClass"
                    >
                        <option
                            v-for="c in countryOptions"
                            :key="c.code"
                            :value="c.code"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                    <InputError :message="errors.country" />
                </div>

                <div class="grid gap-2">
                    <template v-if="regionOptions">
                        <Label for="province" :class="authLabel">{{
                            t.auth.register.province
                        }}</Label>
                        <select
                            id="province"
                            :key="country"
                            name="province"
                            required
                            :tabindex="4"
                            :class="selectClass"
                        >
                            <option value="" disabled selected>
                                {{ t.auth.register.select }}
                            </option>
                            <option
                                v-for="r in regionOptions"
                                :key="r.code"
                                :value="r.code"
                            >
                                {{ r.name }}
                            </option>
                        </select>
                    </template>
                    <template v-else>
                        <Label for="province" :class="authLabel">{{
                            t.auth.register.region
                        }}</Label>
                        <Input
                            id="province"
                            :key="country"
                            type="text"
                            name="province"
                            maxlength="100"
                            :tabindex="4"
                            :class="authInput"
                        />
                    </template>
                    <InputError :message="errors.province" />
                    <p class="text-[13px] text-[#6B5647]">
                        {{ t.auth.register.statsNote }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password" :class="authLabel">{{
                        t.auth.register.password
                    }}</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password"
                        :passwordrules="passwordRules"
                        :class="authInput"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" :class="authLabel">
                        {{ t.auth.register.passwordConfirm }}
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="6"
                        autocomplete="new-password"
                        name="password_confirmation"
                        :passwordrules="passwordRules"
                        :class="authInput"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    :class="authButton"
                    tabindex="7"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    {{ t.auth.register.submit }}
                </Button>
            </div>

            <div class="text-center text-sm text-[#6B5647]">
                {{ t.auth.register.haveAccount }}
                <TextLink :href="login()" :class="authLink" :tabindex="8">
                    {{ t.auth.register.logIn }}
                </TextLink>
            </div>
        </Form>
    </AuthCard>
</template>
