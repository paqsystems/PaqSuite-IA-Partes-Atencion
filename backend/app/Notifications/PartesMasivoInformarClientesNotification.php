<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PartesMasivoInformarClientesNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $mailLocale,
        private readonly string $empresaNombre,
        private readonly string $fechaDesde,
        private readonly string $fechaHasta,
        private readonly string $portalUrl,
        private readonly string $forgotUrl,
        private readonly string $codigoUsuario,
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

        return (new MailMessage)
            ->subject(__('partes_masivo.mailInformarClientes.subject', [
                'empresa' => $this->empresaNombre,
            ], $locale))
            ->greeting(__('partes_masivo.mailInformarClientes.greeting', [
                'name' => $notifiable->name ?? '',
            ], $locale))
            ->line(__('partes_masivo.mailInformarClientes.intro', [
                'empresa' => $this->empresaNombre,
                'desde' => $this->fechaDesde,
                'hasta' => $this->fechaHasta,
            ], $locale))
            ->line(__('partes_masivo.mailInformarClientes.review', [], $locale))
            ->action(__('partes_masivo.mailInformarClientes.actionPortal', [], $locale), $this->portalUrl)
            ->line(__('partes_masivo.mailInformarClientes.codigoUsuario', [
                'codigo' => $this->codigoUsuario,
            ], $locale))
            ->line(__('partes_masivo.mailInformarClientes.forgotHint', [], $locale))
            ->action(__('partes_masivo.mailInformarClientes.actionForgot', [], $locale), $this->forgotUrl);
    }
}
