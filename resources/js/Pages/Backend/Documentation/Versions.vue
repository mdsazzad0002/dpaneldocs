<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const deleteForm = useForm({});

const props = defineProps({
    versions: {
        type: Array,
        default: () => [],
    },
    query: {
        type: String,
        default: '',
    },
});

const search = ref(props.query);

const runSearch = () => {
    router.get(route('documentation.versions.index'), { q: search.value }, { preserveState: true });
};

const removeVersion = (docId, versionId, version) => {
    if (!confirm(`Remove version ${version}? This deletes the uploaded file.`)) return;
    deleteForm.delete(route('documentation.versions.destroy', { id: docId, versionId }), { preserveScroll: true });
};

const formatSize = (bytes) => {
    if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
    if (bytes >= 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${bytes} B`;
};
</script>

<template>
    <Head title="Versions" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Versions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Every downloadable file uploaded across all documentation posts.</p>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>

            <form class="flex gap-2" @submit.prevent="runSearch">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by post title or version..."
                    class="w-full max-w-sm rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                />
                <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Search
                </button>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3">Post</th>
                            <th class="px-4 py-3">Version</th>
                            <th class="px-4 py-3">File</th>
                            <th class="px-4 py-3">Size</th>
                            <th class="px-4 py-3">Downloads</th>
                            <th class="px-4 py-3">Uploaded</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in versions" :key="v.id" class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-3">
                                <Link :href="route('documentation.edit', { id: v.post_id })" class="font-medium text-blue-600 hover:underline dark:text-blue-400">
                                    {{ v.post_title }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ v.version }}</td>
                            <td class="px-4 py-3 text-xs">{{ v.file_name }}</td>
                            <td class="px-4 py-3 text-xs">{{ formatSize(v.file_size) }}</td>
                            <td class="px-4 py-3 text-xs">{{ v.downloads }}</td>
                            <td class="px-4 py-3 text-xs">{{ v.created_at }}</td>
                            <td class="px-4 py-3">
                                <button
                                    :disabled="deleteForm.processing"
                                    class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400"
                                    @click="removeVersion(v.documentation_id, v.id, v.version)"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                        <tr v-if="versions.length === 0">
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">No versions found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
