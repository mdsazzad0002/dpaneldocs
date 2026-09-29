<figure class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" itemscope itemtype="https://schema.org/Review">
    <div class="flex items-center justify-between gap-3">
        @include('public.partials.stars', ['value' => $review->rating])
        <time class="text-xs text-slate-500" datetime="{{ ($review->approved_at ?? $review->created_at)->toDateString() }}" itemprop="datePublished">{{ ($review->approved_at ?? $review->created_at)->format('M j, Y') }}</time>
    </div>
    <span itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating" class="hidden">
        <meta itemprop="ratingValue" content="{{ $review->rating }}"><meta itemprop="bestRating" content="5">
    </span>
    <span itemprop="itemReviewed" itemscope itemtype="https://schema.org/SoftwareApplication" class="hidden">
        <meta itemprop="name" content="{{ config('site.name') }}"><meta itemprop="applicationCategory" content="DeveloperApplication"><meta itemprop="operatingSystem" content="Linux">
    </span>
    <h3 class="mt-4 font-semibold" itemprop="name">{{ $review->title }}</h3>
    <blockquote class="mt-2 flex-1 text-sm leading-6 text-slate-600 dark:text-slate-400" itemprop="reviewBody">{{ Str::limit($review->body, $limit ?? 320) }}</blockquote>
    <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800">
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 text-sm font-semibold text-white" aria-hidden="true">{{ Str::upper(Str::substr($review->name, 0, 1)) }}</span>
        <span class="text-sm">
            <span class="block font-medium" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name">{{ $review->name }}</span></span>
            @if ($review->company)
                <span class="block text-xs text-slate-500">{{ $review->company }}</span>
            @endif
        </span>
    </figcaption>
</figure>
