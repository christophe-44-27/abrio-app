<script setup lang="ts">
defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <img
        src="/assets/images/logo.webp"
        alt=""
        width="64"
        height="64"
        class="rounded-[22%]"
        v-bind="$attrs"
    />
</template>
