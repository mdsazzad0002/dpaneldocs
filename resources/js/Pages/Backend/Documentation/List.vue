<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const deleteForm = useForm({});
const approveForm = useForm({});
const rejectForm = useForm({ rejection_reason: '' });

const props = defineProps({
    documentation: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const statusStyles = {
    published: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    rejected: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
};

const destroy = (id, title) => {
    if (!confirm(`Delete "${title}"? This also removes all uploaded versions.`)) return;
    deleteForm.delete(route('documentation.destroy', { id }));
};

const approve = (id) => {
    approveForm.post(route('documentation.approve', { id }));
};

const reject = (id) => {
    const reason = prompt('Reason for rejection (optional):');
    rejectForm.rejection_reason = reason ?? '';
    rejectForm.post(route('documentation.reject', { id }));
};
</script>

<template>
    <Head title="Documentation" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Documentation</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ canManage ? 'Review, publish and manage every documentation post.' : 'Your submitted documentation posts.' }}
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                {{ page.props.flash.error }}
            </div>

            <div class="flex items-center justify-end gap-2">
                <Link :href="route('documentation.bulk-generate')" class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                    Bulk Generate (AI)
                </Link>
                <Link :href="route('documentation.create')" class="rounded-md bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700">
                    New Post
                </Link>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Submitted by</th>
                            <th class="px-4 py-3">Versions</th>
                            <th class="px-4 py-3">Views</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="doc in documentation" :key="doc.id" class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ doc.title }}</div>
                                <div class="text-xs text-slate-500">{{ doc.category ?? 'Uncategorized' }} · {{ doc.created_at }}</div>
                                <div v-if="doc.status === 'rejected' && doc.rejection_reason" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                    Reason: {{ doc.rejection_reason }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium capitalize" :class="statusStyles[doc.status]">
                                    {{ doc.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ doc.submitted_by ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ doc.version_count }}</td>
                            <td class="px-4 py-3 text-sm">{{ doc.views }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <Link v-if="doc.can_edit" :href="route('documentation.edit', { id: doc.id })" class="rounded-md border border-slate-300 px-2 py-1 text-xs hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                        Edit
                                    </Link>
                                    <button v-if="canManage && doc.status !== 'published'" :disabled="approveForm.processing" class="rounded-md border border-emerald-300 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400" @click="approve(doc.id)">
                                        Approve
                                    </button>
                                    <button v-if="canManage && doc.status !== 'rejected'" :disabled="rejectForm.processing" class="rounded-md border border-amber-300 px-2 py-1 text-xs text-amber-700 hover:bg-amber-50 dark:border-amber-700 dark:text-amber-400" @click="reject(doc.id)">
                                        Reject
                                    </button>
                                    <button v-if="doc.can_edit" :disabled="deleteForm.processing" class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400" @click="destroy(doc.id, doc.title)">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="documentation.length === 0">
                            <td colspan="6" class="px-4 py-6 text-center text-slate-500">No documentation posts yet. Create one to get started.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
