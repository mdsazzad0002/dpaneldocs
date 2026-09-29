<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
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

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
