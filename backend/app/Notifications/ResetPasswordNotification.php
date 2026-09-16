<?php

namespace App\Notifications;

use App\Services\Auth\PasswordResetLinkBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $mailLocale,
        private readonly ?string $cliente = null
    ) {
        $this->locale = $mailLocale;
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
        $resetUrl = app(PasswordResetLinkBuilder::class)->build(
            $this->token,
            $this->mailLocale,
            $this->cliente
        );
        $expireMin = max(1, (int) config('auth.passwords.users.expire', 60));

        return (new MailMessage)
            ->subject(__('auth.resetPasswordSubject', [], $this->mailLocale))
            ->greeting(__('auth.resetPasswordGreeting', [], $this->mailLocale))
            ->line(__('auth.resetPasswordLine', [], $this->mailLocale))
            ->action(
                __('auth.resetPasswordAction', [], $this->mailLocale),
                $resetUrl
            )
            ->line(__('auth.resetPasswordExpire', ['count' => $expireMin], $this->mailLocale))
            ->line(__('auth.resetPasswordIgnore', [], $this->mailLocale));
    }
}
