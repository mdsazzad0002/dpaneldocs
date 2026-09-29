<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tickets: Object,
    filters: Object,
    statuses: Object,
    categories: Object,
    counts: Object,
});

const search = ref(props.filters.search ?? '');

const filter = (params) => {
    router.get(route('admin.tickets.index'), { status: props.filters.status, search: search.value, ...params }, {
        preserveState: true,
        replace: true,
    });
};

const when = (date) => new Date(date).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Support tickets</h2>
        </template>

        <div class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                        :class="!filters.status ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                        @click="filter({ status: null })"
                    >All</button>
                    <button
                        v-for="(label, key) in statuses"
                        :key="key"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
                        :class="filters.status === key ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                        @click="filter({ status: key })"
                    >{{ label }} <span class="opacity-70">{{ counts[key] ?? 0 }}</span></button>
                </div>

                <form class="flex gap-2" @submit.prevent="filter({})">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Reference, subject, email…"
                        class="w-64 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                    >
                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-700">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3">Ticket</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Topic</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Last activity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <Link :href="route('admin.tickets.show', ticket.id)" class="font-medium text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">{{ ticket.subject }}</Link>
                                <div class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                    <span class="font-mono">{{ ticket.reference }}</span>
                                    <span>· {{ ticket.replies_count }} replies</span>
                                    <StatusBadge v-if="ticket.priority === 'high'" status="high">urgent</StatusBadge>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ ticket.name }}</div>
                                <div class="text-xs text-slate-500">{{ ticket.email }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ categories[ticket.category] ?? ticket.category }}</td>
                            <td class="px-4 py-3"><StatusBadge :status="ticket.status" /></td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ when(ticket.last_activity_at) }}</td>
                        </tr>
                        <tr v-if="!tickets.data.length">
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">No tickets match.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="tickets.links" />
        </div>
    </AuthenticatedLayout>
</template>
