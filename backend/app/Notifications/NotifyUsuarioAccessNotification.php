<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NotifyUsuarioAccessNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $mailLocale,
        public readonly string $empresaCodigo,
        public readonly string $portalUrl,
        public readonly string $usuario,
        public readonly string $plainPassword,
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
        $locale = $this->mailLocale;
        $suffix = (string) __('partes_notify_access.subjectSuffix', [], $locale);
        $subject = trim($this->empresaCodigo) === ''
            ? $suffix
            : $this->empresaCodigo.' - '.$suffix;

        return (new MailMessage)
            ->subject($subject)
            ->greeting(__('partes_notify_access.greeting', [
                'name' => $notifiable->name ?? '',
            ], $locale))
            ->line(__('partes_notify_access.bodyIntro', [], $locale))
            ->action(__('partes_notify_access.actionPortal', [], $locale), $this->portalUrl)
            ->line(__('partes_notify_access.bodyUsuario', [
                'usuario' => $this->usuario,
            ], $locale))
            ->line(__('partes_notify_access.bodyPassword', [
                'password' => $this->plainPassword,
            ], $locale))
            ->line(__('partes_notify_access.bodyFirstLogin', [], $locale));
    }
}
