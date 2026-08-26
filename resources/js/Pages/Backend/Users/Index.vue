<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
});

const forms = Object.fromEntries(props.users.map((u) => [u.id, useForm({ role: u.role ?? 'contributor' })]));

const updateRole = (userId) => {
    forms[userId].patch(route('users.update-role', { id: userId }), { preserveScroll: true });
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Users</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Assign each user a role to control what they can manage.</p>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <select v-model="forms[user.id].role" class="rounded-md border border-slate-300 px-2 py-1 text-sm capitalize dark:border-slate-700 dark:bg-slate-800">
                                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                                </select>
                                <p v-if="forms[user.id].errors.role" class="mt-1 text-xs text-red-600">{{ forms[user.id].errors.role }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    :disabled="forms[user.id].processing"
                                    class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                                    @click="updateRole(user.id)"
                                >
                                    Save
                                </button>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No users found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
