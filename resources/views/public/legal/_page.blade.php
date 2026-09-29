@extends('layouts.public')

@section('content')
<article class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
    <header class="border-b border-slate-200 pb-6 dark:border-slate-800">
        <h1 class="text-4xl font-bold tracking-tight">@yield('heading')</h1>
        <p class="mt-2 text-sm text-slate-500">Last updated: @yield('updated')</p>
    </header>
    <div class="doc-prose mt-8">
        @yield('body')
    </div>
</article>
@endsection
