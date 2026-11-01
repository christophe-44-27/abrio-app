<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    danger?: boolean;
}>();
</script>

<template>
    <section
        :class="[
            'flex flex-col gap-6 rounded-2xl border bg-white p-7',
            danger ? 'border-[#E9C3BE]' : 'border-[#E8DACB]',
        ]"
    >
        <header class="flex flex-col gap-1">
            <h2 class="text-xl font-extrabold tracking-[-0.02em]">
                {{ title }}
            </h2>
            <p v-if="description" class="text-sm leading-normal text-[#6B5647]">
                {{ description }}
            </p>
        </header>
        <slot />
    </section>
</template>
