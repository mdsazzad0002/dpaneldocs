<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('public.reviews', [
            'reviews' => Review::approved()->latest('approved_at')->paginate(12),
            'rating' => Review::summary(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:20', 'max:3000'],
            'website' => ['prohibited'],
        ]);

        Review::create($data + ['status' => 'pending', 'ip_address' => $request->ip()]);

        return redirect()
            ->to(route('reviews.index').'#write-review')
            ->with('status', 'Thank you! Your review was received and will appear once it has been checked.');
    }
}
