@extends('public.legal._page')

@section('title', 'Privacy Policy — dPanel')
@section('meta_description', 'How the dPanel website handles the information you send through support tickets, reviews, and page feedback.')
@section('canonical', route('privacy'))
@section('heading', 'Privacy Policy')
@section('updated', 'September 29, 2026')

@section('body')
<p>This policy explains what information the dPanel website ({{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'this site' }}) collects and how it is used. It is operated by <a href="{{ config('site.company.url') }}">{{ config('site.company.name') }}</a>. The dPanel software you install on your own server is covered by its own <a href="{{ config('site.repositories.panel') }}/blob/main/LICENSE">license</a>; this site never receives data from your installation.</p>

<h2>What we collect</h2>
<ul>
    <li><strong>Support tickets:</strong> your name, email address, and the details you write, including optional dPanel version and server OS.</li>
    <li><strong>Reviews:</strong> your name, email address, optional company or role, rating, and review text. Only your name, company or role, rating, and text are ever shown publicly.</li>
    <li><strong>Page feedback:</strong> whether a page was helpful and any optional comment.</li>
    <li><strong>Technical data:</strong> the IP address used to send a form, kept to prevent spam and abuse, and standard web server logs.</li>
</ul>
<p>We do not use advertising trackers and we do not sell your information.</p>

<h2>How we use it</h2>
<ul>
    <li>To answer your support request and email you about it.</li>
    <li>To moderate and publish reviews.</li>
    <li>To improve the documentation.</li>
    <li>To protect the site from spam and abuse.</li>
</ul>

<h2>Cookies</h2>
<p>The site uses a session cookie and a security (CSRF) cookie so forms work. Your light or dark theme choice is stored in your browser only.</p>

<h2>Keeping and deleting data</h2>
<p>Tickets are kept for as long as they are useful for support history. Email <a href="mailto:{{ config('site.support_email') }}">{{ config('site.support_email') }}</a> with your ticket reference or the email you used, and we will delete your tickets or reviews.</p>

<h2>Contact</h2>
<p>Questions about this policy: <a href="mailto:{{ config('site.support_email') }}">{{ config('site.support_email') }}</a>.</p>
@endsection
