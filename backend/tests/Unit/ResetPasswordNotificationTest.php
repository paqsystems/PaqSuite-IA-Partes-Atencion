<?php

namespace Tests\Unit;

use App\Notifications\ResetPasswordNotification;
use PaqSuite\LaravelCore\Auth\InMemoryParametroStore;
use PaqSuite\LaravelCore\Auth\ParametroStore;
use Tests\TestCase;

final class ResetPasswordNotificationTest extends TestCase
{
    public function test_mail_en_espanol_no_expone_claves_i18n(): void
    {
        config()->set('app.frontend_url', 'https://paq.partesatencion.paqsystems.com');
        config()->set('app.frontend_spa_url', '');
        config()->set('auth.passwords.users.expire', 60);

        $store = new InMemoryParametroStore();
        $store->insertIfAbsent('PasswordResetTtlMin', 'I', 10, []);
        $this->app->instance(ParametroStore::class, $store);

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
        $this->assertNotEmpty($mail->outroLines);
        $this->assertStringContainsString('caduca en 10 minutos', $mail->outroLines[0]);
        $this->assertStringNotContainsString('60', $mail->outroLines[0]);
    }

    public function test_mail_usa_ttl_del_diccionario_no_config_laravel(): void
    {
        config()->set('app.frontend_url', 'https://paq.partesatencion.paqsystems.com');
        config()->set('auth.passwords.users.expire', 60);

        $store = new InMemoryParametroStore();
        $store->insertIfAbsent('PasswordResetTtlMin', 'I', 7, []);
        $this->app->instance(ParametroStore::class, $store);

        $mail = (new ResetPasswordNotification('abcToken', 'es', 'PAQ'))->toMail((object) []);

        $this->assertStringContainsString('caduca en 7 minutos', $mail->outroLines[0]);
    }
}
