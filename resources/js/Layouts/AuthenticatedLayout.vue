<script setup>
import { computed, ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/theme';

const page = usePage();
const badges = computed(() => page.props.badges ?? {});
const can = computed(() => page.props.auth.can ?? {});
const flash = computed(() => page.props.flash?.status);
// Errors that belong to the page rather than to a form field.
const pageErrors = computed(() => ['user', 'role'].map((key) => page.props.errors?.[key]).filter(Boolean));

const showingSidebar = ref(false);
const { isDark, toggle: toggleTheme } = useTheme();

// Desktop: the sidebar can shrink to icons only; remembered per browser.
const collapsed = ref(false);
try {
    collapsed.value = localStorage.getItem('sidebar') === 'collapsed';
} catch (e) {
    // storage unavailable, ignore
}
watch(collapsed, (value) => {
    try {
        localStorage.setItem('sidebar', value ? 'collapsed' : 'open');
    } catch (e) {
        // storage unavailable, ignore
    }
});

const icons = {
    dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    tickets: 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z',
    comments: 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155',
    reviews: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    feedback: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
    mail: 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
    docs: 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
    donations: 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
    users: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    roles: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    settings: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    profile: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
};

const sections = computed(() => [
    {
        title: null,
        items: [{ label: 'Dashboard', routeName: 'dashboard', match: 'dashboard', icon: icons.dashboard }],
    },
    {
        title: 'Help desk',
        items: [
            { label: 'Tickets', routeName: 'admin.tickets.index', match: 'admin.tickets.*', icon: icons.tickets, badge: badges.value.tickets, permission: 'tickets.manage' },
            { label: 'Comments', routeName: 'admin.comments.index', match: 'admin.comments.*', icon: icons.comments, badge: badges.value.comments, permission: 'comments.manage' },
            { label: 'Reviews', routeName: 'admin.reviews.index', match: 'admin.reviews.*', icon: icons.reviews, badge: badges.value.reviews, permission: 'reviews.manage' },
            { label: 'Mail', routeName: 'admin.mail.index', match: 'admin.mail.*', icon: icons.mail, permission: 'mail.send' },
        ],
    },
    {
        title: 'Content',
        items: [
            { label: 'Documentation', routeName: 'admin.docs.index', match: 'admin.docs.*', icon: icons.docs, permission: 'docs.sync' },
            { label: 'Page feedback', routeName: 'admin.feedback.index', match: 'admin.feedback.*', icon: icons.feedback, permission: 'feedback.view' },
            { label: 'Donations', routeName: 'admin.donations.index', match: 'admin.donations.*', icon: icons.donations, badge: badges.value.donations, permission: 'donations.manage' },
        ],
    },
    {
        title: 'Administration',
        items: [
            { label: 'Users', routeName: 'admin.users.index', match: 'admin.users.*', icon: icons.users, permission: 'users.manage' },
            { label: 'Roles & permissions', routeName: 'admin.roles.index', match: 'admin.roles.*', icon: icons.roles, permission: 'roles.manage' },
            { label: 'Settings', routeName: 'admin.settings.edit', match: 'admin.settings.*', icon: icons.settings, permission: 'settings.manage' },
            { label: 'Profile', routeName: 'profile.edit', match: 'profile.*', icon: icons.profile },
        ],
    },
]
    .map((section) => ({ ...section, items: section.items.filter((item) => !item.permission || can.value[item.permission]) }))
    .filter((section) => section.items.length));
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
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full flex-col overflow-y-auto bg-slate-900 transition-all duration-200 lg:translate-x-0"
            :class="{ 'translate-x-0': showingSidebar, 'lg:w-[4.5rem]': collapsed }"
        >
            <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-5">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo src="/icon-192.png" size-class="h-8 w-8" />
                    <span class="text-sm font-semibold tracking-wide text-white" :class="{ 'lg:hidden': collapsed }">{{ $page.props.appName ?? 'dPanel' }}</span>
                </Link>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                <div v-for="(section, i) in sections" :key="i" class="space-y-1">
                    <p
                        v-if="section.title"
                        class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500"
                        :class="{ 'lg:hidden': collapsed }"
                    >{{ section.title }}</p>
                    <Link
                        v-for="item in section.items"
                        :key="item.label"
                        :href="route(item.routeName)"
                        :title="collapsed ? item.label : null"
                        class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                        :class="route().current(item.match)
                            ? 'bg-blue-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                        @click="showingSidebar = false"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        <span class="flex-1 truncate" :class="{ 'lg:hidden': collapsed }">{{ item.label }}</span>
                        <span
                            v-if="item.badge"
                            class="rounded-full bg-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-200"
                            :class="[{ 'bg-white/20 text-white': route().current(item.match) }, collapsed ? 'lg:absolute lg:-right-0.5 lg:-top-0.5 lg:px-1.5 lg:text-[10px]' : '']"
                        >{{ item.badge }}</span>
                    </Link>
                </div>
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
                    <span :class="{ 'lg:hidden': collapsed }">Log Out</span>
                </Link>
                <button
                    type="button"
                    class="mt-1 hidden w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white lg:flex"
                    :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    @click="collapsed = !collapsed"
                >
                    <svg class="h-5 w-5 shrink-0 transition-transform" :class="{ 'rotate-180': collapsed }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                    </svg>
                    <span :class="{ 'lg:hidden': collapsed }">Collapse</span>
                </button>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex min-h-screen min-w-0 flex-1 flex-col transition-all duration-200" :class="collapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64'">
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
                                <span class="hidden text-left sm:block">
                                    <span class="block leading-tight">{{ $page.props.auth.user.name }}</span>
                                    <span v-if="$page.props.auth.user.role" class="block text-xs font-normal leading-tight text-slate-400">{{ $page.props.auth.user.role }}</span>
                                </span>
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
                <div
                    v-for="error in pageErrors"
                    :key="error"
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200"
                    role="alert"
                >
                    {{ error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
