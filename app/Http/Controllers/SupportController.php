<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Notifications\TicketCustomerNotification;
use App\Notifications\TicketStaffNotification;
use App\Support\Docs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        return view('public.support.index', [
            'categories' => SupportTicket::CATEGORIES,
            'priorities' => SupportTicket::PRIORITIES,
            'popular' => array_slice(Docs::all(), 0, 6),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'category' => ['required', Rule::in(array_keys(SupportTicket::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(SupportTicket::PRIORITIES))],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:20', 'max:10000'],
            'dpanel_version' => ['nullable', 'string', 'max:40'],
            'server_os' => ['nullable', 'string', 'max:80'],
            'website' => ['prohibited'],
        ]);

        $ticket = SupportTicket::create($data + ['status' => 'open', 'ip_address' => $request->ip()]);

        rescue(fn () => Notification::route('mail', $ticket->email)->notify(new TicketCustomerNotification($ticket)));
        rescue(fn () => Notification::route('mail', config('site.support_email'))->notify(new TicketStaffNotification($ticket)));

        return redirect()
            ->to($ticket->publicUrl())
            ->with('status', "Your ticket {$ticket->reference} was created. We've emailed you a copy of this private link.");
    }

    public function show(Request $request, string $reference): View
    {
        $ticket = $this->authorizedTicket($request, $reference);

        return view('public.support.ticket', [
            'ticket' => $ticket->load('replies'),
            'token' => $ticket->access_token,
        ]);
    }

    public function reply(Request $request, string $reference): RedirectResponse
    {
        $ticket = $this->authorizedTicket($request, $reference);

        abort_if($ticket->isClosed(), 403, 'This ticket is closed. Please open a new one.');

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'website' => ['prohibited'],
        ]);

        $reply = $ticket->replies()->create([
            'author_name' => $ticket->name,
            'is_staff' => false,
            'body' => $data['body'],
        ]);

        $ticket->update(['status' => 'open', 'last_activity_at' => now()]);

        rescue(fn () => Notification::route('mail', config('site.support_email'))->notify(new TicketStaffNotification($ticket, $reply)));

        return redirect()->to($ticket->publicUrl().'#reply')->with('status', 'Your reply was added.');
    }

    private function authorizedTicket(Request $request, string $reference): SupportTicket
    {
        $ticket = SupportTicket::where('reference', $reference)->first();

        abort_unless(
            $ticket && hash_equals($ticket->access_token, (string) $request->input('token', '')),
            404
        );

        return $ticket;
    }
}
