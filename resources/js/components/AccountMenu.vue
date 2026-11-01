<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useI18n } from '@/composables/useI18n';
import { landingPath } from '@/i18n';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';

const { t, locale } = useI18n();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const initial = computed(
    () => user.value?.name?.trim().charAt(0).toUpperCase() || '?',
);

// Drop cached pages so the next visitor on this browser can't see them.
const handleLogout = () => router.flushAll();
</script>

<template>
    <DropdownMenu v-if="user">
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                :aria-label="t.account.menu"
                class="flex size-11 cursor-pointer items-center justify-center rounded-full bg-[#2B1D14] text-sm font-extrabold text-white hover:bg-[#43302A]"
            >
                {{ initial }}
            </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-56">
            <DropdownMenuLabel class="flex flex-col gap-0.5 font-normal">
                <span class="truncate text-sm font-semibold">{{
                    user.name
                }}</span>
                <span class="truncate text-xs text-muted-foreground">{{
                    user.email
                }}</span>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link href="/menage" class="block w-full cursor-pointer">
                    {{ t.account.household }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/logements" class="block w-full cursor-pointer">
                    {{ t.account.housings }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link href="/verdicts" class="block w-full cursor-pointer">
                    {{ t.account.verdicts }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link :href="edit()" class="block w-full cursor-pointer">
                    {{ t.account.settings }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link
                    :href="landingPath(locale)"
                    class="block w-full cursor-pointer"
                >
                    {{ t.account.home }}
                </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link
                    :href="logout()"
                    as="button"
                    class="block w-full cursor-pointer text-left"
                    data-test="logout-button"
                    @click="handleLogout"
                >
                    {{ t.account.logout }}
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
