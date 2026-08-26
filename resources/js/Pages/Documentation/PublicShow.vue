<script setup>
import PublicDocsLayout from '@/Layouts/PublicDocsLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
});

const formatSize = (bytes) => {
    if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
    if (bytes >= 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${bytes} B`;
};
</script>

<template>
    <Head :title="post.title" />

    <PublicDocsLayout :category="post.category">
        <Link href="/docs" class="mb-6 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
            &larr; All documentation
        </Link>

        <article>
            <span v-if="post.category" class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                {{ post.category }}
            </span>
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ post.title }}</h1>
            <div class="mt-2 flex items-center gap-3 text-sm text-slate-500">
                <span>{{ post.created_at }}</span>
                <span>·</span>
                <span>{{ post.views }} views</span>
            </div>

            <div class="mt-8 max-w-none whitespace-pre-wrap text-base leading-7 text-slate-700 dark:text-slate-300">{{ post.content }}</div>
        </article>

        <section v-if="post.versions.length" class="mt-12 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="mb-4 text-lg font-semibold">Downloads</h2>
            <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                <li v-for="v in post.versions" :key="v.id" class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <div class="font-medium">Version {{ v.version }}</div>
                        <div v-if="v.changelog" class="text-sm text-slate-600 dark:text-slate-400">{{ v.changelog }}</div>
                        <div class="text-xs text-slate-500">{{ formatSize(v.file_size) }} · {{ v.downloads }} downloads · {{ v.created_at }}</div>
                    </div>
                    <a
                        :href="route('docs.public.download', { slug: post.slug, versionId: v.id })"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        Download ZIP
                    </a>
                </li>
            </ul>
        </section>
    </PublicDocsLayout>
</template>
