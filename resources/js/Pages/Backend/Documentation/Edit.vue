<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    documentation: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    title: props.documentation.title,
    category_id: props.documentation.category_id ?? '',
    excerpt: props.documentation.excerpt ?? '',
    content: props.documentation.content,
});

const versionForm = useForm({
    version: '',
    changelog: '',
    install_guide: '',
    archive: null,
});

const deleteVersionForm = useForm({});

const submit = () => {
    form.patch(route('documentation.update', { id: props.documentation.id }));
};

const uploadVersion = () => {
    versionForm.post(route('documentation.versions.store', { id: props.documentation.id }), {
        forceFormData: true,
        onSuccess: () => versionForm.reset(),
    });
};

const removeVersion = (versionId, version) => {
    if (!confirm(`Remove version ${version}? This deletes the uploaded file.`)) return;
    deleteVersionForm.delete(route('documentation.versions.destroy', { id: props.documentation.id, versionId }));
};

const formatSize = (bytes) => {
    if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
    if (bytes >= 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${bytes} B`;
};
</script>

<template>
    <Head title="Edit Documentation Post" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Edit Documentation Post</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Update content and manage downloadable versions.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>

            <div class="flex justify-end">
                <Link :href="route('documentation.index')" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Back to Documentation
                </Link>
            </div>

            <form class="grid gap-4 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm">Title</label>
                    <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
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
                    <textarea v-model="form.excerpt" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                </div>

                <div>
                    <label class="mb-1 block text-sm">Content</label>
                    <textarea v-model="form.content" rows="14" class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                    <p v-if="form.errors.content" class="mt-1 text-xs text-red-600">{{ form.errors.content }}</p>
                </div>

                <p v-if="!canManage" class="text-xs text-amber-600 dark:text-amber-400">
                    Saving changes will resubmit this post for review.
                </p>

                <div>
                    <button type="submit" :disabled="form.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        Save Changes
                    </button>
                </div>
            </form>

            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold">Versions</h2>

                <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-3 py-2">Version</th>
                                <th class="px-3 py-2">File</th>
                                <th class="px-3 py-2">Size</th>
                                <th class="px-3 py-2">Downloads</th>
                                <th class="px-3 py-2">Install Guide</th>
                                <th class="px-3 py-2">Uploaded</th>
                                <th class="px-3 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="v in documentation.versions" :key="v.id" class="border-t border-slate-200 dark:border-slate-800">
                                <td class="px-3 py-2 font-medium">{{ v.version }}</td>
                                <td class="px-3 py-2 text-xs">{{ v.file_name }}</td>
                                <td class="px-3 py-2 text-xs">{{ formatSize(v.file_size) }}</td>
                                <td class="px-3 py-2 text-xs">{{ v.downloads }}</td>
                                <td class="px-3 py-2 text-xs">
                                    <span v-if="v.install_guide" class="text-emerald-600 dark:text-emerald-400">Yes</span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-3 py-2 text-xs">{{ v.created_at }}</td>
                                <td class="px-3 py-2">
                                    <button :disabled="deleteVersionForm.processing" class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400" @click="removeVersion(v.id, v.version)">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="documentation.versions.length === 0">
                                <td colspan="7" class="px-3 py-4 text-center text-slate-500">No versions uploaded yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form class="grid gap-3 sm:grid-cols-2" @submit.prevent="uploadVersion">
                    <div>
                        <label class="mb-1 block text-sm">Version</label>
                        <input v-model="versionForm.version" type="text" placeholder="e.g. 1.0.0" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                        <p v-if="versionForm.errors.version" class="mt-1 text-xs text-red-600">{{ versionForm.errors.version }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm">ZIP File</label>
                        <input type="file" accept=".zip" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" @change="versionForm.archive = $event.target.files[0]" />
                        <p v-if="versionForm.errors.archive" class="mt-1 text-xs text-red-600">{{ versionForm.errors.archive }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Changelog</label>
                        <textarea v-model="versionForm.changelog" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm">Installation Guide (optional)</label>
                        <textarea
                            v-model="versionForm.install_guide"
                            rows="4"
                            placeholder="e.g. # Install&#10;&#10;1. Download and extract the ZIP&#10;2. Run ./installer.sh&#10;3. Follow the on-screen prompts"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800"
                        ></textarea>
                        <p v-if="versionForm.errors.install_guide" class="mt-1 text-xs text-red-600">{{ versionForm.errors.install_guide }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Shown to visitors on the public download page for this version. Leave blank to show none.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" :disabled="versionForm.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                            Upload Version
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
