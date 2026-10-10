<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DonationMethodController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        DonationMethod::create($this->validated($request));

        return back()->with('status', 'Payment method added.');
    }

    public function update(Request $request, DonationMethod $method): RedirectResponse
    {
        $method->update($this->validated($request));

        return back()->with('status', 'Payment method updated.');
    }

    public function destroy(DonationMethod $method): RedirectResponse
    {
        $method->delete();

        return back()->with('status', 'Payment method removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(DonationMethod::TYPES))],
            'label' => ['required', 'string', 'max:80'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'branch' => ['nullable', 'string', 'max:120'],
            'routing_number' => ['nullable', 'string', 'max:40'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        return ['is_active' => $request->boolean('is_active'), 'sort_order' => (int) ($data['sort_order'] ?? 0)] + $data;
    }
}
