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

        {{-- Comments --}}
        <section id="comments" class="mt-10 scroll-mt-24">
            <h2 class="text-xl font-bold tracking-tight">Questions &amp; comments <span class="text-base font-normal text-slate-500">({{ $comments->count() }})</span></h2>

            @if ($comments->isNotEmpty())
                <ol class="mt-5 space-y-4">
                    @foreach ($comments as $comment)
                        <li id="comment-{{ $comment->id }}" class="scroll-mt-24 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                            <p class="text-sm">
                                <span class="font-semibold">{{ $comment->name }}</span>
                                <time class="text-slate-500" datetime="{{ $comment->created_at->toIso8601String() }}"> · {{ $comment->created_at->format('M j, Y') }}</time>
                            </p>
                            <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $comment->body }}</p>

                            @foreach ($comment->replies as $reply)
                                <div class="mt-4 rounded-lg border-l-4 {{ $reply->is_staff ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/30' : 'border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-900' }} px-4 py-3">
                                    <p class="text-sm">
                                        <span class="font-semibold">{{ $reply->name }}</span>
                                        @if ($reply->is_staff)
                                            <span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">Team</span>
                                        @endif
                                        <time class="text-slate-500" datetime="{{ $reply->created_at->toIso8601String() }}"> · {{ $reply->created_at->format('M j, Y') }}</time>
                                    </p>
                                    <p class="mt-1.5 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $reply->body }}</p>
                                </div>
                            @endforeach
                        </li>
                    @endforeach
                </ol>
            @endif

            @if (session('comment'))
                <p class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200" role="status">{{ session('comment') }}</p>
            @else
                <form action="{{ route('docs.comments.store') }}" method="POST" class="mt-5 space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-900/60">
                    @csrf
                    <input type="hidden" name="page" value="{{ $page['slug'] }}">
                    <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <p class="text-sm font-medium">Ask a question or leave a note about this page</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="comment-name" class="field-label">Name</label>
                            <input id="comment-name" name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="name" class="field">
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="comment-email" class="field-label">Email <span class="font-normal text-slate-500">(private, for our reply)</span></label>
                            <input id="comment-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="comment-body" class="field-label">Comment</label>
                        <textarea id="comment-body" name="body" rows="4" required minlength="5" maxlength="3000" class="field" placeholder="What would you like to know?">{{ old('body') }}</textarea>
                        @error('body')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">Comments appear after a quick check. Need private help? <a href="{{ route('support.index') }}#ticket" class="underline">Open a ticket</a>.</p>
                        <button type="submit" class="btn-primary">Post comment</button>
                    </div>
                </form>
            @endif
        </section>

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
