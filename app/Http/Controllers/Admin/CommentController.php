<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\Docs;
use App\Support\Outbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = in_array($request->query('status'), Comment::STATUSES, true) ? $request->query('status') : 'pending';
        $titles = collect(Docs::all())->pluck('title', 'slug');

        $comments = Comment::query()
            ->whereNull('parent_id')
            ->where('status', $status)
            ->with('replies')
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Comment $comment) => $comment->makeVisible(['email', 'ip_address'])->toArray() + [
                'title' => $titles[$comment->page] ?? $comment->page,
                'url' => $comment->url(),
            ]);

        return Inertia::render('Backend/Comments/Index', [
            'comments' => $comments,
            'status' => $status,
            'counts' => Comment::whereNull('parent_id')->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Comment::STATUSES)],
        ]);

        $comment->update($data);

        return back()->with('status', 'Comment marked '.$data['status'].'.');
    }

    /**
     * Post a staff reply under the comment (publishing the comment too) and
     * optionally email it to the person who asked.
     */
    public function reply(Request $request, Comment $comment): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'notify' => ['boolean'],
        ]);

        $parent = $comment->parent ?? $comment;

        $reply = $parent->replies()->create([
            'page' => $parent->page,
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'body' => $data['body'],
            'status' => 'approved',
            'is_staff' => true,
        ]);

        if ($parent->status === 'pending') {
            $parent->update(['status' => 'approved']);
        }

        if ($request->boolean('notify') && $parent->email) {
            $title = collect(Docs::all())->firstWhere('slug', $parent->page)['title'] ?? $parent->page;

            $mail = Outbox::send(
                $parent->email,
                "We replied to your comment on \"{$title}\"",
                $reply->body,
                $request->user(),
                $parent->name,
                'comment:'.$parent->id,
                'View the conversation',
                $reply->url(),
            );

            if ($mail->status !== 'sent') {
                return back()->with('status', 'Reply posted, but the email could not be sent. Check the mail settings.');
            }

            return back()->with('status', 'Reply posted and emailed to '.$parent->email.'.');
        }

        return back()->with('status', 'Reply posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
