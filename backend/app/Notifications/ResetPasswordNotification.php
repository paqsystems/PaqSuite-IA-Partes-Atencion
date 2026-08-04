<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $mailLocale
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', ''), '/');
        $query = http_build_query([
            'token' => $this->token,
            'locale' => $this->mailLocale,
        ]);

        return (new MailMessage())
            ->subject(__('auth.resetPasswordSubject', [], $this->mailLocale))
            ->line(__('auth.resetPasswordLine', [], $this->mailLocale))
            ->action(
                __('auth.resetPasswordAction', [], $this->mailLocale),
                $frontendUrl.'/reset-password?'.$query
            );
    }
}
