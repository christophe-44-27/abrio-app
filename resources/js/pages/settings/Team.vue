<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import TeamController from '@/actions/App/Http/Controllers/Settings/TeamController';
import InputError from '@/components/InputError.vue';
import SettingsSection from '@/components/SettingsSection.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import { fill } from '@/lib/utils';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import {
    authInput,
    authLabel,
    settingsButton,
    settingsButtonOutline,
} from '@/lib/authStyles';

defineOptions({ layout: SettingsLayout, inheritAttrs: false });

type Member = {
    id: number;
    name: string;
    email: string;
    isOwner: boolean;
    isYou: boolean;
};

defineProps<{
    unlocked: boolean;
    team: {
        isOwner: boolean;
        maxUsers: number;
        freeSeats: number;
        members: Member[];
        invitations: { id: number; email: string; expired: boolean }[];
        shareUrl: string | null;
    } | null;
}>();

const { t } = useI18n();

const invite = useForm({ email: '' });

const sendInvitation = () =>
    invite.post(TeamController.invite.url(), {
        preserveScroll: true,
        onSuccess: () => invite.reset(),
    });

// A resend is an invitation to the same address: it replaces the old link.
const resend = (email: string) =>
    router.post(TeamController.invite.url(), { email }, { preserveScroll: true });

const cancelInvitation = (id: number) =>
    router.delete(TeamController.cancelInvitation.url(id), { preserveScroll: true });

const removeMember = (member: Member) => {
    if (window.confirm(fill(t.value.settings.team.removeConfirm, { name: member.name }))) {
        router.delete(TeamController.removeMember.url(member.id), {
            preserveScroll: true,
        });
    }
};

const leave = () => {
    if (window.confirm(t.value.settings.team.leaveConfirm)) {
        router.post(TeamController.leave.url());
    }
};

const startSharing = () =>
    router.post(TeamController.startSharing.url(), {}, { preserveScroll: true });
const stopSharing = () =>
    router.delete(TeamController.stopSharing.url(), { preserveScroll: true });

const copied = ref(false);
// The async Clipboard API only exists on secure origins (https, localhost);
// elsewhere, e.g. a local http .test domain, fall back to selecting and copying.
const writeToClipboard = async (text: string): Promise<boolean> => {
    if (navigator.clipboard) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // Blocked by permissions: try the fallback below.
        }
    }

    const field = document.createElement('textarea');
    field.value = text;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();

    try {
        return document.execCommand('copy');
    } finally {
        document.body.removeChild(field);
    }
};

const copy = async (url: string) => {
    // On failure the link stays selectable in its field.
    if (await writeToClipboard(url)) {
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    }
};
</script>

