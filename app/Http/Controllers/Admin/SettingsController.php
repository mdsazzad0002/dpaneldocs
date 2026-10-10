<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\AiAssistant;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SettingsController extends Controller
{
    public function edit(AiAssistant $ai): Response
    {
        return Inertia::render('Backend/Settings/Edit', [
            'mail' => [
                'enabled' => Setting::get('mail.enabled') === '1',
                'host' => Setting::get('mail.host', ''),
                'port' => Setting::get('mail.port', '587'),
                'encryption' => Setting::get('mail.encryption', 'tls'),
                'username' => Setting::get('mail.username', ''),
                'from_address' => Setting::get('mail.from_address', config('mail.from.address')),
                'from_name' => Setting::get('mail.from_name', config('mail.from.name')),
                'has_password' => Setting::has('mail.password'),
            ],
            'envMailer' => config('mail.default'),
            'ai' => [
                'enabled' => Setting::get('ai.enabled', '1') === '1',
                'model' => $ai->model(),
                'instructions' => Setting::get('ai.instructions', ''),
                'has_key' => Setting::has('ai.api_key'),
                'env_key' => filled(config('services.anthropic.key')),
            ],
            'models' => AiAssistant::MODELS,
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['boolean'],
            'host' => ['required_if:enabled,true', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:enabled,true', 'nullable', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:120'],
        ]);

        Setting::put([
            'mail.enabled' => $request->boolean('enabled'),
            'mail.host' => $data['host'] ?? '',
            'mail.port' => $data['port'] ?? '',
            'mail.encryption' => $data['encryption'],
            'mail.username' => $data['username'] ?? '',
            'mail.password' => $data['password'] ?? null,
            'mail.from_address' => $data['from_address'],
            'mail.from_name' => $data['from_name'],
        ]);

        return back()->with('status', 'Mail settings saved.');
    }

    public function testMail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to' => ['required', 'email', 'max:255'],
        ]);

        MailSettings::apply();
        Mail::purge();

        try {
            Mail::raw('This is a test email from the '.config('site.name').' admin panel. Your mail settings work.', function ($message) use ($data) {
                $message->to($data['to'])->subject('Test email from '.config('site.name'));
            });
        } catch (Throwable $e) {
            return back()->withErrors(['to' => 'Sending failed: '.$e->getMessage()]);
        }

        return back()->with('status', "Test email sent to {$data['to']} via ".config('mail.default').'.');
    }

    public function updateAi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['boolean'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'model' => ['required', Rule::in(array_keys(AiAssistant::MODELS))],
            'instructions' => ['nullable', 'string', 'max:4000'],
            'remove_key' => ['boolean'],
        ]);

        if ($request->boolean('remove_key')) {
            Setting::forget('ai.api_key');
        }

        Setting::put([
            'ai.enabled' => $request->boolean('enabled'),
            'ai.api_key' => $data['api_key'] ?? null,
            'ai.model' => $data['model'],
            'ai.instructions' => $data['instructions'] ?? '',
        ]);

        return back()->with('status', 'AI assistant settings saved.');
    }
}
