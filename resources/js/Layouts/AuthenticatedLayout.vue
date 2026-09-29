<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/theme';

const page = usePage();
const badges = computed(() => page.props.badges ?? {});
const flash = computed(() => page.props.flash?.status);

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
        label: 'Tickets',
        routeName: 'admin.tickets.index',
        active: () => route().current('admin.tickets.*'),
        badge: badges.value.tickets,
        icon: 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
    },
    {
        label: 'Reviews',
        routeName: 'admin.reviews.index',
        active: () => route().current('admin.reviews.*'),
        badge: badges.value.reviews,
        icon: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    },
    {
        label: 'Page feedback',
        routeName: 'admin.feedback.index',
        active: () => route().current('admin.feedback.*'),
        icon: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
    },
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
                    <span class="flex-1">{{ item.label }}</span>
                    <span
                        v-if="item.badge"
                        class="rounded-full bg-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-200"
                        :class="{ 'bg-white/20 text-white': item.active() }"
                    >{{ item.badge }}</span>
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
                        href="/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-400 sm:inline-flex"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View site
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
                <div
                    v-if="flash"
                    class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200"
                    role="status"
                >
                    {{ flash }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
