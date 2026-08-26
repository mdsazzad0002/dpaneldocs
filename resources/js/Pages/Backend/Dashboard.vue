<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const stats = [
    { label: 'Documentation Posts', value: '—', hint: 'View and manage', href: 'documentation.index', accent: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' },
    { label: 'Public Docs Site', value: '/docs', hint: 'Browse published docs', href: 'docs.public.index', accent: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' },
    { label: 'Your Profile', value: page.props.auth.user.name, hint: 'Account settings', href: 'profile.edit', accent: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Dashboard</h2>
        </template>

        <div class="space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <h1 class="text-xl font-semibold">Welcome back, {{ page.props.auth.user.name }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Here's a quick overview of your panel.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="stat in stats"
                    :key="stat.label"
                    :href="route(stat.href)"
                    class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg text-sm font-bold" :class="stat.accent">
                        {{ stat.label.charAt(0) }}
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</div>
                    <div class="mt-1 text-lg font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ stat.value }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ stat.hint }}</div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
