<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationMethod;
use App\Support\Donations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonateController extends Controller
{
    public function index(): View
    {
        return $this->page();
    }

    /**
     * The donate page with one of the suggested PCs from config/site.php selected.
     */
    public function pc(string $slug): View
    {
        abort_unless(is_array(config("site.pcs.{$slug}")), 404);

        return $this->page($slug);
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
            'pc' => ['nullable', Rule::in(array_keys(config('site.pcs')))],
            'website' => ['prohibited'],
        ]);

        $pc = Arr::pull($data, 'pc');

        if ($pc) {
            $data['message'] = trim('For: '.config("site.pcs.{$pc}.name")."\n".($data['message'] ?? ''));
        }

        Donation::create($data + [
            'currency' => Donations::setting('donation.currency'),
            'is_public' => $request->boolean('is_public'),
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->to(($pc ? route('donate.pc', $pc) : route('donate.index')).'#report')
            ->with('status', 'Thank you! We will check the transfer and add it to the goal shortly.');
    }

    private function page(?string $selectedPc = null): View
    {
        $goal = Donations::goal();

        abort_unless($goal['enabled'], 404);

        $accounts = config('site.donation_accounts');
        $staticNumbers = collect($accounts)->pluck('details.Account number')->filter()->all();

        return view('public.donate', [
            'goal' => $goal,
            'pcs' => config('site.pcs'),
            'selectedPc' => $selectedPc,
            'pc' => $selectedPc ? config("site.pcs.{$selectedPc}") : null,
            'accounts' => $accounts,
            'methods' => DonationMethod::active()
                ->where(fn ($query) => $query->whereNull('account_number')->orWhereNotIn('account_number', $staticNumbers))
                ->get(),
            'reportMethods' => DonationMethod::active()->get(),
            'supporters' => Donation::verified()->where('is_public', true)->latest('verified_at')->limit(12)->get(),
        ]);
    }
}
