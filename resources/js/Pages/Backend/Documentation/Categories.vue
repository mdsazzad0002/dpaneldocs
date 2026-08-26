<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
});

const createForm = useForm({ name: '' });
const editForm = useForm({ name: '' });
const deleteForm = useForm({});

const editingId = ref(null);

const create = () => {
    createForm.post(route('categories.store'), {
        onSuccess: () => createForm.reset(),
    });
};

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.clearErrors();
};

const cancelEdit = () => {
    editingId.value = null;
};

const saveEdit = (id) => {
    editForm.patch(route('categories.update', { id }), {
        onSuccess: () => {
            editingId.value = null;
        },
    });
};

const destroy = (id, name) => {
    if (!confirm(`Delete category "${name}"? Posts in this category will become uncategorized.`)) return;
    deleteForm.delete(route('categories.destroy', { id }));
};
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Categories</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Organize documentation posts into categories.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>

            <form class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-end dark:border-slate-800 dark:bg-slate-900" @submit.prevent="create">
                <div class="flex-1">
                    <label class="mb-1 block text-sm">New category</label>
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="e.g. Guides, API, Release Notes"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                    />
                    <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-600">{{ createForm.errors.name }}</p>
                </div>
                <button
                    type="submit"
                    :disabled="createForm.processing"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                >
                    Add Category
                </button>
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Posts</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="category in categories" :key="category.id" class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-3">
                                <input
                                    v-if="editingId === category.id"
                                    v-model="editForm.name"
                                    type="text"
                                    class="w-full rounded-md border border-slate-300 px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-800"
                                    @keyup.enter="saveEdit(category.id)"
                                    @keyup.esc="cancelEdit"
                                />
                                <span v-else class="font-medium">{{ category.name }}</span>
                                <p v-if="editingId === category.id && editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ category.slug }}</td>
                            <td class="px-4 py-3">{{ category.post_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <template v-if="editingId === category.id">
                                        <button :disabled="editForm.processing" class="rounded-md border border-emerald-300 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400" @click="saveEdit(category.id)">
                                            Save
                                        </button>
                                        <button class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" @click="cancelEdit">
                                            Cancel
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800" @click="startEdit(category)">
                                            Rename
                                        </button>
                                        <button :disabled="deleteForm.processing" class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400" @click="destroy(category.id, category.name)">
                                            Delete
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No categories yet. Add one above.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
