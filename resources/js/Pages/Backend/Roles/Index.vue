<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';

const page = usePage();

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

const permissionForms = reactive({});

const formFor = (role) => {
    if (!permissionForms[role.id]) {
        permissionForms[role.id] = useForm({ permissions: [...role.permissions] });
    }
    return permissionForms[role.id];
};

const savePermissions = (roleId) => {
    permissionForms[roleId].patch(route('roles.update', { id: roleId }), { preserveScroll: true });
};

const newRoleForm = useForm({ name: '' });

const createRole = () => {
    newRoleForm.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => newRoleForm.reset(),
    });
};

const deleteForm = useForm({});

const deleteRole = (roleId, name) => {
    if (!confirm(`Delete role "${name}"?`)) return;
    deleteForm.delete(route('roles.destroy', { id: roleId }), { preserveScroll: true });
};
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold">Roles & Permissions</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Define exactly what each role is allowed to manage.</p>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="page.props.flash?.success" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.errors?.role" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                {{ page.props.errors.role }}
            </div>

            <div class="space-y-4">
                <div v-for="role in roles" :key="role.id" class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold capitalize">{{ role.name }}</h2>
                            <p class="text-xs text-slate-500">{{ role.user_count }} user(s)</p>
                        </div>
                        <button
                            v-if="!role.protected"
                            :disabled="deleteForm.processing"
                            class="rounded-md border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400"
                            @click="deleteRole(role.id, role.name)"
                        >
                            Delete Role
                        </button>
                        <span v-else class="text-xs text-slate-400">Protected</span>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <label v-for="permission in permissions" :key="permission" class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                :value="permission"
                                v-model="formFor(role).permissions"
                                :disabled="role.protected"
                                class="rounded border-slate-300"
                            />
                            {{ permission.replaceAll('_', ' ') }}
                        </label>
                    </div>

                    <div v-if="!role.protected" class="mt-4">
                        <button
                            :disabled="formFor(role).processing"
                            class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                            @click="savePermissions(role.id)"
                        >
                            Save Permissions
                        </button>
                    </div>
                </div>
            </div>

            <form class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" @submit.prevent="createRole">
                <div>
                    <label class="mb-1 block text-sm">New Role Name</label>
                    <input v-model="newRoleForm.name" type="text" placeholder="e.g. reviewer" class="rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800" />
                    <p v-if="newRoleForm.errors.name" class="mt-1 text-xs text-red-600">{{ newRoleForm.errors.name }}</p>
                </div>
                <button type="submit" :disabled="newRoleForm.processing" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                    Create Role
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
