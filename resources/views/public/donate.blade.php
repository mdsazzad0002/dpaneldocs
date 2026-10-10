@extends('layouts.public')

@section('title', $goal['title'].' — Support '.config('site.name'))
@section('meta_description', 'Support '.config('site.name').', the free self-hosted hosting control panel. Donate by bank transfer or mobile banking and help fund a new development machine.')
@section('canonical', route('donate.index'))

@php
    $money = fn ($amount) => \App\Support\Donations::format((float) $amount, $goal['currency']);
@endphp

@section('content')
<section class="border-b border-slate-200 bg-gradient-to-b from-amber-50 to-white dark:border-slate-800 dark:from-amber-950/20 dark:to-slate-950">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_400px] lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Support {{ config('site.name') }}</p>
            <h1 class="mt-2 flex items-center gap-3 text-4xl font-bold tracking-tight">
                <svg class="h-10 w-10 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/></svg>
                {{ $goal['title'] }}
            </h1>
            <p class="mt-4 max-w-2xl whitespace-pre-line text-lg leading-8 text-slate-600 dark:text-slate-400">{{ $goal['description'] }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#methods" class="btn-primary">How to donate</a>
                <a href="#report" class="btn-secondary">I have sent a donation</a>
            </div>
        </div>

        <div class="self-start rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500">Raised so far</p>
            <p class="mt-1 text-4xl font-bold tracking-tight">{{ $money($goal['raised']) }}</p>
            <p class="mt-1 text-sm text-slate-500">of {{ $money($goal['amount']) }} goal</p>
            <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" role="progressbar" aria-valuenow="{{ $goal['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Goal progress">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500" style="width: {{ $goal['percent'] }}%"></div>
            </div>
            <div class="mt-3 flex justify-between text-sm">
                <span class="font-semibold">{{ $goal['percent'] }}%</span>
                <span class="text-slate-500">{{ $goal['supporters'] }} {{ Str::plural('supporter', $goal['supporters']) }}</span>
            </div>
        </div>
    </div>
</section>

<div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <section id="methods" class="scroll-mt-24">
        <h2 class="text-2xl font-bold tracking-tight">Ways to send a donation</h2>
        <p class="mt-2 text-slate-600 dark:text-slate-400">Send any amount using one of the accounts below, then <a href="#report" class="font-medium text-blue-600 underline dark:text-blue-400">tell us about it</a> so we can thank you and add it to the goal.</p>

        @if ($methods->isEmpty())
            <p class="mt-6 rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">Payment details will be published here soon. Meanwhile, email <a href="mailto:{{ config('site.support_email') }}" class="underline">{{ config('site.support_email') }}</a>.</p>
        @else
            <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($methods as $method)
                    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold">{{ $method->label }}</h3>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ \App\Models\DonationMethod::TYPES[$method->type] ?? $method->type }}</span>
                        </div>
                        <dl class="mt-4 space-y-2.5 text-sm">
                            @foreach ([
                                'bank_name' => 'Bank',
                                'account_name' => 'Account name',
                                'account_number' => $method->type === 'mobile' ? 'Number' : 'Account number',
                                'branch' => 'Branch',
                                'routing_number' => 'Routing number',
                                'swift_code' => 'SWIFT / BIC',
                            ] as $field => $label)
                                @if ($method->{$field})
                                    <div class="flex items-start justify-between gap-3">
                                        <dt class="text-slate-500">{{ $label }}</dt>
                                        <dd class="flex items-center gap-2 text-right font-medium">
                                            <span class="{{ in_array($field, ['account_number', 'routing_number', 'swift_code']) ? 'font-mono' : '' }}">{{ $method->{$field} }}</span>
                                            @if (in_array($field, ['account_number', 'routing_number', 'swift_code']))
                                                <button type="button" data-copy-text="{{ $method->{$field} }}" class="rounded border border-slate-200 px-1.5 py-0.5 text-[11px] font-medium text-slate-500 hover:border-blue-400 hover:text-blue-600 dark:border-slate-700" aria-label="Copy {{ $label }}">Copy</button>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                        @if ($method->instructions)
                            <p class="mt-4 whitespace-pre-line border-t border-slate-100 pt-4 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:text-slate-400">{{ $method->instructions }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div class="mt-16 grid gap-10 lg:grid-cols-[1fr_380px]">
        <section id="report" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-10">
            <h2 class="text-2xl font-bold tracking-tight">I have sent a donation</h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Tell us what you sent so we can match the transfer. It counts towards the goal once we have checked it.</p>

            <form action="{{ route('donate.store') }}" method="POST" class="mt-8 space-y-6">
                @csrf
                <div class="hidden" aria-hidden="true"><label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="donor-name" class="field-label">Your name</label>
                        <input id="donor-name" name="name" value="{{ old('name') }}" required maxlength="80" autocomplete="name" class="field">
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="donor-email" class="field-label">Email <span class="font-normal text-slate-500">(optional, for a thank-you)</span></label>
                        <input id="donor-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="field">
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="donor-amount" class="field-label">Amount ({{ $goal['currency'] }})</label>
                        <input id="donor-amount" type="number" name="amount" value="{{ old('amount') }}" required min="1" step="0.01" class="field">
                        @error('amount')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="donor-method" class="field-label">Sent with</label>
                        <select id="donor-method" name="donation_method_id" class="field">
                            <option value="">Other / not listed</option>
                            @foreach ($methods as $method)
                                <option value="{{ $method->id }}" @selected((string) old('donation_method_id') === (string) $method->id)>{{ $method->label }}</option>
                            @endforeach
                        </select>
                        @error('donation_method_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="donor-trx" class="field-label">Transaction ID or reference</label>
                    <input id="donor-trx" name="transaction_id" value="{{ old('transaction_id') }}" required maxlength="120" class="field font-mono" placeholder="e.g. TrxID 9A7B6C5D or bank reference">
                    @error('transaction_id')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="donor-message" class="field-label">Message <span class="font-normal text-slate-500">(optional)</span></label>
                    <textarea id="donor-message" name="message" rows="3" maxlength="1000" class="field" placeholder="Say hi, or tell us what you use dPanel for">{{ old('message') }}</textarea>
                    @error('message')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', '1') === '1') class="rounded border-slate-300 text-blue-600 dark:border-slate-700 dark:bg-slate-900">
                    Show my name and message on the supporters list (the amount is never shown)
                </label>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Send details</button>
                </div>
            </form>
        </section>

        <aside>
            <h2 class="text-lg font-semibold">Recent supporters</h2>
            @if ($supporters->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Be the first to support the new machine!</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($supporters as $supporter)
                        <li class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                            <p class="flex items-center gap-2 text-sm font-medium">
                                <span class="text-amber-500" aria-hidden="true">♥</span> {{ $supporter->name }}
                                <span class="ml-auto text-xs font-normal text-slate-500">{{ $supporter->verified_at?->format('M j, Y') }}</span>
                            </p>
                            @if ($supporter->message)
                                <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ Str::limit($supporter->message, 200) }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
</div>
@endsection
