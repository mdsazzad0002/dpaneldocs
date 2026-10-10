<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\Outbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $status = in_array($request->query('status'), Review::STATUSES, true) ? $request->query('status') : 'pending';

        return Inertia::render('Backend/Reviews/Index', [
            'reviews' => Review::where('status', $status)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Review $review) => $review->makeVisible(['email', 'ip_address'])),
            'status' => $status,
            'counts' => Review::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'rating' => Review::summary(),
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Review::STATUSES)],
        ]);

        $review->update([
            'status' => $data['status'],
            'approved_at' => $data['status'] === 'approved' ? ($review->approved_at ?? now()) : null,
        ]);

        return back()->with('status', 'Review '.$data['status'].'.');
    }

    /**
     * Publish (or clear) the team's public response to a review, and
     * optionally email it to the reviewer.
     */
    public function reply(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'reply' => ['nullable', 'string', 'max:3000'],
            'notify' => ['boolean'],
        ]);

        $reply = trim((string) ($data['reply'] ?? ''));

        $review->update([
            'reply' => $reply !== '' ? $reply : null,
            'replied_at' => $reply !== '' ? now() : null,
        ]);

        if ($reply === '') {
            return back()->with('status', 'Response removed.');
        }

        if ($request->boolean('notify')) {
            $mail = Outbox::send(
                $review->email,
                'We replied to your review of '.config('site.name'),
                $reply,
                $request->user(),
                $review->name,
                'review:'.$review->id,
                $review->status === 'approved' ? 'See it on the reviews page' : null,
                $review->status === 'approved' ? route('reviews.index') : null,
            );

            return back()->with('status', $mail->status === 'sent'
                ? 'Response saved and emailed to '.$review->email.'.'
                : 'Response saved, but the email could not be sent. Check the mail settings.');
        }

        return back()->with('status', 'Response saved.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
