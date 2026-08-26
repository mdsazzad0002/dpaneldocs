<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/theme';

const page = usePage();
const can = (permission) => !!page.props.auth?.user?.[`can_${permission}`];

const showingSidebar = ref(false);
const { isDark, toggle: toggleTheme } = useTheme();

const navItems = computed(() => [
    {
        label: 'Dashboard',
        routeName: 'dashboard',
        active: () => route().current('dashboard'),
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
        label: 'Documentation',
        routeName: 'documentation.index',
        active: () => route().current('documentation.*'),
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    ...(can('manage_versions') ? [{
        label: 'Versions',
        routeName: 'documentation.versions.index',
        active: () => route().current('documentation.versions.index'),
        icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
    }] : []),
    ...(can('manage_categories') ? [{
        label: 'Categories',
        routeName: 'categories.index',
        active: () => route().current('categories.*'),
        icon: 'M7 7h.01M7 3h5.586a1 1 0 01.707.293l7.414 7.414a1 1 0 010 1.414l-7.586 7.586a1 1 0 01-1.414 0L4.293 12.293A1 1 0 014 11.586V5a2 2 0 012-2z',
    }] : []),
    ...(can('manage_users') ? [{
        label: 'Users',
        routeName: 'users.index',
        active: () => route().current('users.*'),
        icon: 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4',
    }] : []),
    ...(can('manage_roles') ? [{
        label: 'Roles',
        routeName: 'roles.index',
        active: () => route().current('roles.*'),
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    }] : []),
    {
        label: 'Profile',
        routeName: 'profile.edit',
        active: () => route().current('profile.*'),
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
]);
</script>

<template>
    <div class="flex min-h-screen bg-slate-100 dark:bg-slate-950">
        <!-- Sidebar backdrop (mobile) -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            @click="showingSidebar = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full flex-col overflow-y-auto bg-slate-900 transition-transform duration-200 lg:translate-x-0"
            :class="{ 'translate-x-0': showingSidebar }"
        >
            <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-5">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo src="/icon-192.png" size-class="h-8 w-8" />
                    <span class="text-sm font-semibold tracking-wide text-white">{{ $page.props.appName ?? 'dPanel' }}</span>
                </Link>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                    :class="item.active()
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-slate-800 p-3">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out
                </Link>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-20 flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="rounded-md p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden"
                        @click="showingSidebar = !showingSidebar"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div v-if="$slots.header">
                        <slot name="header" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="/docs"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-400 sm:inline-flex"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Documentation
                    </a>

                    <!-- Theme toggle -->
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="isDark"
                        aria-label="Toggle dark mode"
                        @click="toggleTheme"
                        class="relative inline-flex h-9 w-16 shrink-0 items-center rounded-full border transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                        :class="isDark
                            ? 'border-slate-700 bg-slate-800'
                            : 'border-amber-200 bg-amber-100'"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full shadow-md transition-transform duration-300"
                            :class="isDark
                                ? 'translate-x-8 bg-gradient-to-br from-indigo-500 to-slate-900 text-indigo-200'
                                : 'translate-x-1 bg-gradient-to-br from-amber-300 to-orange-400 text-amber-900'"
                        >
                            <svg v-if="isDark" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                            <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-9.9a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 9a1 1 0 100 2h1a1 1 0 100-2h-1zM4.464 4.05a1 1 0 00-1.414 1.414l.707.707A1 1 0 005.17 4.757l-.707-.707zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm2.464 5.95a1 1 0 001.414 0l.707-.707a1 1 0 00-1.414-1.414l-.707.707a1 1 0 000 1.414zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
                            </svg>
                        </span>
                    </button>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                                <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
