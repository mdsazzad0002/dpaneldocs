<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Support\AiAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Returns an AI-written draft for a reply box in the panel. The draft is
 * only placed in the editor; staff review it before anything is sent.
 */
class AiController extends Controller
{
    private const PERMISSIONS = [
        'ticket' => 'tickets.manage',
        'review' => 'reviews.manage',
        'comment' => 'comments.manage',
        'mail' => 'mail.send',
    ];

    public function draft(Request $request, AiAssistant $ai): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::PERMISSIONS))],
            'id' => ['required_unless:type,mail', 'nullable', 'integer'],
            'guidance' => ['nullable', 'string', 'max:2000'],
            'draft' => ['nullable', 'string', 'max:10000'],
            'to' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:200'],
        ]);

        Gate::authorize(self::PERMISSIONS[$data['type']]);

        if (! $ai->isEnabled()) {
            return response()->json(['message' => 'The AI assistant is turned off or has no API key. Set it up in Settings.'], 422);
        }

        $guidance = $data['guidance'] ?? '';
        $draft = $data['draft'] ?? '';

        try {
            $text = match ($data['type']) {
                'ticket' => $ai->draftReply('support ticket', $this->ticketThread(SupportTicket::findOrFail($data['id'])), $guidance, $draft),
                'review' => $ai->draftReply('public review (the reply is shown publicly under the review)', $this->reviewText(Review::findOrFail($data['id'])), $guidance, $draft),
                'comment' => $ai->draftReply('comment on a documentation page (the reply is shown publicly under it)', $this->commentThread(Comment::findOrFail($data['id'])), $guidance, $draft),
                'mail' => $ai->composeEmail($data['to'] ?: 'a dPanel user', $data['subject'] ?: '(no subject yet)', $guidance ?: 'Improve the draft.', $draft),
            };
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['text' => $text]);
    }

    private function ticketThread(SupportTicket $ticket): string
    {
        $lines = [
            "Ticket {$ticket->reference} · Topic: {$ticket->categoryLabel()} · Priority: {$ticket->priority}",
            'dPanel version: '.($ticket->dpanel_version ?: 'not given').' · Server OS: '.($ticket->server_os ?: 'not given'),
            "Subject: {$ticket->subject}",
            '',
            "{$ticket->name} (customer) wrote:\n{$ticket->message}",
        ];

        foreach ($ticket->replies as $reply) {
            $lines[] = '';
            $lines[] = $reply->author_name.($reply->is_staff ? ' (support team)' : ' (customer)')." wrote:\n".$reply->body;
        }

        return implode("\n", $lines);
    }

    private function reviewText(Review $review): string
    {
        return "{$review->name}".($review->company ? " from {$review->company}" : '')." gave {$review->rating} out of 5 stars.\nTitle: {$review->title}\n\n{$review->body}";
    }

    private function commentThread(Comment $comment): string
    {
        $parent = $comment->parent ?? $comment;
        $lines = ["Comment on the documentation page \"{$parent->page}\" (".route('docs.show', $parent->page).')', '', "{$parent->name} wrote:\n{$parent->body}"];

        foreach ($parent->replies as $reply) {
            $lines[] = '';
            $lines[] = $reply->name.($reply->is_staff ? ' (team)' : '')." wrote:\n".$reply->body;
        }

        return implode("\n", $lines);
    }
}
