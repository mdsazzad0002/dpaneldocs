<?php

namespace App\Http\Controllers;

use App\Models\PageFeedback;
use App\Support\Docs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'page' => ['required', 'string', Rule::in(array_column(Docs::all(), 'slug'))],
            'helpful' => ['required', 'boolean'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'website' => ['prohibited'],
        ]);

        PageFeedback::create($data + ['ip_address' => $request->ip()]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Thanks for the feedback!']);
        }

        return back()->with('feedback', 'Thanks for the feedback!');
    }
}
