<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Notifications\TicketCustomerNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $tickets = SupportTicket::query()
            ->when(array_key_exists((string) $status, SupportTicket::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('reference', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->withCount('replies')
            ->latest('last_activity_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Backend/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => ['status' => $status, 'search' => $search],
            'statuses' => SupportTicket::STATUSES,
            'categories' => SupportTicket::CATEGORIES,
            'counts' => SupportTicket::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(SupportTicket $ticket): Response
    {
        return Inertia::render('Backend/Tickets/Show', [
            'ticket' => $ticket->load('replies')->makeVisible('ip_address'),
            'statuses' => SupportTicket::STATUSES,
            'categories' => SupportTicket::CATEGORIES,
            'priorities' => SupportTicket::PRIORITIES,
            'publicUrl' => $ticket->publicUrl(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::in(array_keys(SupportTicket::STATUSES))],
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'author_name' => $request->user()->name,
            'is_staff' => true,
            'body' => $data['body'],
        ]);

        $ticket->update(['status' => $data['status'], 'last_activity_at' => now()]);

        $sent = rescue(function () use ($ticket, $reply) {
            Notification::route('mail', $ticket->email)->notify(new TicketCustomerNotification($ticket, $reply));

            return true;
        }, false);

        return back()->with('status', $sent ? 'Reply sent to '.$ticket->email.'.' : 'Reply saved, but the email could not be sent. Check the mail settings.');
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(array_keys(SupportTicket::STATUSES))],
            'priority' => ['sometimes', Rule::in(array_keys(SupportTicket::PRIORITIES))],
            'category' => ['sometimes', Rule::in(array_keys(SupportTicket::CATEGORIES))],
        ]);

        $ticket->update($data);

        return back()->with('status', 'Ticket updated.');
    }

    public function destroy(SupportTicket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('status', "Ticket {$ticket->reference} deleted.");
    }
}
