<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ReplyBox from '@/Components/ReplyBox.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    comments: Object,
    status: String,
    counts: Object,
});

const tabs = [
    { key: 'pending', label: 'Waiting' },
    { key: 'approved', label: 'Published' },
    { key: 'spam', label: 'Spam' },
];

const setStatus = (comment, status) => router.patch(route('admin.comments.update', comment.id), { status }, { preserveScroll: true });

const destroy = (comment) => {
    if (confirm('Delete this comment and its replies permanently?')) {
        router.delete(route('admin.comments.destroy', comment.id), { preserveScroll: true });
    }
};

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head title="Comments" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Docs comments</h2>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex gap-2">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="route('admin.comments.index', { status: tab.key })"
                        class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                        :class="status === tab.key ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                    >{{ tab.label }} <span class="opacity-70">{{ counts[tab.key] ?? 0 }}</span></Link>
                </div>
                <p class="text-sm text-slate-500">Replying publishes the comment and can email your answer to the visitor.</p>
            </div>

            <article v-for="comment in comments.data" :key="comment.id" class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <p>
                        <span class="font-semibold">{{ comment.name }}</span>
                        <span class="text-slate-500"> on </span>
                        <a :href="comment.url" target="_blank" class="font-medium text-blue-600 hover:underline dark:text-blue-400">{{ comment.title }}</a>
                    </p>
                    <span class="text-xs text-slate-500">{{ when(comment.created_at) }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ comment.body }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    <a v-if="comment.email" :href="`mailto:${comment.email}`" class="hover:underline">{{ comment.email }}</a>
                    <template v-if="comment.ip_address"> · {{ comment.ip_address }}</template>
                </p>

                <div v-for="reply in comment.replies" :key="reply.id" class="mt-4 rounded-lg border-l-4 border-blue-500 bg-blue-50/60 px-4 py-3 dark:bg-blue-950/30">
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <p>
                            <span class="font-semibold">{{ reply.name }}</span>
                            <span v-if="reply.is_staff" class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">Team</span>
                            <span class="text-slate-500"> · {{ when(reply.created_at) }}</span>
                        </p>
                        <button type="button" class="text-xs font-medium text-red-600 hover:underline" @click="destroy(reply)">Delete</button>
                    </div>
                    <p class="mt-1.5 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ reply.body }}</p>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <ReplyBox
                        :action="route('admin.comments.reply', comment.id)"
                        ai-type="comment"
                        :ai-id="comment.id"
                        :email="comment.email"
                        :placeholder="`Reply to ${comment.name} (shown publicly under the comment)…`"
                    />
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button v-if="comment.status !== 'approved'" type="button" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700" @click="setStatus(comment, 'approved')">Publish</button>
                    <button v-if="comment.status === 'approved'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="setStatus(comment, 'pending')">Unpublish</button>
                    <button v-if="comment.status !== 'spam'" type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800" @click="setStatus(comment, 'spam')">Spam</button>
                    <button type="button" class="ml-auto rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40" @click="destroy(comment)">Delete</button>
                </div>
            </article>

            <p v-if="!comments.data.length" class="rounded-xl border border-dashed border-slate-300 py-12 text-center text-sm text-slate-500 dark:border-slate-700">Nothing here.</p>

            <Pagination :links="comments.links" />
        </div>
    </AuthenticatedLayout>
</template>
