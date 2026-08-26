<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    topics: '',
});

const submit = () => {
    form.post(route('documentation.bulk-generate.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Bulk Generate Documentation (AI)" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Bulk Generate Documentation with AI</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    One topic per line. Each topic is queued as a background job that writes a full post
                    (title, category, excerpt, content) and submits it for review.
                </p>
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
                    <label class="mb-1 block text-sm">Topics</label>
                    <textarea
                        v-model="form.topics"
                        rows="12"
                        placeholder="Setting up automated backups&#10;Configuring SSL certificates&#10;Migrating a site to a new server"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800"
                    ></textarea>
                    <p v-if="form.errors.topics" class="mt-1 text-xs text-red-600">{{ form.errors.topics }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Up to 30 topics per batch. New categories are created automatically if needed.</p>
                </div>

                <div>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        Queue Generation
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
