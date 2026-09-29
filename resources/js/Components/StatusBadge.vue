<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, required: true },
});

const classes = {
    open: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    answered: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    resolved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    closed: 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    approved: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    high: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    normal: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    low: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
};

const cls = computed(() => classes[props.status] ?? classes.closed);
</script>

<template>
    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold capitalize" :class="cls">
        <slot>{{ status }}</slot>
    </span>
</template>
