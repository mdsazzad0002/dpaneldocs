<script setup>
defineProps({
    sizeClass: {
        type: String,
        default: 'w-[220px]',
    },
    src: {
        type: String,
        default: '/dpanel_logo.png',
    },
});
</script>

<template>
    <img :src="src" alt="dPanel Logo" :class="[sizeClass, 'h-auto']" />
</template>
