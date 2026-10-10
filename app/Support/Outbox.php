<?php

namespace App\Support;

use App\Mail\PanelMessage;
use App\Models\OutboundMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends an email written in the admin panel and records it in the mail log,
 * whether it was delivered or not, so staff can see what went out.
 */
class Outbox
{
    public static function send(
        string $toEmail,
        string $subject,
        string $body,
        ?User $sender = null,
        ?string $toName = null,
        ?string $context = null,
        ?string $actionText = null,
        ?string $actionUrl = null,
    ): OutboundMail {
        $log = new OutboundMail([
            'user_id' => $sender?->id,
            'to_email' => $toEmail,
            'to_name' => $toName,
            'subject' => $subject,
            'body' => $body,
            'context' => $context,
        ]);

        try {
            Mail::to($toEmail, $toName)->send(new PanelMessage($subject, $body, $actionText, $actionUrl, $toName));
            $log->status = 'sent';
        } catch (Throwable $e) {
            report($e);
            $log->status = 'failed';
            $log->error = $e->getMessage();
        }

        $log->save();

        return $log;
    }
}
