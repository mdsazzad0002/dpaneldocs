<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SearchPalette from '@/Components/SearchPalette.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    category: {
        type: String,
        default: null,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const categories = computed(() => page.props.docCategories ?? []);
const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
            <!-- Row 1: logo · search · account -->
            <div class="mx-auto flex xl:w-[90%] max-w-[1500px] items-center gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
                <Link href="/" class="flex shrink-0 items-center gap-2 text-lg font-bold">
                    <ApplicationLogo src="/icon-192.png" size-class="h-8 w-8" />
                    <span>dPanel</span>
                </Link>

                <div class="flex flex-1 justify-center">
                    <SearchPalette />
                </div>

                <div class="flex shrink-0 items-center gap-3 text-sm">
                    <template v-if="user">
                        <Link
                            href="/dashboard"
                            class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hidden font-medium text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 sm:inline">Login</Link>
                        <Link
                            href="/register"
                            class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700"
                        >
                            Sign up
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Row 2: category strip -->
            <div class="border-t border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900/60">
                <nav class="mx-auto flex xl:w-[90%] max-w-[1500px] items-center gap-5 overflow-x-auto whitespace-nowrap px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 sm:px-6 lg:px-8">
                    <Link href="/" class="flex shrink-0 items-center gap-1.5 hover:text-blue-600 dark:hover:text-blue-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Home
                    </Link>
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="`/docs?category=${encodeURIComponent(category.name)}`"
                        class="shrink-0 hover:text-blue-600 dark:hover:text-blue-400"
                    >
                        {{ category.name }}
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Sub-header: current category -->
        <div v-if="category" class="border-b border-slate-200 bg-blue-50/70 dark:border-slate-800 dark:bg-blue-950/30">
            <div class="mx-auto flex xl:w-[90%] max-w-[1500px] items-center gap-2 px-4 py-2.5 text-sm sm:px-6 lg:px-8">
                <span class="text-slate-500 dark:text-slate-400">Category:</span>
                <span class="font-semibold text-blue-700 dark:text-blue-300">{{ category }}</span>
            </div>
        </div>

        <slot name="hero" />

        <main class="mx-auto w-full xl:w-[90%] max-w-[1500px] flex-1 px-4 py-10 sm:px-6 lg:px-8">
            <slot />
        </main>

        <footer class="relative overflow-hidden bg-[#03060f] text-slate-300">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_0%,rgba(16,185,129,0.14),transparent_35%),radial-gradient(circle_at_85%_100%,rgba(59,130,246,0.12),transparent_35%)]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-30 [background-image:linear-gradient(rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.035)_1px,transparent_1px)] [background-size:56px_56px]"></div>

            <div class="relative mx-auto xl:w-[90%] max-w-[1500px] px-4 py-16 sm:px-6 lg:px-8">
                <!-- CTA strip -->
                <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-white/10 bg-white/[0.04] p-8 backdrop-blur sm:flex-row sm:items-center">
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.3em] text-emerald-300/80">Open source</p>
                        <h3 class="mt-2 text-xl font-semibold text-white sm:text-2xl">Run your own dPanel, free forever.</h3>
                        <p class="mt-1 text-sm text-slate-400">Star the repos, ship a PR, or just read the docs.</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-3">
                        <a
                            href="https://github.com/mdsazzad0002/dpanel"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-emerald-400 to-cyan-400 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:from-emerald-300 hover:to-cyan-300"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 .5C5.65.5.5 5.66.5 12.03c0 5.1 3.29 9.43 7.86 10.96.58.1.79-.25.79-.56 0-.27-.01-1.17-.02-2.12-3.2.7-3.88-1.36-3.88-1.36-.52-1.34-1.28-1.7-1.28-1.7-1.04-.72.08-.71.08-.71 1.15.08 1.76 1.19 1.76 1.19 1.03 1.76 2.69 1.25 3.34.96.1-.75.4-1.25.73-1.54-2.56-.29-5.26-1.28-5.26-5.71 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.79 0c2.2-1.49 3.17-1.18 3.17-1.18.64 1.58.24 2.76.12 3.05.74.81 1.18 1.84 1.18 3.1 0 4.44-2.7 5.42-5.28 5.7.42.36.78 1.08.78 2.17 0 1.57-.01 2.83-.01 3.22 0 .31.21.67.8.56A10.53 10.53 0 0 0 23.5 12.03C23.5 5.66 18.35.5 12 .5Z" />
                            </svg>
                            Star on GitHub
                        </a>
                    </div>
                </div>

                <!-- Link columns -->
                <div class="mt-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="lg:col-span-2">
                        <Link href="/" class="flex items-center gap-2 text-lg font-bold text-white">
                            <ApplicationLogo src="/icon-192.png" size-class="h-8 w-8" />
                            <span>dPanel</span>
                        </Link>
                        <p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">
                            A focused, open-source server and website control panel — built for calm, reliable
                            production operations.
                        </p>
                        <div class="mt-5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                MIT Licensed
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-400/20 bg-sky-400/10 px-3 py-1 text-xs font-medium text-sky-300">
                                Actively maintained
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Product</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><Link href="/" class="text-slate-400 transition hover:text-emerald-300">Home</Link></li>
                            <li><Link href="/docs" class="text-slate-400 transition hover:text-emerald-300">Documentation</Link></li>
                            <li>
                                <Link
                                    :href="user ? '/dashboard' : '/login'"
                                    class="text-slate-400 transition hover:text-emerald-300"
                                >
                                    {{ user ? 'Dashboard' : 'Login' }}
                                </Link>
                            </li>
                            <li v-if="!user"><Link href="/register" class="text-slate-400 transition hover:text-emerald-300">Create account</Link></li>
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Source code</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li>
                                <a
                                    href="https://github.com/mdsazzad0002/dpanel"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group inline-flex items-center gap-1.5 text-slate-400 transition hover:text-emerald-300"
                                >
                                    dpanel
                                    <span class="text-slate-600 group-hover:text-emerald-400">&#8599;</span>
                                </a>
                                <p class="mt-0.5 text-xs text-slate-500">Server project</p>
                            </li>
                            <li class="pt-1.5">
                                <a
                                    href="https://github.com/mdsazzad0002/dpaneldocs"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group inline-flex items-center gap-1.5 text-slate-400 transition hover:text-emerald-300"
                                >
                                    dpaneldocs
                                    <span class="text-slate-600 group-hover:text-emerald-400">&#8599;</span>
                                </a>
                                <p class="mt-0.5 text-xs text-slate-500">Docs project</p>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Legal</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li><span class="text-slate-400">Privacy Policy</span></li>
                            <li><span class="text-slate-400">Terms &amp; Conditions</span></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row">
                    <p>&copy; {{ year }} dPanel. Built by D Engr Web.</p>
                    <slot name="footer">
                        <p>Documentation is free to browse. Sign in to submit a post.</p>
                    </slot>
                </div>
            </div>
        </footer>
    </div>
</template>
