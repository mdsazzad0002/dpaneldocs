<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageFeedback;
use App\Models\Review;
use App\Models\SupportTicket;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $feedback = PageFeedback::query()->selectRaw('count(*) as total, sum(case when helpful then 1 else 0 end) as helpful')->first();

        return Inertia::render('Backend/Dashboard', [
            'stats' => [
                'open_tickets' => SupportTicket::where('status', 'open')->count(),
                'tickets_total' => SupportTicket::count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
                'rating' => Review::summary(),
                'feedback_total' => (int) $feedback->total,
                'feedback_helpful' => $feedback->total ? (int) round($feedback->helpful / $feedback->total * 100) : null,
            ],
            'recentTickets' => SupportTicket::latest('last_activity_at')->limit(6)
                ->get(['id', 'reference', 'name', 'subject', 'status', 'priority', 'last_activity_at']),
            'pendingReviews' => Review::where('status', 'pending')->latest()->limit(4)
                ->get(['id', 'name', 'rating', 'title', 'created_at']),
        ]);
    }
}
