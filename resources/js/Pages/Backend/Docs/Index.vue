<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    pages: Array,
    syncs: Array,
    repository: String,
    branch: String,
});

const form = useForm({ branch: props.branch });
const sync = () => form.post(route('admin.docs.sync'), { preserveScroll: true });

const lastSync = computed(() => props.syncs[0] ?? null);
const lastUpdated = computed(() => Math.max(0, ...props.pages.map((p) => p.updated_at ?? 0)));

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
const fromUnix = (seconds) => (seconds ? when(seconds * 1000) : '—');
const kb = (bytes) => `${(bytes / 1024).toFixed(1)} KB`;
</script>

<template>
    <Head title="Documentation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Documentation</h2>
        </template>

        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm text-slate-500">Last sync</div>
                    <div class="mt-2 text-lg font-semibold">{{ lastSync ? when(lastSync.created_at) : 'Never' }}</div>
                    <div v-if="lastSync" class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                        <StatusBadge :status="lastSync.status === 'success' ? 'approved' : lastSync.status === 'partial' ? 'pending' : 'rejected'">{{ lastSync.status }}</StatusBadge>
                        {{ lastSync.user?.name ?? 'command line' }}
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="text-sm text-slate-500">Content last changed</div>
                    <div class="mt-2 text-lg font-semibold">{{ fromUnix(lastUpdated) }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ pages.length }} pages</div>
                </div>
                <form class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="sync">
                    <div class="text-sm text-slate-500">Pull the latest Markdown from <a :href="repository" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">GitHub</a></div>
                    <div class="mt-3 flex gap-2">
                        <input v-model="form.branch" aria-label="Branch" class="w-28 rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-mono text-sm dark:border-slate-700 dark:bg-slate-950" />
                        <button type="submit" :disabled="form.processing" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">
                            <svg class="h-4 w-4" :class="{ 'animate-spin': form.processing }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            {{ form.processing ? 'Syncing…' : 'Sync now' }}
                        </button>
                    </div>
                    <p v-if="form.errors.branch" class="mt-1 text-xs text-red-600">{{ form.errors.branch }}</p>
                </form>
            </div>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3">Page</th>
                                <th class="px-4 py-3">Source</th>
                                <th class="px-4 py-3">Last updated</th>
                                <th class="px-4 py-3 text-right">Size</th>
                                <th class="px-4 py-3 text-right">Helpful</th>
                                <th class="px-4 py-3 text-right">Comments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="page in pages" :key="page.slug">
                                <td class="px-4 py-3">
                                    <a v-if="page.url" :href="page.url" target="_blank" class="font-medium hover:text-blue-600 dark:hover:text-blue-400">{{ page.title }}</a>
                                    <span v-else class="font-medium text-slate-400">{{ page.title }}</span>
                                    <div class="text-xs text-slate-500">{{ page.section ?? 'missing file' }}</div>
                                </td>
                                <td class="px-4 py-3"><a :href="page.source_url" target="_blank" class="font-mono text-xs text-slate-500 hover:text-blue-600">{{ page.source }}</a></td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-300">{{ fromUnix(page.updated_at) }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ kb(page.size) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span v-if="page.helpful !== null" :class="page.helpful < 60 ? 'text-red-600' : 'text-emerald-600'">{{ page.helpful }}%</span>
                                    <span v-else class="text-slate-400">—</span>
                                    <div class="text-xs text-slate-400">{{ page.votes }} votes</div>
                                </td>
                                <td class="px-4 py-3 text-right">{{ page.comments }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <h3 class="border-b border-slate-200 px-5 py-4 font-semibold dark:border-slate-800">Sync history</h3>
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="item in syncs" :key="item.id" class="px-5 py-3 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusBadge :status="item.status === 'success' ? 'approved' : item.status === 'partial' ? 'pending' : 'rejected'">{{ item.status }}</StatusBadge>
                            <span class="font-medium">{{ when(item.created_at) }}</span>
                            <span class="text-slate-500">· {{ item.source }} · {{ item.user?.name ?? 'command line' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            <template v-if="item.changed?.length">Updated: {{ item.changed.join(', ') }}</template>
                            <template v-else>No changes</template>
                            <template v-if="item.skipped?.length"> · Not found: {{ item.skipped.join(', ') }}</template>
                            <template v-if="item.message"> · {{ item.message }}</template>
                        </p>
                    </li>
                    <li v-if="!syncs.length" class="px-5 py-10 text-center text-sm text-slate-500">No syncs yet. Press “Sync now” to pull the docs from GitHub.</li>
                </ul>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
