@php $activeSlug = $activeSlug ?? null; @endphp
<div class="sticky top-24 space-y-6 pr-2 text-sm">
    <a href="{{ route('docs.public.index') }}" class="block font-semibold text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">
        All Documentation
    </a>
    @foreach ($sidebar as $category)
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                {{ $category['name'] }}
            </p>
            <ul class="space-y-1 border-l border-slate-200 dark:border-slate-800">
                @foreach ($category['posts'] as $doc)
                    <li>
                        <a
                            href="{{ route('docs.public.show', ['slug' => $doc['slug']]) }}"
                            class="-ml-px block border-l-2 py-1 pl-3 transition {{ $doc['slug'] === $activeSlug ? 'border-blue-600 font-medium text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:text-slate-400 dark:hover:border-slate-700 dark:hover:text-slate-100' }}"
                        >
                            {{ $doc['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
