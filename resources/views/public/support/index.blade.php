@extends('layouts.public')

@section('title', 'dPanel Help Center — Support, FAQ & Tickets')
@section('meta_description', 'Get help with dPanel: browse guides and FAQs, ask the community on GitHub, or open a support ticket for installation, migration, and troubleshooting.')
@section('canonical', route('support.index'))

@php $faq = config('site.faq'); @endphp

@push('jsonld')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faq)->map(fn ($item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => 'dPanel Help Center',
    'url' => route('support.index'),
    'mainEntity' => [
        '@type' => 'Organization',
        'name' => config('site.company.name'),
        'url' => config('site.company.url'),
        'contactPoint' => [[
            '@type' => 'ContactPoint',
            'contactType' => 'technical support',
            'email' => config('site.support_email'),
            'url' => route('support.index'),
            'availableLanguage' => ['English', 'Bengali'],
        ]],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Help center</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight">How can we help?</h1>
        <p class="mx-auto mt-3 max-w-2xl text-lg text-slate-600 dark:text-slate-400">Search the guides, check the FAQ, or talk to a person.</p>
        <form action="{{ route('docs.search') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-xl gap-2">
            <label for="help-search" class="sr-only">Search the documentation</label>
            <input id="help-search" type="search" name="q" placeholder="Describe your problem, e.g. “permission denied”" class="field py-3">
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Documentation', 'Step-by-step guides for install, operations, and the CLI.', route('docs.index'), 'Browse guides', false],
            ['Community', 'Ask questions and report bugs in the public issue tracker.', config('site.repositories.panel').'/issues', 'Open GitHub issues', true],
            ['Support ticket', 'Private help from our team, answered by email.', '#ticket', 'Open a ticket', false],
            ['Security', 'Found a vulnerability? Report it privately — never in public.', route('docs.show', 'security'), 'Read the policy', false],
        ] as [$name, $text, $href, $cta, $external])
            <a href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @endif class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-700">
                <h2 class="font-semibold">{{ $name }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $text }}</p>
                <span class="mt-4 inline-flex text-sm font-semibold text-blue-600 group-hover:underline dark:text-blue-400">{{ $cta }} &rarr;</span>
            </a>
        @endforeach
    </div>

    <div class="mt-16 grid gap-12 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Frequently asked questions</h2>
            <div class="mt-6 divide-y divide-slate-200 rounded-2xl border border-slate-200 dark:divide-slate-800 dark:border-slate-800">
                @foreach ($faq as $item)
                    <details class="group px-5 py-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-medium">
                            {{ $item['q'] }}
                            <span class="text-slate-400 transition group-open:rotate-45" aria-hidden="true">+</span>
                        </summary>
                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <h2 class="mt-12 text-lg font-bold">Popular guides</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($popular as $page)
                    <li><a href="{{ route('docs.show', $page['slug']) }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">{{ $page['title'] }}</a> <span class="text-slate-500">— {{ $page['description'] }}</span></li>
                @endforeach
            </ul>
        </div>

        <section id="ticket" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            <h2 class="text-2xl font-bold tracking-tight">Open a support ticket</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                We reply by email, usually within one business day. You'll get a private link to follow the conversation — no account needed.
            </p>

            <form action="{{ route('support.tickets.store') }}" method="POST" class="mt-8 space-y-5">
                @csrf
                <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="t-name" class="field-label">Name</label>
                        <input id="t-name" name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="name" class="field">
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="t-email" class="field-label">Email</label>
                        <input id="t-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field">
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="t-category" class="field-label">Topic</label>
                        <select id="t-category" name="category" required class="field">
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', 'installation') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="t-priority" class="field-label">Urgency</label>
                        <select id="t-priority" name="priority" required class="field">
                            @foreach ($priorities as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="t-subject" class="field-label">Subject</label>
                    <input id="t-subject" name="subject" value="{{ old('subject') }}" required maxlength="160" placeholder="A short summary of the problem" class="field">
                    @error('subject')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="t-message" class="field-label">Details</label>
                    <textarea id="t-message" name="message" rows="7" required minlength="20" maxlength="10000" class="field" placeholder="What did you try, what did you expect, and what happened instead? Paste any error messages (remove passwords and secrets).">{{ old('message') }}</textarea>
                    @error('message')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="t-version" class="field-label">dPanel version <span class="font-normal text-slate-500">(optional)</span></label>
                        <input id="t-version" name="dpanel_version" value="{{ old('dpanel_version') }}" maxlength="40" placeholder="e.g. v1.2.3" class="field">
                        @error('dpanel_version')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="t-os" class="field-label">Server OS <span class="font-normal text-slate-500">(optional)</span></label>
                        <input id="t-os" name="server_os" value="{{ old('server_os') }}" maxlength="80" placeholder="e.g. Ubuntu 24.04" class="field">
                        @error('server_os')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                    Never send passwords, API tokens, or private keys. For security vulnerabilities, follow the <a href="{{ route('docs.show', 'security') }}" class="font-semibold underline">Security Policy</a>.
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <p class="text-xs text-slate-500">See our <a href="{{ route('privacy') }}" class="underline">Privacy Policy</a>.</p>
                    <button type="submit" class="btn-primary">Submit ticket</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
