<script setup>
defineProps({
    value: { type: Number, required: true },
});
</script>

<template>
    <span class="inline-flex text-amber-400" :aria-label="`${value} out of 5 stars`">
        <span v-for="i in 5" :key="i" :class="i <= Math.round(value) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700'">★</span>
    </span>
</template>
