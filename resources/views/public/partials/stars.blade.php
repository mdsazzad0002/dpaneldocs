@php $size = $size ?? 'h-4 w-4'; @endphp
<span class="inline-flex items-center gap-0.5" role="img" aria-label="{{ number_format($value, 1) }} out of 5 stars">
    @for ($i = 1; $i <= 5; $i++)
        @php $fill = max(0, min(1, $value - $i + 1)) * 100; @endphp
        <svg class="{{ $size }}" viewBox="0 0 20 20" aria-hidden="true">
            <defs><linearGradient id="star-{{ $i }}-{{ $fill }}"><stop offset="{{ $fill }}%" stop-color="#fbbf24"/><stop offset="{{ $fill }}%" stop-color="currentColor"/></linearGradient></defs>
            <path class="text-slate-300 dark:text-slate-700" fill="url(#star-{{ $i }}-{{ $fill }})" d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.26 3.88a1 1 0 0 0 .95.69h4.08c.97 0 1.37 1.24.59 1.81l-3.3 2.4a1 1 0 0 0-.36 1.11l1.26 3.88c.3.92-.76 1.69-1.54 1.12l-3.3-2.4a1 1 0 0 0-1.18 0l-3.3 2.4c-.78.57-1.84-.2-1.54-1.12l1.26-3.88a1 1 0 0 0-.36-1.1l-3.3-2.4c-.78-.58-.38-1.82.59-1.82h4.08a1 1 0 0 0 .95-.69l1.26-3.88Z"/>
        </svg>
    @endfor
</span>
