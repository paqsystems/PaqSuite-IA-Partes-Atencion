<?php

namespace Tests\Unit;

use App\Notifications\ResetPasswordNotification;
use Tests\TestCase;

final class ResetPasswordNotificationTest extends TestCase
{
    public function test_mail_en_espanol_no_expone_claves_i18n(): void
    {
        config()->set('app.frontend_url', 'https://paq.partesatencion.paqsystems.com');
        config()->set('app.frontend_spa_url', '');
        config()->set('auth.passwords.users.expire', 60);

        $mail = (new ResetPasswordNotification('abcToken', 'es', 'PAQ'))->toMail((object) []);

        $this->assertSame('Restablecer contraseña', $mail->subject);
        $this->assertSame('¡Hola!', $mail->greeting);
        $this->assertSame('Restablecer contraseña', $mail->actionText);
        $this->assertStringNotContainsString('auth.resetPassword', (string) $mail->subject);
        $this->assertStringContainsString(
            'https://partesatencionpaqsystems.vercel.app/reset-password?',
            (string) $mail->actionUrl
        );
        $this->assertStringContainsString('token=abcToken', (string) $mail->actionUrl);
        $this->assertStringContainsString('cliente=PAQ', (string) $mail->actionUrl);
        $this->assertNotEmpty($mail->introLines);
        $this->assertStringContainsString('restablecer', strtolower($mail->introLines[0]));
    }
}
