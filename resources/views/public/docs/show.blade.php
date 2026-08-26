@extends('layouts.public')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonical)
@section('og_type', 'article')
@section('json_ld')
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'TechArticle',
    'headline' => $post['title'],
    'description' => $seoDescription,
    'url' => $canonical,
    'articleSection' => $post['category'],
    'datePublished' => optional($post['created_at_iso'])->toIso8601String(),
    'dateModified' => optional($post['updated_at'])->toIso8601String(),
    'author' => ['@type' => 'Organization', 'name' => config('app.name', 'dPanel')],
    'publisher' => ['@type' => 'Organization', 'name' => config('app.name', 'dPanel')],
])) !!}
@endsection

@section('content')
<div class="grid grid-cols-1 gap-8 lg:grid-cols-[240px_minmax(0,1fr)_220px]">
    <aside class="hidden lg:block">
        @include('public.docs._sidebar', ['sidebar' => $sidebar, 'activeSlug' => $post['slug']])
    </aside>

    <div class="min-w-0">
        <a href="{{ route('docs.public.index') }}" class="mb-6 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400 lg:hidden">
            &larr; All documentation
        </a>

        <article>
            @if ($post['category'])
                <span class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    {{ $post['category'] }}
                </span>
            @endif
            <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $post['title'] }}</h1>
            <div class="mt-2 flex items-center gap-3 text-sm text-slate-500">
                <span>{{ $post['created_at'] }}</span>
                <span>·</span>
                <span>{{ $post['views'] }} views</span>
            </div>

            <div class="mt-8 max-w-none text-base leading-7 text-slate-700 dark:text-slate-300">
                @foreach ($blocks as $block)
                    @switch($block['type'])
                        @case('h1')
                            <h2 id="{{ $block['id'] }}" class="mb-4 mt-10 scroll-mt-24 text-2xl font-bold text-slate-900 dark:text-white">{{ $block['text'] }}</h2>
                            @break
                        @case('h2')
                            <h3 id="{{ $block['id'] }}" class="mb-3 mt-8 scroll-mt-24 text-xl font-semibold text-slate-900 dark:text-white">{{ $block['text'] }}</h3>
                            @break
                        @case('h3')
                            <h4 id="{{ $block['id'] }}" class="mb-2 mt-6 scroll-mt-24 text-lg font-semibold text-slate-900 dark:text-white">{{ $block['text'] }}</h4>
                            @break
                        @case('ul')
                            <ul class="mb-4 list-disc space-y-1 pl-6">
                                @foreach ($block['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                            @break
                        @default
                            <p class="mb-4">{{ $block['text'] }}</p>
                    @endswitch
                @endforeach
            </div>
        </article>

        @if (count($post['versions']))
            <section class="mt-12 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="mb-1 text-lg font-semibold">Versions</h2>
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">Every release is listed below — pick whichever one you need.</p>
                <ul class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($post['versions'] as $v)
                        <li class="py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="font-medium">Version {{ $v['version'] }}</div>
                                    @if ($v['changelog'])
                                        <div class="text-sm text-slate-600 dark:text-slate-400">{{ $v['changelog'] }}</div>
                                    @endif
                                    <div class="text-xs text-slate-500">{{ number_format($v['file_size'] / 1024, 0) }} KB · {{ $v['downloads'] }} downloads · {{ $v['created_at'] }}</div>
                                </div>
                                <a
                                    href="{{ route('docs.public.download', ['slug' => $post['slug'], 'versionId' => $v['id']]) }}"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                >
                                    Download ZIP
                                </a>
                            </div>

                            @if ($v['install_guide'])
                                <details class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 dark:border-slate-800 dark:bg-slate-800/50">
                                    <summary class="cursor-pointer text-sm font-medium text-blue-600 dark:text-blue-400">Installation guide for {{ $v['version'] }}</summary>
                                    <div class="prose-docs mt-3 text-sm leading-6 text-slate-700 dark:text-slate-300">
                                        @foreach (\App\Support\MarkdownLite::parse($v['install_guide']) as $block)
                                            @switch($block['type'])
                                                @case('h1')
                                                    <h3 class="mb-2 mt-4 text-base font-semibold text-slate-900 dark:text-white">{{ $block['text'] }}</h3>
                                                    @break
                                                @case('h2')
                                                @case('h3')
                                                    <h4 class="mb-2 mt-3 font-semibold text-slate-900 dark:text-white">{{ $block['text'] }}</h4>
                                                    @break
                                                @case('ul')
                                                    <ul class="mb-2 list-disc space-y-1 pl-5">
                                                        @foreach ($block['items'] as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                    @break
                                                @default
                                                    <p class="mb-2">{{ $block['text'] }}</p>
                                            @endswitch
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>

    @if (count($toc))
        <aside class="hidden lg:block">
            <div class="sticky top-24 text-sm">
                <p class="mb-3 font-semibold text-slate-900 dark:text-white">On this page</p>
                <ul class="space-y-2 border-l border-slate-200 dark:border-slate-800">
                    @foreach ($toc as $item)
                        <li>
                            <a
                                href="#{{ $item['id'] }}"
                                class="-ml-px block border-l-2 border-transparent py-0.5 text-slate-500 transition hover:border-slate-300 hover:text-slate-900 dark:text-slate-400 dark:hover:border-slate-700 dark:hover:text-slate-100 {{ $item['type'] === 'h1' ? 'pl-3' : 'pl-5' }}"
                            >
                                {{ $item['text'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    @endif
</div>
@endsection
