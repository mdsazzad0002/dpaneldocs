@extends('layouts.public')

@section('title', 'dPanel Documentation — Install, Configure & Operate')
@section('meta_description', 'Official dPanel documentation: installation, architecture, operations, the dpanel CLI, the drust service and API, backups, WHMCS integration, and the security policy.')
@section('canonical', route('docs.index'))

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'dPanel Documentation',
    'url' => route('docs.index'),
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => collect($sections)->flatten(1)->values()->map(fn ($page, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('docs.show', $page['slug']),
            'name' => $page['title'],
        ])->all(),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Documentation</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight">Learn dPanel</h1>
        <p class="mt-3 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
            Guides for installing, configuring, and running dPanel on your own server, plus reference material for the CLI and the drust API.
        </p>
        <form action="{{ route('docs.search') }}" method="GET" role="search" class="mt-8 flex max-w-xl gap-2">
            <label for="docs-search" class="sr-only">Search the documentation</label>
            <input id="docs-search" type="search" name="q" placeholder="Search, e.g. “SSL renewal” or “backup restore”" class="field py-3">
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>
</section>

<div class="mx-auto max-w-7xl space-y-14 px-4 py-14 sm:px-6 lg:px-8">
    @foreach ($sections as $section => $pages)
        <section aria-labelledby="section-{{ Str::slug($section) }}">
            <h2 id="section-{{ Str::slug($section) }}" class="text-xl font-bold tracking-tight">{{ $section }}</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($pages as $page)
                    <a href="{{ route('docs.show', $page['slug']) }}" class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700">
                        <h3 class="font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $page['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $page['description'] }}</p>
                        <span class="mt-3 inline-flex text-xs font-semibold text-blue-600 opacity-0 transition group-hover:opacity-100 dark:text-blue-400">Read guide &rarr;</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-8 dark:border-slate-800 dark:bg-slate-900/40 md:grid-cols-[1fr_auto] md:items-center">
        <div>
            <h2 class="text-xl font-bold">Can't find what you need?</h2>
            <p class="mt-1 text-slate-600 dark:text-slate-400">Open a support ticket and a person will get back to you by email.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('support.index') }}#ticket" class="btn-primary">Open a ticket</a>
            <a href="{{ config('site.repositories.panel') }}/issues" target="_blank" rel="noopener" class="btn-secondary">Ask on GitHub</a>
        </div>
    </section>
</div>
@endsection
