<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const isOpen = ref(false);
const query = ref('');
const results = ref([]);
const loading = ref(false);
const activeIndex = ref(0);
const inputEl = ref(null);
let debounceTimer = null;
let requestToken = 0;

const isMac = typeof navigator !== 'undefined' && /Mac/i.test(navigator.platform);

function open() {
    isOpen.value = true;
    activeIndex.value = 0;
    nextTick(() => inputEl.value?.focus());
}

function close() {
    isOpen.value = false;
    query.value = '';
    results.value = [];
}

function onGlobalKeydown(e) {
    const metaK = (e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k';
    if (metaK) {
        e.preventDefault();
        isOpen.value ? close() : open();
        return;
    }
    if (e.key === 'Escape' && isOpen.value) {
        close();
    }
}

onMounted(() => window.addEventListener('keydown', onGlobalKeydown));
onUnmounted(() => window.removeEventListener('keydown', onGlobalKeydown));

watch(query, (value) => {
    clearTimeout(debounceTimer);
    activeIndex.value = 0;

    if (!value.trim()) {
        results.value = [];
        loading.value = false;
        return;
    }

    loading.value = true;
    debounceTimer = setTimeout(async () => {
        const token = ++requestToken;
        try {
            const response = await fetch(`/docs/search?q=${encodeURIComponent(value.trim())}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (token === requestToken) {
                results.value = data.results ?? [];
            }
        } catch (e) {
            if (token === requestToken) results.value = [];
        } finally {
            if (token === requestToken) loading.value = false;
        }
    }, 220);
});

const hasResults = computed(() => results.value.length > 0);

function go(index) {
    const item = results.value[index];
    if (!item) return;
    close();
    router.visit(`/docs/${item.slug}`);
}

function onKeydown(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (hasResults.value) activeIndex.value = (activeIndex.value + 1) % results.value.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (hasResults.value) activeIndex.value = (activeIndex.value - 1 + results.value.length) % results.value.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        go(activeIndex.value);
    }
}

defineExpose({ open });
</script>

<template>
    <button
        type="button"
        @click="open"
        class="flex w-full max-w-xs items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-400 shadow-sm transition hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500 dark:hover:border-blue-500"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
        <span class="flex-1 text-left">Search docs...</span>
        <kbd class="hidden items-center gap-0.5 rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 font-mono text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500 sm:inline-flex">
            {{ isMac ? '⌘' : 'Ctrl' }} K
        </kbd>
    </button>

    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-[12vh]">
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="close" />

            <div class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 dark:border-slate-800">
                    <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <input
                        ref="inputEl"
                        v-model="query"
                        type="text"
                        placeholder="Search documentation..."
                        class="flex-1 border-0 bg-transparent text-base text-slate-900 placeholder-slate-400 outline-none focus:ring-0 dark:text-slate-100"
                        @keydown="onKeydown"
                    />
                    <kbd class="rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 font-mono text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                        ESC
                    </kbd>
                </div>

                <div class="max-h-[60vh] overflow-y-auto">
                    <div v-if="loading" class="flex items-center justify-center gap-2 px-4 py-10 text-sm text-slate-400">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Searching...
                    </div>

                    <ul v-else-if="hasResults" class="divide-y divide-slate-100 py-2 dark:divide-slate-800">
                        <li v-for="(item, index) in results" :key="item.slug">
                            <Link
                                :href="`/docs/${item.slug}`"
                                class="block px-4 py-3 transition"
                                :class="index === activeIndex ? 'bg-blue-50 dark:bg-blue-950/40' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                                @mouseenter="activeIndex = index"
                                @click="close"
                            >
                                <div class="flex items-center gap-2">
                                    <span v-if="item.category" class="inline-block rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ item.category }}
                                    </span>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ item.title }}</span>
                                </div>
                                <p v-if="item.excerpt" class="mt-1 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ item.excerpt }}</p>
                            </Link>
                        </li>
                    </ul>

                    <div v-else-if="query.trim()" class="px-4 py-10 text-center text-sm text-slate-400">
                        No results for "<span class="font-medium text-slate-600 dark:text-slate-300">{{ query }}</span>"
                    </div>

                    <div v-else class="px-4 py-10 text-center text-sm text-slate-400">
                        Start typing to search documentation.
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2 text-[11px] text-slate-400 dark:border-slate-800 dark:bg-slate-900/60">
                    <span class="flex items-center gap-1">
                        <kbd class="rounded border border-slate-300 bg-white px-1 dark:border-slate-700 dark:bg-slate-800">↑</kbd>
                        <kbd class="rounded border border-slate-300 bg-white px-1 dark:border-slate-700 dark:bg-slate-800">↓</kbd>
                        to navigate
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="rounded border border-slate-300 bg-white px-1 dark:border-slate-700 dark:bg-slate-800">Enter</kbd>
                        to select
                    </span>
                </div>
            </div>
        </div>
    </Teleport>
</template>
