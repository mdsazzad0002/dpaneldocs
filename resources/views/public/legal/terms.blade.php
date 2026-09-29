@extends('public.legal._page')

@section('title', 'Terms of Use — dPanel')
@section('meta_description', 'Terms for using the dPanel website, documentation, reviews, and help desk.')
@section('canonical', route('terms'))
@section('heading', 'Terms of Use')
@section('updated', 'September 29, 2026')

@section('body')
<p>These terms cover your use of the dPanel website, documentation, and help desk, operated by <a href="{{ config('site.company.url') }}">{{ config('site.company.name') }}</a>. Using the dPanel software itself is governed by the <a href="{{ config('site.repositories.panel') }}/blob/main/LICENSE">dPanel Free Use License</a>.</p>

<h2>Documentation</h2>
<p>The documentation is provided free of charge and "as is". Server administration carries risk: back up your data and test changes before applying them to production systems. We are not liable for loss or damage caused by following the documentation.</p>

<h2>Support</h2>
<p>Free help is offered on a best-effort basis with no guaranteed response time. Paid expert assistance, where agreed, covers human work such as installation, migration, troubleshooting, and managed operations; its terms are confirmed with you before any work starts. Never send passwords, private keys, or other secrets in a ticket.</p>

<h2>Reviews</h2>
<ul>
    <li>Reviews must reflect your own genuine experience with dPanel.</li>
    <li>No spam, advertising, offensive content, or personal information about others.</li>
    <li>We may decline or remove reviews that break these rules. We do not edit the meaning of a review.</li>
    <li>By submitting a review you allow us to display it on this site.</li>
</ul>

<h2>Acceptable use</h2>
<p>Do not attempt to disrupt the site, send automated submissions, or access other people's tickets. Report security issues privately as described in the <a href="{{ route('docs.show', 'security') }}">Security Policy</a>.</p>

<h2>Changes</h2>
<p>We may update these terms. The date at the top shows the latest version.</p>

<h2>Contact</h2>
<p><a href="mailto:{{ config('site.support_email') }}">{{ config('site.support_email') }}</a></p>
@endsection
