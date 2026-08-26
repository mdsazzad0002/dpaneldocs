@extends('layouts.public')

@section('title', 'dPanel — Server & Website Control Panel')
@section('meta_description', 'dPanel is a calm, open-source server and website control panel — deploy sites, manage servers, and read the docs, free forever.')
@section('canonical', route('home'))
@section('og_type', 'website')
@section('json_ld')
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => config('app.name', 'dPanel'),
    'url' => route('home'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => route('docs.public.index').'?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
]) !!}
@endsection

@section('hero')
<section class="relative overflow-hidden bg-gradient-to-b from-blue-50 to-slate-50 dark:from-slate-900 dark:to-slate-950">
    <div class="mx-auto xl:w-[90%] max-w-[1500px] px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8">
        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-6xl">
            A calm, open-source
            <span class="block text-blue-600 dark:text-blue-400">server & website control panel</span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
            Everything you need to set up, manage, and get the most out of Dpanel — release notes, how-to guides, and downloadable versions, free for everyone.
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('docs.public.index') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Browse Documentation
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-6 py-3 text-base font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Create an Account
                </a>
            @endauth
        </div>

        <div class="mx-auto mt-16 max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center gap-2 border-b border-slate-200 bg-slate-100 px-4 py-3 dark:border-slate-800 dark:bg-slate-800">
                <span class="h-3 w-3 rounded-full bg-red-400"></span>
                <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                <span class="h-3 w-3 rounded-full bg-green-400"></span>
                <span class="ml-3 truncate rounded bg-white px-3 py-1 text-xs text-slate-400 dark:bg-slate-900">dpanel.local</span>
            </div>
            <div class="grid grid-cols-1 gap-4 bg-slate-950 p-6 text-left sm:grid-cols-3">
                <div class="rounded-lg bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Documentation</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $stats['posts'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Published posts</p>
                </div>
                <div class="rounded-lg bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Categories</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $stats['categories'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Topics covered</p>
                </div>
                <div class="rounded-lg bg-slate-900 p-4">
                    <p class="text-xs text-slate-400">Total views</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $stats['views'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Across all guides</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<section class="grid gap-6 py-4 sm:grid-cols-3">
    <div class="rounded-xl border border-slate-200 p-6 dark:border-slate-800">
        <h2 class="text-lg font-semibold">Deploy sites in minutes</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Point Dpanel at your server and get a working site up fast, without wrestling with server config.</p>
    </div>
    <div class="rounded-xl border border-slate-200 p-6 dark:border-slate-800">
        <h2 class="text-lg font-semibold">Built for calm operations</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Monitoring, deployments, and site management designed to stay predictable under real production load.</p>
    </div>
    <div class="rounded-xl border border-slate-200 p-6 dark:border-slate-800">
        <h2 class="text-lg font-semibold">Free & source available</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Self-host it, read the source, or contribute — Dpanel is free and actively maintained.</p>
    </div>
</section>
@endsection
