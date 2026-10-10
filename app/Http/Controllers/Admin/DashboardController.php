<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\DocSync;
use App\Models\Donation;
use App\Models\PageFeedback;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Support\Donations;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $feedback = PageFeedback::query()->selectRaw('count(*) as total, sum(case when helpful then 1 else 0 end) as helpful')->first();
        $lastSync = DocSync::with('user:id,name')->latest()->first();

        return Inertia::render('Backend/Dashboard', [
            'stats' => [
                'open_tickets' => SupportTicket::where('status', 'open')->count(),
                'tickets_total' => SupportTicket::count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
                'rating' => Review::summary(),
                'feedback_total' => (int) $feedback->total,
                'feedback_helpful' => $feedback->total ? (int) round($feedback->helpful / $feedback->total * 100) : null,
                'pending_donations' => Donation::where('status', 'pending')->count(),
                'goal' => Donations::goal(),
            ],
            'lastSync' => $lastSync,
            'recentTickets' => $user->hasPermission('tickets.manage')
                ? SupportTicket::latest('last_activity_at')->limit(6)->get(['id', 'reference', 'name', 'subject', 'status', 'priority', 'last_activity_at'])
                : [],
            'pendingReviews' => $user->hasPermission('reviews.manage')
                ? Review::where('status', 'pending')->latest()->limit(4)->get(['id', 'name', 'rating', 'title', 'created_at'])
                : [],
            'pendingComments' => $user->hasPermission('comments.manage')
                ? Comment::where('status', 'pending')->latest()->limit(4)->get(['id', 'page', 'name', 'body', 'created_at'])
                : [],
        ]);
    }
}
