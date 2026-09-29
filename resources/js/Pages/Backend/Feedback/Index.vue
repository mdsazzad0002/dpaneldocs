<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    pages: Array,
    comments: Object,
});

const destroy = (item) => {
    if (confirm('Delete this comment?')) {
        router.delete(route('admin.feedback.destroy', item.id), { preserveScroll: true });
    }
};

const when = (date) => new Date(date).toLocaleDateString(undefined, { dateStyle: 'medium' });
</script>

<template>
    <Head title="Page feedback" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Page feedback</h2>
        </template>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h3 class="font-semibold">“Was this page helpful?”</h3>
                    <p class="text-sm text-slate-500">Lowest-scoring pages first — these need attention.</p>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="page in pages" :key="page.page" class="px-5 py-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <a v-if="page.url" :href="page.url" target="_blank" class="font-medium hover:text-blue-600">{{ page.title }}</a>
                            <span v-else class="font-medium">{{ page.title }}</span>
                            <span class="text-slate-500">{{ page.helpful }}/{{ page.total }} helpful</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-red-100 dark:bg-red-950/50">
                            <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${page.score}%` }" />
                        </div>
                    </li>
                    <li v-if="!pages.length" class="px-5 py-10 text-center text-sm text-slate-500">No votes yet.</li>
                </ul>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h3 class="font-semibold">Comments</h3>
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="item in comments.data" :key="item.id" class="px-5 py-4">
                        <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                            <span><span :class="item.helpful ? 'text-emerald-600' : 'text-red-600'">{{ item.helpful ? '👍 Helpful' : '👎 Not helpful' }}</span> · {{ item.title }} · {{ when(item.created_at) }}</span>
                            <button type="button" class="text-red-600 hover:underline" @click="destroy(item)">Delete</button>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm">{{ item.comment }}</p>
                    </li>
                    <li v-if="!comments.data.length" class="px-5 py-10 text-center text-sm text-slate-500">No comments yet.</li>
                </ul>
                <div class="px-5 py-4"><Pagination :links="comments.links" /></div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
