<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationMethod;
use App\Support\Donations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonateController extends Controller
{
    public function index(): View
    {
        $goal = Donations::goal();

        abort_unless($goal['enabled'], 404);

        return view('public.donate', [
            'goal' => $goal,
            'methods' => DonationMethod::active()->get(),
            'supporters' => Donation::verified()->where('is_public', true)->latest('verified_at')->limit(12)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Donations::goal()['enabled'], 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'donation_method_id' => ['nullable', Rule::exists('donation_methods', 'id')->where('is_active', true)],
            'transaction_id' => ['required', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'website' => ['prohibited'],
        ]);

        Donation::create($data + [
            'currency' => Donations::setting('donation.currency'),
            'is_public' => $request->boolean('is_public'),
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->to(route('donate.index').'#report')
            ->with('status', 'Thank you! We will check the transfer and add it to the goal shortly.');
    }
}
