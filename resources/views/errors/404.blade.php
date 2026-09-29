@extends('layouts.public')

@section('title', 'Page not found — dPanel')
@section('robots', 'noindex, follow')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
    <p class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">404</p>
    <h1 class="mt-3 text-4xl font-bold tracking-tight">Page not found</h1>
    <p class="mt-4 text-lg text-slate-600 dark:text-slate-400">The page you're looking for doesn't exist or has moved.</p>
    <form action="{{ route('docs.search') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-md gap-2">
        <input type="search" name="q" placeholder="Search the docs" aria-label="Search the docs" class="field">
        <button type="submit" class="btn-primary">Search</button>
    </form>
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="{{ route('home') }}" class="btn-secondary">Go home</a>
        <a href="{{ route('docs.index') }}" class="btn-secondary">Documentation</a>
        <a href="{{ route('support.index') }}" class="btn-secondary">Get help</a>
    </div>
</div>
@endsection
