<script setup>
import PublicDocsLayout from '@/Layouts/PublicDocsLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const initialCategory = new URLSearchParams(window.location.search).get('category');
const activeCategory = ref(initialCategory && props.categories.includes(initialCategory) ? initialCategory : 'All');

const filteredPosts = computed(() => {
    if (activeCategory.value === 'All') return props.posts;
    return props.posts.filter((post) => post.category === activeCategory.value);
});
</script>

<template>
    <Head title="Dpanel — Documentation" />

    <PublicDocsLayout :category="activeCategory !== 'All' ? activeCategory : null">
        <template #hero>
            <section class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-slate-50 dark:from-slate-900 dark:to-slate-950">
                <div class="mx-auto xl:w-[90%] max-w-[1500px] px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
                    <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                        Documentation &amp; Guides for
                        <span class="block text-blue-600 dark:text-blue-400">Dpanel</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                        Everything you need to set up, manage, and get the most out of Dpanel — release notes, how-to guides, and downloadable versions, free for everyone.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <Link
                            href="/docs"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Browse Documentation
                        </Link>
                        <Link
                            v-if="user"
                            href="/dashboard"
                            class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Go to Dashboard
                        </Link>
                        <Link
                            v-else
                            href="/register"
                            class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            Create an Account
                        </Link>
                    </div>

                    <!-- Mock panel preview -->
                    <div class="mx-auto mt-16 max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-100 px-4 py-3 dark:border-slate-800 dark:bg-slate-800">
                            <span class="h-3 w-3 rounded-full bg-red-400"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                            <span class="h-3 w-3 rounded-full bg-green-400"></span>
                            <span class="ml-3 truncate rounded bg-white px-3 py-1 text-xs text-slate-400 dark:bg-slate-900">dpanel.local</span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 bg-slate-950 p-6 text-left sm:grid-cols-3">
                            <div class="rounded-lg bg-slate-900 p-4">
                                <p class="text-xs text-slate-400">Documentation</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ posts.length }}</p>
                                <p class="mt-1 text-xs text-slate-500">Published posts</p>
                            </div>
                            <div class="rounded-lg bg-slate-900 p-4">
                                <p class="text-xs text-slate-400">Categories</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ categories.length }}</p>
                                <p class="mt-1 text-xs text-slate-500">Topics covered</p>
                            </div>
                            <div class="rounded-lg bg-slate-900 p-4">
                                <p class="text-xs text-slate-400">Total views</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ posts.reduce((sum, p) => sum + (p.views || 0), 0) }}</p>
                                <p class="mt-1 text-xs text-slate-500">Across all guides</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Latest Documentation</h2>
            <p class="mt-2 text-slate-600 dark:text-slate-400">Guides, release notes, and downloadable versions — free for everyone.</p>
        </div>

        <div v-if="categories.length" class="mb-6 flex flex-wrap gap-2">
            <button
                class="rounded-full px-3 py-1 text-sm"
                :class="activeCategory === 'All' ? 'bg-blue-600 text-white' : 'border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'"
                @click="activeCategory = 'All'"
            >
                All
            </button>
            <button
                v-for="cat in categories"
                :key="cat"
                class="rounded-full px-3 py-1 text-sm"
                :class="activeCategory === cat ? 'bg-blue-600 text-white' : 'border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'"
                @click="activeCategory = cat"
            >
                {{ cat }}
            </button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <Link
                v-for="post in filteredPosts"
                :key="post.slug"
                :href="route('docs.public.show', { slug: post.slug })"
                class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700"
            >
                <span v-if="post.category" class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ post.category }}
                </span>
                <h2 class="text-lg font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ post.title }}</h2>
                <p v-if="post.excerpt" class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ post.excerpt }}</p>
                <div class="mt-3 flex items-center gap-3 text-xs text-slate-500">
                    <span>{{ post.created_at }}</span>
                    <span>·</span>
                    <span>{{ post.views }} views</span>
                </div>
            </Link>

            <p v-if="filteredPosts.length === 0" class="col-span-2 py-12 text-center text-slate-500">
                No documentation posts published yet.
            </p>
        </div>
    </PublicDocsLayout>
</template>
