<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Setting;
use App\Support\Donations;
use App\Support\Outbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    public function index(Request $request): Response
    {
        $status = in_array($request->query('status'), Donation::STATUSES, true) ? $request->query('status') : 'pending';

        return Inertia::render('Backend/Donations/Index', [
            'donations' => Donation::with('method:id,label')
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Donation $donation) => $donation->makeVisible(['email', 'ip_address', 'transaction_id'])),
            'status' => $status,
            'counts' => Donation::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'goal' => Donations::goal(),
            'settings' => collect(Donations::DEFAULTS)->mapWithKeys(fn ($default, $key) => [str_replace('donation.', '', $key) => Donations::setting($key)]),
        ]);
    }

    public function update(Request $request, Donation $donation): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Donation::STATUSES)],
            'notify' => ['boolean'],
        ]);

        $donation->update([
            'status' => $data['status'],
            'verified_at' => $data['status'] === 'verified' ? ($donation->verified_at ?? now()) : null,
        ]);

        if ($data['status'] === 'verified' && $request->boolean('notify') && $donation->email) {
            $mail = Outbox::send(
                $donation->email,
                'Thank you for supporting '.config('site.name'),
                Donations::setting('donation.thank_you'),
                $request->user(),
                $donation->name,
                'donation:'.$donation->id,
                'See the goal',
                route('donate.index'),
            );

            return back()->with('status', $mail->status === 'sent'
                ? 'Donation verified and a thank-you email was sent.'
                : 'Donation verified, but the thank-you email could not be sent.');
        }

        return back()->with('status', 'Donation marked '.$data['status'].'.');
    }

    public function destroy(Donation $donation): RedirectResponse
    {
        $donation->delete();

        return back()->with('status', 'Donation deleted.');
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['boolean'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:3000'],
            'goal_amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'raised_offset' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'thank_you' => ['nullable', 'string', 'max:3000'],
        ]);

        Setting::put([
            'donation.enabled' => $request->boolean('enabled'),
            'donation.title' => $data['title'],
            'donation.description' => $data['description'] ?? '',
            'donation.goal_amount' => $data['goal_amount'],
            'donation.currency' => strtoupper($data['currency']),
            'donation.raised_offset' => $data['raised_offset'] ?? 0,
            'donation.thank_you' => $data['thank_you'] ?? '',
        ]);

        return back()->with('status', 'Donation page updated.');
    }
}
