@extends('layouts.public')

@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('canonical', $canonical)
@section('og_type', 'website')
@section('json_ld')
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $seoTitle,
    'description' => $seoDescription,
    'url' => $canonical,
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => $posts->values()->map(fn ($post, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('docs.public.show', ['slug' => $post['slug']]),
            'name' => $post['title'],
        ])->all(),
    ],
]) !!}
@endsection

@section('content')
<div class="grid grid-cols-1 gap-8 lg:grid-cols-[240px_minmax(0,1fr)]">
    <aside class="hidden lg:block">
        @include('public.docs._sidebar', ['sidebar' => $sidebar])
    </aside>

    <div class="min-w-0">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $activeCategory ?? 'Documentation' }}</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400">
                @if ($query !== '')
                    Search results for "{{ $query }}".
                @else
                    Guides, release notes, and downloadable versions — free for everyone.
                @endif
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($posts as $post)
                <a
                    href="{{ route('docs.public.show', ['slug' => $post['slug']]) }}"
                    class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700"
                >
                    @if ($post['category'])
                        <span class="mb-2 inline-block rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            {{ $post['category'] }}
                        </span>
                    @endif
                    <h2 class="text-lg font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $post['title'] }}</h2>
                    @if ($post['excerpt'])
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $post['excerpt'] }}</p>
                    @endif
                    <div class="mt-3 flex items-center gap-3 text-xs text-slate-500">
                        <span>{{ $post['created_at'] }}</span>
                        <span>·</span>
                        <span>{{ $post['views'] }} views</span>
                    </div>
                </a>
            @endforeach

            @if ($posts->isEmpty())
                <p class="col-span-2 py-12 text-center text-slate-500">No documentation posts found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
