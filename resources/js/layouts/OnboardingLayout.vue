<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui/sonner';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>
    <Toaster position="top-center" />
    <div
        class="min-h-screen bg-[#FBF5EE] px-10 pt-8 pb-16 font-['Manrope',system-ui,sans-serif] text-[#2B1D14]"
    >
        <slot />
    </div>
</template>
