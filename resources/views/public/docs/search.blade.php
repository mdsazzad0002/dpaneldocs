@extends('layouts.public')

@section('title', ($query !== '' ? 'Search: '.$query : 'Search').' — dPanel Documentation')
@section('meta_description', 'Search the dPanel documentation.')
@section('canonical', route('docs.search'))
@section('robots', 'noindex, follow')

@section('content')
<div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[220px_minmax(0,1fr)] lg:px-8">
    <aside class="hidden lg:block">
        <div class="sticky top-24">
            @include('public.docs._sidebar', ['sections' => $sections])
        </div>
    </aside>

    <div class="min-w-0 max-w-3xl">
        <h1 class="text-3xl font-bold tracking-tight">Search the docs</h1>
        <form action="{{ route('docs.search') }}" method="GET" role="search" class="mt-6 flex gap-2">
            <label for="search-q" class="sr-only">Search</label>
            <input id="search-q" type="search" name="q" value="{{ $query }}" autofocus placeholder="What are you looking for?" class="field py-3" data-search-input>
            <button type="submit" class="btn-primary">Search</button>
        </form>

        @if ($query !== '')
            <p class="mt-6 text-sm text-slate-500">{{ count($results) }} {{ Str::plural('result', count($results)) }} for “{{ $query }}”</p>

            <ol class="mt-4 space-y-4">
                @forelse ($results as $result)
                    <li>
                        <a href="{{ route('docs.show', $result['slug']) }}{{ $result['anchor'] ? '#'.$result['anchor'] : '' }}" class="group block rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700">
                            <span class="text-xs font-medium text-slate-500">{{ $result['section'] }}{{ $result['heading'] ? ' › '.$result['heading'] : '' }}</span>
                            <span class="mt-1 block text-lg font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400">{{ $result['title'] }}</span>
                            <span class="mt-1.5 block text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $result['excerpt'] }}</span>
                        </a>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                        <p class="font-semibold">No pages match your search.</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Try different words, or <a href="{{ route('support.index') }}#ticket" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">ask our support team</a>.</p>
                    </li>
                @endforelse
            </ol>
        @endif
    </div>
</div>
@endsection
