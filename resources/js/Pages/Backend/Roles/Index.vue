<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    roles: Array,
    groups: Object,
});

const labels = computed(() => Object.assign({}, ...Object.values(props.groups)));

const editing = ref(null);
const showing = ref(false);
const form = useForm({ name: '', description: '', permissions: [] });

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    showing.value = true;
};

const openEdit = (role) => {
    editing.value = role;
    form.clearErrors();
    form.name = role.name;
    form.description = role.description ?? '';
    form.permissions = [...role.permissions];
    showing.value = true;
};

const toggleGroup = (keys, on) => {
    const set = new Set(form.permissions);
    keys.forEach((key) => (on ? set.add(key) : set.delete(key)));
    form.permissions = [...set];
};

const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (showing.value = false) };
    if (editing.value) {
        form.put(route('admin.roles.update', editing.value.id), options);
    } else {
        form.post(route('admin.roles.store'), options);
    }
};

const destroy = (role) => {
    if (confirm(`Delete the role "${role.name}"?`)) {
        router.delete(route('admin.roles.destroy', role.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Roles & permissions" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Roles &amp; permissions</h2>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-sm text-slate-500">Give each staff member one role. A role decides which sections of the panel they can open. Users without a role cannot sign in to the panel.</p>
                <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" @click="openCreate">New role</button>
            </div>

            <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                <article v-for="role in roles" :key="role.id" class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold">{{ role.name }}</h3>
                            <p class="mt-0.5 text-sm text-slate-500">{{ role.description }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ role.users_count }} {{ role.users_count === 1 ? 'user' : 'users' }}</span>
                    </div>
                    <div class="mt-4 flex flex-1 flex-wrap content-start gap-1.5">
                        <span v-if="role.permissions.includes('*')" class="rounded-md bg-violet-100 px-2 py-1 text-xs font-medium text-violet-700 dark:bg-violet-950 dark:text-violet-300">Every permission</span>
                        <span v-for="perm in role.permissions.filter((p) => p !== '*')" :key="perm" class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300" :title="labels[perm]">{{ perm }}</span>
                        <span v-if="!role.permissions.length" class="text-xs text-slate-400">No permissions (dashboard only)</span>
                    </div>
                    <div class="mt-4 flex gap-4 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                        <button type="button" class="font-medium text-blue-600 hover:underline dark:text-blue-400" @click="openEdit(role)">Edit</button>
                        <button v-if="!role.is_system" type="button" class="font-medium text-red-600 hover:underline" @click="destroy(role)">Delete</button>
                    </div>
                </article>
            </div>
        </div>

        <Modal :show="showing" max-width="2xl" @close="showing = false">
            <form class="space-y-4 p-6" @submit.prevent="save">
                <h3 class="text-lg font-semibold">{{ editing ? `Edit ${editing.name}` : 'New role' }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">Name</span>
                        <input v-model="form.name" required maxlength="60" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                        <span v-if="form.errors.name" class="mt-1 block text-xs text-red-600">{{ form.errors.name }}</span>
                    </label>
                    <label class="block text-sm">
                        <span class="mb-1 block font-medium">Description</span>
                        <input v-model="form.description" maxlength="255" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950" />
                    </label>
                </div>

                <p v-if="editing?.is_system" class="rounded-lg bg-violet-50 px-3 py-2 text-sm text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">The Administrator role always has every permission.</p>

                <div v-else class="max-h-[50vh] space-y-4 overflow-y-auto pr-1">
                    <fieldset v-for="(perms, group) in groups" :key="group" class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                        <legend class="flex items-center gap-3 px-1 text-sm font-semibold">
                            {{ group }}
                            <button type="button" class="text-xs font-normal text-blue-600 hover:underline dark:text-blue-400" @click="toggleGroup(Object.keys(perms), true)">all</button>
                            <button type="button" class="text-xs font-normal text-slate-500 hover:underline" @click="toggleGroup(Object.keys(perms), false)">none</button>
                        </legend>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label v-for="(label, key) in perms" :key="key" class="flex items-start gap-2 text-sm">
                                <input v-model="form.permissions" type="checkbox" :value="key" class="mt-0.5 rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900" />
                                <span>{{ label }} <span class="block font-mono text-[11px] text-slate-400">{{ key }}</span></span>
                            </label>
                        </div>
                    </fieldset>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="showing = false">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">Save role</button>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
