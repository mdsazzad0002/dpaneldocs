@extends('layouts.public')

@section('title', $page['title'].' — dPanel Documentation')
@section('meta_description', $page['description'])
@section('canonical', route('docs.show', $page['slug']))
@section('og_type', 'article')

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'TechArticle',
    'headline' => $page['title'],
    'description' => $page['description'],
    'url' => route('docs.show', $page['slug']),
    'mainEntityOfPage' => route('docs.show', $page['slug']),
    'articleSection' => $page['section'],
    'dateModified' => $page['updated_at'] ? date(DATE_ATOM, $page['updated_at']) : null,
    'inLanguage' => 'en',
    'about' => ['@type' => 'SoftwareApplication', 'name' => config('site.name'), 'applicationCategory' => 'DeveloperApplication', 'operatingSystem' => 'Linux'],
    'author' => ['@type' => 'Organization', 'name' => config('site.company.name'), 'url' => config('site.company.url')],
    'publisher' => ['@type' => 'Organization', 'name' => config('site.company.name'), 'logo' => ['@type' => 'ImageObject', 'url' => url('/icon-512.png')]],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Documentation', 'item' => route('docs.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $page['title'], 'item' => route('docs.show', $page['slug'])],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[220px_minmax(0,1fr)] lg:px-8 xl:grid-cols-[220px_minmax(0,1fr)_200px]">
    <aside class="hidden lg:block">
        <div class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto pb-6 pr-2">
            @include('public.docs._sidebar', ['sections' => $sections, 'active' => $page['slug']])
        </div>
    </aside>

    <div class="min-w-0">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li><a href="{{ route('docs.index') }}" class="hover:text-slate-900 dark:hover:text-white">Docs</a></li>
                <li aria-hidden="true">/</li>
                <li>{{ $page['section'] }}</li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-slate-900 dark:text-white" aria-current="page">{{ $page['title'] }}</li>
            </ol>
        </nav>

        <details class="mt-4 rounded-lg border border-slate-200 dark:border-slate-800 lg:hidden">
            <summary class="cursor-pointer px-4 py-2.5 text-sm font-medium">Browse documentation</summary>
            <div class="border-t border-slate-200 p-4 dark:border-slate-800">
                @include('public.docs._sidebar', ['sections' => $sections, 'active' => $page['slug']])
            </div>
        </details>

        <article class="mt-6">
            <header class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $page['title'] }}</h1>
                <p class="mt-3 text-lg text-slate-600 dark:text-slate-400">{{ $page['description'] }}</p>
            </header>

            <div class="doc-prose">
                {!! $page['html'] !!}
            </div>
        </article>

        <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-6 text-sm text-slate-500 dark:border-slate-800">
            <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                Edit this page on GitHub
            </a>
            @if ($page['updated_at'])
                <span>Last updated <time datetime="{{ date('Y-m-d', $page['updated_at']) }}">{{ date('F j, Y', $page['updated_at']) }}</time></span>
            @endif
        </div>

        {{-- Was this page helpful? --}}
        <form action="{{ route('docs.feedback') }}" method="POST" data-feedback class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/60">
            @csrf
            <input type="hidden" name="page" value="{{ $page['slug'] }}">
            <input type="hidden" name="helpful" value="" data-feedback-value>
            <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            @if (session('feedback'))
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">{{ session('feedback') }}</p>
            @else
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-sm font-medium">Was this page helpful?</p>
                    <button type="submit" name="helpful" value="1" aria-pressed="false" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium transition hover:border-emerald-400 aria-pressed:border-emerald-500 aria-pressed:bg-emerald-50 aria-pressed:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:aria-pressed:bg-emerald-950">👍 Yes</button>
                    <button type="submit" name="helpful" value="0" aria-pressed="false" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium transition hover:border-red-400 aria-pressed:border-red-500 aria-pressed:bg-red-50 aria-pressed:text-red-700 dark:border-slate-700 dark:bg-slate-900 dark:aria-pressed:bg-red-950">👎 No</button>
                </div>
                <div class="mt-4 hidden" data-feedback-comment>
                    <label for="feedback-comment" class="field-label">Anything we should add or fix? <span class="font-normal text-slate-500">(optional)</span></label>
                    <textarea id="feedback-comment" name="comment" rows="3" maxlength="1000" class="field" placeholder="Tell us what was missing or unclear…"></textarea>
                    <button type="submit" class="btn-primary mt-3">Send feedback</button>
                </div>
            @endif
        </form>

        <nav class="mt-8 grid gap-4 sm:grid-cols-2" aria-label="Pagination">
            @if ($page['previous'])
                <a href="{{ route('docs.show', $page['previous']['slug']) }}" rel="prev" class="group rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 dark:border-slate-800 dark:hover:border-blue-700">
                    <span class="text-xs text-slate-500">&larr; Previous</span>
                    <span class="mt-1 block font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $page['previous']['title'] }}</span>
                </a>
            @else
                <span></span>
            @endif
            @if ($page['next'])
                <a href="{{ route('docs.show', $page['next']['slug']) }}" rel="next" class="group rounded-xl border border-slate-200 p-4 text-right transition hover:border-blue-300 dark:border-slate-800 dark:hover:border-blue-700">
                    <span class="text-xs text-slate-500">Next &rarr;</span>
                    <span class="mt-1 block font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $page['next']['title'] }}</span>
                </a>
            @endif
        </nav>
    </div>

    @if (count($page['toc']))
        <aside class="hidden xl:block">
            <div class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto text-sm" data-toc>
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">On this page</p>
                <ul class="space-y-1 border-l border-slate-200 dark:border-slate-800">
                    @foreach ($page['toc'] as $item)
                        <li>
                            <a href="#{{ $item['id'] }}" class="-ml-px block border-l border-transparent py-1 text-slate-600 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white {{ $item['level'] === 3 ? 'pl-6 text-[13px]' : 'pl-4' }}">{{ $item['text'] }}</a>
                        </li>
                    @endforeach
                </ul>
                <a href="#main" class="mt-6 inline-flex text-xs text-slate-500 hover:text-slate-900 dark:hover:text-white">Back to top &uarr;</a>
            </div>
        </aside>
    @endif
</div>
@endsection
