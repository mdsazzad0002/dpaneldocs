<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboundMail;
use App\Support\Outbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MailController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('Backend/Mail/Index', [
            'mails' => OutboundMail::with('user:id,name')
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                    ->where('to_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'filters' => ['search' => $search],
            'prefill' => [
                'to_email' => (string) $request->query('to', ''),
                'to_name' => (string) $request->query('name', ''),
                'subject' => (string) $request->query('subject', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to_email' => ['required', 'email', 'max:255'],
            'to_name' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:20000'],
        ]);

        $mail = Outbox::send($data['to_email'], $data['subject'], $data['body'], $request->user(), $data['to_name'] ?? null, 'compose');

        return back()->with('status', $mail->status === 'sent'
            ? 'Email sent to '.$mail->to_email.'.'
            : 'The email could not be sent: '.$mail->error);
    }

    public function destroy(OutboundMail $mail): RedirectResponse
    {
        $mail->delete();

        return back()->with('status', 'Removed from the mail log.');
    }
}
