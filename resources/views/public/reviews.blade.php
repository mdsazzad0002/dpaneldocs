@extends('layouts.public')

@section('title', 'dPanel Reviews — Ratings from Real Users')
@section('meta_description', $rating['count']
    ? 'dPanel is rated '.number_format($rating['average'], 1).' out of 5 from '.$rating['count'].' reviews. Read what server admins and hosting providers say, and share your own experience.'
    : 'Read what server admins and hosting providers say about dPanel, the free self-hosted hosting control panel, and share your own experience.')
@section('canonical', $reviews->currentPage() > 1 ? $reviews->url($reviews->currentPage()) : route('reviews.index'))

@if ($rating['count'] > 0)
@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => config('site.name'),
    'url' => route('home'),
    'applicationCategory' => 'DeveloperApplication',
    'operatingSystem' => 'Linux',
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => $rating['average'],
        'ratingCount' => $rating['count'],
        'bestRating' => 5,
        'worstRating' => 1,
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@endif

@section('content')
<section class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/40">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_380px] lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Reviews</p>
            <h1 class="mt-2 text-4xl font-bold tracking-tight">What people say about dPanel</h1>
            <p class="mt-3 max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                Honest feedback from people running dPanel on their own servers. Every review is read by a person before it is published.
            </p>
            <a href="#write-review" class="btn-primary mt-8">Write a review</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @if ($rating['count'] > 0)
                <div class="flex items-center gap-4">
                    <p class="text-5xl font-bold tracking-tight">{{ number_format($rating['average'], 1) }}</p>
                    <div>
                        @include('public.partials.stars', ['value' => $rating['average'], 'size' => 'h-5 w-5'])
                        <p class="mt-1 text-sm text-slate-500">Based on {{ $rating['count'] }} {{ Str::plural('review', $rating['count']) }}</p>
                    </div>
                </div>
                <dl class="mt-6 space-y-2">
                    @foreach ($rating['breakdown'] as $star => $count)
                        <div class="flex items-center gap-3 text-sm">
                            <dt class="w-12 shrink-0 text-slate-600 dark:text-slate-400">{{ $star }} star</dt>
                            <dd class="flex flex-1 items-center gap-3">
                                <span class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <span class="block h-full rounded-full bg-amber-400" style="width: {{ $rating['count'] ? round($count / $rating['count'] * 100) : 0 }}%"></span>
                                </span>
                                <span class="w-8 text-right text-slate-500">{{ $count }}</span>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="text-lg font-semibold">No reviews yet</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Be the first to tell others how dPanel works for you.</p>
            @endif
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    @if ($reviews->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($reviews as $review)
                @include('public.partials.review-card', ['review' => $review, 'limit' => 3000])
            @endforeach
        </div>
        <div class="mt-10">{{ $reviews->links() }}</div>
    @endif

    <section id="write-review" class="mx-auto mt-16 max-w-3xl scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-10">
        <h2 class="text-2xl font-bold tracking-tight">Share your experience</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Your email is never shown publicly. Reviews appear after a quick check for spam.</p>

        <form action="{{ route('reviews.store') }}" method="POST" class="mt-8 space-y-6">
            @csrf
            <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <fieldset>
                <legend class="field-label">Your rating</legend>
                <div class="flex items-center gap-1" data-rating>
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="relative">
                            <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked((int) old('rating', 5) === $i) required>
                            <span class="star peer-focus-visible:rounded peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500 {{ $i <= (int) old('rating', 5) ? 'is-on' : '' }}" aria-hidden="true">★</span>
                            <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                        </label>
                    @endfor
                </div>
                @error('rating')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="review-name" class="field-label">Name</label>
                    <input id="review-name" name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="name" class="field">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="review-email" class="field-label">Email <span class="font-normal text-slate-500">(private)</span></label>
                    <input id="review-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="review-company" class="field-label">Company or role <span class="font-normal text-slate-500">(optional)</span></label>
                <input id="review-company" name="company" value="{{ old('company') }}" maxlength="120" placeholder="e.g. Hosting provider, Freelance developer" class="field">
                @error('company')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="review-title" class="field-label">Title</label>
                <input id="review-title" name="title" value="{{ old('title') }}" required maxlength="120" placeholder="Sum it up in a few words" class="field">
                @error('title')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="review-body" class="field-label">Your review</label>
                <textarea id="review-body" name="body" rows="5" required minlength="20" maxlength="3000" placeholder="What do you use dPanel for? What works well, and what could be better?" class="field">{{ old('body') }}</textarea>
                @error('body')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-xs text-slate-500">By submitting you agree to our <a href="{{ route('terms') }}" class="underline">Terms</a> and <a href="{{ route('privacy') }}" class="underline">Privacy Policy</a>.</p>
                <button type="submit" class="btn-primary">Submit review</button>
            </div>
        </form>
    </section>
</div>
@endsection
