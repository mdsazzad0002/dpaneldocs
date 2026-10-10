@extends('layouts.public')

@section('title', 'dPanel — Free, Self-Hosted Web Hosting Control Panel')
@section('meta_description', config('site.description'))
@section('canonical', route('home'))

@php
    $features = [
        ['Websites', 'Per-site Linux accounts and PHP-FPM pools, multiple PHP versions, one-click WordPress and Laravel, Git deployments, Node.js and Python apps.', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.5 0 4-4 4-9s-1.5-9-4-9-4 4-4 9 1.5 9 4 9ZM3.5 9h17M3.5 15h17'],
        ['Edge gateway', 'A Rust HTTP/TLS server with SNI certificates, static files, PHP-FPM dispatch, compression, and live config reloads.', 'M13 10V3L4 14h7v7l9-11h-7Z'],
        ['Automatic SSL', 'Let\'s Encrypt certificates issued, validated, and renewed for every site without lifting a finger.', 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z'],
        ['Databases', 'MySQL/MariaDB and PostgreSQL with per-database users, phpMyAdmin and pgAdmin, and remote-access rules.', 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125'],
        ['Email', 'Postfix, Dovecot, and Roundcube with mailbox provisioning and delivery diagnostics.', 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-9.75 6.75L2.25 6.75'],
        ['DNS', 'Authoritative DNS through PowerDNS with automatic zone reconciliation.', 'M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3'],
        ['File manager', 'Account-scoped file manager with upload, zip/unzip, permission repair, and a trash can.', 'M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z'],
        ['Backups & migration', 'Scheduled backups, portable restore packages, and imports from cPanel and CyberPanel.', 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
        ['Operations', 'Cron jobs, FTP accounts, Redis, monitoring, security scans, and a server task runner.', 'M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75'],
        ['Business ready', 'Resellers, package plans, roles and permissions, and WHMCS billing integration.', 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0'],
    ];
    $faq = config('site.faq');
@endphp

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => config('site.name'),
    'url' => route('home'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('docs.search').'?q={search_term_string}'],
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => config('site.name'),
    'description' => config('site.description'),
    'url' => route('home'),
    'applicationCategory' => 'DeveloperApplication',
    'applicationSubCategory' => 'Web hosting control panel',
    'operatingSystem' => 'Linux',
    'downloadUrl' => config('site.repositories.panel'),
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
    'publisher' => ['@type' => 'Organization', 'name' => config('site.company.name'), 'url' => config('site.company.url')],
    'aggregateRating' => $rating['count'] > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => $rating['average'],
        'ratingCount' => $rating['count'],
        'bestRating' => 5,
        'worstRating' => 1,
    ] : null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faq)->map(fn ($item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(37,99,235,0.12),transparent_60%)] dark:bg-[radial-gradient(ellipse_at_top,rgba(59,130,246,0.18),transparent_60%)]"></div>
    <div class="pointer-events-none absolute inset-0 opacity-40 [background-image:linear-gradient(rgba(148,163,184,0.12)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.12)_1px,transparent_1px)] [background-size:48px_48px] [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>

    <div class="relative mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 sm:pt-24 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <a href="{{ config('site.repositories.panel') }}/releases" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-xs font-medium text-slate-600 shadow-sm backdrop-blur transition hover:border-blue-300 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Free forever · Laravel, Vue &amp; Rust
                <span aria-hidden="true">&rarr;</span>
            </a>

            <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
                The free, self-hosted
                <span class="bg-gradient-to-r from-blue-600 to-cyan-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-cyan-300">hosting control panel</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-400">
                Manage websites, databases, email, DNS, SSL, and backups on your own Linux server.
                No license fees, no feature locks, and the same updates for everyone.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('docs.show', 'installation') }}" class="btn-primary px-6 py-3 text-base">
                    Install dPanel
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <a href="{{ route('docs.index') }}" class="btn-secondary px-6 py-3 text-base">Read the docs</a>
            </div>

            @if ($rating['count'] > 0)
                <a href="{{ route('reviews.index') }}" class="mt-6 inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    @include('public.partials.stars', ['value' => $rating['average']])
                    <span><strong class="font-semibold text-slate-900 dark:text-white">{{ number_format($rating['average'], 1) }}</strong> from {{ $rating['count'] }} {{ Str::plural('review', $rating['count']) }}</span>
                </a>
            @endif
        </div>

        <div class="mx-auto mt-14 grid max-w-8xl gap-6 md:grid-cols-2">
            <div class="min-w-0 overflow-hidden rounded-xl border border-slate-800 bg-slate-950 text-left shadow-2xl shadow-blue-900/10">
                <div class="flex items-center justify-between border-b border-slate-800 px-4 py-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="ml-3 font-mono text-xs text-slate-500">Install & update</span>
                    </div>
                    <button type="button" data-copy="#install-command" class="rounded-md border border-slate-700 px-2 py-1 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">Copy</button>
                </div>
                <pre class="overflow-x-auto p-5 font-mono text-[13px] leading-7 text-slate-100"><code id="install-command">{{ config('site.install_command') }}</code></pre>
            </div>

            <div class="min-w-0 overflow-hidden rounded-xl border border-slate-800 bg-slate-950 text-left shadow-2xl shadow-blue-900/10">
                <div class="flex items-center justify-between border-b border-slate-800 px-4 py-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="h-3 w-3 rounded-full bg-slate-700"></span>
                        <span class="ml-3 font-mono text-xs text-slate-500">Deploy & development</span>
                    </div>
                    <button type="button" data-copy="#install-command-2" class="rounded-md border border-slate-700 px-2 py-1 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">Copy</button>
                </div>
                <pre class="overflow-x-auto p-5 font-mono text-[13px] leading-7 text-slate-100"><code id="install-command-2">{{ config('site.development_command') }}</code></pre>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="scroll-mt-16 py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Everything in one panel</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">All the tools to run a hosting server</h2>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">Every feature ships to every user. There is no paid tier and nothing is held back.</p>
        </div>

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach ($features as [$name, $text, $icon])
                <div class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-slate-200/60 dark:border-slate-800 dark:bg-slate-900 dark:hover:shadow-none">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </div>
                    <h3 class="mt-4 font-semibold">{{ $name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- PCs to donate --}}
@if ($pcs)
<section id="pcs" class="scroll-mt-16 border-t border-slate-200 bg-gradient-to-b from-amber-50 to-white py-20 dark:border-slate-800 dark:from-amber-950/20 dark:to-slate-950 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Support dPanel</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Help us buy a development PC</h2>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">dPanel is built on an old machine. Pick a PC below to see what it is for and how to donate towards it.</p>
        </div>

        <div class="mt-14 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($pcs as $slug => $pc)
                <a href="{{ route('donate.pc', $slug) }}" class="group flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-5 text-center transition hover:-translate-y-0.5 hover:border-amber-400 hover:shadow-lg hover:shadow-amber-200/40 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-500 dark:hover:shadow-none">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition group-hover:bg-amber-100 dark:bg-amber-950/60 dark:text-amber-400">
                        @include('public.partials.pc-icon', ['class' => 'h-8 w-8'])
                    </span>
                    <span class="mt-4 font-semibold">{{ $pc['name'] }}</span>
                    <span class="mt-1 text-xs leading-5 text-slate-500">{{ $pc['specs'] }}</span>
                    <span class="mt-3 text-sm font-semibold text-amber-600 dark:text-amber-400">{{ \App\Support\Donations::format((float) $pc['price'], 'BDT') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Architecture --}}
<section class="border-y border-slate-200 bg-slate-50 py-20 dark:border-slate-800 dark:bg-slate-900/40 sm:py-24">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Secure by design</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">The panel never runs root commands itself</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600 dark:text-slate-400">
                The web panel handles users and workflows. Small Rust services carry out privileged host operations
                through a validated localhost API, and serve public website traffic.
            </p>
            <dl class="mt-8 space-y-5">
                @foreach ([
                    ['dpanel', 'Laravel + Vue panel: UI, authentication, authorization, records, and queues.'],
                    ['drust', 'Rust privileged API on 127.0.0.1 and the public edge gateway on ports 80 and 443.'],
                    ['dscript', 'Shell toolkit that installs, updates, diagnoses, and repairs the server.'],
                ] as [$name, $text])
                    <div class="flex gap-4">
                        <dt class="w-20 shrink-0 font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $name }}</dt>
                        <dd class="text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $text }}</dd>
                    </div>
                @endforeach
            </dl>
            <a href="{{ route('docs.show', 'architecture') }}" class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400">Read the architecture guide <span aria-hidden="true">&rarr;</span></a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950 p-6 shadow-xl">
<pre class="font-mono text-[12.5px] leading-6 text-slate-300">             Browser
                │
                ▼
   ┌──────────────────────────┐
   │   <span class="text-cyan-300">edge-gateway.service</span>   │  public :80 / :443
   └──────────────────────────┘
        │ static · PHP-FPM · panel
        ▼
   ┌──────────────────────────┐      ┌────────────────────┐
   │     <span class="text-blue-300">dpanel (Laravel)</span>     │ ───▶ │   <span class="text-emerald-300">drust.service</span>    │
   │  UI · auth · queues      │token │  127.0.0.1:9500    │
   └──────────────────────────┘      └────────────────────┘
                                               │
                           files · users · databases · SSL</pre>
        </div>
    </div>
</section>

{{-- Support model --}}
<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Pricing</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">One product. One release channel. Free.</h2>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">Paid support covers human work only, and only if you ask for it.</p>
        </div>

        <div class="mx-auto mt-12 grid max-w-4xl gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-8 dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold">Self-service</h3>
                <p class="mt-1 text-sm text-slate-500">Everything you need to run it yourself.</p>
                <p class="mt-6 text-4xl font-bold tracking-tight">$0</p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    @foreach (['All features, no limits', 'All updates, forever', 'Full documentation', 'Community help on GitHub'] as $item)
                        <li class="flex gap-3"><span class="text-emerald-500" aria-hidden="true">✓</span>{{ $item }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('docs.show', 'installation') }}" class="btn-secondary mt-8 w-full">Install now</a>
            </div>
            <div class="relative rounded-2xl border-2 border-blue-600 bg-white p-8 shadow-xl shadow-blue-600/10 dark:bg-slate-900">
                <span class="absolute -top-3 left-8 rounded-full bg-blue-600 px-3 py-0.5 text-xs font-semibold text-white">Optional</span>
                <h3 class="text-lg font-semibold">Supported</h3>
                <p class="mt-1 text-sm text-slate-500">The same free software, plus an expert on call.</p>
                <p class="mt-6 text-4xl font-bold tracking-tight">Free <span class="text-base font-medium text-slate-500">+ paid help</span></p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    @foreach (['Installation and migration', 'Troubleshooting', 'Priority response', 'Managed operations'] as $item)
                        <li class="flex gap-3"><span class="text-emerald-500" aria-hidden="true">✓</span>{{ $item }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('support.index') }}#ticket" class="btn-primary mt-8 w-full">Contact support</a>
            </div>
        </div>
    </div>
