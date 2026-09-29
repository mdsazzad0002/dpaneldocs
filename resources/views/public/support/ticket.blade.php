@extends('layouts.public')

@section('title', 'Ticket '.$ticket->reference.' — dPanel Support')
@section('meta_description', 'Your dPanel support ticket.')
@section('robots', 'noindex, nofollow')
@section('canonical', route('support.index'))

@push('head')
    <meta name="referrer" content="no-referrer">
@endpush

@php
    $badge = [
        'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
        'answered' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'resolved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        'closed' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    ][$ticket->status] ?? 'bg-slate-200 text-slate-700';
    $statusText = [
        'open' => 'Waiting for our team',
        'answered' => 'We replied — waiting for you',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ][$ticket->status] ?? ucfirst($ticket->status);
@endphp

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('support.index') }}" class="text-sm text-slate-500 hover:text-slate-900 dark:hover:text-white">&larr; Help center</a>

    <header class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-mono text-sm font-semibold text-slate-500">{{ $ticket->reference }}</span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">{{ $statusText }}</span>
        </div>
        <h1 class="mt-3 text-2xl font-bold tracking-tight">{{ $ticket->subject }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ $ticket->categoryLabel() }} · Opened {{ $ticket->created_at->format('M j, Y \a\t H:i') }}
            @if ($ticket->dpanel_version) · dPanel {{ $ticket->dpanel_version }} @endif
            @if ($ticket->server_os) · {{ $ticket->server_os }} @endif
        </p>
        <p class="mt-4 rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:bg-slate-800/60 dark:text-slate-400">
            Bookmark this page to come back later. This link is private — anyone with it can read this ticket.
        </p>
    </header>

    <ol class="mt-8 space-y-5">
        <li class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm"><span class="font-semibold">{{ $ticket->name }}</span> <span class="text-slate-500">· {{ $ticket->created_at->diffForHumans() }}</span></p>
            <div class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $ticket->message }}</div>
        </li>
        @foreach ($ticket->replies as $reply)
            <li @class([
                'rounded-2xl border p-5',
                'border-blue-200 bg-blue-50/60 dark:border-blue-900 dark:bg-blue-950/30' => $reply->is_staff,
                'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' => ! $reply->is_staff,
            ])>
                <p class="text-sm">
                    <span class="font-semibold">{{ $reply->author_name }}</span>
                    @if ($reply->is_staff)<span class="ml-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-white">dPanel team</span>@endif
                    <span class="text-slate-500">· {{ $reply->created_at->diffForHumans() }}</span>
                </p>
                <div class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $reply->body }}</div>
            </li>
        @endforeach
    </ol>

    <section id="reply" class="mt-8 scroll-mt-24">
        @if ($ticket->isClosed())
            <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-600 dark:border-slate-700 dark:text-slate-400">
                This ticket is closed. Need more help? <a href="{{ route('support.index') }}#ticket" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">Open a new ticket</a>.
            </div>
        @else
            <form action="{{ route('support.tickets.reply', ['reference' => $ticket->reference, 'token' => $token]) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <label for="reply-body" class="field-label">Add a reply</label>
                <textarea id="reply-body" name="body" rows="5" required maxlength="10000" class="field" placeholder="Add more details or answer our questions…">{{ old('body') }}</textarea>
                @error('body')<p class="field-error">{{ $message }}</p>@enderror
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary">Send reply</button>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection
