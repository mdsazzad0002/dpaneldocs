<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    title: '',
    category_id: '',
    excerpt: '',
    content: '',
});

const submit = () => {
    form.post(route('documentation.store'));
};
</script>

<template>
    <Head title="New Documentation Post" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">New Documentation Post</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Submissions from non-admins go to review before they're public.</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Link :href="route('documentation.index')" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Back to Documentation
                </Link>
            </div>

            <form class="grid gap-4 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm">Title</label>
                    <input v-model="form.title" type="text" placeholder="e.g. Getting Started with the API" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm">Category</label>
                    <select v-model="form.category_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">Uncategorized</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-xs text-red-600">{{ form.errors.category_id }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm">Excerpt</label>
                    <textarea v-model="form.excerpt" rows="2" placeholder="Short summary shown in listings" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                    <p v-if="form.errors.excerpt" class="mt-1 text-xs text-red-600">{{ form.errors.excerpt }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm">Content</label>
                    <textarea v-model="form.content" rows="14" placeholder="Full documentation content (Markdown supported by your renderer of choice)" class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                    <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">{{ form.errors.content }}</p>
                </div>

                <div>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        Submit Post
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
