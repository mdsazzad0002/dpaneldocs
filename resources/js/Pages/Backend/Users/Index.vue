<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const page = usePage();
const editing = ref(null);
const showing = ref(false);

const form = useForm({ name: '', email: '', password: '', role_id: '' });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.role_id = props.roles.find((r) => r.slug === 'support')?.id ?? '';
    showing.value = true;
};

const openEdit = (user) => {
    editing.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role_id = user.role_id ?? '';
    showing.value = true;
};

const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (showing.value = false) };
    form.transform((data) => ({ ...data, role_id: data.role_id || null }));
    if (editing.value) {
        form.put(route('admin.users.update', editing.value.id), options);
    } else {
        form.post(route('admin.users.store'), options);
    }
};

const destroy = (user) => {
    if (confirm(`Delete ${user.email}? They will no longer be able to sign in.`)) {
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    }
};

const search = ref(props.filters.search);
const applySearch = () => router.get(route('admin.users.index'), { search: search.value || undefined }, { preserveState: true });
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Users</h2>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <form @submit.prevent="applySearch">
                    <input v-model="search" type="search" placeholder="Search name or email…" class="w-64 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900" />
                </form>
                <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" @click="openCreate">Add user</button>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-4 py-3 font-medium">{{ user.name }} <span v-if="user.id === page.props.auth.user.id" class="text-xs font-normal text-slate-400">(you)</span></td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="user.role" class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="user.role.slug === 'admin' ? 'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'">{{ user.role.name }}</span>
                                    <span v-else class="text-xs text-slate-400">No access</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <button type="button" class="font-medium text-blue-600 hover:underline dark:text-blue-400" @click="openEdit(user)">Edit</button>
                                    <button v-if="user.id !== page.props.auth.user.id" type="button" class="ml-4 font-medium text-red-600 hover:underline" @click="destroy(user)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination :links="users.links" />
        </div>

        <Modal :show="showing" max-width="lg" @close="showing = false">
            <form class="space-y-4 p-6" @submit.prevent="save">
                <h3 class="text-lg font-semibold">{{ editing ? `Edit ${editing.name}` : 'Add user' }}</h3>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Name</span>
                    <input v-model="form.name" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Email</span>
                    <input v-model="form.email" type="email" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <span v-if="form.errors.email" class="mt-1 block text-xs text-red-600">{{ form.errors.email }}</span>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Password <span v-if="editing" class="font-normal text-slate-400">(leave blank to keep)</span></span>
                    <input v-model="form.password" type="password" :required="!editing" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    <span v-if="form.errors.password" class="mt-1 block text-xs text-red-600">{{ form.errors.password }}</span>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">Role</span>
                    <select v-model="form.role_id" class="w-full rounded-lg border border-slate-300 bg-white py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                        <option value="">No access to the panel</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                    </select>
                    <span v-if="form.errors.role_id" class="mt-1 block text-xs text-red-600">{{ form.errors.role_id }}</span>
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="showing = false">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Save</button>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
