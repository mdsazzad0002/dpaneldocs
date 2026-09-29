@php
    $siteName = config('site.name', 'dPanel');
    $title = trim($__env->yieldContent('title')) ?: $siteName.' — '.config('site.tagline');
    $description = trim($__env->yieldContent('meta_description')) ?: config('site.description');
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogImage = url('/dpanel_logo.png');
    $nav = [
        ['label' => 'Documentation', 'route' => 'docs.index', 'active' => request()->routeIs('docs.*')],
        ['label' => 'Support', 'route' => 'support.index', 'active' => request()->routeIs('support.*')],
        ['label' => 'Reviews', 'route' => 'reviews.index', 'active' => request()->routeIs('reviews.*')],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large')">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('site.company.name'),
        'url' => config('site.company.url'),
        'logo' => url('/icon-512.png'),
        'email' => config('site.support_email'),
        'sameAs' => [config('site.company.facebook'), config('site.repositories.panel')],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @stack('jsonld')
    @stack('head')

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}">

    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('theme'); } catch (e) {}
            var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/site.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-blue-600 focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-950/85">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 text-lg font-bold tracking-tight" aria-label="{{ $siteName }} home">
                <img src="/icon-192.png" alt="" width="32" height="32" class="h-8 w-8 rounded-lg">
                <span>{{ $siteName }}</span>
            </a>

            <nav class="hidden items-center gap-1 text-sm font-medium md:flex" aria-label="Main">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" @class([
                        'rounded-md px-3 py-2 transition',
                        'text-blue-600 dark:text-blue-400' => $item['active'],
                        'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' => ! $item['active'],
                    ]) @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <form action="{{ route('docs.search') }}" method="GET" role="search" class="ml-auto hidden max-w-xs flex-1 sm:block">
                <label for="site-search" class="sr-only">Search the documentation</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                    <input id="site-search" type="search" name="q" value="{{ request()->routeIs('docs.search') ? request('q') : '' }}" placeholder="Search docs…" data-search-input
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-12 text-sm placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-800 dark:bg-slate-900 dark:focus:bg-slate-900">
                    <kbd class="pointer-events-none absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded border border-slate-200 bg-white px-1.5 font-mono text-[10px] text-slate-400 dark:border-slate-700 dark:bg-slate-800 lg:block">/</kbd>
                </div>
            </form>

            <div class="ml-auto flex items-center gap-2 sm:ml-0">
                <a href="{{ config('site.repositories.panel') }}" target="_blank" rel="noopener" class="hidden rounded-md p-2 text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white lg:inline-flex" aria-label="dPanel on GitHub">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5C5.65.5.5 5.66.5 12.03c0 5.1 3.29 9.43 7.86 10.96.58.1.79-.25.79-.56 0-.27-.01-1.17-.02-2.12-3.2.7-3.88-1.36-3.88-1.36-.52-1.34-1.28-1.7-1.28-1.7-1.04-.72.08-.71.08-.71 1.15.08 1.76 1.19 1.76 1.19 1.03 1.76 2.69 1.25 3.34.96.1-.75.4-1.25.73-1.54-2.56-.29-5.26-1.28-5.26-5.71 0-1.26.45-2.29 1.19-3.1-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.18 1.18a11 11 0 0 1 5.79 0c2.2-1.49 3.17-1.18 3.17-1.18.64 1.58.24 2.76.12 3.05.74.81 1.18 1.84 1.18 3.1 0 4.44-2.7 5.42-5.28 5.7.42.36.78 1.08.78 2.17 0 1.57-.01 2.83-.01 3.22 0 .31.21.67.8.56A10.53 10.53 0 0 0 23.5 12.03C23.5 5.66 18.35.5 12 .5Z"/></svg>
                </a>
                <button type="button" data-theme-toggle class="rounded-md p-2 text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white" aria-label="Toggle dark mode">
                    <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9-5.998Z"/></svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                </button>
                <a href="{{ route('docs.show', 'installation') }}" class="hidden rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 md:inline-flex">Get started</a>
                <button type="button" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false" class="rounded-md p-2 text-slate-600 dark:text-slate-300 md:hidden" aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-slate-200 px-4 pb-4 pt-3 dark:border-slate-800 md:hidden">
            <form action="{{ route('docs.search') }}" method="GET" role="search" class="mb-3">
                <input type="search" name="q" placeholder="Search docs…" aria-label="Search the documentation" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900">
            </form>
            <nav class="grid gap-1 text-sm font-medium" aria-label="Mobile">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-900">{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ config('site.repositories.panel') }}" target="_blank" rel="noopener" class="rounded-md px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-900">GitHub</a>
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="border-b border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/60" role="status">
            <p class="mx-auto max-w-7xl px-4 py-3 text-sm font-medium text-emerald-800 dark:text-emerald-200 sm:px-6 lg:px-8">{{ session('status') }}</p>
        </div>
    @endif

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/40">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[1.4fr_repeat(4,1fr)]">
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-lg font-bold">
                        <img src="/icon-192.png" alt="" width="32" height="32" class="h-8 w-8 rounded-lg">
                        <span>{{ $siteName }}</span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-6 text-slate-600 dark:text-slate-400">
                        A free, self-hosted web hosting control panel built on Laravel, Vue, and Rust. Same software and same features for everyone.
                    </p>
                    <a href="{{ route('reviews.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-blue-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        <span class="text-amber-500">★</span> Share your experience
                    </a>
                </div>

                <div>
                    <p class="text-sm font-semibold">Product</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        <li><a href="{{ route('home') }}#features" class="hover:text-blue-600 dark:hover:text-blue-400">Features</a></li>
                        <li><a href="{{ route('docs.show', 'installation') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Installation</a></li>
                        <li><a href="{{ route('docs.show', 'architecture') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Architecture</a></li>
                        <li><a href="{{ config('site.repositories.panel') }}/releases" target="_blank" rel="noopener" class="hover:text-blue-600 dark:hover:text-blue-400">Releases</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold">Help</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        <li><a href="{{ route('docs.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Documentation</a></li>
                        <li><a href="{{ route('support.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Help center</a></li>
                        <li><a href="{{ route('support.index') }}#ticket" class="hover:text-blue-600 dark:hover:text-blue-400">Open a ticket</a></li>
                        <li><a href="{{ route('reviews.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Reviews</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold">Community</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        <li><a href="{{ config('site.repositories.panel') }}" target="_blank" rel="noopener" class="hover:text-blue-600 dark:hover:text-blue-400">GitHub</a></li>
                        <li><a href="{{ config('site.repositories.panel') }}/issues" target="_blank" rel="noopener" class="hover:text-blue-600 dark:hover:text-blue-400">Issue tracker</a></li>
                        <li><a href="{{ route('docs.show', 'contributing') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Contributing</a></li>
                        <li><a href="{{ config('site.company.facebook') }}" target="_blank" rel="noopener" class="hover:text-blue-600 dark:hover:text-blue-400">Facebook</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold">Legal</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        <li><a href="{{ route('privacy') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Terms of Use</a></li>
                        <li><a href="{{ config('site.repositories.panel') }}/blob/main/LICENSE" target="_blank" rel="noopener" class="hover:text-blue-600 dark:hover:text-blue-400">License</a></li>
                        <li><a href="{{ route('docs.show', 'security') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Security</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-3 border-t border-slate-200 pt-6 text-xs text-slate-500 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $siteName }}, a product of <a href="{{ config('site.company.url') }}" target="_blank" rel="noopener" class="font-medium hover:text-blue-600">{{ config('site.company.name') }}</a>. All rights reserved.</p>
                <p><a href="mailto:{{ config('site.support_email') }}" class="hover:text-blue-600">{{ config('site.support_email') }}</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
