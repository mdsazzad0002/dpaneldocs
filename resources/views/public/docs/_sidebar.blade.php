@php $active = $active ?? null; @endphp
<nav aria-label="Documentation" class="space-y-7 text-sm">
    @foreach ($sections as $section => $pages)
        <div>
            <p class="mb-2.5 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $section }}</p>
            <ul class="space-y-0.5 border-l border-slate-200 dark:border-slate-800">
                @foreach ($pages as $item)
                    <li>
                        <a href="{{ route('docs.show', $item['slug']) }}" @class([
                            '-ml-px block border-l py-1.5 pl-4 transition',
                            'border-blue-600 font-medium text-blue-600 dark:border-blue-400 dark:text-blue-400' => $item['slug'] === $active,
                            'border-transparent text-slate-600 hover:border-slate-400 hover:text-slate-900 dark:text-slate-400 dark:hover:border-slate-500 dark:hover:text-white' => $item['slug'] !== $active,
                        ]) @if ($item['slug'] === $active) aria-current="page" @endif>{{ $item['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
        <p class="font-semibold">Need a hand?</p>
        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400">Our team can help with installs, migrations, and troubleshooting.</p>
        <a href="{{ route('support.index') }}#ticket" class="mt-3 inline-flex text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400">Open a ticket &rarr;</a>
    </div>
</nav>
