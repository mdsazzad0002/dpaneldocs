<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageFeedback;
use App\Support\Docs;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(): Response
    {
        $titles = collect(Docs::all())->pluck('title', 'slug');

        $pages = PageFeedback::query()
            ->selectRaw('page, count(*) as total, sum(case when helpful then 1 else 0 end) as helpful')
            ->groupBy('page')
            ->get()
            ->map(fn ($row) => [
                'page' => $row->page,
                'title' => $titles[$row->page] ?? $row->page,
                'url' => $titles->has($row->page) ? route('docs.show', $row->page) : null,
                'total' => (int) $row->total,
                'helpful' => (int) $row->helpful,
                'score' => (int) round($row->helpful / max($row->total, 1) * 100),
            ])
            ->sortBy('score')
            ->values();

        return Inertia::render('Backend/Feedback/Index', [
            'pages' => $pages,
            'comments' => PageFeedback::whereNotNull('comment')->latest()->paginate(20)
                ->through(fn (PageFeedback $f) => $f->toArray() + ['title' => $titles[$f->page] ?? $f->page]),
        ]);
    }

    public function destroy(PageFeedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return back()->with('status', 'Feedback deleted.');
    }
}
