<x-mail::message>
@if ($recipientName)
Hi {{ $recipientName }},

@endif
{!! nl2br(e($body)) !!}

@if ($actionUrl)
<x-mail::button :url="$actionUrl">
{{ $actionText ?? 'Open' }}
</x-mail::button>
@endif

{{ config('site.name') }} team
</x-mail::message>