<template>
    <Head :title="t.settings.team.metaTitle" />

    <SettingsSection
        :title="t.settings.team.title"
        :description="t.settings.team.description"
    >
        <p v-if="!team" class="text-[15px] text-[#6B5647]">
            {{ t.settings.team.noHousehold }}
        </p>

        <template v-else>
            <ul class="flex flex-col divide-y divide-[#E8DACB]">
                <li
                    v-for="member in team.members"
                    :key="member.id"
                    class="flex flex-wrap items-center justify-between gap-3 py-3"
                >
                    <div class="flex flex-col">
                        <span class="text-base font-bold">
                            {{ member.name }}
                            <span
                                v-if="member.isYou"
                                class="ml-1 text-sm font-semibold text-[#6B5647]"
                            >
                                ({{ t.settings.team.you }})
                            </span>
                        </span>
                        <span class="text-sm text-[#6B5647]">{{
                            member.email
                        }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            v-if="member.isOwner"
                            class="rounded-full bg-[#F0E4D6] px-3 py-1 text-[13px] font-bold"
                        >
                            {{ t.settings.team.owner }}
                        </span>
                        <button
                            v-else-if="team.isOwner"
                            type="button"
                            class="cursor-pointer text-sm font-semibold text-[#8F1B13]"
                            data-test="team-remove"
                            @click="removeMember(member)"
                        >
                            {{ t.settings.team.remove }}
                        </button>
                        <button
                            v-else-if="member.isYou"
                            type="button"
                            class="cursor-pointer text-sm font-semibold text-[#8F1B13]"
                            data-test="team-leave"
                            @click="leave"
                        >
                            {{ t.settings.team.leave }}
                        </button>
                    </div>
                </li>
            </ul>
        </template>
    </SettingsSection>

    <template v-if="team?.isOwner">
        <SettingsSection
            v-if="!unlocked"
            :title="t.settings.team.lockedTitle"
            :description="t.settings.team.lockedText"
        >
            <div>
                <!-- Plain link: the payment page lives on stripe.com -->
                <a
                    href="/unlock"
                    :class="[
                        settingsButton,
                        'inline-flex items-center justify-center',
                    ]"
                    data-test="team-unlock"
                >
                    {{ t.settings.team.unlock }}
                </a>
            </div>
        </SettingsSection>

        <template v-else>
            <SettingsSection
                :title="t.settings.team.inviteTitle"
                :description="t.settings.team.inviteText"
            >
                <form
                    class="flex flex-col gap-4"
                    @submit.prevent="sendInvitation"
                >
                    <div class="grid gap-2">
                        <Label for="invite-email" :class="authLabel">{{
                            t.settings.team.email
                        }}</Label>
                        <Input
                            id="invite-email"
                            v-model="invite.email"
                            type="email"
                            required
                            autocomplete="off"
                            :disabled="team.freeSeats < 1"
                            :class="authInput"
                        />
                        <InputError :message="invite.errors.email" />
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <Button
                            :class="settingsButton"
                            :disabled="invite.processing || team.freeSeats < 1"
                            data-test="team-invite"
                        >
                            {{ t.settings.team.invite }}
                        </Button>
                        <span class="text-sm text-[#6B5647]">
                            {{
                                fill(t.settings.team.seats, {
                                    n: team.freeSeats,
                                    max: team.maxUsers,
                                })
                            }}
                        </span>
                    </div>
                </form>

                <div v-if="team.invitations.length" class="flex flex-col gap-2">
                    <h3 class="text-sm font-bold">
                        {{ t.settings.team.pending }}
                    </h3>
                    <ul class="flex flex-col divide-y divide-[#E8DACB]">
                        <li
                            v-for="invitation in team.invitations"
                            :key="invitation.id"
                            class="flex flex-wrap items-center justify-between gap-3 py-2.5"
                        >
                            <span class="text-[15px]">
                                {{ invitation.email }}
                                <span
                                    v-if="invitation.expired"
                                    class="ml-1 text-sm text-[#8F3F08]"
                                >
                                    ({{ t.settings.team.expired }})
                                </span>
                            </span>
                            <span class="flex items-center gap-4 text-sm font-semibold">
                                <button
                                    type="button"
                                    class="cursor-pointer text-[#B4540F] hover:text-[#8F3F08]"
                                    @click="resend(invitation.email)"
                                >
                                    {{ t.settings.team.resend }}
                                </button>
                                <button
                                    type="button"
                                    class="cursor-pointer text-[#8F1B13]"
                                    @click="cancelInvitation(invitation.id)"
                                >
                                    {{ t.settings.team.cancelInvitation }}
                                </button>
                            </span>
                        </li>
                    </ul>
                </div>
            </SettingsSection>

            <SettingsSection
                :title="t.settings.team.shareTitle"
                :description="t.settings.team.shareText"
            >
                <div v-if="team.shareUrl" class="flex flex-col gap-4">
                    <div class="flex flex-wrap gap-3">
                        <Input
                            :model-value="team.shareUrl"
                            readonly
                            :class="[authInput, 'min-w-0 flex-1']"
                            data-test="team-share-url"
                            @focus="($event.target as HTMLInputElement).select()"
                        />
                        <Button
                            type="button"
                            :class="settingsButtonOutline"
                            @click="copy(team.shareUrl)"
                        >
                            {{ copied ? t.settings.team.copied : t.settings.team.copy }}
                        </Button>
                    </div>
                    <div>
                        <Button
                            type="button"
                            :class="settingsButtonOutline"
                            data-test="team-share-stop"
                            @click="stopSharing"
                        >
                            {{ t.settings.team.shareStop }}
                        </Button>
                    </div>
                </div>
                <div v-else>
                    <Button
                        type="button"
                        :class="settingsButton"
                        data-test="team-share-start"
                        @click="startSharing"
                    >
                        {{ t.settings.team.shareStart }}
                    </Button>
                </div>
            </SettingsSection>
        </template>
    </template>
</template>
