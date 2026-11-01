<script setup lang="ts">
import AccountMenu from '@/components/AccountMenu.vue';
import LocaleSwitch from '@/components/LocaleSwitch.vue';
</script>

<template>
    <div class="flex items-center gap-3 print:hidden">
        <LocaleSwitch />
        <AccountMenu />
    </div>
</template>
