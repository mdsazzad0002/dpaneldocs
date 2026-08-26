<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'dPanel'))</title>
    <meta name="description" content="@yield('meta_description', 'dPanel is a free, source-available server and website control panel.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:site_name" content="{{ config('app.name', 'dPanel') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', config('app.name', 'dPanel'))">
    <meta property="og:description" content="@yield('meta_description', 'dPanel is a free, source-available server and website control panel.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta name="twitter:card" content="summary_large_image">

    @hasSection('json_ld')
        <script type="application/ld+json">@yield('json_ld')</script>
    @endif

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('theme'); } catch (e) {}
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    <style>
        html.dark #theme-icon-sun { display: none; }
        html:not(.dark) #theme-icon-moon { display: none; }
    </style>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
        <div class="mx-auto flex xl:w-[90%] max-w-[1500px] items-center gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 text-lg font-bold">
                <img src="/icon-192.png" alt="dPanel" class="h-8 w-8 rounded">
                <span>dPanel</span>
            </a>

            <form action="{{ route('docs.public.index') }}" method="GET" class="flex flex-1 justify-center">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search docs..."
                    class="w-full max-w-lg rounded-md border border-slate-300 bg-slate-100 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                >
            </form>

            <div class="flex shrink-0 items-center gap-3 text-sm">
                <button
                    type="button"
                    id="theme-toggle"
                    role="switch"
                    aria-label="Toggle dark mode"
                    class="relative inline-flex h-8 w-14 shrink-0 items-center rounded-full border border-slate-300 bg-amber-100 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 dark:border-slate-700 dark:bg-slate-800"
                >
                    <span
                        id="theme-toggle-knob"
                        class="flex h-6 w-6 translate-x-1 items-center justify-center rounded-full bg-gradient-to-br from-amber-300 to-orange-400 text-amber-900 shadow-md transition-transform duration-300 dark:translate-x-7 dark:from-indigo-500 dark:to-slate-900 dark:text-indigo-200"
                    >
                        <svg id="theme-icon-sun" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-9.9a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 9a1 1 0 100 2h1a1 1 0 100-2h-1zM4.464 4.05a1 1 0 00-1.414 1.414l.707.707A1 1 0 005.17 4.757l-.707-.707zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm2.464 5.95a1 1 0 001.414 0l.707-.707a1 1 0 00-1.414-1.414l-.707.707a1 1 0 000 1.414zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
                        </svg>
                        <svg id="theme-icon-moon" class="hidden h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </span>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden font-medium text-slate-600 hover:text-blue-600 dark:text-slate-300 dark:hover:text-blue-400 sm:inline">Login</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-blue-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        Sign up
                    </a>
                @endauth
            </div>
        </div>
    </header>

    @hasSection('hero')
        @yield('hero')
    @endif

    <main class="mx-auto w-full xl:w-[90%] max-w-[1500px] flex-1 px-4 py-10 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <footer class="relative overflow-hidden bg-[#03060f] text-slate-300">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_15%_0%,rgba(16,185,129,0.14),transparent_35%),radial-gradient(circle_at_85%_100%,rgba(59,130,246,0.12),transparent_35%)]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-30 [background-image:linear-gradient(rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.035)_1px,transparent_1px)] [background-size:56px_56px]"></div>

        <div class="relative mx-auto xl:w-[90%] max-w-[1500px] px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-white/10 bg-white/[0.04] p-8 backdrop-blur sm:flex-row sm:items-center">
                <div>
                    <p class="font-mono text-[11px] uppercase tracking-[0.3em] text-emerald-300/80">Source available</p>
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

            <div class="mt-14">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-white">
                    <img src="/icon-192.png" alt="dPanel" class="h-8 w-8 rounded">
                    <span>dPanel</span>
                </a>
                <p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">
                    A focused, source-available server and website control panel — built for calm, reliable
                    production operations.
                </p>
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Free & Source Available
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-400/20 bg-sky-400/10 px-3 py-1 text-xs font-medium text-sky-300">
                        Actively maintained
                    </span>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-4 gap-6 sm:gap-10">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Product</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('home') }}" class="text-slate-400 transition hover:text-emerald-300">Home</a></li>
                        <li><a href="{{ route('docs.public.index') }}" class="text-slate-400 transition hover:text-emerald-300">Documentation</a></li>
                        <li>
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-slate-400 transition hover:text-emerald-300">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-slate-400 transition hover:text-emerald-300">Login</a>
                            @endauth
                        </li>
                        @guest
                            <li><a href="{{ route('register') }}" class="text-slate-400 transition hover:text-emerald-300">Create account</a></li>
                        @endguest
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
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Company</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="https://dengrweb.com" target="_blank" rel="noopener noreferrer" class="text-slate-400 transition hover:text-emerald-300">dengrweb.com</a></li>
                        <li><a href="https://www.facebook.com/dengrweblimited/" target="_blank" rel="noopener noreferrer" class="text-slate-400 transition hover:text-emerald-300">Facebook</a></li>
                        <li><a href="mailto:dev@dengrweb.com" class="text-slate-400 transition hover:text-emerald-300">dev@dengrweb.com</a></li>
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
                <p>&copy; {{ date('Y') }} dPanel, a product of <a href="https://dengrweb.com" target="_blank" rel="noopener noreferrer" class="text-slate-400 transition hover:text-emerald-300">D Engr Web</a>.</p>
                <p>Documentation is free to browse. Sign in to submit a post.</p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var toggle = document.getElementById('theme-toggle');
            if (!toggle) return;
            toggle.addEventListener('click', function () {
                var dark = !document.documentElement.classList.contains('dark');
                document.documentElement.classList.toggle('dark', dark);
                try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
            });
        })();
    </script>
</body>
</html>
