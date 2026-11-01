<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import {
    authInput,
    settingsButtonDanger,
    settingsButtonOutline,
} from '@/lib/authStyles';

const { t } = useI18n();
const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <SettingsSection
        danger
        :title="t.settings.delete.title"
        :description="t.settings.delete.description"
    >
        <div
            class="flex flex-col gap-4 rounded-xl border border-[#E9C3BE] bg-[#FBEDEA] p-5"
        >
            <div class="flex flex-col gap-0.5 text-[#8F1B13]">
                <p class="font-bold">{{ t.settings.delete.warning }}</p>
                <p class="text-sm">{{ t.settings.delete.warningText }}</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        :class="[settingsButtonDanger, 'self-start']"
                        data-test="delete-user-button"
                    >
                        {{ t.settings.delete.button }}
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle>{{
                                t.settings.delete.confirmTitle
                            }}</DialogTitle>
                            <DialogDescription>
                                {{ t.settings.delete.confirmText }}
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">{{
                                t.settings.delete.password
                            }}</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                :placeholder="t.settings.delete.password"
                                :class="authInput"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    :class="settingsButtonOutline"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    {{ t.settings.cancel }}
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                :class="settingsButtonDanger"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                {{ t.settings.delete.button }}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </SettingsSection>
</template>
