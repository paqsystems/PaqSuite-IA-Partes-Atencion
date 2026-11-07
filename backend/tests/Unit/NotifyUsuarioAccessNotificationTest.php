<?php

namespace Tests\Unit;

use App\Notifications\NotifyUsuarioAccessNotification;
use Tests\TestCase;

final class NotifyUsuarioAccessNotificationTest extends TestCase
{
    public function test_asunto_incluye_codigo_empresa_y_cuerpo_lleva_usuario_y_clave(): void
    {
        $notification = new NotifyUsuarioAccessNotification(
            'es',
            'DEMO',
            'https://demo.partesatencion.paqsystems.com',
            'juan.perez',
            'ClaveInicial1!',
        );

        $mail = $notification->toMail((object) ['name' => 'Juan Perez']);

        $this->assertSame(
            'DEMO - ingreso a nuestro sistema de Partes de Atención',
            $mail->subject
        );
        $this->assertSame('https://demo.partesatencion.paqsystems.com', $mail->actionUrl);
        $rendered = implode("\n", array_merge($mail->introLines, $mail->outroLines));
        $this->assertStringContainsString('Usuario: juan.perez', $rendered);
        $this->assertStringContainsString('Contraseña inicial: ClaveInicial1!', $rendered);
    }
}