</section>

{{-- Reviews --}}
<section class="border-y border-slate-200 bg-slate-50 py-20 dark:border-slate-800 dark:bg-slate-900/40 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Reviews</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">What people say about dPanel</h2>
            </div>
            <a href="{{ route('reviews.index') }}#write-review" class="btn-secondary">Write a review</a>
        </div>

        @if ($reviews->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-lg font-semibold">Be the first to review dPanel</p>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Running dPanel on your server? Tell others how it went.</p>
                <a href="{{ route('reviews.index') }}#write-review" class="btn-primary mt-6">Share your experience</a>
            </div>
        @else
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($reviews as $review)
                    @include('public.partials.review-card', ['review' => $review])
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight sm:text-4xl">Frequently asked questions</h2>
        <div class="mt-10 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            @foreach ($faq as $item)
                <details class="group px-6 py-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium">
                        {{ $item['q'] }}
                        <span class="text-slate-400 transition group-open:rotate-45" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
            Still have questions? <a href="{{ route('support.index') }}" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">Visit the help center</a>.
        </p>
    </div>
</section>

{{-- CTA --}}
<section class="px-4 pb-20 sm:px-6 lg:px-8">
    <div class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl bg-slate-950 px-6 py-16 text-center sm:px-12">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_0%,rgba(59,130,246,0.35),transparent_45%),radial-gradient(circle_at_80%_100%,rgba(16,185,129,0.25),transparent_45%)]"></div>
        <div class="relative">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Run your own hosting panel today</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-slate-300">One command on a fresh Linux server. Free forever.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('docs.show', 'installation') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 font-semibold text-slate-900 transition hover:bg-slate-100">Get started</a>
                <a href="{{ config('site.repositories.panel') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-white/20 px-6 py-3 font-semibold text-white transition hover:bg-white/10">Star on GitHub</a>
            </div>
        </div>
    </div>
</section>
@endsection
